<?php
/**
 * Theme content model + defaults.
 *
 * Rule from the brief: never fabricate statistics, reviews or credentials.
 * Anything unverified ships EMPTY and is editable in the admin, so the site
 * renders an honest empty state rather than an invented number.
 *
 * @package AFT
 */

defined( 'ABSPATH' ) || exit;

/**
 * Canonical defaults.
 *
 * A Customizer control's `default` only pre-fills the control UI — an unsaved
 * setting still makes get_theme_mod() return nothing. So defaults live here and
 * aft_get() consults them, which keeps a fresh install fully populated.
 */
function aft_defaults() {
	static $d = null;
	if ( null !== $d ) {
		return $d;
	}
	$d = [
		'topbar_on'        => true,
		'topbar_text'      => __( '2027 ENROLMENT NOW OPEN — Start your journey with Accountants for Tomorrow', 'aft' ),
		'topbar_link_text' => __( 'Learn More', 'aft' ),

		'hero_eyebrow'      => __( 'Accounting Education • Exam Preparation • Career Development', 'aft' ),
		'hero_title'        => __( 'Build Your Future in', 'aft' ),
		'hero_title_accent' => __( 'Accounting', 'aft' ),
		'hero_lede'         => __( 'Expert-led accounting education, exam preparation and professional development designed to help you progress with confidence.', 'aft' ),
		'hero_cta1_text'    => __( 'Explore Courses', 'aft' ),
		'hero_cta2_text'    => __( 'Practice Exams', 'aft' ),
		'hero_cards'        => true,
		'hero_image'        => AFT_URI . '/assets/img/hero.jpg',

		'accred_enabled' => true,
		'accred_title'   => __( 'CIMA Accredited Global Learning Provider', 'aft' ),
		'accred_strap'   => __( 'Trusted accounting education', 'aft' ),
		'accred_text'    => __( 'Professional learning and exam support aligned with globally recognised accounting qualifications.', 'aft' ),
		'accred_url'     => home_url( '/new/cima-courses-4/' ),

		'exam_url'   => 'http://16.28.52.213/exam/',
		'exam_title' => __( "Practice Like You're Already in the Exam", 'aft' ),
		'exam_text'  => __( 'Build confidence with realistic mock exams, case studies, written-response practice and performance feedback.', 'aft' ),

		// --- hero slide 2: admissions campaign -------------------------
		// Mirrors the banner on accountantsfortomorrow.co.za, rebuilt as real
		// markup. Deadline dates are deliberately omitted.
		'hero2_eyebrow'      => __( 'CIMA Registered Tuition Provider', 'aft' ),
		'hero2_title'        => __( 'Admissions Open for', 'aft' ),
		'hero2_year'         => __( '2027', 'aft' ),
		'hero2_kicker'       => __( 'Two Routes. One Career Goal.', 'aft' ),
		'hero2_route1_title' => __( 'Traditional Route', 'aft' ),
		'hero2_route1_text'  => __( 'Open for enrolment all year round.', 'aft' ),
		'hero2_route2_title' => __( 'Finance Leadership Programme (FLP)', 'aft' ),
		'hero2_route2_text'  => __( 'Digital-first pathway to the CGMA designation.', 'aft' ),
		'hero2_cta_text'     => __( 'Enrol Now', 'aft' ),
		'hero_interval'      => 7,    // seconds between hero cross-fades

		'footer_about' => __( 'Empowering the accountants of tomorrow.', 'aft' ),

		// Deliberately blank — unverified figures are never invented.
		'hero_students'   => '',
		'reviews_rating'  => '',
		'reviews_count'   => '',
	];
	return $d;
}

/** Thin wrapper over get_theme_mod so templates stay readable. */
function aft_get( $key, $default = null ) {
	if ( null === $default ) {
		$defs    = aft_defaults();
		$default = $defs[ $key ] ?? '';
	}
	return get_theme_mod( 'aft_' . $key, $default );
}

/* ------------------------------------------------------- exam portal -- */

/**
 * Exam portal URL. Editable, because the brief explicitly says the IP
 * address is temporary and will move to a production domain.
 */
function aft_exam_url() {
	return esc_url( aft_get( 'exam_url', 'http://16.28.52.213/exam/' ) );
}

/* ---------------------------------------------------------- reviews --- */

/**
 * Google reviews.
 *
 * Preference order:
 *  1. A real integration (Trustindex is already installed on the live site) —
 *     rendered via its shortcode so reviews stay genuine and self-updating.
 *  2. Approved existing review content captured from the current site.
 *
 * We never generate reviews.
 */
function aft_reviews_shortcode() {
	return trim( (string) aft_get( 'reviews_shortcode', '' ) );
}

function aft_reviews_fallback() {
	$file = AFT_DIR . '/assets/data/reviews-fallback.json';
	if ( ! file_exists( $file ) ) {
		return [];
	}
	$data = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore
	return is_array( $data ) ? $data : [];
}

function aft_reviews_summary() {
	return [
		'rating' => aft_get( 'reviews_rating', '' ),  // e.g. "4.8"
		'count'  => aft_get( 'reviews_count', '' ),   // e.g. "500"
		'url'    => aft_get( 'reviews_url', '' ),
	];
}

/* ------------------------------------------------------------ stats --- */

/**
 * Trust statistics.
 *
 * The current website's counters all render 0 — they are placeholders. So the
 * defaults here are deliberately blank. A stat with no value is not displayed.
 */
function aft_stats() {
	$defs = [
		[ 'key' => 'years',     'icon' => 'shield',  'label' => __( 'Years of Excellence', 'aft' ) ],
		[ 'key' => 'graduates', 'icon' => 'cap',     'label' => __( 'Successful Graduates', 'aft' ) ],
		[ 'key' => 'passrate',  'icon' => 'target',  'label' => __( 'Pass Rate', 'aft' ) ],
		[ 'key' => 'courses',   'icon' => 'book',    'label' => __( 'Courses & Resources', 'aft' ) ],
		[ 'key' => 'reviews',   'icon' => 'star',    'label' => __( 'Student Reviews', 'aft' ) ],
	];

	$out = [];
	foreach ( $defs as $d ) {
		$value = trim( (string) aft_get( 'stat_' . $d['key'], '' ) );
		if ( '' === $value ) {
			continue; // Unverified -> not shown.
		}
		$d['value']  = $value;
		$d['label']  = aft_get( 'stat_' . $d['key'] . '_label', $d['label'] );
		$d['suffix'] = aft_get( 'stat_' . $d['key'] . '_suffix', '' );
		$out[]       = $d;
	}
	return $out;
}

/* ------------------------------------------------------- programmes --- */

/** Preserves the offerings the live site already promotes. */
function aft_programmes() {
	$base = AFT_URI . '/assets/img/partners/';

	$items = [
		[
			'key'   => 'cima',
			'title' => __( 'CIMA', 'aft' ),
			'desc'  => __( 'Professional accounting and management qualification.', 'aft' ),
			'cta'   => __( 'Explore CIMA', 'aft' ),
			'url'   => aft_get( 'url_cima', home_url( '/new/cima-courses-4/' ) ),
			'icon'  => 'award',
			// Official AICPA & CIMA logo, supplied by the client.
			'logo'  => $base . 'aicpa-cima-logo.svg',
			'alt'   => __( 'AICPA & CIMA', 'aft' ),
		],
		[
			'key'   => 'acca',
			'title' => __( 'ACCA', 'aft' ),
			'desc'  => __( 'Global accountancy qualification for future leaders.', 'aft' ),
			'cta'   => __( 'Explore ACCA', 'aft' ),
			'url'   => aft_get( 'url_acca', home_url( '/acca-courses/' ) ),
			'icon'  => 'cap',
			// Official ACCA logo, supplied by the client.
			'logo'  => $base . 'acca-logo.png',
			'alt'   => 'ACCA',
		],
		[
			'key'   => 'flp',
			'title' => __( 'CIMA FLP', 'aft' ),
			'desc'  => __( 'Digital-first pathway for modern finance professionals.', 'aft' ),
			'cta'   => __( 'Explore FLP', 'aft' ),
			'url'   => aft_get( 'url_flp', home_url( '/cima-financial-leadership-programme-flp' ) ),
			'icon'  => 'laptop',
			// CIMA corporate logo, supplied by the client.
			'logo'  => $base . 'cima-logo.png',
			'alt'   => __( 'CIMA — Chartered Institute of Management Accountants', 'aft' ),
		],
		[
			'key'   => 'exams',
			'title' => __( 'Exam Preparation', 'aft' ),
			'desc'  => __( 'Practice exams, mock assessments and structured preparation.', 'aft' ),
			'cta'   => __( 'Practice Exams', 'aft' ),
			'url'   => aft_exam_url(),
			'icon'  => 'target',
			// Illustration rather than a logo, so it is allowed more of the plate.
			'logo'  => $base . 'exam-prep.png',
			'alt'   => __( 'Plan, prepare, perform — AFT exam preparation', 'aft' ),
			'illus' => true,
		],
	];

	// Customizer uploads win over the bundled defaults.
	foreach ( $items as &$item ) {
		$custom = aft_get( 'prog_logo_' . $item['key'], '' );
		if ( $custom ) {
			$item['logo'] = $custom;
		}
	}
	unset( $item );

	return $items;
}

/* ---------------------------------------------------------- journey --- */

function aft_journey_steps() {
	return [
		[
			'key'   => 'certificate',
			'icon'  => 'book',
			'title' => __( 'Certificate', 'aft' ),
			'sub'   => __( 'CIMA Certificate in Business Accounting', 'aft' ),
			'desc'  => __( 'The entry route into CIMA. Builds the fundamentals of business accounting, economics, management and law.', 'aft' ),
		],
		[
			'key'   => 'operational',
			'icon'  => 'layers',
			'title' => __( 'Operational', 'aft' ),
			'sub'   => __( 'CIMA Diploma in Management Accounting', 'aft' ),
			'desc'  => __( 'Focuses on implementing decisions and reporting performance, closing with the Operational Case Study.', 'aft' ),
		],
		[
			'key'   => 'management',
			'icon'  => 'chart',
			'title' => __( 'Management', 'aft' ),
			'sub'   => __( 'CIMA Advanced Diploma in Management Accounting', 'aft' ),
			'desc'  => __( 'Translates strategy into practice through performance management, project control and the Management Case Study.', 'aft' ),
		],
		[
			'key'   => 'strategic',
			'icon'  => 'target',
			'title' => __( 'Strategic', 'aft' ),
			'sub'   => __( 'CIMA Strategic Level', 'aft' ),
			'desc'  => __( 'Senior decision making — strategy, risk and financial strategy, completed with the Strategic Case Study.', 'aft' ),
		],
		[
			'key'   => 'cgma',
			'icon'  => 'award',
			'title' => __( 'CGMA', 'aft' ),
			'sub'   => __( 'Membership of CIMA + CGMA designation', 'aft' ),
			'desc'  => __( 'Full membership of CIMA and the globally recognised CGMA designation.', 'aft' ),
		],
	];
}

/* ------------------------------------------------------------ story --- */

/** Genuine milestones only (brief §14). */
function aft_timeline() {
	return [
		[ 'label' => __( 'AFT Founded', 'aft' ),            'year' => '2015',  'icon' => '' ],
		[ 'label' => __( 'CIMA syllabus transition', 'aft' ), 'year' => '',      'icon' => 'refresh' ],
		[ 'label' => __( 'One-on-one tailored learning', 'aft' ), 'year' => '', 'icon' => 'user' ],
		[ 'label' => __( 'Growing student community', 'aft' ), 'year' => '',    'icon' => 'users' ],
		[ 'label' => __( 'Modern digital learning', 'aft' ), 'year' => '',      'icon' => 'monitor' ],
		[ 'label' => __( 'AFT Exam Portal', 'aft' ),         'year' => '',      'icon' => 'bolt' ],
	];
}

/* --------------------------------------------------------- approach --- */

function aft_approach() {
	return [
		[ 'icon' => 'heart',     'title' => __( 'Personalised Support', 'aft' ),      'desc' => __( 'Learning support built around individual student needs.', 'aft' ) ],
		[ 'icon' => 'cap',       'title' => __( 'Expert Tuition', 'aft' ),            'desc' => __( 'Learn from experienced accounting professionals.', 'aft' ) ],
		[ 'icon' => 'target',    'title' => __( 'Exam-Focused Preparation', 'aft' ),  'desc' => __( 'Practice with structured preparation and mock exams.', 'aft' ) ],
		[ 'icon' => 'clock',     'title' => __( 'Flexible Learning', 'aft' ),         'desc' => __( 'Study around your career and personal commitments.', 'aft' ) ],
	];
}

/* --------------------------------------------------------- partners --- */

/**
 * Partner logos. Sourced from the live site's existing media library.
 * Admin-editable; see Customizer -> AFT -> Partners.
 */
function aft_partners() {
	$stored = aft_get( 'partners_json', '' );
	if ( $stored ) {
		$decoded = json_decode( $stored, true );
		if ( is_array( $decoded ) && $decoded ) {
			return $decoded;
		}
	}

	$base = AFT_URI . '/assets/img/partners/';
	// NOTE: the live site's "cima-.png" is a 2026 enrolment campaign tile, not a
	// partner logo, so it is deliberately excluded. Supply the official CIMA
	// logo via Customizer -> AFT -> Partners to add it back.
	return [
		[ 'name' => 'Kaplan',          'logo' => $base . 'Kiplan.png', 'url' => '' ],
		[ 'name' => 'BPP',             'logo' => $base . 'BPP.png',    'url' => '' ],
		[ 'name' => 'AICPA & CIMA',    'logo' => $base . 'Aicpa.png',  'url' => 'https://www.aicpa-cima.com/' ],
		[ 'name' => 'SAQA',            'logo' => $base . 'saqa.png',   'url' => 'https://www.saqa.org.za/' ],
		// logo2.png is the IFAC mark (verified by inspecting the asset).
		[ 'name' => 'IFAC',            'logo' => $base . 'logo2.png',  'url' => 'https://www.ifac.org/' ],
	];
}

/* ---------------------------------------------------- accreditation --- */

/**
 * The official CIMA badge already present in the site's media library.
 * If it is ever withdrawn, clearing this setting shows an editable slot
 * instead of an unauthorised claim.
 */
function aft_accred_badge() {
	return aft_get( 'accred_badge', AFT_URI . '/assets/img/partners/Global-Learning-Provider-2026.svg' );
}

function aft_accred_enabled() {
	return (bool) aft_get( 'accred_enabled', true );
}

/* -------------------------------------------------- navigation -------- */

/**
 * Primary navigation, including the Exam Portal link:
 * 8 top-level items, 6 children, 14 total.
 *
 * Used only as a fallback. Once a menu is assigned to the "primary"
 * location in Appearance -> Menus, that menu wins.
 */
function aft_default_menu() {
	return [
		[ 'label' => __( 'Home', 'aft' ), 'url' => home_url( '/' ) ],
		[
			'label'    => __( 'Courses', 'aft' ),
			'url'      => home_url( '/courses/' ),
			'children' => [
				[ 'label' => __( 'Cima', 'aft' ), 'url' => home_url( '/new/cima-courses-4/' ) ],
				[ 'label' => __( 'Acca', 'aft' ), 'url' => home_url( '/acca-courses/' ) ],
			],
		],
		[ 'label' => __( 'Exam Portal', 'aft' ), 'url' => home_url( '/exam/' ) ],
		[ 'label' => __( 'FLP', 'aft' ),         'url' => home_url( '/cima-financial-leadership-programme-flp' ) ],
		[ 'label' => __( 'Blog', 'aft' ),        'url' => home_url( '/blog/' ) ],
		[ 'label' => __( 'Book Store', 'aft' ),  'url' => home_url( '/product-category/books/' ) ],
		[ 'label' => __( 'Answer Base', 'aft' ), 'url' => home_url( '/ai-assistant/' ) ],
		[
			'label'    => __( 'About Us', 'aft' ),
			'url'      => home_url( '/about/' ),
			'children' => [
				[ 'label' => __( 'Company Profile', 'aft' ),    'url' => home_url( '/company-profile/' ) ],
				[ 'label' => __( 'Products & Services', 'aft' ),'url' => home_url( '/products-services' ) ],
				[ 'label' => __( 'Our Team', 'aft' ),           'url' => home_url( '/our-team/' ) ],
				[ 'label' => __( 'Contact', 'aft' ),            'url' => home_url( '/contact/' ) ],
			],
		],
	];
}

/** Render the fallback menu as a nested <ul>. */
function aft_render_fallback_menu() {
	echo '<ul>';
	foreach ( aft_default_menu() as $item ) {
		$has = ! empty( $item['children'] );
		printf(
			'<li class="%s"><a href="%s">%s</a>',
			$has ? 'menu-item menu-item-has-children' : 'menu-item',
			esc_url( $item['url'] ),
			esc_html( $item['label'] )
		);
		if ( $has ) {
			echo '<ul class="sub-menu">';
			foreach ( $item['children'] as $child ) {
				printf(
					'<li class="menu-item"><a href="%s">%s</a></li>',
					esc_url( $child['url'] ),
					esc_html( $child['label'] )
				);
			}
			echo '</ul>';
		}
		echo '</li>';
	}
	echo '</ul>';
}

/* -------------------------------------------------------------- team -- */

/**
 * Team — real people, taken verbatim from accountantsfortomorrow.co.za/our-team/.
 * Roles, qualifications and photographs are the client's own; nothing invented.
 * Overridden automatically if a `team` post type exists.
 */
function aft_team() {
	$img = AFT_URI . '/assets/img/team/';
	$url = aft_get( 'team_url', home_url( '/our-team/' ) );

	return [
		[
			'name'  => 'Patrick Pfidze',
			'role'  => __( 'Founder', 'aft' ),
			'cred'  => 'MBA, ACMA, CGMA, SAAA, BAP SA, BCom',
			'photo' => $img . 'patrick.jpg',
			'url'   => $url,
		],
		[
			'name'  => 'Stanley Mukarati',
			'role'  => __( 'Administration', 'aft' ),
			'cred'  => __( 'BSc Honours in Accountancy (Chinhoyi University of Technology) · CIMA candidate', 'aft' ),
			'photo' => $img . 'stanley.jpg',
			'url'   => $url,
		],
		[
			'name'  => 'Millicent Pfidze',
			'role'  => __( 'Facilitator / Moderator', 'aft' ),
			'cred'  => __( 'CIMA studies in progress', 'aft' ),
			'photo' => $img . 'millicent.jpg',
			'url'   => $url,
		],
		[
			'name'  => 'Cicilia Lekgetho',
			'role'  => __( 'Facilitator / Moderator', 'aft' ),
			'cred'  => __( 'Dip Accountancy · Adv Dip & BCom Hons Financial Management (UJ) · CIMA candidate', 'aft' ),
			'photo' => $img . 'cicilia.jpg',
			'url'   => $url,
		],
	];
}
