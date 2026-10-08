<?php
/**
 * Accountants For Tomorrow — theme bootstrap.
 *
 * @package AFT
 */

defined( 'ABSPATH' ) || exit;

define( 'AFT_VERSION', '1.1.0' );
define( 'AFT_DIR', get_template_directory() );
define( 'AFT_URI', get_template_directory_uri() );

/* -------------------------------------------------------------- setup -- */

function aft_setup() {
	load_theme_textdomain( 'aft', AFT_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'custom-logo', [
		'height'      => 64,
		'width'       => 240,
		'flex-height' => true,
		'flex-width'  => true,
	] );
	add_theme_support( 'html5', [
		'search-form', 'comment-form', 'comment-list',
		'gallery', 'caption', 'style', 'script', 'navigation-widgets',
	] );

	// WooCommerce — use the plugin's own templates, just declare support.
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus( [
		'primary'  => __( 'Primary Navigation', 'aft' ),
		'footer_1' => __( 'Footer — Quick Links', 'aft' ),
		'footer_2' => __( 'Footer — Learning', 'aft' ),
		'footer_3' => __( 'Footer — Resources', 'aft' ),
	] );

	add_image_size( 'aft-card', 720, 460, true );
	add_image_size( 'aft-wide', 1280, 720, true );
}
add_action( 'after_setup_theme', 'aft_setup' );

function aft_content_width() {
	$GLOBALS['content_width'] = 1240;
}
add_action( 'after_setup_theme', 'aft_content_width', 0 );

/* ------------------------------------------------------------ assets -- */

function aft_assets() {
	// Inter, self-hosted-friendly. Swap to a local file for best CWV.
	wp_enqueue_style(
		'aft-inter',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap',
		[],
		null
	);

	wp_enqueue_style( 'aft-main', AFT_URI . '/assets/css/main.css', [], aft_asset_version( '/assets/css/main.css' ) );

	// Customizer colour overrides, injected as CSS custom properties.
	wp_add_inline_style( 'aft-main', aft_dynamic_css() );

	wp_enqueue_script( 'aft-main', AFT_URI . '/assets/js/main.js', [], aft_asset_version( '/assets/js/main.js' ), true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'aft_assets' );

/**
 * Version assets by file modification time.
 *
 * A hardcoded version string means browsers keep serving a cached stylesheet
 * after a deploy — the change is live on the server but invisible to anyone
 * who has already visited. filemtime() changes on every upload, so the URL
 * changes with it and the cache busts itself.
 */
function aft_asset_version( $rel ) {
	$path = AFT_DIR . $rel;
	return file_exists( $path ) ? (string) filemtime( $path ) : AFT_VERSION;
}

function aft_dynamic_css() {
	// Defaults mirror the AFT Exam Portal palette.
	$primary = get_theme_mod( 'aft_color_primary', '#00FF88' ); // portal accent (mint)
	$deep    = get_theme_mod( 'aft_color_deep', '#130628' );    // portal --background
	$accent  = get_theme_mod( 'aft_color_accent', '#00E5FF' );  // portal data-viz cyan

	return sprintf(
		':root{--aft-primary:%1$s;--aft-bg:%2$s;--aft-navy-800:%2$s;--aft-cyan:%3$s;--aft-viz:%3$s;}',
		esc_attr( $primary ),
		esc_attr( $deep ),
		esc_attr( $accent )
	);
}

/* ------------------------------------------------------------ widgets -- */

function aft_widgets() {
	register_sidebar( [
		'name'          => __( 'Sidebar', 'aft' ),
		'id'            => 'sidebar-1',
		'before_widget' => '<section id="%1$s" class="aft-widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="aft-widget__title">',
		'after_title'   => '</h4>',
	] );
}
add_action( 'widgets_init', 'aft_widgets' );

/* ------------------------------------------------------------ helpers -- */

require_once AFT_DIR . '/inc/helpers.php';
require_once AFT_DIR . '/inc/settings.php';
require_once AFT_DIR . '/inc/customizer.php';
require_once AFT_DIR . '/inc/blog.php';

if ( class_exists( 'WooCommerce' ) ) {
	require_once AFT_DIR . '/inc/woocommerce.php';
}

/* ------------------------------------------------------------- misc ---- */

/** Cart count for the header bubble (WooCommerce optional). */
function aft_cart_count() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return 0;
	}
	return (int) WC()->cart->get_cart_contents_count();
}

/** Live-update the header cart bubble via Woo's AJAX fragments. */
function aft_cart_fragment( $fragments ) {
	ob_start();
	?>
	<span class="aft-cart__count" data-aft-cart-count><?php echo esc_html( aft_cart_count() ); ?></span>
	<?php
	$fragments['span[data-aft-cart-count]'] = ob_get_clean();
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'aft_cart_fragment' );

/** Reading time used on post cards. */
function aft_reading_time( $post_id = null ) {
	$post_id = $post_id ?: get_the_ID();
	$words   = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ) );
	$minutes = max( 1, (int) ceil( $words / 200 ) );
	/* translators: %d: minutes */
	return sprintf( _n( '%d min read', '%d min read', $minutes, 'aft' ), $minutes );
}

/** Body classes that drive layout variants. */
function aft_body_class( $classes ) {
	if ( is_front_page() ) {
		$classes[] = 'aft-home';
	}
	return $classes;
}
add_filter( 'body_class', 'aft_body_class' );
