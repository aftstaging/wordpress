<?php
/**
 * Hero — two distinct heroes that cross-fade.
 *
 * Hero A: brand / value proposition.
 * Hero B: admissions campaign — a different composition, background and
 *         rhythm, so it reads as a new hero rather than a second slide.
 *
 * Slides stack; the inactive one is taken out of flow and faded to 0.
 *
 * @package AFT
 */

defined( 'ABSPATH' ) || exit;

$aft_total    = 2;
$aft_interval = max( 3, (int) aft_get( 'hero_interval' ) ) * 1000;
?>
<section class="aft-hero" data-hero data-hero-interval="<?php echo esc_attr( $aft_interval ); ?>"
	aria-roledescription="carousel"
	aria-label="<?php esc_attr_e( 'Featured highlights', 'aft' ); ?>">

	<div class="aft-hero__viewport" data-hero-viewport>

		<!-- ============================================== HERO A ======= -->
		<div class="aft-hero__slide aft-hero__slide--brand is-active" data-hero-slide role="group"
			aria-roledescription="<?php esc_attr_e( 'slide', 'aft' ); ?>"
			aria-label="<?php printf( esc_attr__( '1 of %d', 'aft' ), $aft_total ); ?>">
			<div class="aft-container">
				<div class="aft-hero__grid">

					<div class="aft-hero__copy">
						<?php if ( aft_get( 'hero_eyebrow' ) ) : ?>
							<p class="aft-eyebrow"><?php echo esc_html( aft_get( 'hero_eyebrow' ) ); ?></p>
						<?php endif; ?>

						<h1 class="aft-hero__title">
							<?php echo esc_html( aft_get( 'hero_title' ) ); ?>
							<span class="aft-grad"><?php echo esc_html( aft_get( 'hero_title_accent' ) ); ?></span>
						</h1>

						<p class="aft-hero__lede"><?php echo esc_html( aft_get( 'hero_lede' ) ); ?></p>

						<div class="aft-hero__cta">
							<a class="aft-btn aft-btn--primary" href="<?php echo esc_url( aft_get( 'hero_cta1_url', home_url( '/courses/' ) ) ); ?>">
								<?php echo esc_html( aft_get( 'hero_cta1_text' ) ); ?>
								<?php aft_the_icon( 'arrow-r', 17 ); ?>
							</a>
							<a class="aft-btn aft-btn--ghost" href="<?php echo esc_url( aft_exam_url() ); ?>">
								<?php echo esc_html( aft_get( 'hero_cta2_text' ) ); ?>
								<?php aft_the_icon( 'arrow-r', 17 ); ?>
							</a>
						</div>

						<ul class="aft-hero__trust">
							<?php foreach ( [
								__( 'Expert tutors', 'aft' ),
								__( 'Flexible online learning', 'aft' ),
								__( 'Exam-focused preparation', 'aft' ),
							] as $aft_t ) : ?>
								<li><span class="aft-hero__tick"><?php aft_the_icon( 'check', 13 ); ?></span><?php echo esc_html( $aft_t ); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>

					<div class="aft-hero__media">
						<div class="aft-hero__imgwrap">
							<?php
							$aft_img = aft_get( 'hero_image' );
							if ( $aft_img ) {
								printf(
									'<img src="%s" alt="%s" width="900" height="720" fetchpriority="high" decoding="async">',
									esc_url( $aft_img ),
									esc_attr__( 'Accounting student learning with Accountants For Tomorrow', 'aft' )
								);
							} else {
								echo '<div class="aft-imgslot"></div>';
							}
							?>
						</div>

						<?php if ( aft_get( 'hero_cards', true ) ) : ?>
							<div class="aft-float aft-float--progress">
								<p class="aft-float__label"><?php esc_html_e( 'My Learning Progress', 'aft' ); ?></p>
								<div class="aft-prog">
									<?php foreach ( [
										[ __( 'Financial Accounting', 'aft' ), 75 ],
										[ __( 'Taxation', 'aft' ), 62 ],
										[ __( 'Audit & Assurance', 'aft' ), 48 ],
										[ __( 'Management Accounting', 'aft' ), 82 ],
									] as $aft_r ) : ?>
										<div class="aft-prog__row">
											<div class="aft-prog__meta"><span><?php echo esc_html( $aft_r[0] ); ?></span><b><?php echo (int) $aft_r[1]; ?>%</b></div>
											<div class="aft-prog__bar"><span class="aft-prog__fill" data-fill="<?php echo (int) $aft_r[1]; ?>"></span></div>
										</div>
									<?php endforeach; ?>
								</div>
							</div>

							<div class="aft-float aft-float--score">
								<p class="aft-float__label"><?php esc_html_e( 'Latest Exam Score', 'aft' ); ?></p>
								<div class="aft-score"><span>82%</span></div>
								<span class="aft-badge-ok"><?php esc_html_e( 'Excellent', 'aft' ); ?></span>
								<p><?php esc_html_e( 'Illustrative portal preview', 'aft' ); ?></p>
							</div>
						<?php endif; ?>

						<?php if ( aft_get( 'hero_students' ) ) : ?>
							<div class="aft-float aft-float--students">
								<span class="aft-ico"><?php aft_the_icon( 'users', 19 ); ?></span>
								<span>
									<b><?php echo esc_html( aft_get( 'hero_students' ) ); ?></b>
									<span><?php esc_html_e( 'Students / learners', 'aft' ); ?></span>
								</span>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<!-- ============================================== HERO B ======= -->
		<div class="aft-hero__slide aft-hero__slide--admissions" data-hero-slide role="group"
			aria-roledescription="<?php esc_attr_e( 'slide', 'aft' ); ?>"
			aria-label="<?php printf( esc_attr__( '2 of %d', 'aft' ), $aft_total ); ?>">

			<span class="aft-adm__glow" aria-hidden="true"></span>

			<div class="aft-container aft-adm">
				<div class="aft-adm__copy">
					<?php if ( aft_get( 'hero2_eyebrow' ) ) : ?>
						<p class="aft-eyebrow aft-eyebrow--chip">
							<?php aft_the_icon( 'shield-c', 15 ); ?>
							<?php echo esc_html( aft_get( 'hero2_eyebrow' ) ); ?>
						</p>
					<?php endif; ?>

					<h1 class="aft-adm__title">
						<span><?php echo esc_html( aft_get( 'hero2_title' ) ); ?></span>
						<em><?php echo esc_html( aft_get( 'hero2_year' ) ); ?></em>
					</h1>

					<p class="aft-adm__kicker"><?php echo esc_html( aft_get( 'hero2_kicker' ) ); ?></p>

					<ul class="aft-adm__routes">
						<li>
							<span class="aft-adm__ico"><?php aft_the_icon( 'book', 18 ); ?></span>
							<span>
								<b><?php echo esc_html( aft_get( 'hero2_route1_title' ) ); ?></b>
								<i><?php echo esc_html( aft_get( 'hero2_route1_text' ) ); ?></i>
							</span>
						</li>
						<li>
							<span class="aft-adm__ico aft-adm__ico--alt"><?php aft_the_icon( 'bolt', 18 ); ?></span>
							<span>
								<b><?php echo esc_html( aft_get( 'hero2_route2_title' ) ); ?></b>
								<i><?php echo esc_html( aft_get( 'hero2_route2_text' ) ); ?></i>
							</span>
						</li>
					</ul>

					<div class="aft-hero__cta">
						<a class="aft-btn aft-btn--primary" href="<?php echo esc_url( aft_get( 'hero2_cta_url', home_url( '/courses/' ) ) ); ?>">
							<?php echo esc_html( aft_get( 'hero2_cta_text' ) ); ?>
							<?php aft_the_icon( 'arrow-r', 17 ); ?>
						</a>
						<a class="aft-btn aft-btn--ghost" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">
							<?php esc_html_e( 'Talk to an Advisor', 'aft' ); ?>
						</a>
					</div>
				</div>

				<div class="aft-adm__figure">
					<img src="<?php echo esc_url( AFT_URI . '/assets/img/aft-student.png' ); ?>"
						alt="<?php esc_attr_e( 'Accountants For Tomorrow student advisor', 'aft' ); ?>"
						width="1190" height="1000" loading="lazy" decoding="async">
					<?php if ( aft_accred_enabled() && aft_accred_badge() ) : ?>
						<span class="aft-adm__seal">
							<img src="<?php echo esc_url( aft_accred_badge() ); ?>"
								alt="<?php esc_attr_e( 'CIMA Accredited Global Learning Provider', 'aft' ); ?>" loading="lazy">
						</span>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<div class="aft-hero__controls">
		<div class="aft-container aft-hero__controls-in">
			<button class="aft-hero__arrow" type="button" data-hero-prev
				aria-label="<?php esc_attr_e( 'Previous slide', 'aft' ); ?>"><?php aft_the_icon( 'chev-l', 18 ); ?></button>
			<div class="aft-hero__dots" role="tablist" aria-label="<?php esc_attr_e( 'Choose slide', 'aft' ); ?>">
				<?php for ( $i = 0; $i < $aft_total; $i++ ) : ?>
					<button class="aft-hero__dot<?php echo 0 === $i ? ' is-active' : ''; ?>" type="button"
						role="tab" data-hero-dot="<?php echo (int) $i; ?>"
						aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>"
						aria-label="<?php printf( esc_attr__( 'Slide %d', 'aft' ), $i + 1 ); ?>"></button>
				<?php endfor; ?>
			</div>
			<button class="aft-hero__arrow" type="button" data-hero-next
				aria-label="<?php esc_attr_e( 'Next slide', 'aft' ); ?>"><?php aft_the_icon( 'chev-r', 18 ); ?></button>
		</div>
	</div>
</section>
