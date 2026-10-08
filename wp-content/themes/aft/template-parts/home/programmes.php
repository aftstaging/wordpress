<?php
/**
 * Programmes — qualification cards with official marks on top.
 *
 * @package AFT
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="aft-section">
	<div class="aft-container">
		<div class="aft-head aft-head--row" data-reveal>
			<div>
				<p class="aft-eyebrow"><?php esc_html_e( 'Our Programmes', 'aft' ); ?></p>
				<h2><?php esc_html_e( 'Choose Your Accounting Journey', 'aft' ); ?></h2>
			</div>
			<a class="aft-link" href="<?php echo esc_url( home_url( '/courses/' ) ); ?>">
				<?php esc_html_e( 'View All Courses', 'aft' ); ?><?php aft_the_icon( 'arrow-r', 16 ); ?>
			</a>
		</div>

		<div class="aft-progs">
			<?php foreach ( aft_programmes() as $aft_i => $aft_p ) : ?>
				<article class="aft-prog-card" data-reveal data-delay="<?php echo esc_attr( min( 4, $aft_i ) ); ?>">

					<div class="aft-prog-card__plate<?php echo ! empty( $aft_p['illus'] ) ? ' aft-prog-card__plate--illus' : ''; ?>">
						<?php if ( ! empty( $aft_p['logo'] ) ) : ?>
							<img src="<?php echo esc_url( $aft_p['logo'] ); ?>"
								alt="<?php echo esc_attr( $aft_p['alt'] ); ?>" loading="lazy">
						<?php else : ?>
							<?php /* No licensed mark available: show a neutral glyph rather than
							         repeating the card title or substituting a look-alike logo. */ ?>
							<span class="aft-prog-card__glyph" aria-hidden="true">
								<?php aft_the_icon( $aft_p['icon'], 40 ); ?>
							</span>
						<?php endif; ?>
					</div>

					<div class="aft-prog-card__body">
						<h3><?php echo esc_html( $aft_p['title'] ); ?></h3>
						<p><?php echo esc_html( $aft_p['desc'] ); ?></p>
						<a class="aft-btn aft-btn--primary aft-btn--sm" href="<?php echo esc_url( $aft_p['url'] ); ?>">
							<?php echo esc_html( $aft_p['cta'] ); ?><?php aft_the_icon( 'arrow-r', 15 ); ?>
						</a>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
