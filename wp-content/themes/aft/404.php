<?php defined( 'ABSPATH' ) || exit; get_header(); ?>
<section class="aft-section">
	<div class="aft-container">
		<div class="aft-head aft-head--center" data-reveal>
			<p class="aft-eyebrow">404</p>
			<h1><?php esc_html_e( 'Page not found', 'aft' ); ?></h1>
			<p class="aft-lede"><?php esc_html_e( 'The page you are looking for has moved or no longer exists.', 'aft' ); ?></p>
			<p style="margin-top:22px">
				<a class="aft-btn aft-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php esc_html_e( 'Back to Home', 'aft' ); ?>
				</a>
			</p>
		</div>
	</div>
</section>
<?php get_footer(); ?>
