<?php
/**
 * Featured courses — pulled from Tutor LMS, never hard-coded.
 */
defined( 'ABSPATH' ) || exit;

$aft_has_tutor = function_exists( 'tutor' ) || post_type_exists( 'courses' );
$aft_q = $aft_has_tutor ? new WP_Query( [
	'post_type'           => 'courses',
	'posts_per_page'      => 6,
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
] ) : null;
?>
<section class="aft-section">
	<div class="aft-container">
		<div class="aft-head aft-head--row" data-reveal>
			<div>
				<p class="aft-eyebrow"><?php esc_html_e( 'Learn With AFT', 'aft' ); ?></p>
				<h2><?php esc_html_e( 'Featured Courses', 'aft' ); ?></h2>
				<p class="aft-lede"><?php esc_html_e( 'Structured courses, expert support and exam-focused preparation.', 'aft' ); ?></p>
			</div>
			<a class="aft-link" href="<?php echo esc_url( home_url( '/courses/' ) ); ?>">
				<?php esc_html_e( 'All Courses', 'aft' ); ?><?php aft_the_icon( 'arrow-r', 16 ); ?>
			</a>
		</div>

		<?php if ( $aft_q && $aft_q->have_posts() ) : ?>
			<div class="aft-courses">
				<?php while ( $aft_q->have_posts() ) : $aft_q->the_post(); ?>
					<article class="aft-course" data-reveal>
						<div class="aft-course__media">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'aft-card', [ 'loading' => 'lazy', 'alt' => get_the_title() ] ); ?>
							<?php endif; ?>
						</div>
						<div class="aft-course__body">
							<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<div class="aft-course__meta">
								<span><?php aft_the_icon( 'user', 14 ); ?><?php echo esc_html( get_the_author() ); ?></span>
								<span><?php aft_the_icon( 'clock', 14 ); ?><?php echo esc_html( aft_reading_time() ); ?></span>
							</div>
							<div class="aft-course__foot">
								<span class="aft-course__price">
									<?php
									$aft_price = function_exists( 'tutor_utils' ) ? tutor_utils()->get_course_price() : '';
									echo $aft_price ? wp_kses_post( $aft_price ) : esc_html__( 'View details', 'aft' );
									?>
								</span>
								<a class="aft-btn aft-btn--primary aft-btn--sm" href="<?php the_permalink(); ?>">
									<?php esc_html_e( 'View Course', 'aft' ); ?><?php aft_the_icon( 'arrow-r', 15 ); ?>
								</a>
							</div>
						</div>
					</article>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>
		<?php else : ?>
			<div class="aft-empty">
				<b><?php esc_html_e( 'Tutor LMS courses will appear here', 'aft' ); ?></b>
				<?php esc_html_e( 'This block reads live from Tutor LMS. Activate the plugin and the real courses render automatically — nothing is hard-coded.', 'aft' ); ?>
			</div>
		<?php endif; ?>
	</div>
</section>
