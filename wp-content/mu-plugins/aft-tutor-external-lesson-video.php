<?php
/**
 * Plugin Name: AFT Tutor External Lesson Video
 * Description: Adds Microsoft Teams/Stream, Zoom and TeamViewer lesson video sources, protected lesson access passwords, and in-page provider/direct-media rendering to Tutor LMS.
 * Version: 1.0.3
 * Author: Accountants For Tomorrow
 *
 * Deployed as an AFT MU-plugin. Tutor and Tutor Pro source files are not
 * modified, so this integration survives controlled private-fork updates.
 */

defined( 'ABSPATH' ) || exit;

/** Video sources shown by the Tutor lesson builder. */
function aft_external_lesson_video_sources() {
	return array(
		'microsoft_teams' => array(
			'title' => __( 'Microsoft Teams / Stream', 'aft' ),
			'icon'  => 'video',
		),
		'zoom' => array(
			'title' => __( 'Zoom', 'aft' ),
			'icon'  => 'video',
		),
		'teamviewer' => array(
			'title' => __( 'TeamViewer', 'aft' ),
			'icon'  => 'video',
		),
	);
}

/** Add provider names to Tutor's native source list. */
function aft_external_lesson_video_source_list( $sources ) {
	return array_merge( (array) $sources, aft_external_lesson_video_sources() );
}
add_filter( 'tutor_preferred_video_sources', 'aft_external_lesson_video_source_list', 20 );

/** Make the custom sources valid for Tutor course-level video validation too. */
function aft_external_lesson_video_supported_option( $sources ) {
	$sources = is_array( $sources ) ? $sources : ( $sources ? array( $sources ) : array() );
	$required = array_merge( array( 'external_url' ), array_keys( aft_external_lesson_video_sources() ) );
	return array_values( array_unique( array_merge( $sources, $required ) ) );
}
add_filter( 'supported_video_sources', 'aft_external_lesson_video_supported_option', 20 );

/** Ensure the new sources are available in the current course-builder payload. */
function aft_external_lesson_video_builder_data( $data ) {
	if ( ! isset( $data['supported_video_sources'] ) || ! is_array( $data['supported_video_sources'] ) ) {
		$data['supported_video_sources'] = array();
	}

	foreach ( aft_external_lesson_video_sources() as $value => $source ) {
		$exists = false;
		foreach ( $data['supported_video_sources'] as $available ) {
			if ( isset( $available['value'] ) && $value === $available['value'] ) {
				$exists = true;
				break;
			}
		}
		if ( ! $exists ) {
			$data['supported_video_sources'][] = array(
				'label' => $source['title'],
				'value' => $value,
			);
		}
	}

	return $data;
}
add_filter( 'tutor_course_builder_localized_data', 'aft_external_lesson_video_builder_data', 20 );

/** Encrypt a provider password for storage; it is never sent to students. */
function aft_external_lesson_video_encrypt( $value ) {
	$value = (string) $value;
	if ( '' === $value || ! function_exists( 'openssl_encrypt' ) ) {
		return '';
	}

	$key = hash( 'sha256', wp_salt( 'auth' ), true );
	$iv  = substr( hash( 'sha256', wp_salt( 'secure_auth' ), true ), 0, 16 );
	return base64_encode( openssl_encrypt( $value, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv ) );
}

/** Save the two password modes when Tutor saves or creates a lesson. */
function aft_external_lesson_video_save_security( $lesson_id ) {
	$lesson_id = absint( $lesson_id );
	if ( ! $lesson_id || ! current_user_can( 'edit_post', $lesson_id ) ) {
		return;
	}

	$has_provider_field = array_key_exists( 'aft_video_provider_password', $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$has_access_field   = array_key_exists( 'aft_lesson_access_password', $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( ! $has_provider_field && ! $has_access_field ) {
		return;
	}

	$provider_password = $has_provider_field ? sanitize_text_field( wp_unslash( $_POST['aft_video_provider_password'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$access_password   = $has_access_field ? (string) wp_unslash( $_POST['aft_lesson_access_password'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$clear_provider    = isset( $_POST['aft_clear_provider_password'] ) && '1' === (string) $_POST['aft_clear_provider_password']; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$clear_access      = isset( $_POST['aft_clear_lesson_access_password'] ) && '1' === (string) $_POST['aft_clear_lesson_access_password']; // phpcs:ignore WordPress.Security.NonceVerification.Missing

	if ( $clear_provider ) {
		delete_post_meta( $lesson_id, '_aft_video_provider_password' );
	} elseif ( '' !== $provider_password ) {
		$encrypted = aft_external_lesson_video_encrypt( $provider_password );
		if ( $encrypted ) {
			update_post_meta( $lesson_id, '_aft_video_provider_password', $encrypted );
		}
	}

	if ( $clear_access ) {
		delete_post_meta( $lesson_id, '_aft_lesson_access_password' );
	} elseif ( '' !== $access_password ) {
		update_post_meta( $lesson_id, '_aft_lesson_access_password', wp_hash_password( $access_password ) );
	}
}
add_action( 'tutor/lesson/created', 'aft_external_lesson_video_save_security', 20 );
add_action( 'tutor/lesson_update/after', 'aft_external_lesson_video_save_security', 20 );

/** Allow the builder to know that protected credentials exist without exposing them. */
function aft_external_lesson_video_details_response( $data, $lesson_id ) {
	$data['aft_video_provider_password_set'] = (bool) get_post_meta( $lesson_id, '_aft_video_provider_password', true );
	$data['aft_lesson_access_password_set']  = (bool) get_post_meta( $lesson_id, '_aft_lesson_access_password', true );
	return $data;
}
add_filter( 'tutor_lesson_details_response', 'aft_external_lesson_video_details_response', 20, 2 );

/** Return the provider host allowlist for safe in-page frames. */
function aft_external_lesson_video_allowed_host( $host, $provider ) {
	$host     = strtolower( (string) $host );
	$provider = sanitize_key( $provider );
	$hosts    = array(
		'microsoft_teams' => array( 'teams.microsoft.com', 'teams.live.com', 'microsoft.com', 'sharepoint.com', 'stream.microsoft.com', 'microsoftstream.com', 'office.com', 'office365.com', 'outlook.com', '1drv.ms' ),
		'zoom'            => array( 'zoom.us', 'zoom.com' ),
		'teamviewer'      => array( 'teamviewer.com' ),
	);

	foreach ( $hosts[ $provider ] ?? array() as $allowed ) {
		if ( $host === $allowed || ( strlen( $host ) > strlen( $allowed ) && substr( $host, -strlen( '.' . $allowed ) ) === '.' . $allowed ) ) {
			return true;
		}
	}
	return false;
}

/** Return the short-lived unlock key for one user and lesson. */
function aft_external_lesson_video_unlock_key( $lesson_id, $user_id = 0 ) {
	$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
	return 'aft_lesson_unlock_' . md5( wp_salt( 'auth' ) . '|' . $user_id . '|' . absint( $lesson_id ) );
}

function aft_external_lesson_video_is_unlocked( $lesson_id, $user_id = 0 ) {
	return (bool) get_transient( aft_external_lesson_video_unlock_key( $lesson_id, $user_id ) );
}

/** Student-side AJAX endpoint for the optional AFT lesson password gate. */
function aft_external_lesson_video_unlock_ajax() {
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Please sign in to unlock this lesson.', 'aft' ) ), 403 );
	}

	$lesson_id = isset( $_POST['lesson_id'] ) ? absint( $_POST['lesson_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	check_ajax_referer( 'aft_unlock_lesson_' . $lesson_id, 'nonce' );

	if ( ! $lesson_id || ! function_exists( 'tutor_utils' ) || get_post_type( $lesson_id ) !== tutor()->lesson_post_type || ( ! current_user_can( 'manage_options' ) && ! tutor_utils()->has_enrolled_content_access( 'lesson', $lesson_id ) ) ) {
		wp_send_json_error( array( 'message' => __( 'You do not have access to this lesson.', 'aft' ) ), 403 );
	}

	$password = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$hash     = get_post_meta( $lesson_id, '_aft_lesson_access_password', true );
	if ( ! $lesson_id || ! $hash || ! wp_check_password( $password, $hash ) ) {
		wp_send_json_error( array( 'message' => __( 'The lesson password is incorrect.', 'aft' ) ), 403 );
	}

	set_transient( aft_external_lesson_video_unlock_key( $lesson_id ), true, HOUR_IN_SECONDS );
	wp_send_json_success( array( 'message' => __( 'Lesson unlocked.', 'aft' ) ) );
}
add_action( 'wp_ajax_aft_unlock_lesson_video', 'aft_external_lesson_video_unlock_ajax' );

/** Render a password gate without exposing the provider credential. */
function aft_external_lesson_video_password_gate( $lesson_id ) {
	$nonce = wp_create_nonce( 'aft_unlock_lesson_' . absint( $lesson_id ) );
	ob_start();
	?>
	<div class="aft-lesson-video-gate" data-aft-lesson-id="<?php echo esc_attr( $lesson_id ); ?>" data-aft-nonce="<?php echo esc_attr( $nonce ); ?>">
		<h3><?php esc_html_e( 'Protected lesson video', 'aft' ); ?></h3>
		<p><?php esc_html_e( 'Enter the lesson password to play this video.', 'aft' ); ?></p>
		<form class="aft-lesson-video-gate-form">
			<label><span class="screen-reader-text"><?php esc_html_e( 'Lesson password', 'aft' ); ?></span><input type="password" name="password" autocomplete="off" required></label>
			<button type="submit"><?php esc_html_e( 'Unlock video', 'aft' ); ?></button>
			<span class="aft-lesson-video-gate-message" role="status"></span>
		</form>
	</div>
	<style>
		.aft-lesson-video-gate{padding:32px;text-align:center;background:#f7f8fa;border:1px solid #dfe3e8;border-radius:8px}
		.aft-lesson-video-gate form{display:flex;justify-content:center;gap:8px;flex-wrap:wrap}
		.aft-lesson-video-gate input{min-width:220px;padding:10px}
		.aft-lesson-video-gate button{padding:10px 16px;border:0;border-radius:4px;background:#1d4ed8;color:#fff;cursor:pointer}
		.aft-lesson-video-gate-message{width:100%;color:#b42318}
	</style>
	<script>
	(function(){var gate=document.querySelector('.aft-lesson-video-gate:not([data-aft-bound])');if(!gate){return;}gate.setAttribute('data-aft-bound','1');var form=gate.querySelector('form'),msg=gate.querySelector('.aft-lesson-video-gate-message');form.addEventListener('submit',function(event){event.preventDefault();msg.textContent='';var body=new FormData();body.append('action','aft_unlock_lesson_video');body.append('lesson_id',gate.getAttribute('data-aft-lesson-id'));body.append('nonce',gate.getAttribute('data-aft-nonce'));body.append('password',form.password.value);fetch(window.ajaxurl||'/wp-admin/admin-ajax.php',{method:'POST',credentials:'same-origin',body:body}).then(function(response){return response.json();}).then(function(result){if(result.success){window.location.reload();}else{msg.textContent=result.data&&result.data.message?result.data.message:'Unable to unlock the video.';}}).catch(function(){msg.textContent='Unable to unlock the video.';});});})();
	</script>
	<?php
	return ob_get_clean();
}

/** Render direct media natively and supported provider links in an iframe. */
function aft_external_lesson_video_render( $output ) {
	if ( ! function_exists( 'tutor_utils' ) ) {
		return $output;
	}

	$video_info = tutor_utils()->get_video_info();
	if ( ! is_object( $video_info ) ) {
		return $output;
	}

	$source = sanitize_key( (string) ( $video_info->source ?? '' ) );
	$sources = aft_external_lesson_video_sources();
	if ( ! isset( $sources[ $source ] ) ) {
		return $output;
	}

	$url = isset( $video_info->{'source_' . $source} ) ? esc_url_raw( html_entity_decode( (string) $video_info->{'source_' . $source}, ENT_QUOTES, 'UTF-8' ) ) : '';
	if ( ! $url || ! wp_http_validate_url( $url ) ) {
		return '<div class="aft-lesson-video-error">' . esc_html__( 'The external video URL is missing or invalid.', 'aft' ) . '</div>';
	}

	$lesson_id = get_the_ID();
	if ( get_post_meta( $lesson_id, '_aft_lesson_access_password', true ) && ! aft_external_lesson_video_is_unlocked( $lesson_id ) ) {
		return aft_external_lesson_video_password_gate( $lesson_id );
	}

	$parts = wp_parse_url( $url );
	$host  = isset( $parts['host'] ) ? $parts['host'] : '';
	$path  = isset( $parts['path'] ) ? strtolower( $parts['path'] ) : '';
	$ext   = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
	$mime  = array(
		'mp4'  => 'video/mp4',
		'webm' => 'video/webm',
		'ogg'  => 'video/ogg',
		'm3u8' => 'application/vnd.apple.mpegurl',
	);

	ob_start();
	if ( isset( $mime[ $ext ] ) ) {
		?>
		<div class="aft-external-lesson-video aft-external-lesson-video-native">
			<video class="tutorPlayer" controls playsinline preload="metadata">
				<source src="<?php echo esc_url( $url ); ?>" type="<?php echo esc_attr( $mime[ $ext ] ); ?>">
				<?php esc_html_e( 'Your browser does not support this video format.', 'aft' ); ?>
			</video>
		</div>
		<?php
	} elseif ( aft_external_lesson_video_allowed_host( $host, $source ) ) {
		$frame_note = __( 'If this provider prevents embedding, its security policy may require the provider page instead of an in-page player.', 'aft' );
		if ( 'zoom' === $source ) {
			$frame_note = __( 'If Zoom requires a passcode, enter it in the Zoom player. The optional AFT lesson password is a separate site-level gate.', 'aft' );
		} elseif ( 'microsoft_teams' === $source ) {
			$frame_note = __( 'A SharePoint or Stream link may require Microsoft sign-in or an embed-enabled sharing permission. Those provider restrictions cannot be bypassed by the lesson page.', 'aft' );
		}
		?>
		<div class="aft-external-lesson-video aft-external-lesson-video-frame">
			<iframe src="<?php echo esc_url( $url ); ?>" title="<?php echo esc_attr( $sources[ $source ]['title'] ); ?>" loading="lazy" allow="autoplay; fullscreen; picture-in-picture; microphone; camera; display-capture" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
			<p class="aft-external-lesson-video-fallback"><small><?php echo esc_html( $frame_note ); ?></small></p>
		</div>
		<?php
	} else {
		?>
		<div class="aft-external-lesson-video-error">
			<p><?php esc_html_e( 'This provider URL cannot be embedded safely on this site.', 'aft' ); ?></p>
			<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open provider link', 'aft' ); ?></a>
		</div>
		<?php
	}
	?>
	<style>
		.aft-external-lesson-video{width:100%;background:#000;border-radius:6px;overflow:hidden}
		.aft-external-lesson-video-native video{display:block;width:100%;max-height:75vh}
		.aft-external-lesson-video-frame iframe{display:block;width:100%;min-height:520px;border:0}
		.aft-external-lesson-video-fallback{margin:0;padding:8px 12px;background:#fff;color:#56616f}
		.aft-external-lesson-video-error{padding:20px;background:#fff4f2;border:1px solid #f0b7b0;color:#8d1d13}
	</style>
	<?php
	return ob_get_clean();
}
add_filter( 'tutor_lesson/single/video', 'aft_external_lesson_video_render', 5 );

/** Add the password fields below Tutor's native Video control in the builder. */
function aft_external_lesson_video_builder_assets() {
	$is_builder = false;
	if ( is_admin() ) {
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$is_builder = in_array( $page, array( 'create-course', 'tutor' ), true );
	} else {
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$is_builder = false !== strpos( $request_uri, 'create-course' ) || 'create-course' === get_query_var( 'tutor_dashboard_page' );
	}
	if ( ! $is_builder ) {
		return;
	}
	?>
	<style>
		#aft-external-lesson-video-security{margin-top:12px;padding:14px;background:#f7f8fa;border:1px solid #dfe3e8;border-radius:8px}
		#aft-external-lesson-video-security h4{margin:0 0 6px}
		#aft-external-lesson-video-security p{margin:4px 0 10px;color:#687386;font-size:12px}
		#aft-external-lesson-video-security label{display:block;margin-top:9px;font-weight:600}
		#aft-external-lesson-video-security input[type=password]{display:block;width:100%;max-width:520px;margin-top:5px}
		#aft-external-lesson-video-security .aft-security-check{display:flex;gap:6px;align-items:center;margin-top:8px;font-weight:400}
	</style>
	<script>
	(function(){
		var state=window.AFTExternalLessonVideoState=window.AFTExternalLessonVideoState||{provider:'',access:'',clearProvider:false,clearAccess:false};
		function decodeUrlEntities(value){var previous='';var textarea=document.createElement('textarea');while(value!==previous){previous=value;textarea.innerHTML=value;value=textarea.value;}return value;}
		if(!window._aftExternalLessonVideoUrlNormalizer){document.addEventListener('input',function(event){var field=event.target;if(!field||!('value' in field)||!/^https?:\\/\\//i.test(field.value)){return;}var decoded=decodeUrlEntities(field.value);if(decoded===field.value){return;}var prototype=field instanceof HTMLTextAreaElement?HTMLTextAreaElement.prototype:HTMLInputElement.prototype;var setter=Object.getOwnPropertyDescriptor(prototype,'value').set;setter.call(field,decoded);field.dispatchEvent(new Event('input',{bubbles:true}));},true);window._aftExternalLessonVideoUrlNormalizer=true;}
		function makePanel(){
			if(document.getElementById('aft-external-lesson-video-security')){return true;}
			var anchor=document.querySelector('[data-cy="add-from-url"]')||document.querySelector('[data-cy="media-preview"]')||document.querySelector('[data-cy="upload-media"]');
			if(!anchor){return false;}
			var parent=anchor.getAttribute('data-cy')==='media-preview'?anchor.parentNode:anchor.closest('div');
			if(!parent||!parent.parentNode){return false;}
			var panel=document.createElement('section');panel.id='aft-external-lesson-video-security';
			panel.innerHTML='<h4>External lesson video access</h4><p>Choose Microsoft Teams / Stream, Zoom, or TeamViewer from Add from URL above, then paste its link in the native Tutor video field. Leave a provider password blank for links that need no password. Password-protected Zoom links can show Zoom\'s own passcode prompt inside the embedded player when Zoom permits embedding.</p><label>Provider password<input id="aft-video-provider-password" type="password" autocomplete="new-password" placeholder="Optional; encrypted for administration"></label><label class="aft-security-check"><input id="aft-clear-provider-password" type="checkbox"> Clear saved provider password</label><label>AFT lesson access password<input id="aft-lesson-access-password" type="password" autocomplete="new-password" placeholder="Optional password students enter on this site"></label><label class="aft-security-check"><input id="aft-clear-lesson-access-password" type="checkbox"> Clear saved AFT lesson password</label>';
			parent.parentNode.insertBefore(panel,parent.nextSibling);
			var provider=document.getElementById('aft-video-provider-password'),access=document.getElementById('aft-lesson-access-password'),clearProvider=document.getElementById('aft-clear-provider-password'),clearAccess=document.getElementById('aft-clear-lesson-access-password');
			provider.value=state.provider;access.value=state.access;clearProvider.checked=state.clearProvider;clearAccess.checked=state.clearAccess;
			provider.addEventListener('input',function(){state.provider=provider.value;});access.addEventListener('input',function(){state.access=access.value;});clearProvider.addEventListener('change',function(){state.clearProvider=clearProvider.checked;});clearAccess.addEventListener('change',function(){state.clearAccess=clearAccess.checked;});
			return true;
		}
		function values(){var provider=document.getElementById('aft-video-provider-password'),access=document.getElementById('aft-lesson-access-password'),clearProvider=document.getElementById('aft-clear-provider-password'),clearAccess=document.getElementById('aft-clear-lesson-access-password');return{provider:provider?provider.value:state.provider,access:access?access.value:state.access,clearProvider:clearProvider&&clearProvider.checked,clearAccess:clearAccess&&clearAccess.checked};}
		function isSaveBody(body){if(typeof body==='string'){return body.indexOf('tutor_save_lesson')>-1;}if(window.FormData&&body instanceof FormData){return body.get('action')==='tutor_save_lesson';}if(window.URLSearchParams&&body instanceof URLSearchParams){return body.get('action')==='tutor_save_lesson';}return body&&body.action==='tutor_save_lesson';}
		function addFields(body){var v=values();if(typeof body==='string'){return body+'&aft_video_provider_password='+encodeURIComponent(v.provider||'')+'&aft_lesson_access_password='+encodeURIComponent(v.access||'')+'&aft_clear_provider_password='+(v.clearProvider?'1':'0')+'&aft_clear_lesson_access_password='+(v.clearAccess?'1':'0');}if(window.FormData&&body instanceof FormData){body.set('aft_video_provider_password',v.provider||'');body.set('aft_lesson_access_password',v.access||'');body.set('aft_clear_provider_password',v.clearProvider?'1':'0');body.set('aft_clear_lesson_access_password',v.clearAccess?'1':'0');return body;}if(window.URLSearchParams&&body instanceof URLSearchParams){body.set('aft_video_provider_password',v.provider||'');body.set('aft_lesson_access_password',v.access||'');body.set('aft_clear_provider_password',v.clearProvider?'1':'0');body.set('aft_clear_lesson_access_password',v.clearAccess?'1':'0');return body;}if(body&&typeof body==='object'){body.aft_video_provider_password=v.provider||'';body.aft_lesson_access_password=v.access||'';body.aft_clear_provider_password=v.clearProvider?'1':'0';body.aft_clear_lesson_access_password=v.clearAccess?'1':'0';return JSON.stringify(body);}return body;}
		function install(){if(window.XMLHttpRequest&&!XMLHttpRequest.prototype._aftExternalLessonVideoBridge){var send=XMLHttpRequest.prototype.send;XMLHttpRequest.prototype.send=function(body){var url=this._aftExternalRequestUrl||'';if(/admin-ajax\\.php/.test(url)&&isSaveBody(body)){body=addFields(body);}return send.call(this,body);};var open=XMLHttpRequest.prototype.open;XMLHttpRequest.prototype.open=function(method,url){this._aftExternalRequestUrl=String(url||'');return open.apply(this,arguments);};XMLHttpRequest.prototype._aftExternalLessonVideoBridge=true;}if(window.fetch&&!window.fetch._aftExternalLessonVideoBridge){var fetch=window.fetch;window.fetch=function(input,init){var url=typeof input==='string'?input:(input&&input.url)||'';if(/admin-ajax\\.php/.test(url)&&init&&isSaveBody(init.body)){init.body=addFields(init.body);}return fetch.apply(this,arguments);};window.fetch._aftExternalLessonVideoBridge=true;}}
		makePanel();install();var observer=new MutationObserver(function(){makePanel();});observer.observe(document.body,{childList:true,subtree:true});
	})();
	</script>
	<?php
}
add_action( 'admin_footer', 'aft_external_lesson_video_builder_assets', 30 );
add_action( 'wp_footer', 'aft_external_lesson_video_builder_assets', 30 );
