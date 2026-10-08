<?php defined( 'ABSPATH' ) || exit; ?>
<section class="aft-section">
	<div class="aft-container">
		<div class="aft-exam" data-reveal>
			<div class="aft-exam__grid">
				<div>
					<p class="aft-eyebrow"><?php esc_html_e( 'Exams & Practice', 'aft' ); ?></p>
					<h2><?php echo esc_html( aft_get( 'exam_title' ) ); ?></h2>
					<p><?php echo esc_html( aft_get( 'exam_text' ) ); ?></p>
					<ul class="aft-exam__list">
						<?php foreach ( [
							__( 'Realistic case-study exams', 'aft' ),
							__( 'Objective-test practice', 'aft' ),
							__( 'Written-response practice', 'aft' ),
							__( 'Timed practice, autosaved', 'aft' ),
							__( 'Track your exam readiness', 'aft' ),
						] as $aft_f ) : ?>
							<li><?php aft_the_icon( 'check-c', 18 ); ?><?php echo esc_html( $aft_f ); ?></li>
						<?php endforeach; ?>
					</ul>
					<div class="aft-exam__cta">
						<a class="aft-btn aft-btn--primary" href="<?php echo esc_url( aft_exam_url() ); ?>">
							<?php esc_html_e( 'Enter Exam Portal', 'aft' ); ?><?php aft_the_icon( 'arrow-r', 17 ); ?>
						</a>
						<a class="aft-btn aft-btn--onDark" href="<?php echo esc_url( home_url( '/courses/' ) ); ?>">
							<?php esc_html_e( 'Explore Exam Preparation', 'aft' ); ?>
						</a>
					</div>
				</div>

				<div class="aft-exam__media">
					<div class="aft-laptop" data-parallax data-speed="0.04">
						<div class="aft-laptop__bar"><i></i><i></i><i></i></div>
						<div class="aft-laptop__screen">
							<img src="<?php echo esc_url( AFT_URI . '/assets/img/exam-portal.jpg' ); ?>"
								alt="<?php esc_attr_e( 'The AFT Exam Portal dashboard', 'aft' ); ?>"
								width="1440" height="810" loading="lazy" decoding="async">
						</div>
					</div>
					<span class="aft-exam__float aft-exam__float--a">
						<b><?php esc_html_e( '45 min', 'aft' ); ?></b>
						<span><?php esc_html_e( 'Timed practice', 'aft' ); ?></span>
					</span>
					<span class="aft-exam__float aft-exam__float--b">
						<b><?php esc_html_e( 'Autosaved', 'aft' ); ?></b>
						<span><?php esc_html_e( 'Every response', 'aft' ); ?></span>
					</span>
				</div>
			</div>
		</div>
	</div>
</section>
