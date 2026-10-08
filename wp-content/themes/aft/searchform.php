<?php defined( 'ABSPATH' ) || exit; ?>
<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="aft-s"><?php esc_html_e( 'Search', 'aft' ); ?></label>
	<input type="search" id="aft-s" name="s" value="<?php echo get_search_query(); ?>"
		placeholder="<?php esc_attr_e( 'Search courses, articles, products…', 'aft' ); ?>">
	<button class="aft-btn aft-btn--primary" type="submit"><?php esc_html_e( 'Search', 'aft' ); ?></button>
</form>
