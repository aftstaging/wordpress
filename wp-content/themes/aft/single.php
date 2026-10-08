<?php
/**
 * Single post — sidebar with table of contents + related posts.
 *
 * @package AFT
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$aft_content   = apply_filters( 'the_content', get_the_content() );
	$aft_content   = aft_normalize_article_content( $aft_content, get_the_ID() );
	$aft_processed = aft_toc_process( $aft_content );
	$aft_toc       = $aft_processed['toc'];
	// Load a generous pool; the browser reveals only as many as fit the article.
	$aft_related   = aft_related_posts( get_the_ID(), 40 );
	$aft_cats      = get_the_category();
	?>

	<article <?php post_class( 'aft-single' ); ?>>

		<header class="aft-single__head"><meta charset="utf-8">
			<div class="aft-container">
				<nav class="aft-crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'aft' ); ?>">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'aft' ); ?></a>
					<span aria-hidden="true">/</span>
					<a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'Blog', 'aft' ); ?></a>
					<?php if ( $aft_cats ) : ?>
						<span aria-hidden="true">/</span>
						<span><?php echo esc_html( $aft_cats[0]->name ); ?></span>
					<?php endif; ?>
				</nav>

				<?php if ( $aft_cats ) : ?>
					<p class="aft-eyebrow"><?php echo esc_html( $aft_cats[0]->name ); ?></p>
				<?php endif; ?>

				<h1 class="aft-single__title"><?php the_title(); ?></h1>

				<?php if ( has_excerpt() ) : ?>
					<p class="aft-single__standfirst"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>

				<ul class="aft-single__meta">
					<li><?php aft_the_icon( 'user', 15 ); ?><?php echo esc_html( get_the_author() ); ?></li>
					<li><?php aft_the_icon( 'calendar', 15 ); ?><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></li>
					<li><?php aft_the_icon( 'clock', 15 ); ?><?php echo esc_html( aft_reading_time() ); ?></li>
				</ul>
			</div>
		</header>

		<div class="aft-container">
			<div class="aft-single__layout">

				<aside class="aft-aside" aria-label="<?php esc_attr_e( 'Article navigation', 'aft' ); ?>">
					<div class="aft-aside__inner">

						<?php if ( count( $aft_toc ) > 1 ) : ?>
							<nav class="aft-toc" aria-labelledby="aft-toc-title">
								<h2 class="aft-aside__title" id="aft-toc-title"><?php esc_html_e( 'On this page', 'aft' ); ?></h2>
								<ol>
									<?php foreach ( $aft_toc as $aft_h ) : ?>
										<li class="aft-toc__l<?php echo (int) $aft_h['level']; ?>">
											<a href="#<?php echo esc_attr( $aft_h['id'] ); ?>"><?php echo esc_html( $aft_h['text'] ); ?></a>
										</li>
									<?php endforeach; ?>
								</ol>
							</nav>
						<?php endif; ?>

						<?php if ( $aft_related ) : ?>
							<section class="aft-rel" aria-labelledby="aft-rel-title" data-aft-related>
								<h2 class="aft-aside__title" id="aft-rel-title"><?php esc_html_e( 'Related reading', 'aft' ); ?></h2>
								<ul data-aft-related-list>
									<?php foreach ( $aft_related as $aft_i => $aft_r ) : ?>
										<li data-aft-related-item<?php echo $aft_i >= 4 ? ' hidden' : ''; ?>>
											<a href="<?php echo esc_url( get_permalink( $aft_r ) ); ?>">
												<?php echo aft_related_thumbnail_html( $aft_r->ID ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
												<span>
													<span class="aft-rel__t"><?php echo esc_html( get_the_title( $aft_r ) ); ?></span>
													<span class="aft-rel__m"><?php echo esc_html( aft_reading_time( $aft_r->ID ) ); ?></span>
												</span>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>
							</section>
						<?php endif; ?>

						<div class="aft-aside__cta">
							<p><?php esc_html_e( 'Ready to start your qualification?', 'aft' ); ?></p>
							<a class="aft-btn aft-btn--primary aft-btn--sm aft-btn--block" href="<?php echo esc_url( home_url( '/courses/' ) ); ?>">
								<?php esc_html_e( 'Explore Courses', 'aft' ); ?><?php aft_the_icon( 'arrow-r', 15 ); ?>
							</a>
						</div>
					</div>
				</aside>

				<div class="aft-single__main">
					<?php if ( has_post_thumbnail() ) : ?>
						<figure class="aft-single__hero">
							<?php the_post_thumbnail( 'aft-wide', [ 'alt' => get_the_title() ] ); ?>
						</figure>
					<?php endif; ?>

					<div class="aft-prose"><?php echo $aft_processed['html']; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>

					<?php
					$aft_tags = get_the_tags();
					if ( $aft_tags ) :
						?>
						<div class="aft-tags">
							<?php foreach ( $aft_tags as $aft_t ) : ?>
								<a href="<?php echo esc_url( get_tag_link( $aft_t ) ); ?>">#<?php echo esc_html( $aft_t->name ); ?></a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<nav class="aft-pager" aria-label="<?php esc_attr_e( 'More posts', 'aft' ); ?>">
						<?php
						$aft_prev = get_previous_post();
						$aft_next = get_next_post();
						?>
						<?php if ( $aft_prev ) : ?>
							<a class="aft-pager__a" href="<?php echo esc_url( get_permalink( $aft_prev ) ); ?>">
								<span><?php aft_the_icon( 'arrow-l', 15 ); ?><?php esc_html_e( 'Previous', 'aft' ); ?></span>
								<b><?php echo esc_html( get_the_title( $aft_prev ) ); ?></b>
							</a>
						<?php endif; ?>
						<?php if ( $aft_next ) : ?>
							<a class="aft-pager__a aft-pager__a--next" href="<?php echo esc_url( get_permalink( $aft_next ) ); ?>">
								<span><?php esc_html_e( 'Next', 'aft' ); ?><?php aft_the_icon( 'arrow-r', 15 ); ?></span>
								<b><?php echo esc_html( get_the_title( $aft_next ) ); ?></b>
							</a>
						<?php endif; ?>
					</nav>

					<?php if ( comments_open() || get_comments_number() ) { comments_template(); } ?>
				</div>
			</div>
		</div>
	</article>

<?php
endwhile;

get_footer();
