<?php
defined( 'ABSPATH' ) || exit;
$aft_q = new WP_Query( [ 'posts_per_page' => 4, 'ignore_sticky_posts' => true, 'no_found_rows' => true ] );
if ( ! $aft_q->have_posts() ) { return; }
?>
<section class="aft-section">
	<div class="aft-container">
		<div class="aft-head aft-head--row" data-reveal>
			<div>
				<p class="aft-eyebrow"><?php esc_html_e( 'Latest Articles & Insights', 'aft' ); ?></p>
				<h2><?php esc_html_e( 'Accounting Insights & News', 'aft' ); ?></h2>
			</div>
			<a class="aft-link" href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/blog/' ) ); ?>">
				<?php esc_html_e( 'View All Posts', 'aft' ); ?><?php aft_the_icon( 'arrow-r', 16 ); ?>
			</a>
		</div>
		<div class="aft-posts">
			<?php while ( $aft_q->have_posts() ) : $aft_q->the_post(); ?>
				<article class="aft-post" data-reveal>
					<div class="aft-post__media">
						<?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'aft-card', [ 'loading' => 'lazy' ] ); } ?>
					</div>
					<div class="aft-post__body">
						<?php $aft_c = get_the_category(); ?>
						<?php if ( $aft_c ) : ?>
							<span class="aft-post__cat"><?php echo esc_html( $aft_c[0]->name ); ?></span>
						<?php endif; ?>
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p class="aft-post__ex"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 16 ) ); ?></p>
						<div class="aft-post__meta">
							<span><?php echo esc_html( get_the_date() ); ?></span>
							<span><?php echo esc_html( aft_reading_time() ); ?></span>
						</div>
					</div>
				</article>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
	</div>
</section>
