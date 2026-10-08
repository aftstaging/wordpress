<?php
/**
 * Accreditation strip — a self-contained trust card.
 *
 * @package AFT
 */

defined( 'ABSPATH' ) || exit;

$aft_badge = aft_accred_badge();
?>
<section class="aft-section aft-section--tight">
	<div class="aft-container">
		<div class="aft-accred" data-reveal>

			<div class="aft-accred__badge">
				<?php if ( $aft_badge ) : ?>
					<img src="<?php echo esc_url( $aft_badge ); ?>"
						alt="<?php echo esc_attr( aft_get( 'accred_title' ) ); ?>" loading="lazy">
				<?php else : ?>
					<span class="aft-accred__slot">
						<?php aft_the_icon( 'award', 22 ); ?>
						<?php esc_html_e( 'Official badge — upload in Customizer', 'aft' ); ?>
					</span>
				<?php endif; ?>
			</div>

			<div class="aft-accred__body">
				<p class="aft-accred__label">
					<?php aft_the_icon( 'shield-c', 14 ); ?>
					<?php echo esc_html( aft_get( 'accred_title' ) ); ?>
				</p>
				<h3><?php echo esc_html( aft_get( 'accred_strap' ) ); ?></h3>
				<p class="aft-accred__text"><?php echo esc_html( aft_get( 'accred_text' ) ); ?></p>
			</div>

			<div class="aft-accred__action">
				<?php if ( aft_get( 'accred_url' ) ) : ?>
					<a class="aft-btn aft-btn--primary" href="<?php echo esc_url( aft_get( 'accred_url' ) ); ?>">
						<?php esc_html_e( 'Learn More', 'aft' ); ?><?php aft_the_icon( 'arrow-r', 16 ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
