<?php defined( 'ABSPATH' ) || exit; ?>
<section class="aft-section">
	<div class="aft-container">
		<div class="aft-story">
			<div data-reveal>
				<p class="aft-eyebrow"><?php esc_html_e( 'Why Accountants For Tomorrow', 'aft' ); ?></p>
				<h2><?php esc_html_e( 'More Than Tuition. A Journey.', 'aft' ); ?></h2>
				<p class="aft-lede"><?php esc_html_e( 'Since 2015, AFT has grown from Patrick Pfidze\'s experience navigating the CIMA syllabus changes and the online examination environment. Today, we\'re a trusted partner in your accounting journey.', 'aft' ); ?></p>
				<p style="margin-top:22px">
					<a class="aft-btn aft-btn--ghost" href="<?php echo esc_url( home_url( '/about/' ) ); ?>">
						<?php esc_html_e( 'Our Story', 'aft' ); ?><?php aft_the_icon( 'arrow-r', 16 ); ?>
					</a>
				</p>
			</div>
			<div class="aft-tl" data-reveal data-delay="1">
				<?php foreach ( aft_timeline() as $aft_t ) : ?>
					<div class="aft-tl__i">
						<span class="aft-tl__dot">
							<?php if ( $aft_t['year'] ) { echo esc_html( $aft_t['year'] ); } else { aft_the_icon( $aft_t['icon'], 21 ); } ?>
						</span>
						<span class="aft-tl__l"><?php echo esc_html( $aft_t['label'] ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
