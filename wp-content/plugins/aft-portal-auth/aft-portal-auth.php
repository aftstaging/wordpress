<?php
/**
 * Plugin Name:       AFT Portal Auth
 * Plugin URI:        https://github.com/aftstaging/wordpress
 * Description:       Read-only credential and enrolment verification endpoint used by the AFT exam portal. Exposes a single shared-secret protected REST route and never writes to WordPress.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Accountants for Tomorrow
 * License:           GPL-2.0-or-later
 * Text Domain:       aft-portal-auth
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AFT_PORTAL_AUTH_VERSION', '1.0.0' );
define( 'AFT_PORTAL_AUTH_NAMESPACE', 'aft-portal/v1' );
define( 'AFT_PORTAL_AUTH_MAX_ATTEMPTS', 10 );
define( 'AFT_PORTAL_AUTH_WINDOW', MINUTE_IN_SECONDS );

/**
 * Shared secret used by the exam portal.
 * Prefer the AFT_PORTAL_SECRET constant (wp-config.php) and fall back to the
 * `aft_portal_secret` option so the secret can also be set through wp-cli.
 *
 * @return string Empty string when the endpoint is not configured.
 */
function aft_portal_secret() {
	if ( defined( 'AFT_PORTAL_SECRET' ) && is_string( AFT_PORTAL_SECRET ) && '' !== trim( AFT_PORTAL_SECRET ) ) {
		return trim( AFT_PORTAL_SECRET );
	}

	$option = get_option( 'aft_portal_secret', '' );

	return is_string( $option ) ? trim( $option ) : '';
}

/**
 * Constant-time secret comparison for a request.
 *
 * @param WP_REST_Request $request Request.
 * @return bool
 */
function aft_portal_secret_matches( WP_REST_Request $request ) {
	$secret = aft_portal_secret();

	if ( '' === $secret ) {
		return false;
	}

	$provided = (string) $request->get_header( 'x-aft-portal-secret' );

	return '' !== $provided && hash_equals( $secret, $provided );
}

/**
 * Per-IP attempt window so the endpoint cannot be used for credential stuffing.
 *
 * @return bool True when another attempt is allowed.
 */
function aft_portal_rate_limit_ok() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) wp_unslash( $_SERVER['REMOTE_ADDR'] ) : 'unknown';
	$key = 'aft_portal_rl_' . md5( $ip );
	$hits = (int) get_transient( $key );

	if ( $hits >= AFT_PORTAL_AUTH_MAX_ATTEMPTS ) {
		return false;
	}

	set_transient( $key, $hits + 1, AFT_PORTAL_AUTH_WINDOW );

	return true;
}

/**
 * Whether the user holds at least one active Tutor LMS enrolment.
 * Mirrors Tutor's own definition: a `tutor_enrolled` post in `completed` status.
 *
 * @param int $user_id User ID.
 * @return array{active:bool,count:int,pending:int}
 */
function aft_portal_enrolment_state( $user_id ) {
	global $wpdb;

	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT post_status, COUNT(*) AS total
			 FROM {$wpdb->posts}
			 WHERE post_type = %s
			   AND post_author = %d
			   AND post_parent > 0
			 GROUP BY post_status",
			'tutor_enrolled',
			(int) $user_id
		)
	);

	$completed = 0;
	$pending   = 0;

	foreach ( (array) $rows as $row ) {
		if ( 'completed' === $row->post_status ) {
			$completed += (int) $row->total;
		} elseif ( 'pending' === $row->post_status ) {
			$pending += (int) $row->total;
		}
	}

	return array(
		'active'  => $completed > 0,
		'count'   => $completed,
		'pending' => $pending,
	);
}

/**
 * Course titles the user is actively enrolled in (max 20, newest first).
 *
 * @param int $user_id User ID.
 * @return array<int,array<string,mixed>>
 */
function aft_portal_enrolled_courses( $user_id ) {
	global $wpdb;

	$course_ids = array_map(
		'absint',
		$wpdb->get_col(
			$wpdb->prepare(
				"SELECT post_parent
				 FROM {$wpdb->posts}
				 WHERE post_type = %s
				   AND post_author = %d
				   AND post_parent > 0
				 ORDER BY post_date DESC
				 LIMIT 20",
				'tutor_enrolled',
				(int) $user_id
			)
		)
	);

	$course_ids = array_values( array_unique( array_filter( $course_ids ) ) );
	$courses    = array();

	foreach ( $course_ids as $course_id ) {
		$title = get_the_title( $course_id );
		if ( '' === $title ) {
			continue;
		}

		$courses[] = array(
			'id'    => $course_id,
			'title' => $title,
		);
	}

	return $courses;
}

/**
 * AFT student status meta written by the aft-tutor-private MU plugin.
 *
 * @param int $user_id User ID.
 * @return string One of current|graduated|''.
 */
function aft_portal_student_status( $user_id ) {
	if ( function_exists( 'aft_get_student_status' ) ) {
		return (string) aft_get_student_status( $user_id );
	}

	if ( defined( 'AFT_STUDENT_STATUS_META' ) ) {
		return (string) get_user_meta( $user_id, AFT_STUDENT_STATUS_META, true );
	}

	return '';
}

/**
 * JSON response helper that is never cached.
 *
 * @param array<string,mixed> $data Payload.
 * @param int                 $code HTTP status.
 * @return WP_REST_Response
 */
function aft_portal_response( array $data, $code ) {
	$response = new WP_REST_Response( $data, $code );
	$response->header( 'Cache-Control', 'no-store, no-cache, must-revalidate' );

	return $response;
}

/**
 * POST /aft-portal/v1/verify
 *
 * Body: { "identity": "login or e-mail", "password": "..." }
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function aft_portal_verify( WP_REST_Request $request ) {
	if ( '' === aft_portal_secret() ) {
		return aft_portal_response(
			array(
				'ok'   => false,
				'code' => 'not_configured',
			),
			503
		);
	}

	if ( ! aft_portal_secret_matches( $request ) ) {
		return aft_portal_response(
			array(
				'ok'   => false,
				'code' => 'invalid_secret',
			),
			401
		);
	}

	if ( ! aft_portal_rate_limit_ok() ) {
		return aft_portal_response(
			array(
				'ok'   => false,
				'code' => 'rate_limited',
			),
			429
		);
	}

	$identity = trim( (string) $request->get_param( 'identity' ) );
	$password = (string) $request->get_param( 'password' );

	if ( '' === $identity || '' === $password ) {
		return aft_portal_response(
			array(
				'ok'   => false,
				'code' => 'missing_credentials',
			),
			400
		);
	}

	$user = get_user_by( 'login', $identity );

	if ( ! $user ) {
		$user = get_user_by( 'email', $identity );
	}

	if ( ! $user ) {
		// Burn comparable CPU time so unknown accounts are not detectable by timing.
		wp_check_password( $password, wp_hash_password( wp_generate_password( 16 ) ), 0 );

		return aft_portal_response(
			array(
				'ok'   => false,
				'code' => 'invalid_credentials',
			),
			401
		);
	}

	if ( ! wp_check_password( $password, $user->user_pass, $user->ID ) ) {
		return aft_portal_response(
			array(
				'ok'   => false,
				'code' => 'invalid_credentials',
			),
			401
		);
	}

	$enrolment = aft_portal_enrolment_state( $user->ID );

	if ( ! $enrolment['active'] ) {
		return aft_portal_response(
			array(
				'ok'            => false,
				'code'          => 'not_enrolled',
				'pending_count' => $enrolment['pending'],
			),
			403
		);
	}

	$avatar = '';

	if ( function_exists( 'get_avatar_url' ) ) {
		$avatar = (string) get_avatar_url( $user->ID );
	}

	return aft_portal_response(
		array(
			'ok'         => true,
			'user'       => array(
				'id'             => (int) $user->ID,
				'login'          => $user->user_login,
				'email'          => $user->user_email,
				'first_name'     => (string) get_user_meta( $user->ID, 'first_name', true ),
				'last_name'      => (string) get_user_meta( $user->ID, 'last_name', true ),
				'display_name'   => $user->display_name,
				'avatar_url'     => $avatar,
				'registered'     => $user->user_registered,
				'student_status' => aft_portal_student_status( $user->ID ),
			),
			'enrolment'  => array(
				'active'  => $enrolment['active'],
				'count'   => $enrolment['count'],
				'pending' => $enrolment['pending'],
				'courses' => aft_portal_enrolled_courses( $user->ID ),
			),
		),
		200
	);
}

/**
 * GET /aft-portal/v1/ping — shared-secret health check.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function aft_portal_ping( WP_REST_request $request ) {
	if ( ! aft_portal_secret_matches( $request ) ) {
		return aft_portal_response(
			array(
				'ok'   => false,
				'code' => 'invalid_secret',
			),
			401
		);
	}

	return aft_portal_response(
		array(
			'ok'      => true,
			'site'    => (string) get_bloginfo( 'name' ),
			'version' => AFT_PORTAL_AUTH_VERSION,
		),
		200
	);
}

/**
 * Register the read-only routes.
 */
function aft_portal_register_routes() {
	register_rest_route(
		AFT_PORTAL_AUTH_NAMESPACE,
		'/verify',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'aft_portal_verify',
			'permission_callback' => '__return_true',
		)
	);

	register_rest_route(
		AFT_PORTAL_AUTH_NAMESPACE,
		'/ping',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'aft_portal_ping',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'aft_portal_register_routes' );
