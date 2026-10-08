<?php
/**
 * Plugin Name: AFT Spam Student Cleaner
 * Description: Reviews student-like WordPress accounts with no Tutor enrollment records and lets an administrator permanently delete selected accounts and their authored content.
 * Version: 1.0.0
 * Author: Accountants For Tomorrow
 *
 * This file is deployed as an AFT MU-plugin so the review tool is always
 * available and cannot be disabled accidentally by a normal plugin update.
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'AFT_SPAM_STUDENT_PAGE' ) ) {
	define( 'AFT_SPAM_STUDENT_PAGE', 'aft-spam-students' );
}

/** Roles considered student-like by default. */
function aft_spam_student_allowed_roles() {
	return apply_filters(
		'aft_spam_student_allowed_roles',
		array(
			'subscriber',
			'student',
			'tutor_student',
			'learner',
			'member',
			'customer',
		)
	);
}

/** Roles that must never be presented as spam students. */
function aft_spam_student_excluded_roles() {
	return apply_filters(
		'aft_spam_student_excluded_roles',
		array(
			'administrator',
			'editor',
			'author',
			'contributor',
			'shop_manager',
			'instructor',
			'tutor_instructor',
			'teacher',
			'manager',
		)
	);
}

/** Check whether the user has a student-like, non-privileged role. */
function aft_spam_student_is_eligible_user( $user ) {
	if ( ! $user instanceof WP_User || ! $user->exists() || is_super_admin( $user->ID ) ) {
		return false;
	}

	$roles = array_map( 'sanitize_key', (array) $user->roles );
	if ( array_intersect( $roles, aft_spam_student_excluded_roles() ) ) {
		return false;
	}

	return (bool) array_intersect( $roles, aft_spam_student_allowed_roles() );
}

/** Strict date validation used by the optional registration-age filter. */
function aft_spam_student_valid_date( $date ) {
	$date = sanitize_text_field( (string) $date );
	if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
		return '';
	}

	$parsed = DateTime::createFromFormat( '!Y-m-d', $date, wp_timezone() );
	return $parsed && $parsed->format( 'Y-m-d' ) === $date ? $date : '';
}

/** Confirm that a user has no Tutor enrollment post of any status. */
function aft_spam_student_has_enrollment( $user_id ) {
	global $wpdb;
	return (bool) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_author = %d AND post_type = %s LIMIT 1",
			absint( $user_id ),
			'tutor_enrolled'
		)
	);
}

/**
 * Query candidate rows. Tutor enrollment records of every status are excluded,
 * not only completed records, so pending or cancelled accounts are protected.
 */
function aft_spam_student_find_candidates( $search = '', $registered_before = '' ) {
	global $wpdb;

	$search            = sanitize_text_field( $search );
	$registered_before = aft_spam_student_valid_date( $registered_before );
	$like              = '%' . $wpdb->esc_like( $search ) . '%';
	$capability_key    = $wpdb->prefix . 'capabilities';
	$date_sql          = '';
	$date_args         = array();

	if ( $registered_before ) {
		$date_sql  = ' AND u.user_registered <= %s';
		$date_args = array( $registered_before . ' 23:59:59' );
	}

	$query =
		"SELECT DISTINCT u.ID, u.user_login, u.user_email, u.display_name, u.user_registered, caps.meta_value AS capabilities
		FROM {$wpdb->users} AS u
		INNER JOIN {$wpdb->usermeta} AS caps
			ON caps.user_id = u.ID AND caps.meta_key = %s
		WHERE NOT EXISTS (
			SELECT 1 FROM {$wpdb->posts} AS enrolled
			WHERE enrolled.post_author = u.ID AND enrolled.post_type = %s
		)
		AND (u.user_login LIKE %s OR u.user_email LIKE %s OR u.display_name LIKE %s)
		{$date_sql}
		ORDER BY u.user_registered ASC, u.ID ASC";

	$args = array_merge(
		array( $capability_key, 'tutor_enrolled', $like, $like, $like ),
		$date_args
	);
	$rows = $wpdb->get_results( $wpdb->prepare( $query, $args ) );
	$candidates = array();

	foreach ( (array) $rows as $row ) {
		$roles = maybe_unserialize( $row->capabilities );
		$user  = new WP_User( $row->ID );
		if ( ! is_array( $roles ) ) {
			$roles = array();
		}
		$user->roles = array_keys( array_filter( $roles ) );
		if ( aft_spam_student_is_eligible_user( $user ) ) {
			$row->roles = $user->roles;
			$candidates[] = $row;
		}
	}

	return $candidates;
}

/** Re-check a user immediately before deletion. */
function aft_spam_student_get_candidate( $user_id ) {
	$user_id = absint( $user_id );
	$user    = $user_id ? get_user_by( 'id', $user_id ) : false;
	if ( ! $user || ! aft_spam_student_is_eligible_user( $user ) || aft_spam_student_has_enrollment( $user_id ) ) {
		return false;
	}
	return $user;
}

/**
 * Permanently remove the account and all content authored by it. This action
 * is only reachable by a manage_options administrator after nonce validation.
 */
function aft_spam_student_delete_user_and_content( $user_id ) {
	$user = aft_spam_student_get_candidate( $user_id );
	if ( ! $user || get_current_user_id() === $user->ID ) {
		return false;
	}

	$post_ids = get_posts(
		array(
			'author'         => $user->ID,
			'post_type'      => 'any',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);
	foreach ( (array) $post_ids as $post_id ) {
		wp_delete_post( $post_id, true );
	}

	$comments = get_comments(
		array(
			'user_id' => $user->ID,
			'number'  => 0,
			'fields'  => 'ids',
		)
	);
	foreach ( (array) $comments as $comment_id ) {
		wp_delete_comment( $comment_id, true );
	}

	return wp_delete_user( $user->ID );
}

/** Register the review page under Tutor. */
function aft_spam_student_admin_menu() {
	add_submenu_page(
		'tutor',
		__( 'Spam Student Cleaner', 'aft' ),
		__( 'Spam Student Cleaner', 'aft' ),
		'manage_options',
		AFT_SPAM_STUDENT_PAGE,
		'aft_spam_student_render_admin_page'
	);
}
add_action( 'admin_menu', 'aft_spam_student_admin_menu', 30 );

/** Process the destructive selection before rendering the review page. */
function aft_spam_student_process_delete() {
	if ( ! is_admin() || ! isset( $_POST['aft_spam_student_delete'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to delete these accounts.', 'aft' ) );
	}
	check_admin_referer( 'aft_spam_student_delete' );

	$ids     = isset( $_POST['user_ids'] ) && is_array( $_POST['user_ids'] ) ? array_map( 'absint', $_POST['user_ids'] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$deleted = 0;
	$failed  = 0;
	foreach ( array_unique( array_filter( $ids ) ) as $user_id ) {
		if ( aft_spam_student_delete_user_and_content( $user_id ) ) {
			++$deleted;
		} else {
			++$failed;
		}
	}

	$redirect = add_query_arg(
		array(
			'page'         => AFT_SPAM_STUDENT_PAGE,
			'aft_deleted'  => $deleted,
			'aft_failed'   => $failed,
		),
		admin_url( 'admin.php' )
	);
	wp_safe_redirect( $redirect );
	exit;
}
add_action( 'admin_init', 'aft_spam_student_process_delete', 1 );

/** Render the searchable candidate list and manual deletion controls. */
function aft_spam_student_render_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to view this page.', 'aft' ) );
	}

	$search            = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$registered_before = isset( $_GET['registered_before'] ) ? aft_spam_student_valid_date( wp_unslash( $_GET['registered_before'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$page              = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$per_page          = 50;
	$candidates        = aft_spam_student_find_candidates( $search, $registered_before );
	$total             = count( $candidates );
	$total_pages       = max( 1, (int) ceil( $total / $per_page ) );
	$page              = min( $page, $total_pages );
	$visible            = array_slice( $candidates, ( $page - 1 ) * $per_page, $per_page );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Spam Student Cleaner', 'aft' ); ?></h1>
		<div class="notice notice-warning inline">
			<p><strong><?php esc_html_e( 'Review before deleting:', 'aft' ); ?></strong> <?php esc_html_e( 'This tool lists student-like accounts with no Tutor enrollment record of any status. Deletion is permanent and removes the selected WordPress user, authored posts, and comments.', 'aft' ); ?></p>
		</div>
		<?php if ( isset( $_GET['aft_deleted'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible"><p><?php printf( esc_html__( 'Deleted %d account(s). %d account(s) were skipped because they no longer matched the safety checks.', 'aft' ), absint( $_GET['aft_deleted'] ), absint( $_GET['aft_failed'] ?? 0 ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?></p></div>
		<?php endif; ?>
		<form method="get" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap;margin:18px 0">
			<input type="hidden" name="page" value="<?php echo esc_attr( AFT_SPAM_STUDENT_PAGE ); ?>">
			<div><label for="aft-spam-search"><strong><?php esc_html_e( 'Search', 'aft' ); ?></strong></label><br><input id="aft-spam-search" type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Name, username or email"></div>
			<div><label for="aft-spam-before"><strong><?php esc_html_e( 'Registered on or before', 'aft' ); ?></strong></label><br><input id="aft-spam-before" type="date" name="registered_before" value="<?php echo esc_attr( $registered_before ); ?>"></div>
			<button class="button button-primary" type="submit"><?php esc_html_e( 'Find candidates', 'aft' ); ?></button>
		</form>
		<p><strong><?php echo esc_html( number_format_i18n( $total ) ); ?></strong> <?php esc_html_e( 'matching student-like account(s) found. Privileged roles and accounts with any Tutor enrollment record are excluded.', 'aft' ); ?></p>
		<?php if ( $visible ) : ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=' . AFT_SPAM_STUDENT_PAGE ) ); ?>" onsubmit="return confirm('Permanently delete the selected student account(s) and all authored content? This cannot be undone.');">
			<?php wp_nonce_field( 'aft_spam_student_delete' ); ?>
			<input type="hidden" name="aft_spam_student_delete" value="1">
			<input type="hidden" name="s" value="<?php echo esc_attr( $search ); ?>">
			<input type="hidden" name="registered_before" value="<?php echo esc_attr( $registered_before ); ?>">
			<p><button class="button button-link-delete" type="submit"><?php esc_html_e( 'Delete selected permanently', 'aft' ); ?></button></p>
			<table class="widefat striped">
				<thead><tr><th class="check-column"><input type="checkbox" id="aft-spam-select-all" aria-label="<?php esc_attr_e( 'Select all visible candidates', 'aft' ); ?>"></th><th><?php esc_html_e( 'Student', 'aft' ); ?></th><th><?php esc_html_e( 'Email', 'aft' ); ?></th><th><?php esc_html_e( 'Roles', 'aft' ); ?></th><th><?php esc_html_e( 'Registered', 'aft' ); ?></th><th><?php esc_html_e( 'Age', 'aft' ); ?></th></tr></thead>
				<tbody>
				<?php foreach ( $visible as $candidate ) : ?>
					<tr>
						<th class="check-column"><input type="checkbox" class="aft-spam-row" name="user_ids[]" value="<?php echo esc_attr( $candidate->ID ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Select %s', 'aft' ), $candidate->display_name ) ); ?>"></th>
						<td><strong><?php echo esc_html( $candidate->display_name ); ?></strong><br><code><?php echo esc_html( $candidate->user_login ); ?></code></td>
						<td><?php echo esc_html( $candidate->user_email ); ?></td>
						<td><?php echo esc_html( implode( ', ', (array) $candidate->roles ) ); ?></td>
						<td><?php echo esc_html( mysql2date( get_option( 'date_format' ), $candidate->user_registered ) ); ?></td>
						<td><?php echo esc_html( human_time_diff( strtotime( $candidate->user_registered ), current_time( 'timestamp' ) ) ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</form>
		<script>
		(function(){var all=document.getElementById('aft-spam-select-all');if(all){all.addEventListener('change',function(){document.querySelectorAll('.aft-spam-row').forEach(function(box){box.checked=all.checked;});});}})();
		</script>
		<?php else : ?>
			<p><?php esc_html_e( 'No matching student-like accounts were found.', 'aft' ); ?></p>
		<?php endif; ?>
		<?php if ( $total_pages > 1 ) : ?>
			<div class="tablenav"><div class="tablenav-pages">
				<?php
				$base = add_query_arg(
					array(
						'page'              => AFT_SPAM_STUDENT_PAGE,
						's'                 => $search,
						'registered_before' => $registered_before,
						'paged'             => '%#%',
					),
					admin_url( 'admin.php' )
				);
				echo wp_kses_post( paginate_links( array( 'base' => $base, 'format' => '', 'current' => $page, 'total' => $total_pages ) ) );
				?>
			</div></div>
		<?php endif; ?>
	</div>
	<?php
}
