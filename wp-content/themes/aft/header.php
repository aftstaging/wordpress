<?php
/**
 * Header: announcement bar, sticky nav, search, mobile drawer.
 *
 * @package AFT
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="aft-skip" href="#aft-main"><?php esc_html_e( 'Skip to content', 'aft' ); ?></a>

<?php if ( aft_get( 'topbar_on', true ) && aft_get( 'topbar_text' ) ) : ?>
	<div class="aft-topbar" id="aft-topbar">
		<div class="aft-container aft-topbar__in">
			<div class="aft-topbar__track">
				<?php for ( $aft_c = 0; $aft_c < 2; $aft_c++ ) : ?>
					<span class="aft-topbar__msg"<?php echo $aft_c ? ' aria-hidden="true"' : ''; ?>>
						<span class="aft-topbar__icon"><?php aft_the_icon( 'megaphone', 15 ); ?></span>
						<span><?php echo esc_html( aft_get( 'topbar_text' ) ); ?></span>
						<?php if ( aft_get( 'topbar_link' ) ) : ?>
							<a href="<?php echo esc_url( aft_get( 'topbar_link' ) ); ?>"<?php echo $aft_c ? ' tabindex="-1"' : ''; ?>>
								<?php echo esc_html( aft_get( 'topbar_link_text', __( 'Learn More', 'aft' ) ) ); ?>
							</a>
						<?php endif; ?>
					</span>
				<?php endfor; ?>
			</div>
		</div>
		<button class="aft-topbar__close" type="button" data-aft-topbar-close
			aria-label="<?php esc_attr_e( 'Dismiss announcement', 'aft' ); ?>">
			<?php aft_the_icon( 'close', 16 ); ?>
		</button>
	</div>
<?php endif; ?>

<header class="aft-header" id="aft-header">
	<div class="aft-container aft-header__in">

		<a class="aft-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php if ( has_custom_logo() ) : ?>
				<?php
				$logo_id = get_theme_mod( 'custom_logo' );
				echo wp_get_attachment_image( $logo_id, 'full', false, [ 'alt' => get_bloginfo( 'name' ) ] );
				?>
			<?php else : ?>
				<img src="<?php echo esc_url( AFT_URI . '/assets/img/partners/rounded-logo-white.png' ); ?>"
					alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="200" height="71">
			<?php endif; ?>
		</a>

		<nav class="aft-nav" aria-label="<?php esc_attr_e( 'Primary', 'aft' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( [
					'theme_location' => 'primary',
					'container'      => false,
					'depth'          => 2,
				] );
			} else {
				aft_render_fallback_menu();
			}
			?>
		</nav>

		<div class="aft-actions">
			<button class="aft-icnbtn" type="button" data-aft-search-toggle
				aria-expanded="false" aria-controls="aft-search"
				aria-label="<?php esc_attr_e( 'Search', 'aft' ); ?>">
				<?php aft_the_icon( 'search', 19 ); ?>
			</button>

			<a class="aft-btn aft-btn--primary aft-btn--sm aft-hide-sm"
				href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>">
				<?php aft_the_icon( 'user', 16 ); ?>
				<span><?php esc_html_e( 'Login / Student Portal', 'aft' ); ?></span>
			</a>

			<?php if ( function_exists( 'wc_get_cart_url' ) ) : ?>
				<a class="aft-icnbtn" href="<?php echo esc_url( wc_get_cart_url() ); ?>"
					aria-label="<?php esc_attr_e( 'View cart', 'aft' ); ?>">
					<?php aft_the_icon( 'cart', 19 ); ?>
					<span class="aft-cart__count" data-aft-cart-count><?php echo esc_html( aft_cart_count() ); ?></span>
				</a>
			<?php endif; ?>

			<button class="aft-icnbtn aft-burger" type="button" data-aft-drawer-open
				aria-label="<?php esc_attr_e( 'Open menu', 'aft' ); ?>" aria-expanded="false">
				<?php aft_the_icon( 'menu', 21 ); ?>
			</button>
		</div>
	</div>

	<div class="aft-search" id="aft-search">
		<div>
			<div class="aft-container">
				<?php get_search_form(); ?>
			</div>
		</div>
	</div>
</header>

<div class="aft-drawer" id="aft-drawer" role="dialog" aria-modal="true"
	aria-label="<?php esc_attr_e( 'Menu', 'aft' ); ?>">
	<div class="aft-drawer__scrim" data-aft-drawer-close></div>
	<div class="aft-drawer__panel">
		<div class="aft-drawer__top">
			<span class="aft-logo">
				<img src="<?php echo esc_url( AFT_URI . '/assets/img/partners/rounded-logo-white.png' ); ?>"
					alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="180" height="64">
			</span>
			<button class="aft-icnbtn" type="button" data-aft-drawer-close
				aria-label="<?php esc_attr_e( 'Close menu', 'aft' ); ?>">
				<?php aft_the_icon( 'close', 20 ); ?>
			</button>
		</div>

		<nav aria-label="<?php esc_attr_e( 'Mobile', 'aft' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( [ 'theme_location' => 'primary', 'container' => false, 'depth' => 2 ] );
			} else {
				aft_render_fallback_menu();
			}
			?>
		</nav>

		<div class="aft-drawer__cta">
			<a class="aft-btn aft-btn--primary aft-btn--block" href="<?php echo esc_url( aft_exam_url() ); ?>">
				<?php esc_html_e( 'Practice Exams', 'aft' ); ?><?php aft_the_icon( 'arrow-r', 16 ); ?>
			</a>
			<a class="aft-btn aft-btn--ghost aft-btn--block" href="<?php echo esc_url( home_url( '/courses/' ) ); ?>">
				<?php esc_html_e( 'Explore Courses', 'aft' ); ?>
			</a>
		</div>
	</div>
</div>

<main id="aft-main">
