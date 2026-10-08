<?php
/**
 * Shop teaser — real WooCommerce products, never hard-coded.
 */
defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'WooCommerce' ) ) { return; }
?>
<section class="aft-section aft-section--soft">
	<div class="aft-container">
		<div class="aft-head aft-head--row" data-reveal>
			<div>
				<p class="aft-eyebrow"><?php esc_html_e( 'Study Materials', 'aft' ); ?></p>
				<h2><?php esc_html_e( 'Books, Kits & Resources', 'aft' ); ?></h2>
			</div>
			<a class="aft-link" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
				<?php esc_html_e( 'Visit Shop', 'aft' ); ?><?php aft_the_icon( 'arrow-r', 16 ); ?>
			</a>
		</div>
		<?php echo do_shortcode( '[products limit="4" columns="4" orderby="popularity"]' ); ?>
	</div>
</section>
