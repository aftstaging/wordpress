<?php
/**
 * Accreditation feature — centred credibility panel.
 *
 * @package AFT
 */

defined( 'ABSPATH' ) || exit;

$aft_badge = aft_accred_badge();
?>
<section class="aft-section aft-section--tight">
	<div class="aft-container">
		<div class="aft-accredx" data-reveal>

			<div class="aft-accredx__badge">
				<?php if ( $aft_badge ) : ?>
					<img src="<?php echo esc_url( $aft_badge ); ?>"
						alt="<?php echo esc_attr( aft_get( 'accred_title' ) ); ?>" loading="lazy">
				<?php else : ?>
					<span class="aft-accred__slot">
						<?php aft_the_icon( 'award', 26 ); ?>
						<?php esc_html_e( 'Official badge slot', 'aft' ); ?>
					</span>
				<?php endif; ?>
			</div>

			<h2 class="aft-accredx__title"><?php echo esc_html( aft_get( 'accred_title' ) ); ?></h2>

			<p class="aft-accredx__text">
				<?php esc_html_e( 'Supporting students on their journey through professional accounting education and exam preparation.', 'aft' ); ?>
			</p>

			<a class="aft-btn aft-btn--primary" href="<?php echo esc_url( aft_get( 'accred_url' ) ); ?>">
				<?php esc_html_e( 'Learn More About Our CIMA Support', 'aft' ); ?><?php aft_the_icon( 'arrow-r', 16 ); ?>
			</a>
		</div>
	</div>
</section>
