<?php
/**
 * WooCommerce integration.
 * We style Woo, we never replace its templates or checkout logic.
 */
defined( 'ABSPATH' ) || exit;

// Wrap Woo pages in the theme container.
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

add_action( 'woocommerce_before_main_content', function () {
	echo '<section class="aft-section"><div class="aft-container">';
}, 10 );

add_action( 'woocommerce_after_main_content', function () {
	echo '</div></section>';
}, 10 );

// 4-up product grid to match the theme.
add_filter( 'loop_shop_columns', fn() => 4, 20 );
add_filter( 'loop_shop_per_page', fn() => 12, 20 );

// Woo's default stylesheet fights the theme; keep the layout sheet only.
add_filter( 'woocommerce_enqueue_styles', function ( $styles ) {
	unset( $styles['woocommerce-general'] );
	return $styles;
} );
