<?php defined( 'ABSPATH' ) || exit; ?>
<section class="aft-cta" data-reveal>
	<div class="aft-container">
		<h2><?php esc_html_e( 'Your Accounting Journey Starts Here.', 'aft' ); ?></h2>
		<p><?php esc_html_e( 'Whether you\'re starting your qualification, preparing for your next exam or taking the next step in your finance career, AFT is here to support your journey.', 'aft' ); ?></p>
		<div class="aft-cta__btns">
			<a class="aft-btn aft-btn--cyan" href="<?php echo esc_url( home_url( '/courses/' ) ); ?>">
				<?php esc_html_e( 'Explore Courses', 'aft' ); ?><?php aft_the_icon( 'arrow-r', 17 ); ?>
			</a>
			<a class="aft-btn aft-btn--onDark" href="<?php echo esc_url( aft_exam_url() ); ?>">
				<?php esc_html_e( 'Practice Exams', 'aft' ); ?>
			</a>
			<a class="aft-btn aft-btn--onDark" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">
				<?php esc_html_e( 'Talk to an Advisor', 'aft' ); ?>
			</a>
		</div>
	</div>
</section>
