<?php
/**
 * Team — real AFT people.
 *
 * Prefers a `team` post type if one exists; otherwise uses the curated list
 * in aft_team(), sourced from the client's own Our Team page.
 *
 * @package AFT
 */

defined( 'ABSPATH' ) || exit;

$aft_q = post_type_exists( 'team' ) ? new WP_Query( [
	'post_type' => 'team', 'posts_per_page' => 4, 'no_found_rows' => true,
] ) : null;
$aft_use_cpt = $aft_q && $aft_q->have_posts();
?>
<section class="aft-section aft-section--soft">
	<div class="aft-container">
		<div class="aft-head aft-head--row" data-reveal>
			<div>
				<p class="aft-eyebrow"><?php esc_html_e( 'Our Experts', 'aft' ); ?></p>
				<h2><?php esc_html_e( 'Learn From Experienced Professionals', 'aft' ); ?></h2>
				<p class="aft-lede"><?php esc_html_e( 'Our team of dedicated lecturers and accounting professionals are here to guide you.', 'aft' ); ?></p>
			</div>
			<a class="aft-link" href="<?php echo esc_url( aft_get( 'team_url', home_url( '/our-team/' ) ) ); ?>">
				<?php esc_html_e( 'Meet Our Team', 'aft' ); ?><?php aft_the_icon( 'arrow-r', 16 ); ?>
			</a>
		</div>

		<div class="aft-team">
			<?php if ( $aft_use_cpt ) : ?>
				<?php while ( $aft_q->have_posts() ) : $aft_q->the_post(); ?>
					<article class="aft-member" data-reveal>
						<div class="aft-member__ph">
							<?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'aft-card', [ 'loading' => 'lazy' ] ); } ?>
						</div>
						<div class="aft-member__body">
							<h3><?php the_title(); ?></h3>
							<div class="aft-member__role"><?php echo esc_html( get_post_meta( get_the_ID(), 'role', true ) ); ?></div>
							<div class="aft-member__cred"><?php echo esc_html( get_post_meta( get_the_ID(), 'credentials', true ) ); ?></div>
							<a class="aft-link" href="<?php the_permalink(); ?>">
								<?php esc_html_e( 'View Profile', 'aft' ); ?><?php aft_the_icon( 'arrow-r', 15 ); ?>
							</a>
						</div>
					</article>
				<?php endwhile; wp_reset_postdata(); ?>
			<?php else : ?>
				<?php foreach ( aft_team() as $aft_i => $aft_m ) : ?>
					<article class="aft-member" data-reveal data-delay="<?php echo esc_attr( min( 4, $aft_i ) ); ?>">
						<div class="aft-member__ph">
							<img src="<?php echo esc_url( $aft_m['photo'] ); ?>"
								alt="<?php echo esc_attr( $aft_m['name'] ); ?>"
								width="600" height="600" loading="lazy" decoding="async">
						</div>
						<div class="aft-member__body">
							<h3><?php echo esc_html( $aft_m['name'] ); ?></h3>
							<div class="aft-member__role"><?php echo esc_html( $aft_m['role'] ); ?></div>
							<div class="aft-member__cred"><?php echo esc_html( $aft_m['cred'] ); ?></div>
							<a class="aft-link" href="<?php echo esc_url( $aft_m['url'] ); ?>">
								<?php esc_html_e( 'View Profile', 'aft' ); ?><?php aft_the_icon( 'arrow-r', 15 ); ?>
							</a>
						</div>
					</article>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
</section>
