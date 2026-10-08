<?php defined( 'ABSPATH' ) || exit; $aft_steps = aft_journey_steps(); ?>
<section class="aft-section aft-section--soft">
	<div class="aft-container">
		<div class="aft-journey">
			<div data-reveal>
				<p class="aft-eyebrow"><?php esc_html_e( 'The CIMA Journey', 'aft' ); ?></p>
				<h2><?php esc_html_e( 'From Foundation to Global Recognition', 'aft' ); ?></h2>
				<p class="aft-lede"><?php esc_html_e( 'Progress through the CIMA qualification and unlock a world of opportunities.', 'aft' ); ?></p>
				<p style="margin-top:22px">
					<a class="aft-btn aft-btn--primary" href="<?php echo esc_url( aft_get( 'url_cima', home_url( '/new/cima-courses-4/' ) ) ); ?>">
						<?php esc_html_e( 'Explore CIMA Courses', 'aft' ); ?><?php aft_the_icon( 'arrow-r', 16 ); ?>
					</a>
				</p>
			</div>

			<div data-reveal data-delay="1">
				<div class="aft-steps" data-steps>
					<span class="aft-steps__fill" data-steps-fill></span>
					<?php foreach ( $aft_steps as $aft_i => $aft_s ) : ?>
						<button class="aft-step<?php echo 0 === $aft_i ? ' is-active' : ''; ?>" type="button"
							data-step="<?php echo esc_attr( $aft_s['key'] ); ?>"
							aria-pressed="<?php echo 0 === $aft_i ? 'true' : 'false'; ?>">
							<span class="aft-step__dot"><?php aft_the_icon( $aft_s['icon'], 24 ); ?></span>
							<span>
								<span class="aft-step__t"><?php echo esc_html( $aft_s['title'] ); ?></span>
								<span class="aft-step__s"><?php echo esc_html( $aft_s['sub'] ); ?></span>
							</span>
						</button>
					<?php endforeach; ?>
				</div>

				<?php foreach ( $aft_steps as $aft_i => $aft_s ) : ?>
					<div class="aft-journey__panel" data-step-panel="<?php echo esc_attr( $aft_s['key'] ); ?>"
						<?php echo 0 === $aft_i ? '' : 'hidden'; ?>>
						<h3><?php echo esc_html( $aft_s['sub'] ); ?></h3>
						<p><?php echo esc_html( $aft_s['desc'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
