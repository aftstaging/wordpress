<?php
defined( 'ABSPATH' ) || exit;
$aft_sc  = aft_reviews_shortcode();
$aft_sum = aft_reviews_summary();
$aft_fb  = aft_reviews_fallback();
?>
<section class="aft-section aft-section--soft">
	<div class="aft-container">
		<div class="aft-reviews__top" data-reveal>
			<div>
				<p class="aft-eyebrow"><?php esc_html_e( 'Trusted by students. Proven by experience.', 'aft' ); ?></p>
				<h2><?php esc_html_e( 'What Our Students Say', 'aft' ); ?></h2>
				<div class="aft-gsum">
					<?php echo aft_google_wordmark(); // phpcs:ignore ?>
					<?php echo aft_stars( 5, true ); // phpcs:ignore ?>
					<?php if ( $aft_sum['rating'] ) : ?>
						<span class="aft-gsum__score">
							<?php echo esc_html( $aft_sum['rating'] ); ?>/5
							<?php if ( $aft_sum['count'] ) : ?>
								<span><?php printf( esc_html__( 'from %s+ reviews', 'aft' ), esc_html( $aft_sum['count'] ) ); ?></span>
							<?php endif; ?>
						</span>
					<?php endif; ?>
				</div>
			</div>
			<div class="aft-railnav">
				<button type="button" data-rail-prev="reviews" aria-label="<?php esc_attr_e( 'Previous reviews', 'aft' ); ?>"><?php aft_the_icon( 'chev-l', 17 ); ?></button>
				<button type="button" data-rail-next="reviews" aria-label="<?php esc_attr_e( 'Next reviews', 'aft' ); ?>"><?php aft_the_icon( 'chev-r', 17 ); ?></button>
			</div>
		</div>

		<?php if ( $aft_sc ) : ?>
			<div class="aft-reviews__embed" data-reveal><?php echo do_shortcode( $aft_sc ); ?></div>
		<?php elseif ( $aft_fb ) : ?>
			<div class="aft-rail" data-rail="reviews" tabindex="0" role="region"
				aria-label="<?php esc_attr_e( 'Student Google reviews', 'aft' ); ?>">
				<?php foreach ( $aft_fb as $aft_i => $aft_rev ) : ?>
					<article class="aft-rev" data-reveal data-delay="<?php echo esc_attr( min( 3, $aft_i ) ); ?>">
						<div class="aft-rev__top">
							<?php echo aft_google_wordmark(); // phpcs:ignore ?>
							<?php echo aft_stars( 5 ); // phpcs:ignore ?>
						</div>
						<p class="aft-rev__quote">&ldquo;<?php echo esc_html( wp_trim_words( $aft_rev['text'], 34 ) ); ?>&rdquo;</p>
						<div class="aft-rev__person">
							<span class="aft-rev__avatar"><?php echo esc_html( aft_initials( $aft_rev['name'] ) ); ?></span>
							<span>
								<span class="aft-rev__name"><?php echo esc_html( $aft_rev['name'] ); ?></span><br>
								<span class="aft-rev__src"><?php esc_html_e( 'Verified Google Review', 'aft' ); ?></span>
							</span>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<div class="aft-empty">
				<b><?php esc_html_e( 'No reviews connected yet', 'aft' ); ?></b>
				<?php esc_html_e( 'Add your Google review widget shortcode in Customizer → AFT Theme Settings → Google Reviews.', 'aft' ); ?>
			</div>
		<?php endif; ?>

		<?php if ( $aft_sum['url'] ) : ?>
			<p style="margin-top:22px">
				<a class="aft-link" href="<?php echo esc_url( $aft_sum['url'] ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'View all Google Reviews', 'aft' ); ?><?php aft_the_icon( 'arrow-r', 16 ); ?>
				</a>
			</p>
		<?php endif; ?>
	</div>
</section>
