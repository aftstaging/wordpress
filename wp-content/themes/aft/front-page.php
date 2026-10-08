<?php
/**
 * Homepage composition.
 *
 * Each block is a template part and can be toggled in
 * Customizer -> AFT Theme Settings -> Homepage Sections.
 *
 * @package AFT
 */

defined( 'ABSPATH' ) || exit;

get_header();

$aft_on = fn( $k ) => (bool) aft_get( 'show_' . $k, true );

get_template_part( 'template-parts/home/hero' );

if ( $aft_on( 'accred' ) && aft_accred_enabled() ) {
	get_template_part( 'template-parts/home/accreditation' );
}
if ( $aft_on( 'reviews' ) ) {
	get_template_part( 'template-parts/home/reviews' );
}
if ( $aft_on( 'stats' ) ) {
	get_template_part( 'template-parts/home/stats' );
}
if ( $aft_on( 'programmes' ) ) {
	get_template_part( 'template-parts/home/programmes' );
}
if ( $aft_on( 'journey' ) ) {
	get_template_part( 'template-parts/home/journey' );
}
// Featured Courses removed at client request (2026-09-29).
// The template part still exists at template-parts/home/courses.php —
// re-enable by restoring this block.
if ( $aft_on( 'exam' ) ) {
	get_template_part( 'template-parts/home/exam' );
}
if ( $aft_on( 'partners' ) ) {
	get_template_part( 'template-parts/home/partners' );
}
if ( $aft_on( 'accredx' ) && aft_accred_enabled() ) {
	get_template_part( 'template-parts/home/accreditation-feature' );
}
if ( $aft_on( 'story' ) ) {
	get_template_part( 'template-parts/home/story' );
}
if ( $aft_on( 'team' ) ) {
	get_template_part( 'template-parts/home/team' );
}
if ( $aft_on( 'approach' ) ) {
	get_template_part( 'template-parts/home/approach' );
}
// Shop teaser removed at client request (2026-09-29).
// Template part retained at template-parts/home/shop.php.
if ( $aft_on( 'posts' ) ) {
	get_template_part( 'template-parts/home/posts' );
}
if ( $aft_on( 'cta' ) ) {
	get_template_part( 'template-parts/home/cta' );
}

get_footer();
