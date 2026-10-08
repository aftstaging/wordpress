<?php
/**
 * Plugin Name: AFT Private Tutor LMS Policy
 * Description: Keeps Tutor LMS Free and Pro working together locally while disabling vendor update, licence and telemetry traffic. Internal updates can be supplied through a private JSON mirror.
 * Version: 1.0.0
 * Author: Accountants For Tomorrow
 *
 * This is an AFT mu-plugin. It intentionally does not modify Tutor LMS source
 * files, so the private policy survives controlled internal package updates.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Set this to an HTTPS JSON endpoint when the private update mirror is ready.
 * Expected shape:
 * {
 *   "tutor/tutor.php": {"new_version":"4.1.1","package":"https://.../tutor.zip","url":"https://..."},
 *   "tutor-pro/tutor-pro.php": {"new_version":"4.0.2","package":"https://.../tutor-pro.zip","url":"https://..."}
 * }
 */
if ( ! defined( 'AFT_TUTOR_PRIVATE_UPDATE_URL' ) ) {
	define( 'AFT_TUTOR_PRIVATE_UPDATE_URL', '' );
}

function aft_tutor_private_plugin_files() {
	return [
		'tutor/tutor.php',
		'tutor-pro/tutor-pro.php',
	];
}

function aft_tutor_private_is_plugin_file( $plugin ) {
	return in_array( (string) $plugin, aft_tutor_private_plugin_files(), true );
}

/** Remove Tutor Free/Pro from WordPress and automatic update queues. */
function aft_tutor_private_remove_vendor_updates( $transient ) {
	if ( ! is_object( $transient ) ) {
		return $transient;
	}

	foreach ( aft_tutor_private_plugin_files() as $plugin ) {
		if ( isset( $transient->response[ $plugin ] ) ) {
			unset( $transient->response[ $plugin ] );
		}
		if ( isset( $transient->no_update[ $plugin ] ) ) {
			unset( $transient->no_update[ $plugin ] );
		}
	}

	return aft_tutor_private_add_internal_updates( $transient );
}
add_filter( 'site_transient_update_plugins', 'aft_tutor_private_remove_vendor_updates', 99 );
add_filter( 'pre_set_site_transient_update_plugins', 'aft_tutor_private_remove_vendor_updates', 99 );
add_filter( 'transient_update_plugins', 'aft_tutor_private_remove_vendor_updates', 99 );

/** Never let WordPress perform an automatic Tutor update. */
function aft_tutor_private_disable_auto_update( $update, $item ) {
	if ( is_object( $item ) && aft_tutor_private_is_plugin_file( $item->plugin ?? '' ) ) {
		return false;
	}
	return $update;
}
add_filter( 'auto_update_plugin', 'aft_tutor_private_disable_auto_update', 99, 2 );

/** Stop the plugin-information request from pulling Tutor vendor metadata. */
function aft_tutor_private_block_plugin_information( $result, $action, $args ) {
	$slug = is_object( $args ) && isset( $args->slug ) ? (string) $args->slug : '';
	if ( 'plugin_information' === $action && in_array( $slug, [ 'tutor', 'tutor-pro' ], true ) ) {
		return new WP_Error( 'aft_tutor_private_plugin', __( 'Tutor LMS is maintained as a private AFT fork.', 'aft' ) );
	}
	return $result;
}
add_filter( 'plugins_api', 'aft_tutor_private_block_plugin_information', 99, 3 );

/**
 * Block Tutor/Themeum licence, update, telemetry and remote-service requests.
 * Tutor Free and Pro still communicate locally inside WordPress; only vendor
 * traffic is stopped.
 */
function aft_tutor_private_block_vendor_http( $pre, $args, $url ) {
	$parts = wp_parse_url( $url );
	$host  = isset( $parts['host'] ) ? strtolower( (string) $parts['host'] ) : '';
	$path  = isset( $parts['path'] ) ? (string) $parts['path'] : '';
	$vendor_domains = [
		'tutorlms.com',
		'tutorplugins.com',
		'themeum.com',
		'gpltimes.com',
	];

	$is_vendor = false;
	foreach ( $vendor_domains as $domain ) {
		if ( $host === $domain || ( strlen( $host ) > strlen( $domain ) && substr( $host, -strlen( '.' . $domain ) ) === '.' . $domain ) ) {
			$is_vendor = true;
			break;
		}
	}

	// Tutor Free's WordPress.org package is also excluded from direct downloads.
	$is_tutor_wordpress_package = 'downloads.wordpress.org' === $host && (bool) preg_match( '#/plugin/tutor(?:[-/.]|$)#i', $path );

	if ( $is_vendor || $is_tutor_wordpress_package ) {
		return new WP_Error( 'aft_tutor_private_vendor_blocked', __( 'Tutor LMS vendor communication is disabled by AFT private-fork policy.', 'aft' ) );
	}

	return $pre;
}
add_filter( 'pre_http_request', 'aft_tutor_private_block_vendor_http', 99, 3 );

/**
 * Optional private mirror integration. The endpoint is intentionally empty
 * until AFT supplies its controlled package URL; no external package source
 * is accepted by this policy.
 */
function aft_tutor_private_add_internal_updates( $transient ) {
	static $loaded = false;
	static $mirror = null;

	if ( $loaded ) {
		if ( is_array( $mirror ) ) {
			foreach ( $mirror as $plugin => $item ) {
				if ( aft_tutor_private_is_plugin_file( $plugin ) ) {
					$transient->response[ $plugin ] = (object) array_merge(
						[
							'id'          => $plugin,
							'slug'        => dirname( $plugin ),
							'plugin'      => $plugin,
							'new_version' => '',
							'url'         => '',
							'package'     => '',
						],
						(array) $item
					);
				}
			}
		}
		return $transient;
	}

	$loaded = true;
	$url = trim( (string) AFT_TUTOR_PRIVATE_UPDATE_URL );
	if ( '' === $url ) {
		return $transient;
	}

	$response = wp_remote_get(
		$url,
		[
			'timeout'   => 10,
			'sslverify' => true,
			'headers'   => [ 'Accept' => 'application/json' ],
		]
	);
	if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		return $transient;
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	$mirror = is_array( $data['plugins'] ?? null ) ? $data['plugins'] : ( is_array( $data ) ? $data : [] );
	foreach ( $mirror as $plugin => $item ) {
		if ( ! aft_tutor_private_is_plugin_file( $plugin ) || ! is_array( $item ) ) {
			continue;
		}
		$transient->response[ $plugin ] = (object) array_merge(
			[
				'id'          => $plugin,
				'slug'        => dirname( $plugin ),
				'plugin'      => $plugin,
				'new_version' => '',
				'url'         => '',
				'package'     => '',
			],
			$item
		);
	}

	return $transient;
}

/** Add low-friction anti-bot fields to Tutor's native registration form. */
function aft_tutor_registration_traps() {
	if ( is_user_logged_in() ) {
		return;
	}
	?>
	<div class="aft-registration-trap" aria-hidden="true">
		<label for="aft-registration-website">Website</label>
		<input id="aft-registration-website" type="text" name="aft_registration_website" value="" tabindex="-1" autocomplete="off">
	</div>
	<input type="hidden" name="aft_registration_started" value="<?php echo esc_attr( (string) time() ); ?>">
	<?php
}
add_action( 'tutor_student_reg_form_start', 'aft_tutor_registration_traps', 5 );

/** Validate the traps and apply a generous per-IP registration limit. */
function aft_tutor_registration_guard( $errors ) {
	if ( 'tutor_register_student' !== (string) ( $_POST['tutor_action'] ?? '' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return $errors;
	}

	$honeypot = isset( $_POST['aft_registration_website'] ) ? trim( (string) wp_unslash( $_POST['aft_registration_website'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$started  = isset( $_POST['aft_registration_started'] ) ? absint( $_POST['aft_registration_started'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$elapsed  = $started ? time() - $started : 0;

	if ( '' !== $honeypot || ! $started || $elapsed < 3 || $elapsed > DAY_IN_SECONDS * 2 ) {
		$errors->add( 'aft_registration_spam', __( 'Please complete the registration form normally and try again.', 'aft' ) );
	}

	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown'; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
	$key = 'aft_reg_rate_' . md5( wp_salt( 'auth' ) . '|' . $ip );
	$count = (int) get_transient( $key );
	if ( $count >= 8 ) {
		$errors->add( 'aft_registration_rate', __( 'Registration is temporarily busy from this network. Please try again later.', 'aft' ) );
	} else {
		set_transient( $key, $count + 1, HOUR_IN_SECONDS );
	}

	return $errors;
}
add_filter( 'registration_errors', 'aft_tutor_registration_guard', 99 );

/** Keep new Tutor accounts unverified until their email address is confirmed. */
add_filter( 'tutor_require_email_verification', '__return_true', 99 );

/**
 * -------------------------------------------------------------------------
 * AFT student status and graduation policy.
 *
 * This section deliberately lives in the MU-plugin rather than in Tutor or
 * Tutor Pro source. The policy therefore survives private plugin updates.
 * -------------------------------------------------------------------------
 */

if ( ! defined( 'AFT_GRADUATION_DATE_META' ) ) {
	define( 'AFT_GRADUATION_DATE_META', 'aft_graduation_date' );
}
if ( ! defined( 'AFT_STUDENT_STATUS_META' ) ) {
	define( 'AFT_STUDENT_STATUS_META', 'aft_student_status' );
}
if ( ! defined( 'AFT_STUDENT_STATUS_OVERRIDE_META' ) ) {
	define( 'AFT_STUDENT_STATUS_OVERRIDE_META', 'aft_student_status_override' );
}

/** Return today's date in the WordPress site timezone. */
function aft_student_today() {
	return current_time( 'Y-m-d' );
}

/** Return a strict, valid Y-m-d date or an empty string. */
function aft_student_valid_date( $date ) {
	$date = sanitize_text_field( (string) $date );
	if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
		return '';
	}

	$parsed = DateTime::createFromFormat( '!Y-m-d', $date, wp_timezone() );
	if ( ! $parsed || $parsed->format( 'Y-m-d' ) !== $date ) {
		return '';
	}

	return $date;
}

/**
 * Reconcile one student's stored status with the graduation date. A manual
 * "current" override is honoured only while there is no past/today date;
 * a real graduation date always promotes the student.
 */
function aft_sync_student_status( $user_id ) {
	$user_id = absint( $user_id );
	if ( ! $user_id ) {
		return 'current';
	}

	$date     = aft_student_valid_date( get_user_meta( $user_id, AFT_GRADUATION_DATE_META, true ) );
	$override = sanitize_key( (string) get_user_meta( $user_id, AFT_STUDENT_STATUS_OVERRIDE_META, true ) );
	$status   = 'current';

	if ( 'graduated' === $override ) {
		$status = 'graduated';
	} elseif ( $date && $date <= aft_student_today() ) {
		$status = 'graduated';
	} elseif ( 'current' === $override ) {
		$status = 'current';
	}

	if ( get_user_meta( $user_id, AFT_STUDENT_STATUS_META, true ) !== $status ) {
		update_user_meta( $user_id, AFT_STUDENT_STATUS_META, $status );
	}

	return $status;
}

/** Return the current computed status for a user. */
function aft_get_student_status( $user_id ) {
	return aft_sync_student_status( $user_id );
}

/** Return true only for an authenticated graduate. */
function aft_is_graduated_student( $user_id = 0 ) {
	$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
	return $user_id && 'graduated' === aft_get_student_status( $user_id );
}

/** Run synchronization on requests and once per day through WP-Cron. */
function aft_sync_graduation_statuses() {
	$users = get_users(
		array(
			'fields'     => 'ids',
			'number'     => -1,
			'meta_key'   => AFT_GRADUATION_DATE_META,
			'meta_value' => '',
			'meta_compare' => '!=',
		)
	);

	foreach ( $users as $user_id ) {
		aft_sync_student_status( $user_id );
	}
}
add_action( 'aft_daily_student_status_sync', 'aft_sync_graduation_statuses' );

function aft_schedule_graduation_status_sync() {
	if ( ! wp_next_scheduled( 'aft_daily_student_status_sync' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'aft_daily_student_status_sync' );
	}
}
add_action( 'init', 'aft_schedule_graduation_status_sync', 20 );

function aft_sync_current_student_status() {
	if ( is_user_logged_in() ) {
		aft_sync_student_status( get_current_user_id() );
	}
}
add_action( 'init', 'aft_sync_current_student_status', 21 );

/** Save a validated date supplied by the Tutor Pro manual-enrollment form. */
function aft_save_graduation_date_after_enrollment( $order_type, $object_id, $student_id, $payment_status ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	$raw_date = isset( $_POST['aft_graduation_date'] ) ? wp_unslash( $_POST['aft_graduation_date'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( '' === trim( (string) $raw_date ) ) {
		return;
	}

	$date = aft_student_valid_date( $raw_date );
	if ( ! $date || ! absint( $student_id ) ) {
		return;
	}

	update_user_meta( absint( $student_id ), AFT_GRADUATION_DATE_META, $date );
	delete_user_meta( absint( $student_id ), AFT_STUDENT_STATUS_OVERRIDE_META );
	aft_sync_student_status( $student_id );
}
add_action( 'tutor_after_enrollment', 'aft_save_graduation_date_after_enrollment', 20, 4 );

/**
 * Prevent a graduate from using Tutor's normal course-enrollment path. Tutor
 * checks this filter before creating the tutor_enrolled post, so existing
 * completed enrollments are not altered.
 */
function aft_block_graduated_course_enrollment( $allowed, $course_id ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	if ( aft_is_graduated_student() ) {
		return new WP_Error(
			'aft_graduated_student',
			__( 'Graduated students cannot enroll in or take additional courses.', 'aft' )
		);
	}
	return $allowed;
}
add_filter( 'tutor_allow_course_enrollment', 'aft_block_graduated_course_enrollment', 1, 2 );

/**
 * The normal filter above evaluates the logged-in user. This second guard
 * covers automated, payment and administrator-triggered enrollment calls,
 * where Tutor passes the target student only in the enrollment post data.
 * A blocked attempt is placed in trash rather than becoming a completed
 * tutor_enrolled record; existing enrollment records are not touched because
 * Tutor returns them before this filter runs.
 */
function aft_block_graduated_enrollment_data( $data ) {
	$student_id = isset( $data['post_author'] ) ? absint( $data['post_author'] ) : 0;
	if ( $student_id && aft_is_graduated_student( $student_id ) ) {
		$data['post_status'] = 'trash';
	}
	return $data;
}
add_filter( 'tutor_enroll_data', 'aft_block_graduated_enrollment_data', 1 );

/** Also stop Tutor's standalone authenticated enrollment AJAX endpoint. */
function aft_block_graduated_course_enrollment_ajax() {
	if ( aft_is_graduated_student() ) {
		wp_send_json_error(
			array( 'message' => __( 'Graduated students cannot enroll in or take additional courses.', 'aft' ) ),
			403
		);
	}
}
add_action( 'wp_ajax_tutor_course_enrollment', 'aft_block_graduated_course_enrollment_ajax', 1 );

/**
 * Remove graduates from the Tutor Pro bulk enrollment request before the Pro
 * handler processes it. CSV rows for already-existing graduates are removed
 * as well; current students remain in a mixed selection.
 */
function aft_filter_graduates_from_bulk_enrollment() {
	if ( ! isset( $_POST['student_ids'] ) && ! isset( $_POST['csv_students'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}

	if ( isset( $_POST['student_ids'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$ids = is_array( $_POST['student_ids'] ) ? $_POST['student_ids'] : array( $_POST['student_ids'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$ids = array_values(
			array_filter(
				array_map( 'absint', $ids ),
				function ( $id ) {
					return $id && ! aft_is_graduated_student( $id );
				}
			)
		);
		$_POST['student_ids'] = $ids; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}

	if ( isset( $_POST['csv_students'] ) && is_array( $_POST['csv_students'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$csv = array();
		foreach ( $_POST['csv_students'] as $encoded_student ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$student = is_string( $encoded_student ) ? json_decode( wp_unslash( $encoded_student ) ) : null;
			$email   = is_object( $student ) && isset( $student->email ) ? sanitize_email( $student->email ) : '';
			$user    = $email ? get_user_by( 'email', $email ) : false;
			if ( ! $user || ! aft_is_graduated_student( $user->ID ) ) {
				$csv[] = $encoded_student;
			}
		}
		$_POST['csv_students'] = $csv; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}
}
add_action( 'wp_ajax_tutor_enroll_bulk_student', 'aft_filter_graduates_from_bulk_enrollment', 1 );

/**
 * Deny direct course, lesson, quiz, assignment and similar Tutor content
 * requests, while deliberately leaving dashboard/profile/certificate pages
 * available to graduates.
 */
function aft_graduated_blocked_post_types() {
	$types = array(
		'courses',
		'lesson',
		'tutor_quiz',
		'tutor_assignment',
		'tutor_assignments',
		'course-bundle',
	);

	if ( function_exists( 'tutor' ) ) {
		$tutor = tutor();
		foreach ( array( 'course_post_type', 'lesson_post_type', 'quiz_post_type', 'assignment_post_type' ) as $property ) {
			if ( is_object( $tutor ) && ! empty( $tutor->{$property} ) ) {
				$types[] = $tutor->{$property};
			}
		}
	}

	return array_values( array_unique( array_filter( $types ) ) );
}

function aft_deny_graduated_learning_request() {
	if ( ! aft_is_graduated_student() || is_admin() ) {
		return;
	}

	$request_uri      = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
	$is_video         = false !== strpos( wp_parse_url( $request_uri, PHP_URL_PATH ) ?: '', '/video-url/' );
	$is_content       = is_singular( aft_graduated_blocked_post_types() );
	$dashboard_page   = get_query_var( 'tutor_dashboard_page' );
	$dashboard_subpage = get_query_var( 'tutor_dashboard_sub_page' );
	$is_learning_area = in_array( $dashboard_page, array( 'courses', 'enrolled-courses', 'quiz-attempts' ), true ) || in_array( $dashboard_subpage, array( 'my-quiz-attempts', 'wishlist' ), true );

	if ( ! $is_video && ! $is_content && ! $is_learning_area ) {
		return;
	}

	$message = __( 'This learning content is not available after graduation. Your profile and certificate pages remain available.', 'aft' );
	if ( wp_doing_ajax() ) {
		wp_send_json_error( array( 'message' => $message ), 403 );
	}

	$dashboard_url = function_exists( 'tutor_utils' ) ? tutor_utils()->tutor_dashboard_url() : home_url( '/' );
	wp_safe_redirect( add_query_arg( 'aft_access', 'graduated', $dashboard_url ) );
	exit;
}
add_action( 'template_redirect', 'aft_deny_graduated_learning_request', 1 );

/** Do not allow a graduate's pending guest-enrollment attempt after login. */
function aft_block_graduated_guest_enrollment( $allowed, $course_id, $user_id ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	return aft_is_graduated_student( $user_id ) ? false : $allowed;
}
add_filter( 'tutor_allow_guest_attempt_enrollment', 'aft_block_graduated_guest_enrollment', 1, 3 );

/** Keep the dashboard available but remove the student course-learning menu. */
function aft_graduate_dashboard_nav( $items ) {
	if ( ! aft_is_graduated_student() ) {
		return $items;
	}

	unset( $items['courses'], $items['enrolled-courses'], $items['wishlist'], $items['quiz-attempts'], $items['my-quiz-attempts'] );
	return $items;
}
add_filter( 'tutor_dashboard/nav_ui_items', 'aft_graduate_dashboard_nav', 1 );

/** Add status and date to every row of Tutor's native Students table. */
function aft_render_student_status_badge( $student_id ) {
	$student_id = absint( $student_id );
	if ( ! $student_id ) {
		return;
	}

	$status = aft_get_student_status( $student_id );
	$date   = aft_student_valid_date( get_user_meta( $student_id, AFT_GRADUATION_DATE_META, true ) );
	$label  = 'graduated' === $status ? __( 'Graduated', 'aft' ) : __( 'Current', 'aft' );
	$title  = $date ? sprintf( __( 'Graduation date: %s', 'aft' ), $date ) : __( 'No graduation date recorded', 'aft' );
	?>
	<span class="aft-student-status aft-student-status-<?php echo esc_attr( $status ); ?>" data-aft-status="<?php echo esc_attr( $status ); ?>" title="<?php echo esc_attr( $title ); ?>">
		<?php echo esc_html( $label ); ?><?php echo $date ? ' · ' . esc_html( $date ) : ''; ?>
	</span>
	<?php
}
add_action( 'tutor_show_email_verified_badge', 'aft_render_student_status_badge', 20 );

/** Add a same-page filter and a link to the complete status directory. */
function aft_student_table_admin_assets() {
	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( 'tutor-students' !== $page ) {
		return;
	}
	$directory_url = admin_url( 'admin.php?page=aft-student-status' );
	?>
	<style>
		.aft-student-status{display:inline-block;margin:5px 0 0;padding:3px 8px;border-radius:999px;font-size:11px;font-weight:600;line-height:1.35;white-space:nowrap}
		.aft-student-status-current{color:#135e2f;background:#e5f6eb}
		.aft-student-status-graduated{color:#7a2800;background:#fff0e8}
		.aft-student-status-tools{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin:0 0 16px;padding:12px 14px;background:#fff;border:1px solid #dcdcde;border-radius:6px}
		.aft-student-status-tools label{font-weight:600}
	</style>
	<script>
	(function(){
		function initAftStudentStatusFilter(){
			var table=document.querySelector('.tutor-table-with-checkbox');
			if(!table||document.querySelector('.aft-student-status-tools')){return;}
			var tools=document.createElement('div');
			tools.className='aft-student-status-tools';
			tools.innerHTML='<label for="aft-student-status-filter">Status</label><select id="aft-student-status-filter"><option value="all">All students on this page</option><option value="current">Current</option><option value="graduated">Graduated</option></select><a class="button" href="<?php echo esc_url( $directory_url ); ?>">Open full status directory</a>';
			table.parentNode.insertBefore(tools,table);
			var select=tools.querySelector('select');
			select.addEventListener('change',function(){
				table.querySelectorAll('tbody tr').forEach(function(row){
					var badge=row.querySelector('[data-aft-status]');
					row.style.display=(!badge||'all'===select.value||badge.getAttribute('data-aft-status')===select.value)?'':'none';
				});
			});
		}
		if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',initAftStudentStatusFilter);}else{initAftStudentStatusFilter();}
	})();
	</script>
	<?php
}
add_action( 'admin_footer', 'aft_student_table_admin_assets', 20 );

/** Add AFT's complete status directory beneath Tutor > Students. */
function aft_add_student_status_admin_menu( $admin_menu ) {
	if ( ! isset( $admin_menu['group_two'] ) || ! is_array( $admin_menu['group_two'] ) ) {
		$admin_menu['group_two'] = array();
	}
	$admin_menu['group_two']['aft_student_status'] = array(
		'parent_slug' => 'tutor',
		'page_title'  => __( 'Student Status', 'aft' ),
		'menu_title'  => __( 'Student Status', 'aft' ),
		'capability'  => 'manage_tutor',
		'menu_slug'   => 'aft-student-status',
		'callback'    => 'aft_render_student_status_admin_page',
	);
	return $admin_menu;
}
add_filter( 'tutor_admin_menu', 'aft_add_student_status_admin_menu', 20 );

/** Return every user with a Tutor enrollment record, regardless of status. */
function aft_get_all_enrolled_student_ids() {
	global $wpdb;
	return array_map(
		'absint',
		(array) $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT post_author FROM {$wpdb->posts} WHERE post_type = %s AND post_author > 0",
				'tutor_enrolled'
			)
		)
	);
}

/** Recalculate stored status metadata for every enrolled student. */
function aft_process_student_status_admin_sync() {
	if ( ! isset( $_POST['aft_sync_all_student_status'] ) || ! current_user_can( 'manage_tutor' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}
	check_admin_referer( 'aft_sync_all_student_status' );

	$synced = 0;
	foreach ( aft_get_all_enrolled_student_ids() as $user_id ) {
		aft_sync_student_status( $user_id );
		++$synced;
	}

	$redirect = add_query_arg(
		array(
			'page'       => 'aft-student-status',
			'aft_synced' => $synced,
		),
		admin_url( 'admin.php' )
	);
	wp_safe_redirect( $redirect );
	exit;
}

function aft_process_student_status_admin_save() {
	if ( ! isset( $_POST['aft_student_status_save'] ) || ! current_user_can( 'manage_tutor' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}
	check_admin_referer( 'aft_save_student_status' );

	$user_id  = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$graduated = isset( $_POST['aft_graduated'] ) && '1' === (string) $_POST['aft_graduated']; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$status   = $graduated ? 'graduated' : 'current';
	$date     = isset( $_POST['aft_graduation_date'] ) ? aft_student_valid_date( wp_unslash( $_POST['aft_graduation_date'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

	if ( ! $user_id ) {
		return;
	}

	if ( 'graduated' === $status ) {
		update_user_meta( $user_id, AFT_GRADUATION_DATE_META, $date ?: aft_student_today() );
		update_user_meta( $user_id, AFT_STUDENT_STATUS_OVERRIDE_META, 'graduated' );
	} else {
		// A current student cannot retain a past/today graduation date.
		if ( ! $date || $date <= aft_student_today() ) {
			delete_user_meta( $user_id, AFT_GRADUATION_DATE_META );
		} else {
			update_user_meta( $user_id, AFT_GRADUATION_DATE_META, $date );
		}
		update_user_meta( $user_id, AFT_STUDENT_STATUS_OVERRIDE_META, 'current' );
	}
	aft_sync_student_status( $user_id );

	$redirect = add_query_arg(
		array(
			'page'      => 'aft-student-status',
			'aft_saved' => '1',
		),
		admin_url( 'admin.php' )
	);
	wp_safe_redirect( $redirect );
	exit;
}

function aft_render_student_status_admin_page() {
	if ( ! current_user_can( 'manage_tutor' ) ) {
		wp_die( esc_html__( 'You do not have permission to view student status.', 'aft' ) );
	}
	aft_process_student_status_admin_sync();
	aft_process_student_status_admin_save();

	global $wpdb;
	$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$filter = isset( $_GET['aft_status'] ) ? sanitize_key( wp_unslash( $_GET['aft_status'] ) ) : 'all'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$paged  = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$per_page = 25;
	$like   = '%' . $wpdb->esc_like( $search ) . '%';
	$rows   = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT DISTINCT u.ID, u.display_name, u.user_login, u.user_email
			FROM {$wpdb->posts} AS e
			INNER JOIN {$wpdb->users} AS u ON u.ID = e.post_author
			WHERE e.post_type = %s
			AND (u.display_name LIKE %s OR u.user_login LIKE %s OR u.user_email LIKE %s)
			ORDER BY u.display_name ASC",
			'tutor_enrolled',
			$like,
			$like,
			$like
		)
	);
	$filtered_rows = array();
	foreach ( (array) $rows as $row ) {
		$row_status = aft_get_student_status( $row->ID );
		if ( 'all' === $filter || $filter === $row_status ) {
			$filtered_rows[] = $row;
		}
	}
	$total_rows  = count( $filtered_rows );
	$total_pages = max( 1, (int) ceil( $total_rows / $per_page ) );
	$paged       = min( $paged, $total_pages );
	$rows        = array_slice( $filtered_rows, ( $paged - 1 ) * $per_page, $per_page );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Student Status', 'aft' ); ?></h1>
		<p><?php esc_html_e( 'Graduation dates are stored as user metadata. A date on or before today automatically makes the student Graduated; a future date keeps the student Current.', 'aft' ); ?></p>
		<?php if ( isset( $_GET['aft_saved'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Student status saved.', 'aft' ); ?></p></div>
		<?php endif; ?>
		<?php if ( isset( $_GET['aft_synced'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible"><p><?php printf( esc_html__( 'Status synchronized for %d enrolled student(s).', 'aft' ), absint( $_GET['aft_synced'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?></p></div>
		<?php endif; ?>
		<div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin:16px 0">
			<form method="post">
				<?php wp_nonce_field( 'aft_sync_all_student_status' ); ?>
				<input type="hidden" name="aft_sync_all_student_status" value="1">
				<button class="button" type="submit"><?php esc_html_e( 'Sync all enrolled students', 'aft' ); ?></button>
			</form>
			<span class="description"><?php esc_html_e( 'Recalculates each enrolled student from the stored graduation date.', 'aft' ); ?></span>
		</div>
		<form method="get" style="margin:16px 0;display:flex;gap:8px;align-items:center;flex-wrap:wrap">
			<input type="hidden" name="page" value="aft-student-status">
			<label for="aft-status-search"><strong><?php esc_html_e( 'Search', 'aft' ); ?></strong></label>
			<input id="aft-status-search" type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Name, username or email">
			<label for="aft-status-filter"><strong><?php esc_html_e( 'Status', 'aft' ); ?></strong></label>
			<select id="aft-status-filter" name="aft_status">
				<option value="all" <?php selected( $filter, 'all' ); ?>><?php esc_html_e( 'All', 'aft' ); ?></option>
				<option value="current" <?php selected( $filter, 'current' ); ?>><?php esc_html_e( 'Current', 'aft' ); ?></option>
				<option value="graduated" <?php selected( $filter, 'graduated' ); ?>><?php esc_html_e( 'Graduated', 'aft' ); ?></option>
			</select>
			<button class="button button-primary" type="submit"><?php esc_html_e( 'Filter', 'aft' ); ?></button>
		</form>
		<table class="widefat striped" style="max-width:1100px">
			<thead><tr><th><?php esc_html_e( 'Student', 'aft' ); ?></th><th><?php esc_html_e( 'Email', 'aft' ); ?></th><th><?php esc_html_e( 'Status', 'aft' ); ?></th><th><?php esc_html_e( 'Graduation date', 'aft' ); ?></th><th><?php esc_html_e( 'Save', 'aft' ); ?></th></tr></thead>
			<tbody>
			<?php
			$visible = 0;
			foreach ( $rows as $row ) :
				$status = aft_get_student_status( $row->ID );
				if ( 'all' !== $filter && $filter !== $status ) {
					continue;
				}
				++$visible;
				$date = aft_student_valid_date( get_user_meta( $row->ID, AFT_GRADUATION_DATE_META, true ) );
				?>
				<tr>
					<td><strong><?php echo esc_html( $row->display_name ); ?></strong><br><code><?php echo esc_html( $row->user_login ); ?></code></td>
					<td><?php echo esc_html( $row->user_email ); ?></td>
					<td><span class="aft-student-status aft-student-status-<?php echo esc_attr( $status ); ?>"><?php echo esc_html( 'graduated' === $status ? __( 'Graduated', 'aft' ) : __( 'Current', 'aft' ) ); ?></span></td>
					<td colspan="2">
						<form method="post" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
							<?php wp_nonce_field( 'aft_save_student_status' ); ?>
							<input type="hidden" name="aft_student_status_save" value="1">
							<input type="hidden" name="user_id" value="<?php echo esc_attr( $row->ID ); ?>">
							<label style="display:inline-flex;gap:6px;align-items:center">
								<input type="checkbox" name="aft_graduated" value="1" <?php checked( $status, 'graduated' ); ?>>
								<?php esc_html_e( 'Graduated', 'aft' ); ?>
							</label>
							<input type="date" name="aft_graduation_date" value="<?php echo esc_attr( $date ); ?>" aria-label="<?php esc_attr_e( 'Graduation date', 'aft' ); ?>">
							<button class="button" type="submit"><?php esc_html_e( 'Save', 'aft' ); ?></button>
						</form>
					</td>
				</tr>
			<?php endforeach; ?>
			<?php if ( ! $visible ) : ?><tr><td colspan="5"><?php esc_html_e( 'No students match this filter.', 'aft' ); ?></td></tr><?php endif; ?>
			</tbody>
		</table>
		<?php if ( $total_pages > 1 ) : ?>
			<div class="tablenav" style="margin-top:12px"><div class="tablenav-pages">
				<?php
				$pagination_url = add_query_arg(
					array(
						'page'       => 'aft-student-status',
						's'          => $search,
						'aft_status' => $filter,
						'paged'      => '%#%',
					),
					admin_url( 'admin.php' )
				);
				echo wp_kses_post( paginate_links( array( 'base' => $pagination_url, 'format' => '', 'current' => $paged, 'total' => $total_pages ) ) );
				?>
			</div></div>
		<?php endif; ?>
	</div>
	<?php
}

/** Add the date field and bridge it into Tutor Pro's React/Axios request. */
function aft_enrollment_date_admin_assets() {
	$page   = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( 'enrollments' !== $page || 'add_new' !== $action ) {
		return;
	}
	?>
	<style>
		#aft-enrollment-graduation-box{margin:0 0 18px;padding:16px 18px;background:#fff;border:1px solid #dcdcde;border-radius:6px;max-width:760px}
		#aft-enrollment-graduation-box label{display:block;margin-bottom:7px;font-weight:600}
		#aft-enrollment-graduation-box input{min-width:190px}
		#aft-enrollment-graduation-box p{margin:8px 0 0;color:#646970}
	</style>
	<script>
	(function(){
		function addAftGraduationField(){
			var root=document.getElementById('tutor-new-enrollment-root');
			if(!root||document.getElementById('aft-enrollment-graduation-box')){return;}
			var box=document.createElement('div');
			box.id='aft-enrollment-graduation-box';
			box.innerHTML='<label for="aft-enrollment-graduation-date">Graduation date for selected student(s)</label><input id="aft-enrollment-graduation-date" type="date"><p>Optional. The date is saved to each selected student after this enrollment. A date on or before today makes the student Graduated.</p>';
			root.parentNode.insertBefore(box,root);
		}
		function getAftDate(){var input=document.getElementById('aft-enrollment-graduation-date');return input?input.value:'';}
		function addAftToBody(body){
			var date=getAftDate();
			if(!date){return body;}
			if(window.FormData&&body instanceof FormData){body.set('aft_graduation_date',date);return body;}
			if(window.URLSearchParams&&body instanceof URLSearchParams){body.set('aft_graduation_date',date);return body;}
			if(typeof body==='string'){
				return body+'&aft_graduation_date='+encodeURIComponent(date);
			}
			if(body&&typeof body==='object'){
				try{body.aft_graduation_date=date;return JSON.stringify(body);}catch(e){}
			}
			return body;
		}
		function isAftEnrollmentBody(body){
			if(typeof body==='string'){return body.indexOf('tutor_enroll_bulk_student')>-1;}
			if(window.FormData&&body instanceof FormData){return body.get('action')==='tutor_enroll_bulk_student';}
			if(window.URLSearchParams&&body instanceof URLSearchParams){return body.get('action')==='tutor_enroll_bulk_student';}
			return body&&body.action==='tutor_enroll_bulk_student';
		}
		function installAftRequestBridge(){
			if(window.XMLHttpRequest&&!XMLHttpRequest.prototype._aftGraduationBridge){
				var send=XMLHttpRequest.prototype.send;
				XMLHttpRequest.prototype.send=function(body){
					var url=this._aftRequestUrl||'';
					if(/admin-ajax\.php/.test(url)&&isAftEnrollmentBody(body)){body=addAftToBody(body);}
					return send.call(this,body);
				};
				var open=XMLHttpRequest.prototype.open;
				XMLHttpRequest.prototype.open=function(method,url){this._aftRequestUrl=String(url||'');return open.apply(this,arguments);};
				XMLHttpRequest.prototype._aftGraduationBridge=true;
			}
			if(window.fetch&&!window.fetch._aftGraduationBridge){
				var fetch=window.fetch;
				window.fetch=function(input,init){
					var url=typeof input==='string'?input:(input&&input.url)||'';
					if(/admin-ajax\.php/.test(url)&&init&&isAftEnrollmentBody(init.body)){init.body=addAftToBody(init.body);}
					return fetch.apply(this,arguments);
				};
				window.fetch._aftGraduationBridge=true;
			}
		}
		addAftGraduationField();
		installAftRequestBridge();
	})();
	</script>
	<?php
}
add_action( 'admin_footer', 'aft_enrollment_date_admin_assets', 20 );
