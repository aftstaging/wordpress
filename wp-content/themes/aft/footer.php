<?php
/**
 * Footer.
 *
 * @package AFT
 */

defined( 'ABSPATH' ) || exit;

$aft_socials = [
	'facebook'  => aft_get( 'social_facebook' ),
	'instagram' => aft_get( 'social_instagram' ),
	'linkedin'  => aft_get( 'social_linkedin' ),
	'youtube'   => aft_get( 'social_youtube' ),
	'twitter'   => aft_get( 'social_twitter' ),
];
?>
</main>

<footer class="aft-footer">
	<div class="aft-container">
		<div class="aft-footer__grid">

			<div>
				<span class="aft-logo">
					<img src="<?php echo esc_url( AFT_URI . '/assets/img/partners/rounded-logo-white.png' ); ?>"
						alt="" width="42" height="42" loading="lazy">
					<span class="aft-logo__txt">
						<b><?php esc_html_e( 'Accountants', 'aft' ); ?></b>
						<span><?php esc_html_e( 'for Tomorrow', 'aft' ); ?></span>
					</span>
				</span>
				<p class="aft-footer__about">
					<?php echo esc_html( aft_get( 'footer_about', __( 'Empowering the accountants of tomorrow.', 'aft' ) ) ); ?>
				</p>
				<?php if ( array_filter( $aft_socials ) ) : ?>
					<div class="aft-social">
						<?php foreach ( $aft_socials as $net => $url ) : ?>
							<?php if ( $url ) : ?>
								<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer"
									aria-label="<?php echo esc_attr( ucfirst( $net ) ); ?>">
									<?php aft_the_icon( $net, 17 ); ?>
								</a>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<div>
				<h4><?php esc_html_e( 'Quick Links', 'aft' ); ?></h4>
				<?php
				if ( has_nav_menu( 'footer_1' ) ) {
					wp_nav_menu( [ 'theme_location' => 'footer_1', 'container' => false, 'depth' => 1 ] );
				} else {
					echo '<ul>';
					foreach ( [
						__( 'About Us', 'aft' )    => home_url( '/about/' ),
						__( 'Courses', 'aft' )     => home_url( '/courses/' ),
						__( 'FLP', 'aft' )         => home_url( '/cima-financial-leadership-programme-flp' ),
						__( 'Blog', 'aft' )        => home_url( '/blog/' ),
						__( 'Book Store', 'aft' )  => home_url( '/product-category/books/' ),
						__( 'Answer Base', 'aft' ) => home_url( '/ai-assistant/' ),
						__( 'Contact', 'aft' )     => home_url( '/contact/' ),
					] as $l => $u ) {
						printf( '<li><a href="%s">%s</a></li>', esc_url( $u ), esc_html( $l ) );
					}
					echo '</ul>';
				}
				?>
			</div>

			<div>
				<h4><?php esc_html_e( 'Learning', 'aft' ); ?></h4>
				<?php
				if ( has_nav_menu( 'footer_2' ) ) {
					wp_nav_menu( [ 'theme_location' => 'footer_2', 'container' => false, 'depth' => 1 ] );
				} else {
					echo '<ul>';
					foreach ( [
						__( 'Cima', 'aft' )             => home_url( '/new/cima-courses-4/' ),
						__( 'Acca', 'aft' )             => home_url( '/acca-courses/' ),
						__( 'CIMA FLP', 'aft' )         => home_url( '/cima-financial-leadership-programme-flp' ),
						__( 'All Courses', 'aft' )      => home_url( '/courses/' ),
						__( 'Exam Preparation', 'aft' ) => aft_exam_url(),
					] as $l => $u ) {
						printf( '<li><a href="%s">%s</a></li>', esc_url( $u ), esc_html( $l ) );
					}
					echo '</ul>';
				}
				?>
			</div>

			<div>
				<h4><?php esc_html_e( 'Resources', 'aft' ); ?></h4>
				<?php
				if ( has_nav_menu( 'footer_3' ) ) {
					wp_nav_menu( [ 'theme_location' => 'footer_3', 'container' => false, 'depth' => 1 ] );
				} else {
					echo '<ul>';
					foreach ( [
						__( 'Answer Base', 'aft' )        => home_url( '/ai-assistant/' ),
						__( 'Book Store', 'aft' )         => home_url( '/product-category/books/' ),
						__( 'Company Profile', 'aft' )    => home_url( '/company-profile/' ),
						__( 'Products & Services', 'aft' )=> home_url( '/products-services' ),
						__( 'Our Team', 'aft' )           => home_url( '/our-team/' ),
					] as $l => $u ) {
						printf( '<li><a href="%s">%s</a></li>', esc_url( $u ), esc_html( $l ) );
					}
					echo '</ul>';
				}
				?>
			</div>

			<?php if ( aft_get( 'phone' ) || aft_get( 'email' ) || aft_get( 'address' ) ) : ?>
			<div>
				<h4><?php esc_html_e( 'Get In Touch', 'aft' ); ?></h4>
				<ul class="aft-contact">
					<?php if ( aft_get( 'phone' ) ) : ?>
						<li><?php aft_the_icon( 'phone', 17 ); ?>
							<a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', aft_get( 'phone' ) ) ); ?>">
								<?php echo esc_html( aft_get( 'phone' ) ); ?></a></li>
					<?php endif; ?>
					<?php if ( aft_get( 'email' ) ) : ?>
						<li><?php aft_the_icon( 'mail', 17 ); ?>
							<a href="mailto:<?php echo esc_attr( aft_get( 'email' ) ); ?>">
								<?php echo esc_html( aft_get( 'email' ) ); ?></a></li>
					<?php endif; ?>
					<?php if ( aft_get( 'address' ) ) : ?>
						<li><?php aft_the_icon( 'pin', 17 ); ?><span><?php echo esc_html( aft_get( 'address' ) ); ?></span></li>
					<?php endif; ?>
				</ul>
			</div>
			<?php endif; ?>
		</div>

		<div class="aft-footer__bar">
			<span>
				&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?>
				<?php echo esc_html( aft_get( 'footer_copy', get_bloginfo( 'name' ) . '. ' . __( 'All rights reserved.', 'aft' ) ) ); ?>
			</span>
			<span class="aft-footer__credit">
				<?php esc_html_e( 'Website developed with', 'aft' ); ?>
				<span class="aft-footer__heart" aria-hidden="true">&#10084;&#65039;</span>
				<span class="screen-reader-text"><?php esc_html_e( 'love', 'aft' ); ?></span>
				<?php esc_html_e( 'by', 'aft' ); ?>
				<a href="https://crazyclicks.co.za/industries/universities/" target="_blank" rel="noopener noreferrer">Crazyclicks Digital</a>
			</span>
			<?php if ( aft_accred_enabled() && aft_accred_badge() ) : ?>
				<span class="aft-footer__marks">
					<img src="<?php echo esc_url( aft_accred_badge() ); ?>"
						alt="<?php esc_attr_e( 'CIMA Accredited Global Learning Provider', 'aft' ); ?>" loading="lazy">
				</span>
			<?php endif; ?>
		</div>
	</div>
</footer>

<?php if ( is_front_page() || is_home() ) : ?>
	<div class="aft-mobar" role="region" aria-label="<?php esc_attr_e( 'Quick actions', 'aft' ); ?>">
		<a class="aft-btn aft-btn--primary" href="<?php echo esc_url( home_url( '/courses/' ) ); ?>">
			<span><?php esc_html_e( 'Explore Courses', 'aft' ); ?></span>
		</a>
		<a class="aft-btn aft-btn--ghost" href="<?php echo esc_url( aft_exam_url() ); ?>">
			<span><?php esc_html_e( 'Practice Exams', 'aft' ); ?></span>
		</a>
	</div>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
