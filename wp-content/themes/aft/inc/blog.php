<?php
/**
 * Blog helpers — table of contents and related posts.
 *
 * @package AFT
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add stable ids to H2/H3 headings and collect them for a table of contents.
 *
 * Done server-side so the TOC exists without JavaScript and the anchors are
 * crawlable and shareable.
 *
 * @return array{html:string,toc:array}
 */
function aft_toc_process( $html ) {
	$toc  = [];
	$used = [];

	$html = preg_replace_callback(
		'/<h([23])([^>]*)>(.*?)<\/h\1>/is',
		function ( $m ) use ( &$toc, &$used ) {
			$level = (int) $m[1];
			$attrs = $m[2];
			$inner = $m[3];
			$text  = trim( wp_strip_all_tags( $inner ) );

			if ( '' === $text ) {
				return $m[0];
			}

			// Reuse an existing id if the author set one.
			if ( preg_match( '/\bid=["\']([^"\']+)["\']/i', $attrs, $has ) ) {
				$id = $has[1];
			} else {
				$base = sanitize_title( $text );
				$base = $base ?: 'section';
				$id   = $base;
				$n    = 2;
				while ( isset( $used[ $id ] ) ) {
					$id = $base . '-' . $n;
					$n++;
				}
				$attrs .= ' id="' . esc_attr( $id ) . '"';
			}
			$used[ $id ] = true;

			$toc[] = [
				'id'    => $id,
				'text'  => $text,
				'level' => $level,
			];

			return '<h' . $level . $attrs . '>' . $inner . '</h' . $level . '>';
		},
		$html
	);

	return [ 'html' => $html, 'toc' => $toc ];
}

/**
 * Remove presentation duplicates from imported article content.
 *
 * The theme already renders the post title and featured image. A number of
 * imported posts also contain the same title as an inner heading and/or the
 * featured image as the first body element. Remove only those duplicates at
 * render time so the stored post content remains untouched.
 */
function aft_normalize_article_content( $html, $post_id = 0 ) {
	$post_id  = (int) $post_id;
	$title    = $post_id ? get_the_title( $post_id ) : '';
	$thumb_id = $post_id ? (int) get_post_thumbnail_id( $post_id ) : 0;

	$normalise = static function ( $text ) {
		$text = html_entity_decode( wp_strip_all_tags( $text ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$text = preg_replace( '/[^\\p{L}\\p{N}]+/u', ' ', $text );
		return strtolower( trim( preg_replace( '/\\s+/u', ' ', $text ) ) );
	};

	$normal_title = $normalise( $title );

	// Drop a title heading or a heading that contains only the featured image.
	$html = preg_replace_callback(
		'/<h([1-6])\\b([^>]*)>(.*?)<\\/h\\1>/is',
		function ( $m ) use ( $normal_title, $thumb_id, $normalise ) {
			$inner = $m[3];
			$text  = $normalise( $inner );
			$image_only = false;

			if ( $thumb_id && preg_match( '/\\bwp-image-' . (int) $thumb_id . '\\b/i', $inner ) ) {
				$without_images = preg_replace( '/<img\\b[^>]*>/i', '', $inner );
				$image_only     = '' === trim( wp_strip_all_tags( $without_images ) );
			}

			if ( $image_only || ( '' !== $normal_title && $text === $normal_title ) ) {
				return '';
			}
			return $m[0];
		},
		$html
	);

	// Also remove a matching featured image when it is the first visible body
	// element but is wrapped in a paragraph/figure instead of a heading.
	if ( $thumb_id ) {
		$pattern = '/<img\\b[^>]*\\bwp-image-' . (int) $thumb_id . '\\b[^>]*>/i';
		if ( preg_match( $pattern, $html, $match, PREG_OFFSET_CAPTURE ) ) {
			$offset = $match[0][1];
			$before = substr( $html, 0, $offset );
			$before = preg_replace( '/<(style|script)\\b[^>]*>.*?<\\/\\1>/is', '', $before );
			$visible_before = trim( html_entity_decode( wp_strip_all_tags( $before ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );

			if ( '' === $visible_before ) {
				$html = substr_replace( $html, '', $offset, strlen( $match[0][0] ) );
				// Removing the image can leave empty editor wrappers behind.
				for ( $i = 0; $i < 4; $i++ ) {
					$html = preg_replace( '/<(span|p|figure|div|h[1-6])\\b([^>]*)>\\s*<\\/\\1>/is', '', $html );
				}
			}
		}
	}

	return $html;
}

/**
 * Related posts: same category first, topped up with recent posts.
 */
function aft_related_posts( $post_id, $limit = 4 ) {
	$cats = wp_get_post_categories( $post_id );

	$args = [
		'post__not_in'        => [ $post_id ],
		'posts_per_page'      => $limit,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'post_status'         => 'publish',
	];
	if ( $cats ) {
		$args['category__in'] = $cats;
	}

	$q     = new WP_Query( $args );
	$posts = $q->posts;

	// Top up if the category is thin.
	if ( count( $posts ) < $limit ) {
		$fill = new WP_Query( [
			'post__not_in'        => array_merge( [ $post_id ], wp_list_pluck( $posts, 'ID' ) ),
			'posts_per_page'      => $limit - count( $posts ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'post_status'         => 'publish',
		] );
		$posts = array_merge( $posts, $fill->posts );
	}

	return $posts;
}

/**
 * Render a reliable related-post thumbnail.
 *
 * Some older uploads are missing their generated 150x150 crop even though the
 * original attachment and medium image still exist. Do not use
 * get_the_post_thumbnail() here because it emits that broken crop in srcset.
 */
function aft_related_thumbnail_html( $post_id ) {
	$thumb_id = (int) get_post_thumbnail_id( $post_id );
	if ( ! $thumb_id ) {
		return '';
	}

	$image = wp_get_attachment_image_src( $thumb_id, 'medium' );
	if ( ! $image ) {
		$image = wp_get_attachment_image_src( $thumb_id, 'full' );
	}
	if ( ! $image ) {
		return '';
	}

	return sprintf(
		'<span class="aft-rel__thumb"><img src="%1$s" width="%2$d" height="%3$d" alt="" loading="lazy" decoding="async" /></span>',
		esc_url( $image[0] ),
		(int) $image[1],
		(int) $image[2]
	);
}

/**
 * Convert markdown headings that were pasted into post content as plain text.
 *
 * Some imported posts contain lines like "#### Step 1: ..." inside a <p>.
 * They render as literal hashes and never make it into the table of contents.
 * This fixes them at render time, so no post content is rewritten and the fix
 * applies to any future paste too.
 */
function aft_fix_markdown_headings( $content ) {
	if ( false === strpos( $content, '#' ) ) {
		return $content;
	}

	return preg_replace_callback(
		'#<p([^>]*)>\s*(\#{2,6})\s+([^<]+?)\s*</p>#u',
		function ( $m ) {
			$level = min( 6, max( 2, strlen( $m[2] ) ) );
			$text  = trim( $m[3] );
			if ( '' === $text ) {
				return $m[0];
			}
			return sprintf( '<h%1$d%2$s>%3$s</h%1$d>', $level, $m[1], $text );
		},
		$content
	);
}
add_filter( 'the_content', 'aft_fix_markdown_headings', 7 );

/**
 * Normalise article tables.
 *
 * Several imported posts contain tables whose first row is plain <td>, so the
 * columns carry no labels and the table reads as an undifferentiated grid.
 * This promotes that first row to a real <thead> of <th scope="col"> cells,
 * and wraps every table so it can scroll on narrow screens instead of
 * overflowing the page.
 *
 * Applied at render time — no post content is rewritten.
 */
function aft_format_tables( $content ) {
	if ( false === stripos( $content, '<table' ) ) {
		return $content;
	}

	// Capture any existing wrapper so a table is never wrapped twice.
	return preg_replace_callback(
		'#(<figure[^>]*aft-table-wrap[^>]*>\s*)?(<table\b[^>]*>)(.*?)(</table>)#is',
		function ( $m ) {
			$already_wrapped = ! empty( $m[1] );
			$open            = $m[2];
			$inner           = $m[3];
			$close           = $m[4];

			// Promote the first row to a real header when the table has none.
			if ( ! preg_match( '#<th\b#i', $inner ) && ! preg_match( '#<thead\b#i', $inner ) ) {
				if ( preg_match( '#<tr\b[^>]*>.*?</tr>#is', $inner, $row ) ) {
					$header = preg_replace(
						[ '#<td\b([^>]*)>#i', '#</td>#i' ],
						[ '<th scope="col"$1>', '</th>' ],
						$row[0]
					);
					// Remove the original row, then place <thead> before <tbody>.
					$inner = preg_replace( '#' . preg_quote( $row[0], '#' ) . '#', '', $inner, 1 );
					$inner = '<thead>' . $header . '</thead>' . $inner;
				}
			}

			$table = $open . $inner . $close;

			if ( $already_wrapped ) {
				return $m[1] . $table;
			}

			return '<figure class="aft-table-wrap" role="group" tabindex="0">' . $table . '</figure>';
		},
		$content
	);
}
add_filter( 'the_content', 'aft_format_tables', 8 );

/**
 * Drop paragraphs that contain nothing but whitespace or a non-breaking space.
 * These come from pasted content and leave odd vertical gaps.
 */
function aft_strip_empty_paragraphs( $content ) {
	return preg_replace( '#<p[^>]*>(?:\s|&nbsp;|<br\s*/?>)*</p>#i', '', $content );
}
add_filter( 'the_content', 'aft_strip_empty_paragraphs', 9 );
