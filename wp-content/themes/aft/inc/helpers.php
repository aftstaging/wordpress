<?php
/**
 * Icons and small view helpers.
 *
 * @package AFT
 */

defined( 'ABSPATH' ) || exit;

/**
 * Inline SVG icon set. Inline (not a font/sprite file) so icons inherit
 * currentColor, need no extra request, and survive the preview sandbox.
 */
function aft_icon( $name, $size = 20, $extra_class = '' ) {
	$stroke = '<svg class="aft-i %5$s" width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="%2$s" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%3$s</svg>';
	$solid  = '<svg class="aft-i %5$s" width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">%4$s</svg>';

	$w = 1.75;

	$paths = [
		'arrow-r'   => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'arrow-l'   => '<path d="M19 12H5M11 18l-6-6 6-6"/>',
		'chev-r'    => '<path d="M9 18l6-6-6-6"/>',
		'chev-l'    => '<path d="M15 18l-6-6 6-6"/>',
		'chev-d'    => '<path d="M6 9l6 6 6-6"/>',
		'check'     => '<path d="M20 6L9 17l-5-5"/>',
		'check-c'   => '<circle cx="12" cy="12" r="10"/><path d="M8.5 12.5l2.5 2.5 4.5-5"/>',
		'search'    => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/>',
		'user'      => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7"/>',
		'users'     => '<circle cx="9" cy="8" r="3.4"/><path d="M2.5 20c0-3.3 2.9-6 6.5-6s6.5 2.7 6.5 6"/><path d="M17 11.2A3.4 3.4 0 0 0 17 4.5"/><path d="M19 20c0-2.3-1-4.3-2.6-5.4"/>',
		'cart'      => '<circle cx="9" cy="20" r="1.6"/><circle cx="18" cy="20" r="1.6"/><path d="M2 3h3l2.4 12.2a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.6L21 7H6"/>',
		'menu'      => '<path d="M3 6h18M3 12h18M3 18h18"/>',
		'close'     => '<path d="M18 6L6 18M6 6l12 12"/>',
		'star'      => '<path d="M12 2.6l2.9 5.9 6.5.95-4.7 4.6 1.1 6.45L12 17.45 6.2 20.5l1.1-6.45-4.7-4.6 6.5-.95z"/>',
		'book'      => '<path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v16H6.5A2.5 2.5 0 0 0 4 20.5z"/><path d="M4 16.5A2.5 2.5 0 0 1 6.5 14H20"/>',
		'cap'       => '<path d="M22 9L12 4 2 9l10 5 10-5z"/><path d="M6 11.5V16c0 1.7 2.7 3 6 3s6-1.3 6-3v-4.5"/>',
		'award'     => '<circle cx="12" cy="9" r="5.5"/><path d="M8.5 13.5L7 22l5-2.6L17 22l-1.5-8.5"/>',
		'shield'    => '<path d="M12 22s8-3.4 8-10V5.5L12 2.5 4 5.5V12c0 6.6 8 10 8 10z"/>',
		'shield-c'  => '<path d="M12 22s8-3.4 8-10V5.5L12 2.5 4 5.5V12c0 6.6 8 10 8 10z"/><path d="M8.8 12.2l2.2 2.2 4.2-4.6"/>',
		'chart'     => '<path d="M3 3v18h18"/><path d="M7 15l3.5-4 3 3L20 7"/>',
		'target'    => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.4"/>',
		'clock'     => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5.2l3.2 1.9"/>',
		'calendar'  => '<rect x="3" y="5" width="18" height="16" rx="2.5"/><path d="M3 10h18M8 3v4M16 3v4"/>',
		'laptop'    => '<rect x="3" y="4" width="18" height="12" rx="2"/><path d="M2 20h20"/>',
		'monitor'   => '<rect x="2.5" y="3.5" width="19" height="13" rx="2.2"/><path d="M8.5 21h7M12 16.5V21"/>',
		'spark'     => '<path d="M12 2.5l1.9 5.6 5.6 1.9-5.6 1.9L12 17.5l-1.9-5.6L4.5 10l5.6-1.9z"/>',
		'bolt'      => '<path d="M13 2L4.5 13.5H11l-1 8.5 8.5-11.5H12z"/>',
		'phone'     => '<path d="M21.5 16.9v2.6a2 2 0 0 1-2.2 2 19.5 19.5 0 0 1-8.5-3 19.2 19.2 0 0 1-5.9-5.9 19.5 19.5 0 0 1-3-8.6A2 2 0 0 1 3.9 2h2.6a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.9a2 2 0 0 1-.5 2.1L7.6 9.9a16 16 0 0 0 5.9 5.9l1.2-1.1a2 2 0 0 1 2.1-.5c.9.3 1.9.6 2.9.7a2 2 0 0 1 1.8 2z"/>',
		'mail'      => '<rect x="2.5" y="4.5" width="19" height="15" rx="2.4"/><path d="M3 6.5l9 6.2 9-6.2"/>',
		'globe'     => '<circle cx="12" cy="12" r="9.5"/><path d="M2.8 12h18.4M12 2.5c2.4 2.6 3.5 5.8 3.5 9.5S14.4 18.9 12 21.5C9.6 18.9 8.5 15.7 8.5 12S9.6 5.1 12 2.5z"/>',
		'message'   => '<path d="M4 5.5h16a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H11l-5.5 3v-3H4a2 2 0 0 1-2-2v-9a2 2 0 0 1 2-2z"/>',
		'pin'       => '<path d="M20 10.5c0 6-8 11.5-8 11.5S4 16.5 4 10.5a8 8 0 1 1 16 0z"/><circle cx="12" cy="10.3" r="2.8"/>',
		'heart'     => '<path d="M20.4 5.6a5 5 0 0 0-7.1 0L12 6.9l-1.3-1.3a5 5 0 1 0-7.1 7.1L12 21l8.4-8.3a5 5 0 0 0 0-7.1z"/>',
		'play'      => '<circle cx="12" cy="12" r="9.2"/><path d="M10 8.6l6 3.4-6 3.4z"/>',
		'refresh'   => '<path d="M21 12a9 9 0 1 1-2.6-6.4"/><path d="M21 4v5h-5"/>',
		'layers'    => '<path d="M12 2.8l9 4.6-9 4.6-9-4.6z"/><path d="M3 12.6l9 4.6 9-4.6"/>',
		'lightbulb' => '<path d="M9 18h6"/><path d="M10 21.5h4"/><path d="M12 2.5a6.5 6.5 0 0 0-3.8 11.8c.5.4.8 1 .8 1.7h6c0-.7.3-1.3.8-1.7A6.5 6.5 0 0 0 12 2.5z"/>',
		'briefcase' => '<rect x="2.5" y="7" width="19" height="13" rx="2.2"/><path d="M8.5 7V5.2A2.2 2.2 0 0 1 10.7 3h2.6a2.2 2.2 0 0 1 2.2 2.2V7"/>',
		'megaphone' => '<path d="M3 11v2a1.5 1.5 0 0 0 1.5 1.5H6l9 5V5.5l-9 5H4.5A1.5 1.5 0 0 0 3 12z"/><path d="M18.5 9.5a3.5 3.5 0 0 1 0 5"/>',
		'facebook'  => '<path d="M14 9h3V6h-3c-2 0-3.2 1.3-3.2 3.3V11H9v3h1.8v7h3v-7h2.6l.4-3H13.8V9.6c0-.4.1-.6.6-.6z"/>',
		'instagram' => '<rect x="2.6" y="2.6" width="18.8" height="18.8" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.6" cy="6.4" r="1.1" fill="currentColor"/>',
		'linkedin'  => '<rect x="2.6" y="2.6" width="18.8" height="18.8" rx="4"/><path d="M7 10v7M7 7.2v.1M11 17v-4a2 2 0 0 1 4 0v4"/>',
		'youtube'   => '<rect x="2.5" y="5" width="19" height="14" rx="4"/><path d="M10.5 9.2l5 2.8-5 2.8z"/>',
		'twitter'   => '<path d="M21 5.5a7.6 7.6 0 0 1-2.2.6 3.8 3.8 0 0 0 1.7-2.1 7.6 7.6 0 0 1-2.4.9A3.8 3.8 0 0 0 11.6 8a10.8 10.8 0 0 1-7.8-4 3.8 3.8 0 0 0 1.2 5.1A3.7 3.7 0 0 1 3.3 8.6a3.8 3.8 0 0 0 3 3.7 3.8 3.8 0 0 1-1.7.1 3.8 3.8 0 0 0 3.5 2.6A7.6 7.6 0 0 1 3 16.6a10.7 10.7 0 0 0 5.8 1.7c7 0 10.8-5.8 10.8-10.8V7a7.7 7.7 0 0 0 1.9-2z"/>',
	];

	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}

	// Icons that read better filled.
	$filled = [ 'star', 'bolt', 'spark', 'facebook', 'twitter', 'megaphone' ];

	if ( in_array( $name, $filled, true ) ) {
		return sprintf( $solid, (int) $size, $w, '', $paths[ $name ], esc_attr( $extra_class ) );
	}

	return sprintf( $stroke, (int) $size, $w, $paths[ $name ], '', esc_attr( $extra_class ) );
}

function aft_the_icon( $name, $size = 20, $class = '' ) {
	echo aft_icon( $name, $size, $class ); // phpcs:ignore WordPress.Security.EscapeOutput
}

/** Five stars, used by the review components. */
function aft_stars( $count = 5, $large = false ) {
	$out = '<span class="aft-stars' . ( $large ? ' aft-stars--lg' : '' ) . '" role="img" aria-label="'
		. esc_attr( sprintf( /* translators: %s: rating */ __( '%s out of 5 stars', 'aft' ), $count ) ) . '">';
	for ( $i = 0; $i < (int) $count; $i++ ) {
		$out .= aft_icon( 'star', 16 );
	}
	return $out . '</span>';
}

/** The Google wordmark, drawn in brand colours (not an image request). */
function aft_google_wordmark() {
	return '<span class="aft-gsum__logo" aria-label="Google">'
		. '<i class="aft-gsum__b">G</i><i class="aft-gsum__r">o</i><i class="aft-gsum__y">o</i>'
		. '<i class="aft-gsum__b">g</i><i class="aft-gsum__g">l</i><i class="aft-gsum__r">e</i></span>';
}

/** Initials fallback when a reviewer has no photo. */
function aft_initials( $name ) {
	$parts = preg_split( '/\s+/', trim( (string) $name ) );
	$ini   = '';
	$has_mb = function_exists( 'mb_substr' ) && function_exists( 'mb_strtoupper' );
	foreach ( array_slice( $parts, 0, 2 ) as $p ) {
		if ( '' === $p ) {
			continue;
		}
		$ini .= $has_mb
			? mb_strtoupper( mb_substr( $p, 0, 1 ), 'UTF-8' )
			: strtoupper( substr( $p, 0, 1 ) );
	}
	return $ini ?: '?';
}
