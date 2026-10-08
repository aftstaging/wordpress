<?php defined( 'ABSPATH' ) || exit; get_header(); ?>
<section class="aft-section">
	<div class="aft-container">
		<div class="aft-head" data-reveal>
			<h1><?php echo esc_html( is_home() ? get_the_title( get_option( 'page_for_posts' ) ) ?: __( 'News & Posts', 'aft' ) : __( 'Archive', 'aft' ) ); ?></h1>
		</div>
		<?php if ( have_posts() ) : ?>
			<div class="aft-posts">
				<?php while ( have_posts() ) : the_post(); ?>
					<article <?php post_class( 'aft-post' ); ?> data-reveal>
						<div class="aft-post__media">
							<?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'aft-card', [ 'loading' => 'lazy' ] ); } ?>
						</div>
						<div class="aft-post__body">
							<?php $c = get_the_category(); if ( $c ) : ?>
								<span class="aft-post__cat"><?php echo esc_html( $c[0]->name ); ?></span>
							<?php endif; ?>
							<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p class="aft-post__ex"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
							<div class="aft-post__meta">
								<span><?php echo esc_html( get_the_date() ); ?></span>
								<span><?php echo esc_html( aft_reading_time() ); ?></span>
							</div>
						</div>
					</article>
				<?php endwhile; ?>
			</div>
			<div style="margin-top:36px"><?php the_posts_pagination( [ 'mid_size' => 1 ] ); ?></div>
		<?php else : ?>
			<div class="aft-empty"><b><?php esc_html_e( 'Nothing found', 'aft' ); ?></b></div>
		<?php endif; ?>
	</div>
</section>
<?php get_footer(); ?>
