<?php
/**
 * Plugin Name: AFT Migration Assistant
 * Description: Admin-only, browser-based WordPress migration export/import assistant. Downloads are sent to the administrator's local machine and uploads can be restored on another WordPress installation.
 * Version: 1.1.1
 * Author: AFT
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AFT_Migration_Assistant {
	const VERSION = '1.1.1';
	const NONCE   = 'aftm_migration_action';

	/** @var AFT_Migration_Assistant|null */
	private static $instance = null;

	/** @var int */
	private $batch_size = 250;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_post_aftm_download', array( $this, 'download' ) );
		add_action( 'admin_post_aftm_import_db', array( $this, 'import_db' ) );
		add_action( 'admin_post_aftm_import_content', array( $this, 'import_content' ) );
		add_action( 'admin_post_aftm_replace_urls', array( $this, 'replace_urls' ) );
		add_action( 'wp_ajax_aftm_upload_chunk', array( $this, 'ajax_upload_chunk' ) );
		add_action( 'wp_ajax_aftm_start_db_import', array( $this, 'ajax_start_db_import' ) );
		add_action( 'wp_ajax_aftm_start_content_import', array( $this, 'ajax_start_content_import' ) );
		add_action( 'wp_ajax_aftm_content_import_step', array( $this, 'ajax_content_import_step' ) );
	}

	public function admin_menu() {
		add_management_page(
			__( 'AFT Migration Assistant', 'aftm' ),
			__( 'AFT Migration', 'aftm' ),
			'manage_options',
			'aft-migration-assistant',
			array( $this, 'render_page' )
		);
	}

	private function authorize() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to use the migration assistant.', 'aftm' ), 403 );
		}
		check_admin_referer( self::NONCE );
		@set_time_limit( 0 );
	}

	private function redirect_notice( $message, $type = 'success' ) {
		$url = add_query_arg(
			array(
				'page'       => 'aft-migration-assistant',
				'aftm_notice' => $message,
				'aftm_type'  => sanitize_key( $type ),
			),
			admin_url( 'tools.php' )
		);
		wp_safe_redirect( $url );
		exit;
	}

	private function content_parts() {
		return array(
			'uploads'    => array(
				'label' => __( 'Media uploads', 'aftm' ),
				'path'  => 'uploads',
			),
			'themes'     => array(
				'label' => __( 'Themes', 'aftm' ),
				'path'  => 'themes',
			),
			'plugins'    => array(
				'label' => __( 'Plugins', 'aftm' ),
				'path'  => 'plugins',
			),
			'mu-plugins' => array(
				'label' => __( 'Must-use plugins', 'aftm' ),
				'path'  => 'mu-plugins',
			),
		);
	}

	private function download_url( $part ) {
		return wp_nonce_url(
			admin_url( 'admin-post.php?action=aftm_download&part=' . rawurlencode( $part ) ),
			self::NONCE
		);
	}

	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$parts       = $this->content_parts();
		$max_upload  = size_format( wp_max_upload_size() );
		$notice      = isset( $_GET['aftm_notice'] ) ? sanitize_text_field( wp_unslash( $_GET['aftm_notice'] ) ) : '';
		$notice_type = isset( $_GET['aftm_type'] ) && 'error' === sanitize_key( $_GET['aftm_type'] ) ? 'error' : 'success';
		$home        = home_url( '/' );
		?>
		<div class="wrap aftm-wrap">
			<h1><?php esc_html_e( 'AFT Migration Assistant', 'aftm' ); ?></h1>
			<?php if ( $notice ) : ?>
				<div class="notice notice-<?php echo esc_attr( $notice_type ); ?> is-dismissible"><p><?php echo esc_html( $notice ); ?></p></div>
			<?php endif; ?>

			<div class="notice notice-warning">
				<p><strong><?php esc_html_e( 'Use only while logged in as a trusted site administrator.', 'aftm' ); ?></strong></p>
				<p><?php esc_html_e( 'Exports are downloaded by your browser to your local machine. Nothing is sent to a third-party service. Keep the database dump and content archives private.', 'aftm' ); ?></p>
			</div>

			<h2><?php esc_html_e( 'Recommended migration sequence', 'aftm' ); ?></h2>
			<ol>
				<li><?php esc_html_e( 'Install and activate this same plugin on the source and destination WordPress sites.', 'aftm' ); ?></li>
				<li><?php esc_html_e( 'On the source, download the database and the content archives below. Your browser saves each file locally.', 'aftm' ); ?></li>
				<li><?php esc_html_e( 'On the destination, import the database first, supplying the source URL and the destination URL. Then import the content archives.', 'aftm' ); ?></li>
				<li><?php esc_html_e( 'Log in again with the source site administrator credentials if WordPress asks you to, then check permalinks, media, forms, email, and plugin settings.', 'aftm' ); ?></li>
			</ol>

			<h2><?php esc_html_e( '1. Download from the source site', 'aftm' ); ?></h2>
			<p><?php esc_html_e( 'Download the database and every content area that you need. The complete content bundle is convenient, while separate archives are useful when the site is large or the hosting upload limit is low.', 'aftm' ); ?></p>
			<p>
				<a class="button button-primary" href="<?php echo esc_url( $this->download_url( 'db' ) ); ?>"><?php esc_html_e( 'Download database (.sql.gz)', 'aftm' ); ?></a>
				<a class="button" href="<?php echo esc_url( $this->download_url( 'full' ) ); ?>"><?php esc_html_e( 'Download complete content bundle (.zip)', 'aftm' ); ?></a>
			</p>
			<table class="widefat striped" style="max-width:850px">
				<thead><tr><th><?php esc_html_e( 'Content area', 'aftm' ); ?></th><th><?php esc_html_e( 'Download', 'aftm' ); ?></th><th><?php esc_html_e( 'Destination', 'aftm' ); ?></th></tr></thead>
				<tbody>
				<?php foreach ( $parts as $key => $part ) : ?>
					<tr>
						<td><?php echo esc_html( $part['label'] ); ?></td>
						<td><a class="button" href="<?php echo esc_url( $this->download_url( $key ) ); ?>"><?php esc_html_e( 'Download archive', 'aftm' ); ?></a></td>
						<td><code>wp-content/<?php echo esc_html( $part['path'] ); ?>/</code></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<h2><?php esc_html_e( '2. Import the database on the destination', 'aftm' ); ?></h2>
			<p><?php esc_html_e( 'Importing the database replaces tables with the same names. Take a destination backup first. The URL replacement runs immediately after import and preserves serialized WordPress data.', 'aftm' ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" class="aftm-card aftm-upload-form" data-aftm-kind="db">
				<input type="hidden" name="action" value="aftm_import_db">
				<?php wp_nonce_field( self::NONCE ); ?>
				<table class="form-table" role="presentation">
					<tr><th><label for="aftm-db-file"><?php esc_html_e( 'Database dump', 'aftm' ); ?></label></th><td><input id="aftm-db-file" type="file" name="aftm_db" accept=".sql,.gz,.sql.gz" required><p class="description"><?php printf( esc_html__( 'Maximum upload shown by this server: %s. Increase the server upload limit or use a hosting file manager if necessary.', 'aftm' ), esc_html( $max_upload ) ); ?></p></td></tr>
					<tr><th><label for="aftm-source-url"><?php esc_html_e( 'Source site URL', 'aftm' ); ?></label></th><td><input class="regular-text" id="aftm-source-url" type="url" name="aftm_source_url" placeholder="https://visionpluss.co.za" required><p class="description"><?php esc_html_e( 'Use the URL that exists in the source database, without a trailing slash.', 'aftm' ); ?></p></td></tr>
					<tr><th><label for="aftm-destination-url"><?php esc_html_e( 'Destination site URL', 'aftm' ); ?></label></th><td><input class="regular-text" id="aftm-destination-url" type="url" name="aftm_destination_url" value="<?php echo esc_attr( untrailingslashit( $home ) ); ?>" required><p class="description"><?php esc_html_e( 'For the first stage, use the IP URL. Repeat the replacement later for the final domain after DNS cutover.', 'aftm' ); ?></p></td></tr>
				</table>
				<?php submit_button( __( 'Import database and replace URLs', 'aftm' ) ); ?>
				<p class="aftm-upload-status" aria-live="polite"></p>
			</form>

			<h2><?php esc_html_e( '3. Import content archives on the destination', 'aftm' ); ?></h2>
			<p><?php esc_html_e( 'Upload each archive you downloaded, or upload the complete content bundle once. Existing files with the same names are overwritten.', 'aftm' ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" class="aftm-card aftm-upload-form" data-aftm-kind="content">
				<input type="hidden" name="action" value="aftm_import_content">
				<?php wp_nonce_field( self::NONCE ); ?>
				<table class="form-table" role="presentation">
					<tr><th><label for="aftm-content-part"><?php esc_html_e( 'Archive type', 'aftm' ); ?></label></th><td><select id="aftm-content-part" name="aftm_part"><option value="full"><?php esc_html_e( 'Complete content bundle', 'aftm' ); ?></option><?php foreach ( $parts as $key => $part ) : ?><option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $part['label'] ); ?></option><?php endforeach; ?></select></td></tr>
					<tr><th><label for="aftm-content-file"><?php esc_html_e( 'Content archive', 'aftm' ); ?></label></th><td><input id="aftm-content-file" type="file" name="aftm_content" accept=".zip" required><p class="description"><?php printf( esc_html__( 'Maximum upload shown by this server: %s.', 'aftm' ), esc_html( $max_upload ) ); ?></p></td></tr>
				</table>
				<?php submit_button( __( 'Import content archive', 'aftm' ) ); ?>
				<p class="aftm-upload-status" aria-live="polite"></p>
			</form>

			<h2><?php esc_html_e( 'URL replacement only', 'aftm' ); ?></h2>
			<p><?php esc_html_e( 'Use this after DNS changes, or if the database was imported without replacement. It scans text and serialized values in the database and does not alter files.', 'aftm' ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="aftm-card">
				<input type="hidden" name="action" value="aftm_replace_urls">
				<?php wp_nonce_field( self::NONCE ); ?>
				<table class="form-table" role="presentation">
					<tr><th><label for="aftm-replace-source"><?php esc_html_e( 'Find URL', 'aftm' ); ?></label></th><td><input class="regular-text" id="aftm-replace-source" type="url" name="aftm_source_url" required></td></tr>
					<tr><th><label for="aftm-replace-destination"><?php esc_html_e( 'Replace with', 'aftm' ); ?></label></th><td><input class="regular-text" id="aftm-replace-destination" type="url" name="aftm_destination_url" value="<?php echo esc_attr( untrailingslashit( $home ) ); ?>" required></td></tr>
				</table>
				<?php submit_button( __( 'Replace URLs', 'aftm' ), 'secondary' ); ?>
			</form>
		</div>
		<script>
		(function () {
			'use strict';
			var cfg = {
				ajaxUrl: <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>,
				nonce: <?php echo wp_json_encode( wp_create_nonce( self::NONCE ) ); ?>,
				chunkSize: 1 * 1024 * 1024
			};
			function makeId() {
				if (window.crypto && window.crypto.getRandomValues) {
					var bytes = new Uint8Array(16);
					window.crypto.getRandomValues(bytes);
					return Array.prototype.map.call(bytes, function (b) { return ('0' + b.toString(16)).slice(-2); }).join('');
				}
				var fallback = (String(Date.now()) + String(Math.random()).replace('.', '')).replace(/[^a-f0-9]/gi, '');
				while (fallback.length < 32) { fallback += '0'; }
				return fallback.slice(0, 32).toLowerCase();
			}
			function wait(ms) { return new Promise(function (resolve) { window.setTimeout(resolve, ms); }); }
			async function request(fields, file) {
				var body = new FormData();
				Object.keys(fields).forEach(function (key) { body.append(key, fields[key]); });
				if (file) { body.append('chunk', file, 'chunk.bin'); }
				var response = await fetch(cfg.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body });
				var text = await response.text();
				var json;
				try { json = JSON.parse(text); } catch (e) { throw new Error('The server closed the connection or returned a non-JSON error.'); }
				if (!response.ok || !json.success) {
					throw new Error(json && json.data && json.data.message ? json.data.message : 'The server rejected the migration request.');
				}
				return json.data || {};
			}
			async function uploadInChunks(file, status) {
				var id = makeId();
				var total = Math.max(1, Math.ceil(file.size / cfg.chunkSize));
				for (var index = 0; index < total; index++) {
					var start = index * cfg.chunkSize;
					var end = Math.min(file.size, start + cfg.chunkSize);
					status.textContent = 'Uploading ' + Math.round((index / total) * 100) + '% — part ' + (index + 1) + ' of ' + total;
					await request({ action: 'aftm_upload_chunk', _ajax_nonce: cfg.nonce, upload_id: id, chunk_index: index, total_chunks: total, chunk_size: cfg.chunkSize, file_size: file.size }, file.slice(start, end));
				}
			status.textContent = 'Upload complete. Starting the migration step…';
			return id;
			}
			function finish(form, status, message) {
			status.textContent = message || 'Migration complete.';
			var button = form.querySelector('[type="submit"]');
			if (button) { button.disabled = false; }
		}
			document.querySelectorAll('.aftm-upload-form').forEach(function (form) {
			form.addEventListener('submit', async function (event) {
				event.preventDefault();
				var status = form.querySelector('.aftm-upload-status');
				var button = form.querySelector('[type="submit"]');
				var kind = form.getAttribute('data-aftm-kind');
				var input = form.querySelector('input[type="file"]');
				var file = input && input.files ? input.files[0] : null;
				if (!file) { status.textContent = 'Choose a file first.'; return; }
				if (button) { button.disabled = true; }
				try {
					var uploadId = await uploadInChunks(file, status);
					if ('db' === kind) {
						var db = await request({ action: 'aftm_start_db_import', _ajax_nonce: cfg.nonce, upload_id: uploadId, source_url: form.querySelector('[name="aftm_source_url"]').value, destination_url: form.querySelector('[name="aftm_destination_url"]').value });
						window.location.href = db.redirect;
						return;
					}
					var start = await request({ action: 'aftm_start_content_import', _ajax_nonce: cfg.nonce, upload_id: uploadId, part: form.querySelector('[name="aftm_part"]').value });
					var jobId = start.job_id;
					var done = false;
					while (!done) {
						var step = await request({ action: 'aftm_content_import_step', _ajax_nonce: cfg.nonce, job_id: jobId });
						status.textContent = 'Importing content ' + step.percent + '% — ' + step.processed + ' of ' + step.total + ' archive entries';
						done = !!step.done;
						if (!done) { await wait(150); }
					}
					window.location.href = step.redirect;
				} catch (error) {
					finish(form, status, 'Migration error: ' + (error && error.message ? error.message : 'the connection was interrupted.')); 
				}
			});
			});
		}());
		</script>
		<style>
			.aftm-wrap .aftm-card{background:#fff;border:1px solid #dcdcde;padding:16px;max-width:850px;margin:12px 0 28px}
			.aftm-wrap h2{margin-top:30px}
			.aftm-wrap code{font-size:12px}
			.aftm-upload-status{font-weight:600;min-height:1.4em}
		</style>
		<?php
	}

	public function download() {
		$this->authorize();
		$part = isset( $_GET['part'] ) ? sanitize_key( wp_unslash( $_GET['part'] ) ) : '';
		$allowed = array_merge( array( 'db', 'full' ), array_keys( $this->content_parts() ) );
		if ( ! in_array( $part, $allowed, true ) ) {
			wp_die( esc_html__( 'Unknown migration download type.', 'aftm' ), 400 );
		}

		if ( 'db' === $part ) {
			$tmp_sql = wp_tempnam( 'aftm-database.sql' );
			$tmp_gz  = wp_tempnam( 'aftm-database.sql.gz' );
			if ( ! $tmp_sql || ! $tmp_gz ) {
				wp_die( esc_html__( 'Could not create a temporary export file.', 'aftm' ), 500 );
			}
			try {
				$this->write_sql_dump( $tmp_sql );
				$this->gzip_file( $tmp_sql, $tmp_gz );
				@unlink( $tmp_sql );
				$this->send_file( $tmp_gz, 'aft-migration-database-' . gmdate( 'Y-m-d-His' ) . '.sql.gz', 'application/gzip' );
			} catch ( Throwable $e ) {
				@unlink( $tmp_sql );
				@unlink( $tmp_gz );
				wp_die( esc_html( $e->getMessage() ), 500 );
			}
		}

		// Stream content ZIPs directly to the browser. Building a large temporary
		// ZIP first can exceed a hosting connection timeout before any bytes are
		// sent. The streaming writer uses ZIP store mode and flushes continuously.
		$this->stream_content_zip( $part );
	}

	private function send_file( $path, $filename, $content_type ) {
		if ( ! is_file( $path ) ) {
			throw new Exception( __( 'The export file was not created.', 'aftm' ) );
		}
		while ( ob_get_level() ) {
			ob_end_clean();
		}
		nocache_headers();
		header( 'Content-Type: ' . $content_type );
		header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $filename ) . '"' );
		header( 'Content-Length: ' . (string) filesize( $path ) );
		$handle = fopen( $path, 'rb' );
		if ( $handle ) {
			while ( ! feof( $handle ) ) {
				$data = fread( $handle, 1024 * 1024 );
				if ( false !== $data ) {
					echo $data;
				}
				flush();
			}
			fclose( $handle );
		}
		@unlink( $path );
		exit;
	}

	private function stream_content_zip( $part ) {
		$parts = $this->content_parts();
		$selected = 'full' === $part ? array_keys( $parts ) : array( $part );
		foreach ( $selected as $key ) {
			if ( ! isset( $parts[ $key ] ) ) {
				wp_die( esc_html__( 'Unknown migration download type.', 'aftm' ), 400 );
			}
		}

		while ( ob_get_level() ) {
			ob_end_clean();
		}
		nocache_headers();
		$filename = 'aft-migration-' . ( 'full' === $part ? 'content' : sanitize_file_name( $part ) ) . '-' . gmdate( 'Y-m-d-His' ) . '.zip';
		header( 'Content-Type: application/zip' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'X-Accel-Buffering: no' );
		@set_time_limit( 0 );
		@ignore_user_abort( true );

		$output = fopen( 'php://output', 'wb' );
		if ( ! $output ) {
			wp_die( esc_html__( 'Could not open the download stream.', 'aftm' ), 500 );
		}
		$offset = 0;
		$entries = array();
		$manifest = wp_json_encode(
			array(
				'plugin'       => 'AFT Migration Assistant',
				'version'      => self::VERSION,
				'source_url'   => untrailingslashit( home_url( '/' ) ),
				'created_gmt'  => gmdate( 'c' ),
				'archive_type' => $part,
			),
			JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
		);
		$this->stream_zip_string( $output, 'AFT-MIGRATION-MANIFEST.json', $manifest, $entries, $offset );

		foreach ( $selected as $key ) {
			$root = trailingslashit( WP_CONTENT_DIR ) . $parts[ $key ]['path'];
			$this->stream_directory_to_zip( $output, $root, 'full' === $part ? $parts[ $key ]['path'] . '/' : '', $entries, $offset );
		}

		$central_offset = $offset;
		foreach ( $entries as $entry ) {
			$name = $entry['name'];
			$central = pack(
				'VvvvvvvVVVvvvvvVV',
				0x02014b50,
				20,
				20,
				0x0008,
				0,
				$entry['time'],
				$entry['date'],
				$entry['crc'],
				$entry['size'],
				$entry['size'],
				strlen( $name ),
				0,
				0,
				0,
				0,
				0,
				$entry['offset']
			);
			$this->stream_zip_bytes( $output, $central . $name, $offset );
		}
		$central_size = $offset - $central_offset;
		$end = pack( 'VvvvvVVv', 0x06054b50, 0, 0, count( $entries ), count( $entries ), $central_size, $central_offset, 0 );
		$this->stream_zip_bytes( $output, $end, $offset );
		fclose( $output );
		flush();
		exit;
	}

	private function stream_directory_to_zip( $output, $root, $archive_prefix, &$entries, &$offset ) {
		if ( ! is_dir( $root ) ) {
			return;
		}
		$root = trailingslashit( wp_normalize_path( $root ) );
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::LEAVES_ONLY
		);
		foreach ( $iterator as $file ) {
			if ( ! $file->isFile() || $file->isLink() ) {
				continue;
			}
			$path = wp_normalize_path( $file->getPathname() );
			$relative = ltrim( substr( $path, strlen( $root ) ), '/' );
			if ( '' === $relative ) {
				continue;
			}
			$this->stream_zip_file( $output, $archive_prefix . $relative, $path, $entries, $offset );
		}
	}

	private function stream_zip_string( $output, $name, $contents, &$entries, &$offset ) {
		$hash = hash_init( 'crc32b' );
		hash_update( $hash, $contents );
		$crc = $this->zip_crc( hash_final( $hash ) );
		$this->stream_zip_local_header( $output, $name, $entries, $offset, $crc, strlen( $contents ), $contents );
	}

	private function stream_zip_file( $output, $name, $path, &$entries, &$offset ) {
		$handle = fopen( $path, 'rb' );
		if ( ! $handle ) {
			throw new Exception( sprintf( __( 'Could not read %s during export.', 'aftm' ), $name ) );
		}
		$hash = hash_init( 'crc32b' );
		$size = 0;
		$local_offset = $offset;
		$mtime = @filemtime( $path );
		list( $time, $date ) = $this->zip_dos_datetime( $mtime ? $mtime : time() );
		$header = pack( 'VvvvvvVVVvv', 0x04034b50, 20, 0x0008, 0, $time, $date, 0, 0, 0, strlen( $name ), 0 ) . $name;
		$this->stream_zip_bytes( $output, $header, $offset );
		while ( ! feof( $handle ) ) {
			$chunk = fread( $handle, 1024 * 1024 );
			if ( false === $chunk || '' === $chunk ) {
				continue;
			}
			hash_update( $hash, $chunk );
			$size += strlen( $chunk );
			$this->stream_zip_bytes( $output, $chunk, $offset );
		}
		fclose( $handle );
		$crc = $this->zip_crc( hash_final( $hash ) );
		$this->stream_zip_bytes( $output, pack( 'VVV', 0x08074b50, $crc, $size, $size ), $offset );
		$entries[] = array( 'name' => $name, 'crc' => $crc, 'size' => $size, 'offset' => $local_offset, 'time' => $time, 'date' => $date );
	}

	private function stream_zip_local_header( $output, $name, &$entries, &$offset, $crc, $size, $contents ) {
		$local_offset = $offset;
		list( $time, $date ) = $this->zip_dos_datetime( time() );
		$header = pack( 'VvvvvvVVVvv', 0x04034b50, 20, 0x0008, 0, $time, $date, 0, 0, 0, strlen( $name ), 0 ) . $name;
		$this->stream_zip_bytes( $output, $header . $contents . pack( 'VVV', 0x08074b50, $crc, $size, $size ), $offset );
		$entries[] = array( 'name' => $name, 'crc' => $crc, 'size' => $size, 'offset' => $local_offset, 'time' => $time, 'date' => $date );
	}

	private function stream_zip_bytes( $output, $bytes, &$offset ) {
		$length = strlen( $bytes );
		$written = 0;
		while ( $written < $length ) {
			$count = fwrite( $output, substr( $bytes, $written ) );
			if ( false === $count || 0 === $count ) {
				throw new Exception( __( 'The browser download stream was interrupted.', 'aftm' ) );
			}
			$written += $count;
		}
		$offset += $length;
		flush();
	}

	private function zip_crc( $hex ) {
		$value = hexdec( $hex );
		return $value > 2147483647 ? (float) $value : (int) $value;
	}

	private function zip_dos_datetime( $timestamp ) {
		$date = getdate( $timestamp );
		$year = max( 1980, min( 2107, (int) $date['year'] ) );
		return array(
			( (int) $date['hours'] << 11 ) | ( (int) $date['minutes'] << 5 ) | (int) floor( (int) $date['seconds'] / 2 ),
			( ( $year - 1980 ) << 9 ) | ( (int) $date['mon'] << 5 ) | (int) $date['mday'],
		);
	}

	private function sql_identifier( $identifier ) {
		return '`' . str_replace( '`', '``', $identifier ) . '`';
	}

	private function write_sql_dump( $path ) {
		global $wpdb;
		$handle = fopen( $path, 'wb' );
		if ( ! $handle ) {
			throw new Exception( __( 'Could not open the database export file.', 'aftm' ) );
		}
		fwrite( $handle, "-- AFT Migration Assistant database export\n" );
		fwrite( $handle, '-- AFT_SOURCE_PREFIX: ' . $wpdb->prefix . "\n" );
		fwrite( $handle, '-- Generated: ' . gmdate( 'c' ) . "\n" );
		fwrite( $handle, "SET FOREIGN_KEY_CHECKS=0;\nSET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n" );

		$tables = $wpdb->get_col( 'SHOW TABLES' );
		if ( null === $tables ) {
			fclose( $handle );
			throw new Exception( __( 'Could not read the database table list.', 'aftm' ) );
		}
		foreach ( $tables as $table ) {
			$table = (string) $table;
			$quoted = $this->sql_identifier( $table );
			$create = $wpdb->get_row( 'SHOW CREATE TABLE ' . $quoted, ARRAY_N );
			if ( ! $create || ! isset( $create[1] ) ) {
				continue;
			}
			fwrite( $handle, "\nDROP TABLE IF EXISTS {$quoted};\n" );
			fwrite( $handle, rtrim( $create[1], ';' ) . ";\n" );

			$columns = $wpdb->get_results( 'SHOW COLUMNS FROM ' . $quoted, ARRAY_A );
			if ( ! $columns ) {
				continue;
			}
			$column_names = array();
			foreach ( $columns as $column ) {
				$column_names[] = $this->sql_identifier( $column['Field'] );
			}
			$offset = 0;
			do {
				$sql  = 'SELECT * FROM ' . $quoted . ' LIMIT ' . (int) $this->batch_size . ' OFFSET ' . (int) $offset;
				$rows = $wpdb->get_results( $sql, ARRAY_A );
				if ( ! $rows ) {
					break;
				}
				$value_sets = array();
				foreach ( $rows as $row ) {
					$values = array();
					foreach ( array_keys( $row ) as $field ) {
						$values[] = $this->sql_value( $row[ $field ] );
					}
					$value_sets[] = '(' . implode( ',', $values ) . ')';
				}
				fwrite( $handle, 'INSERT INTO ' . $quoted . ' (' . implode( ',', $column_names ) . ') VALUES ' . implode( ',', $value_sets ) . ";\n" );
				$count  = count( $rows );
				$offset += $count;
			} while ( $count === $this->batch_size );
		}
		fwrite( $handle, "\nSET FOREIGN_KEY_CHECKS=1;\n" );
		fclose( $handle );
	}

	private function sql_value( $value ) {
		global $wpdb;
		if ( null === $value ) {
			return 'NULL';
		}
		return "'" . $wpdb->_real_escape( (string) $value ) . "'";
	}

	private function gzip_file( $source, $destination ) {
		$input = fopen( $source, 'rb' );
		$output = gzopen( $destination, 'wb9' );
		if ( ! $input || ! $output ) {
			if ( $input ) {
				fclose( $input );
			}
			throw new Exception( __( 'Could not compress the database export.', 'aftm' ) );
		}
		while ( ! feof( $input ) ) {
			$chunk = fread( $input, 1024 * 1024 );
			if ( false !== $chunk && '' !== $chunk ) {
				gzwrite( $output, $chunk );
			}
		}
		fclose( $input );
		gzclose( $output );
	}

	private function write_content_zip( $part, $destination ) {
		$zip = new ZipArchive();
		$flags = ZipArchive::CREATE | ZipArchive::OVERWRITE;
		if ( true !== $zip->open( $destination, $flags ) ) {
			throw new Exception( __( 'Could not create the content archive.', 'aftm' ) );
		}
		$manifest = array(
			'plugin'       => 'AFT Migration Assistant',
			'version'      => self::VERSION,
			'source_url'   => untrailingslashit( home_url( '/' ) ),
			'created_gmt'  => gmdate( 'c' ),
			'archive_type' => $part,
		);
		$zip->addFromString( 'AFT-MIGRATION-MANIFEST.json', wp_json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
		$parts = $this->content_parts();
		$selected = 'full' === $part ? array_keys( $parts ) : array( $part );
		foreach ( $selected as $key ) {
			if ( ! isset( $parts[ $key ] ) ) {
				continue;
			}
			$root = trailingslashit( WP_CONTENT_DIR ) . $parts[ $key ]['path'];
			$this->add_directory_to_zip( $zip, $root, 'full' === $part ? $parts[ $key ]['path'] . '/' : '' );
		}
		$zip->close();
	}

	private function add_directory_to_zip( $zip, $root, $archive_prefix ) {
		if ( ! is_dir( $root ) ) {
			return;
		}
		$root = trailingslashit( wp_normalize_path( $root ) );
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::LEAVES_ONLY
		);
		foreach ( $iterator as $file ) {
			if ( ! $file->isFile() || $file->isLink() ) {
				continue;
			}
			$path = wp_normalize_path( $file->getPathname() );
			$relative = ltrim( substr( $path, strlen( $root ) ), '/' );
			if ( '' !== $relative ) {
				$zip->addFile( $path, $archive_prefix . $relative );
				// Migration archives are transport files. Store entries without
				// compression so large media/plugin archives do not spend the
				// hosting request timeout recompressing already-compressed files.
				if ( method_exists( $zip, 'setCompressionIndex' ) && defined( 'ZipArchive::CM_STORE' ) ) {
					$zip->setCompressionIndex( $zip->numFiles - 1, ZipArchive::CM_STORE );
				}
			}
		}
	}

	public function import_db() {
		$this->authorize();
		if ( empty( $_FILES['aftm_db']['tmp_name'] ) || UPLOAD_ERR_OK !== (int) $_FILES['aftm_db']['error'] ) {
			$this->redirect_notice( __( 'Please select a valid database dump. The server upload limit may have been exceeded.', 'aftm' ), 'error' );
		}
		$source = $this->clean_url( isset( $_POST['aftm_source_url'] ) ? wp_unslash( $_POST['aftm_source_url'] ) : '' );
		$destination = $this->clean_url( isset( $_POST['aftm_destination_url'] ) ? wp_unslash( $_POST['aftm_destination_url'] ) : '' );
		if ( ! $source || ! $destination ) {
			$this->redirect_notice( __( 'Both source and destination URLs are required.', 'aftm' ), 'error' );
		}
		$file = $_FILES['aftm_db']['tmp_name'];
		try {
			$this->import_sql_file( $file, $this->is_gzip_file( $file ) );
			$result = $this->replace_database_urls( $source, $destination );
			$message = sprintf( __( 'Database imported. URL replacement changed %1$d values in %2$d rows; %3$d tables without a simple primary key were skipped.', 'aftm' ), $result['values'], $result['rows'], $result['skipped'] );
			$this->redirect_notice( $message );
		} catch ( Throwable $e ) {
			$this->redirect_notice( sprintf( __( 'Database import failed: %s', 'aftm' ), $e->getMessage() ), 'error' );
		}
	}

	private function is_gzip_file( $path ) {
		$handle = fopen( $path, 'rb' );
		if ( ! $handle ) {
			return false;
		}
		$bytes = fread( $handle, 2 );
		fclose( $handle );
		return "\x1f\x8b" === $bytes;
	}

	private function import_sql_file( $path, $gzip ) {
		global $wpdb;
		$handle = $gzip ? gzopen( $path, 'rb' ) : fopen( $path, 'rb' );
		if ( ! $handle ) {
			throw new Exception( __( 'Could not open the database upload.', 'aftm' ) );
		}
		$source_prefix = '';
		$first_chunk = $gzip ? gzgets( $handle, 8192 ) : fgets( $handle, 8192 );
		if ( false !== $first_chunk && preg_match( '/AFT_SOURCE_PREFIX:\s*(\S+)/', $first_chunk, $match ) ) {
			$source_prefix = $match[1];
		}
		if ( $gzip ) {
			gzrewind( $handle );
		} else {
			rewind( $handle );
		}
		$destination_prefix = $wpdb->prefix;
		$reader = function( $length ) use ( $handle, $gzip ) {
			return $gzip ? gzread( $handle, $length ) : fread( $handle, $length );
		};
		$buffer = '';
		$statement = '';
		$quote = '';
		$line_comment = false;
		$block_comment = false;
		$length = 8192;
		while ( true ) {
			$chunk = $reader( $length );
			if ( false === $chunk || '' === $chunk ) {
				break;
			}
			$buffer .= $chunk;
			$buffer_length = strlen( $buffer );
			for ( $i = 0; $i < $buffer_length; $i++ ) {
				$char = $buffer[ $i ];
				$next = $i + 1 < $buffer_length ? $buffer[ $i + 1 ] : '';
				if ( $line_comment ) {
					$statement .= $char;
					if ( "\n" === $char ) {
						$line_comment = false;
					}
					continue;
				}
				if ( $block_comment ) {
					$statement .= $char;
					if ( '*' === $char && '/' === $next ) {
						$statement .= $next;
						$i++;
						$block_comment = false;
					}
					continue;
				}
				if ( $quote ) {
					$statement .= $char;
					if ( '\\' === $char && $i + 1 < $buffer_length ) {
						$statement .= $buffer[ ++$i ];
					} elseif ( $char === $quote ) {
						$quote = '';
					}
					continue;
				}
				if ( ( "'" === $char || '"' === $char || '`' === $char ) ) {
					$quote = $char;
					$statement .= $char;
					continue;
				}
				if ( '#' === $char || ( '-' === $char && '-' === $next && ( $i + 2 >= $buffer_length || ctype_space( $buffer[ $i + 2 ] ) ) ) ) {
					$line_comment = true;
					$statement .= $char;
					if ( '-' === $char ) {
						$statement .= $next;
						$i++;
					}
					continue;
				}
				if ( '/' === $char && '*' === $next ) {
					$block_comment = true;
					$statement .= $char . $next;
					$i++;
					continue;
				}
				if ( ';' === $char ) {
					$this->execute_import_statement( $statement, $source_prefix, $destination_prefix );
					$statement = '';
					continue;
				}
				$statement .= $char;
			}
			$buffer = '';
		}
		if ( trim( $statement ) ) {
			$this->execute_import_statement( $statement, $source_prefix, $destination_prefix );
		}
		if ( $gzip ) {
			gzclose( $handle );
		} else {
			fclose( $handle );
		}
	}

	private function execute_import_statement( $statement, $source_prefix, $destination_prefix ) {
		global $wpdb;
		$statement = trim( $statement );
		if ( '' === $statement || 0 === strpos( $statement, '--' ) ) {
			return;
		}
		if ( $source_prefix && $source_prefix !== $destination_prefix ) {
			$statement = str_replace( '`' . $source_prefix, '`' . $destination_prefix, $statement );
		}
		$result = $wpdb->query( $statement );
		if ( false === $result ) {
			$error = $wpdb->last_error ? $wpdb->last_error : __( 'Unknown database error.', 'aftm' );
			throw new Exception( sprintf( __( 'Database statement failed: %s', 'aftm' ), $error ) );
		}
	}

	public function import_content() {
		$this->authorize();
		$part = isset( $_POST['aftm_part'] ) ? sanitize_key( wp_unslash( $_POST['aftm_part'] ) ) : '';
		$allowed = array_merge( array( 'full' ), array_keys( $this->content_parts() ) );
		if ( ! in_array( $part, $allowed, true ) || empty( $_FILES['aftm_content']['tmp_name'] ) || UPLOAD_ERR_OK !== (int) $_FILES['aftm_content']['error'] ) {
			$this->redirect_notice( __( 'Please select a valid ZIP content archive and archive type.', 'aftm' ), 'error' );
		}
		if ( ! class_exists( 'ZipArchive' ) ) {
			$this->redirect_notice( __( 'The PHP ZipArchive extension is required for content imports.', 'aftm' ), 'error' );
		}
		try {
			$count = $this->extract_content_zip( $_FILES['aftm_content']['tmp_name'], $part );
			$this->redirect_notice( sprintf( _n( '%d file imported from the content archive.', '%d files imported from the content archive.', $count, 'aftm' ), $count ) );
		} catch ( Throwable $e ) {
			$this->redirect_notice( sprintf( __( 'Content import failed: %s', 'aftm' ), $e->getMessage() ), 'error' );
		}
	}

	private function ajax_authorize() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to use the migration assistant.', 'aftm' ) ), 403 );
		}
		check_ajax_referer( self::NONCE );
		@set_time_limit( 0 );
	}

	private function migration_inbox_dir() {
		$uploads = wp_upload_dir();
		if ( ! empty( $uploads['error'] ) ) {
			throw new Exception( $uploads['error'] );
		}
		$dir = trailingslashit( $uploads['basedir'] ) . 'aft-migration-inbox';
		if ( ! wp_mkdir_p( $dir ) ) {
			throw new Exception( __( 'Could not create the migration upload directory.', 'aftm' ) );
		}
		if ( ! file_exists( trailingslashit( $dir ) . '.htaccess' ) ) {
			@file_put_contents( trailingslashit( $dir ) . '.htaccess', "Deny from all\nRequire all denied\n", LOCK_EX );
		}
		if ( ! file_exists( trailingslashit( $dir ) . 'index.php' ) ) {
			@file_put_contents( trailingslashit( $dir ) . 'index.php', "<?php\n", LOCK_EX );
		}
		return $dir;
	}

	private function migration_upload_id( $id ) {
		$id = strtolower( sanitize_key( (string) $id ) );
		if ( ! preg_match( '/^[a-f0-9]{32}$/', $id ) ) {
			throw new Exception( __( 'Invalid migration upload identifier.', 'aftm' ) );
		}
		return $id;
	}

	private function migration_upload_path( $id ) {
		return trailingslashit( $this->migration_inbox_dir() ) . $this->migration_upload_id( $id ) . '.part';
	}

	private function migration_job_path( $id ) {
		return trailingslashit( $this->migration_inbox_dir() ) . $this->migration_upload_id( $id ) . '.job.json';
	}

	public function ajax_upload_chunk() {
		try {
			$this->ajax_authorize();
			$id = $this->migration_upload_id( isset( $_POST['upload_id'] ) ? wp_unslash( $_POST['upload_id'] ) : '' );
			$index = isset( $_POST['chunk_index'] ) ? absint( $_POST['chunk_index'] ) : -1;
			$total = isset( $_POST['total_chunks'] ) ? absint( $_POST['total_chunks'] ) : 0;
			$chunk_size = isset( $_POST['chunk_size'] ) ? absint( $_POST['chunk_size'] ) : 0;
			$file_size = isset( $_POST['file_size'] ) ? absint( $_POST['file_size'] ) : 0;
			if ( $total < 1 || $index < 0 || $index >= $total || $chunk_size < 1 || $chunk_size > 8 * 1024 * 1024 || $file_size < 1 ) {
				throw new Exception( __( 'Invalid chunk information.', 'aftm' ) );
			}
			if ( empty( $_FILES['chunk']['tmp_name'] ) || UPLOAD_ERR_OK !== (int) $_FILES['chunk']['error'] ) {
				throw new Exception( __( 'The server did not receive this upload chunk.', 'aftm' ) );
			}
			$temporary = $_FILES['chunk']['tmp_name'];
			$actual_size = (int) filesize( $temporary );
			$offset = $index * $chunk_size;
			$expected_size = min( $chunk_size, max( 0, $file_size - $offset ) );
			if ( $actual_size !== $expected_size || $offset >= $file_size ) {
				throw new Exception( __( 'The upload chunk size did not match the expected size.', 'aftm' ) );
			}
			$path = $this->migration_upload_path( $id );
			$handle = fopen( $path, 0 === $index ? 'wb' : 'c+b' );
			if ( ! $handle || 0 !== fseek( $handle, $offset ) ) {
				if ( $handle ) {
					fclose( $handle );
				}
				throw new Exception( __( 'Could not open the server-side migration upload.', 'aftm' ) );
			}
			$input = fopen( $temporary, 'rb' );
			while ( ! feof( $input ) ) {
				$data = fread( $input, 1024 * 1024 );
				if ( false !== $data && '' !== $data ) {
					$written = fwrite( $handle, $data );
					if ( false === $written || $written !== strlen( $data ) ) {
						fclose( $input );
						fclose( $handle );
						throw new Exception( __( 'The server could not save the migration upload chunk.', 'aftm' ) );
					}
				}
			}
			fclose( $input );
			fclose( $handle );
			if ( $index === $total - 1 ) {
				clearstatcache( true, $path );
				if ( (int) filesize( $path ) !== $file_size ) {
					throw new Exception( __( 'The complete server-side upload has an unexpected size.', 'aftm' ) );
				}
			}
			wp_send_json_success( array( 'percent' => min( 100, (int) floor( ( ( $index + 1 ) / $total ) * 100 ) ) ) );
		} catch ( Throwable $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ), 400 );
		}
	}

	public function ajax_start_db_import() {
		try {
			$this->ajax_authorize();
			$id = $this->migration_upload_id( isset( $_POST['upload_id'] ) ? wp_unslash( $_POST['upload_id'] ) : '' );
			$path = $this->migration_upload_path( $id );
			if ( ! is_file( $path ) ) {
				throw new Exception( __( 'The uploaded database file is not available on the server.', 'aftm' ) );
			}
			$source = $this->clean_url( isset( $_POST['source_url'] ) ? wp_unslash( $_POST['source_url'] ) : '' );
			$destination = $this->clean_url( isset( $_POST['destination_url'] ) ? wp_unslash( $_POST['destination_url'] ) : '' );
			if ( ! $source || ! $destination ) {
				throw new Exception( __( 'Both source and destination URLs are required.', 'aftm' ) );
			}
			$this->import_sql_file( $path, $this->is_gzip_file( $path ) );
			$result = $this->replace_database_urls( $source, $destination );
			@unlink( $path );
			$message = sprintf( __( 'Database imported. URL replacement changed %1$d values in %2$d rows; %3$d tables without a simple primary key were skipped.', 'aftm' ), $result['values'], $result['rows'], $result['skipped'] );
			wp_send_json_success( array( 'redirect' => $this->page_redirect( $message ) ) );
		} catch ( Throwable $e ) {
			wp_send_json_error( array( 'message' => sprintf( __( 'Database import failed: %s', 'aftm' ), $e->getMessage() ) ), 400 );
		}
	}

	public function ajax_start_content_import() {
		try {
			$this->ajax_authorize();
			$id = $this->migration_upload_id( isset( $_POST['upload_id'] ) ? wp_unslash( $_POST['upload_id'] ) : '' );
			$part = isset( $_POST['part'] ) ? sanitize_key( wp_unslash( $_POST['part'] ) ) : '';
			$allowed = array_merge( array( 'full' ), array_keys( $this->content_parts() ) );
			if ( ! in_array( $part, $allowed, true ) ) {
				throw new Exception( __( 'Unknown content archive type.', 'aftm' ) );
			}
			if ( ! class_exists( 'ZipArchive' ) ) {
				throw new Exception( __( 'The PHP ZipArchive extension is required for content imports.', 'aftm' ) );
			}
			$path = $this->migration_upload_path( $id );
			if ( ! is_file( $path ) ) {
				throw new Exception( __( 'The uploaded content archive is not available on the server.', 'aftm' ) );
			}
			$zip = new ZipArchive();
			if ( true !== $zip->open( $path ) ) {
				throw new Exception( __( 'The uploaded file is not a readable ZIP archive.', 'aftm' ) );
			}
			$total = (int) $zip->numFiles;
			$zip->close();
			$state = array( 'id' => $id, 'path' => $path, 'part' => $part, 'index' => 0, 'total' => $total, 'imported' => 0 );
			if ( false === file_put_contents( $this->migration_job_path( $id ), wp_json_encode( $state ), LOCK_EX ) ) {
				throw new Exception( __( 'Could not create the content import job.', 'aftm' ) );
			}
			wp_send_json_success( array( 'job_id' => $id, 'total' => $total ) );
		} catch ( Throwable $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ), 400 );
		}
	}

	public function ajax_content_import_step() {
		try {
			$this->ajax_authorize();
			$id = $this->migration_upload_id( isset( $_POST['job_id'] ) ? wp_unslash( $_POST['job_id'] ) : '' );
			$job_path = $this->migration_job_path( $id );
			$state = file_exists( $job_path ) ? json_decode( file_get_contents( $job_path ), true ) : false;
			if ( ! is_array( $state ) || empty( $state['path'] ) || ! is_file( $state['path'] ) ) {
				throw new Exception( __( 'The content import job is no longer available.', 'aftm' ) );
			}
			$zip = new ZipArchive();
			if ( true !== $zip->open( $state['path'] ) ) {
				throw new Exception( __( 'The uploaded content archive could not be reopened.', 'aftm' ) );
			}
			$started = microtime( true );
			$processed = 0;
			$limit = min( (int) $state['total'], (int) $state['index'] + 20 );
			while ( (int) $state['index'] < $limit && ( microtime( true ) - $started ) < 12 ) {
				if ( $this->extract_content_zip_entry( $zip, (int) $state['index'], $state['part'] ) ) {
					$state['imported']++;
				}
				$state['index']++;
				$processed++;
			}
			$zip->close();
			if ( (int) $state['index'] >= (int) $state['total'] ) {
				@unlink( $state['path'] );
				@unlink( $job_path );
				$message = sprintf( _n( '%d file imported from the content archive.', '%d files imported from the content archive.', (int) $state['imported'], 'aftm' ), (int) $state['imported'] );
				wp_send_json_success( array( 'done' => true, 'percent' => 100, 'processed' => (int) $state['index'], 'total' => (int) $state['total'], 'redirect' => $this->page_redirect( $message ) ) );
			}
			file_put_contents( $job_path, wp_json_encode( $state ), LOCK_EX );
			$percent = (int) floor( ( (int) $state['index'] / max( 1, (int) $state['total'] ) ) * 100 );
			wp_send_json_success( array( 'done' => false, 'percent' => $percent, 'processed' => (int) $state['index'], 'total' => (int) $state['total'] ) );
		} catch ( Throwable $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ), 400 );
		}
	}

	private function page_redirect( $message, $type = 'success' ) {
		return add_query_arg(
			array( 'page' => 'aft-migration-assistant', 'aftm_notice' => $message, 'aftm_type' => sanitize_key( $type ) ),
			admin_url( 'tools.php' )
		);
	}

	private function safe_archive_name( $name ) {
		$name = str_replace( '\\', '/', (string) $name );
		$name = ltrim( $name, '/' );
		if ( '' === $name || false !== strpos( $name, "\0" ) || preg_match( '#(^|/)\.\.?(?:/|$)#', $name ) || preg_match( '#^[A-Za-z]:/#', $name ) ) {
			return false;
		}
		return $name;
	}

	private function extract_content_zip_entry( $zip, $index, $part ) {
		$info = $zip->statIndex( $index );
		if ( ! $info || empty( $info['name'] ) ) {
			return false;
		}
		$name = $this->safe_archive_name( $info['name'] );
		if ( false === $name || '/' === substr( $name, -1 ) || 'AFT-MIGRATION-MANIFEST.json' === $name ) {
			return false;
		}
		$parts = $this->content_parts();
		$relative = $name;
		$destination_root = WP_CONTENT_DIR;
		if ( 'full' === $part ) {
			$first = strtok( $name, '/' );
			if ( ! isset( $parts[ $first ] ) ) {
				return false;
			}
		} else {
			if ( ! isset( $parts[ $part ] ) ) {
				throw new Exception( __( 'Unknown content archive type.', 'aftm' ) );
			}
			$destination_root = trailingslashit( WP_CONTENT_DIR ) . $parts[ $part ]['path'];
		}
		$target = trailingslashit( $destination_root ) . $relative;
		if ( ! wp_mkdir_p( dirname( $target ) ) ) {
			throw new Exception( __( 'Could not create a destination content directory.', 'aftm' ) );
		}
		$input = $zip->getStream( $info['name'] );
		if ( ! $input ) {
			throw new Exception( __( 'Could not read a file from the ZIP archive.', 'aftm' ) );
		}
		$output = fopen( $target, 'wb' );
		if ( ! $output ) {
			fclose( $input );
			throw new Exception( __( 'Could not write a destination content file.', 'aftm' ) );
		}
		while ( ! feof( $input ) ) {
			$chunk = fread( $input, 1024 * 1024 );
			if ( false !== $chunk && '' !== $chunk ) {
				fwrite( $output, $chunk );
			}
		}
		fclose( $input );
		fclose( $output );
		return true;
	}

	private function extract_content_zip( $path, $part ) {
		$zip = new ZipArchive();
		if ( true !== $zip->open( $path ) ) {
			throw new Exception( __( 'The uploaded file is not a readable ZIP archive.', 'aftm' ) );
		}
		$parts = $this->content_parts();
		$imported = 0;
		for ( $i = 0; $i < $zip->numFiles; $i++ ) {
			$info = $zip->statIndex( $i );
			if ( ! $info || empty( $info['name'] ) ) {
				continue;
			}
			$name = $this->safe_archive_name( $info['name'] );
			if ( false === $name || '/' === substr( $name, -1 ) || 'AFT-MIGRATION-MANIFEST.json' === $name ) {
				continue;
			}
			$relative = $name;
			$destination_root = WP_CONTENT_DIR;
			if ( 'full' === $part ) {
				$first = strtok( $name, '/' );
				if ( ! isset( $parts[ $first ] ) ) {
					continue;
				}
			} else {
				if ( ! isset( $parts[ $part ] ) ) {
					$zip->close();
					throw new Exception( __( 'Unknown content archive type.', 'aftm' ) );
				}
				$destination_root = trailingslashit( WP_CONTENT_DIR ) . $parts[ $part ]['path'];
			}
			$target = trailingslashit( $destination_root ) . $relative;
			$target_dir = dirname( $target );
			if ( ! wp_mkdir_p( $target_dir ) ) {
				$zip->close();
				throw new Exception( __( 'Could not create a destination content directory.', 'aftm' ) );
			}
			$input = $zip->getStream( $info['name'] );
			if ( ! $input ) {
				$zip->close();
				throw new Exception( __( 'Could not read a file from the ZIP archive.', 'aftm' ) );
			}
			$output = fopen( $target, 'wb' );
			if ( ! $output ) {
				fclose( $input );
				$zip->close();
				throw new Exception( __( 'Could not write a destination content file.', 'aftm' ) );
			}
			while ( ! feof( $input ) ) {
				$chunk = fread( $input, 1024 * 1024 );
				if ( false !== $chunk && '' !== $chunk ) {
					fwrite( $output, $chunk );
				}
			}
			fclose( $input );
			fclose( $output );
			$imported++;
		}
		$zip->close();
		return $imported;
	}

	public function replace_urls() {
		$this->authorize();
		$source = $this->clean_url( isset( $_POST['aftm_source_url'] ) ? wp_unslash( $_POST['aftm_source_url'] ) : '' );
		$destination = $this->clean_url( isset( $_POST['aftm_destination_url'] ) ? wp_unslash( $_POST['aftm_destination_url'] ) : '' );
		if ( ! $source || ! $destination || $source === $destination ) {
			$this->redirect_notice( __( 'Enter two different, valid URLs.', 'aftm' ), 'error' );
		}
		try {
			$result = $this->replace_database_urls( $source, $destination );
			$this->redirect_notice( sprintf( __( 'URL replacement changed %1$d values in %2$d rows; %3$d tables without a simple primary key were skipped.', 'aftm' ), $result['values'], $result['rows'], $result['skipped'] ) );
		} catch ( Throwable $e ) {
			$this->redirect_notice( sprintf( __( 'URL replacement failed: %s', 'aftm' ), $e->getMessage() ), 'error' );
		}
	}

	private function clean_url( $url ) {
		$url = trim( (string) $url );
		$url = esc_url_raw( $url );
		if ( ! $url || ! preg_match( '#^https?://#i', $url ) ) {
			return false;
		}
		return untrailingslashit( $url );
	}

	private function replace_database_urls( $old, $new ) {
		global $wpdb;
		$old_variants = array_unique( array( $old, str_replace( '/', '\\/', $old ) ) );
		$new_variants = array( $new, str_replace( '/', '\\/', $new ) );
		$old_like = '%' . $wpdb->esc_like( $old ) . '%';
		$old_escaped_like = '%' . $wpdb->esc_like( str_replace( '/', '\\/', $old ) ) . '%';
		$tables = $wpdb->get_col( 'SHOW TABLES' );
		$result = array( 'values' => 0, 'rows' => 0, 'skipped' => 0 );
		foreach ( $tables as $table ) {
			$quoted_table = $this->sql_identifier( $table );
			$columns = $wpdb->get_results( 'SHOW COLUMNS FROM ' . $quoted_table, ARRAY_A );
			if ( ! $columns ) {
				continue;
			}
			$primary = '';
			$candidates = array();
			foreach ( $columns as $column ) {
				if ( 'PRI' === $column['Key'] && ! $primary ) {
					$primary = $column['Field'];
				}
				$type = strtolower( $column['Type'] );
				if ( false !== strpos( $type, 'char' ) || false !== strpos( $type, 'text' ) || false !== strpos( $type, 'blob' ) || false !== strpos( $type, 'json' ) ) {
					$candidates[] = $column['Field'];
				}
			}
			if ( ! $primary ) {
				if ( $candidates ) {
					$result['skipped']++;
				}
				continue;
			}
			foreach ( $candidates as $column ) {
				$quoted_column = $this->sql_identifier( $column );
				$quoted_primary = $this->sql_identifier( $primary );
				$sql = $wpdb->prepare( 'SELECT ' . $quoted_primary . ', ' . $quoted_column . ' FROM ' . $quoted_table . ' WHERE ' . $quoted_column . ' LIKE %s OR ' . $quoted_column . ' LIKE %s', $old_like, $old_escaped_like );
				$rows = $wpdb->get_results( $sql, ARRAY_A );
				foreach ( $rows as $row ) {
					if ( ! array_key_exists( $column, $row ) || null === $row[ $column ] ) {
						continue;
					}
					$old_value = $row[ $column ];
					$new_value = $this->replace_value( $old_value, $old_variants, $new_variants );
					if ( $new_value === $old_value ) {
						continue;
					}
					$where = array( $primary => $row[ $primary ] );
					if ( false !== $wpdb->update( $table, array( $column => $new_value ), $where ) ) {
						$result['values']++;
						$result['rows']++;
					}
				}
			}
		}
		return $result;
	}

	private function replace_value( $value, $old_variants, $new_variants ) {
		if ( ! is_string( $value ) ) {
			return $value;
		}
		if ( is_serialized( $value ) ) {
			$data = @unserialize( $value );
			if ( false !== $data || 'b:0;' === $value ) {
				$changed = $this->replace_recursive( $data, $old_variants, $new_variants );
				return $changed ? serialize( $data ) : $value;
			}
		}
		$changed = false;
		$new_value = $value;
		foreach ( $old_variants as $index => $old ) {
			$replacement = isset( $new_variants[ $index ] ) ? $new_variants[ $index ] : $new_variants[0];
			if ( false !== strpos( $new_value, $old ) ) {
				$new_value = str_replace( $old, $replacement, $new_value );
				$changed = true;
			}
		}
		return $changed ? $new_value : $value;
	}

	private function replace_recursive( &$value, $old_variants, $new_variants ) {
		$changed = false;
		if ( is_string( $value ) ) {
			$new_value = $this->replace_value( $value, $old_variants, $new_variants );
			if ( $new_value !== $value ) {
				$value = $new_value;
				$changed = true;
			}
		} elseif ( is_array( $value ) ) {
			foreach ( $value as $key => &$item ) {
				if ( is_string( $key ) ) {
					$new_key = $this->replace_value( $key, $old_variants, $new_variants );
					if ( $new_key !== $key ) {
						$value[ $new_key ] = $item;
						unset( $value[ $key ] );
						$changed = true;
						$key = $new_key;
					}
				}
				if ( $this->replace_recursive( $item, $old_variants, $new_variants ) ) {
					$changed = true;
				}
			}
			unset( $item );
		}
		return $changed;
	}
}

AFT_Migration_Assistant::instance();
