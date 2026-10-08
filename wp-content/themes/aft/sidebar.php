<?php defined( 'ABSPATH' ) || exit;
if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
	<aside class="aft-sidebar"><?php dynamic_sidebar( 'sidebar-1' ); ?></aside>
<?php endif; ?>
