<?php
/**
 * AFT Theme Settings (Customizer).
 *
 * Goal from brief §27: marketing changes must never require editing PHP.
 *
 * @package AFT
 */

defined( 'ABSPATH' ) || exit;

function aft_customize( WP_Customize_Manager $wp ) {

	$wp->add_panel( 'aft_panel', [
		'title'       => __( 'AFT Theme Settings', 'aft' ),
		'priority'    => 20,
		'description' => __( 'Brand, homepage content, partners, accreditation and integrations.', 'aft' ),
	] );

	$section = function ( $id, $title, $desc = '', $priority = 10 ) use ( $wp ) {
		$wp->add_section( $id, [
			'title'       => $title,
			'panel'       => 'aft_panel',
			'description' => $desc,
			'priority'    => $priority,
		] );
	};

	$text = function ( $id, $section, $label, $default = '', $desc = '', $type = 'text' ) use ( $wp ) {
		$wp->add_setting( $id, [
			'default'           => $default,
			'sanitize_callback' => 'textarea' === $type ? 'wp_kses_post' : ( 'url' === $type ? 'esc_url_raw' : 'sanitize_text_field' ),
			'transport'         => 'refresh',
		] );
		$wp->add_control( $id, [
			'label'       => $label,
			'section'     => $section,
			'type'        => $type,
			'description' => $desc,
		] );
	};

	$toggle = function ( $id, $section, $label, $default = true, $desc = '' ) use ( $wp ) {
		$wp->add_setting( $id, [
			'default'           => $default,
			'sanitize_callback' => fn( $v ) => (bool) $v,
		] );
		$wp->add_control( $id, [
			'label'       => $label,
			'section'     => $section,
			'type'        => 'checkbox',
			'description' => $desc,
		] );
	};

	$image = function ( $id, $section, $label, $desc = '' ) use ( $wp ) {
		$wp->add_setting( $id, [ 'default' => '', 'sanitize_callback' => 'esc_url_raw' ] );
		$wp->add_control( new WP_Customize_Image_Control( $wp, $id, [
			'label'       => $label,
			'section'     => $section,
			'description' => $desc,
		] ) );
	};

	$color = function ( $id, $section, $label, $default ) use ( $wp ) {
		$wp->add_setting( $id, [ 'default' => $default, 'sanitize_callback' => 'sanitize_hex_color' ] );
		$wp->add_control( new WP_Customize_Color_Control( $wp, $id, [
			'label' => $label, 'section' => $section,
		] ) );
	};

	/* ---------------------------------------------------------- brand -- */
	$section( 'aft_brand', __( 'Brand & Colours', 'aft' ), __( 'Colours feed CSS custom properties shared with the Exam Portal.', 'aft' ), 5 );
	$color( 'aft_color_primary', 'aft_brand', __( 'Accent (portal mint)', 'aft' ), '#00FF88' );
	$color( 'aft_color_deep',    'aft_brand', __( 'Background (portal deep indigo)', 'aft' ), '#130628' );
	$color( 'aft_color_accent',  'aft_brand', __( 'Data-viz cyan', 'aft' ), '#00E5FF' );

	/* -------------------------------------------------------- topbar --- */
	$section( 'aft_topbar', __( 'Announcement Bar', 'aft' ), '', 10 );
	$toggle( 'aft_topbar_on', 'aft_topbar', __( 'Show announcement bar', 'aft' ), true );
	$text( 'aft_topbar_text', 'aft_topbar', __( 'Text', 'aft' ),
		__( '2027 ENROLMENT NOW OPEN — Start your journey with Accountants for Tomorrow', 'aft' ) );
	$text( 'aft_topbar_link_text', 'aft_topbar', __( 'Link label', 'aft' ), __( 'Learn More', 'aft' ) );
	$text( 'aft_topbar_link', 'aft_topbar', __( 'Link URL', 'aft' ), '', '', 'url' );

	/* ----------------------------------------------------------- hero -- */
	$section( 'aft_hero', __( 'Hero', 'aft' ), '', 15 );
	$text( 'aft_hero_eyebrow', 'aft_hero', __( 'Eyebrow', 'aft' ),
		__( 'Accounting Education • Exam Preparation • Career Development', 'aft' ) );
	$text( 'aft_hero_title', 'aft_hero', __( 'Headline', 'aft' ), __( 'Build Your Future in', 'aft' ) );
	$text( 'aft_hero_title_accent', 'aft_hero', __( 'Headline (gradient words)', 'aft' ), __( 'Accounting', 'aft' ) );
	$text( 'aft_hero_lede', 'aft_hero', __( 'Supporting text', 'aft' ),
		__( 'Expert-led accounting education, exam preparation and professional development designed to help you progress with confidence.', 'aft' ),
		'', 'textarea' );
	$image( 'aft_hero_image', 'aft_hero', __( 'Hero image', 'aft' ), __( 'Professional student / accounting photograph.', 'aft' ) );
	$text( 'aft_hero_cta1_text', 'aft_hero', __( 'Primary button', 'aft' ), __( 'Explore Courses', 'aft' ) );
	$text( 'aft_hero_cta1_url',  'aft_hero', __( 'Primary button URL', 'aft' ), '', '', 'url' );
	$text( 'aft_hero_cta2_text', 'aft_hero', __( 'Secondary button', 'aft' ), __( 'Practice Exams', 'aft' ) );

	// Floating dashboard cards — editable, never presented as verified data.
	$toggle( 'aft_hero_cards', 'aft_hero', __( 'Show floating progress/score cards', 'aft' ), true,
		__( 'Illustrative UI. Turn off if you prefer not to display sample figures.', 'aft' ) );
	$text( 'aft_hero_students', 'aft_hero', __( 'Students figure', 'aft' ), '',
		__( 'Leave blank to hide. Only enter a verified number.', 'aft' ) );

	/* ------------------------------------------- hero slide 2 (admissions) */
	$section( 'aft_hero2', __( 'Hero Slide 2 — Admissions', 'aft' ),
		__( 'The rotating second hero slide. Only publish accreditation wording you are authorised to use.', 'aft' ), 17 );
	$text( 'aft_hero2_eyebrow', 'aft_hero2', __( 'Badge text', 'aft' ), __( 'CIMA Registered Tuition Provider', 'aft' ) );
	$text( 'aft_hero2_title', 'aft_hero2', __( 'Headline', 'aft' ), __( 'Admissions Open for', 'aft' ) );
	$text( 'aft_hero2_year', 'aft_hero2', __( 'Headline (highlighted year)', 'aft' ), '2027' );
	$text( 'aft_hero2_kicker', 'aft_hero2', __( 'Kicker', 'aft' ), __( 'Two Routes. One Career Goal.', 'aft' ) );
	$text( 'aft_hero2_route1_title', 'aft_hero2', __( 'Route 1 — title', 'aft' ), __( 'Traditional Route', 'aft' ) );
	$text( 'aft_hero2_route1_text', 'aft_hero2', __( 'Route 1 — text', 'aft' ), __( 'Open for enrolment all year round.', 'aft' ) );
	$text( 'aft_hero2_route2_title', 'aft_hero2', __( 'Route 2 — title', 'aft' ), __( 'Finance Leadership Programme (FLP)', 'aft' ) );
	$text( 'aft_hero2_route2_text', 'aft_hero2', __( 'Route 2 — text', 'aft' ), __( 'Digital-first pathway to the CGMA designation.', 'aft' ) );
	$text( 'aft_hero2_cta_text', 'aft_hero2', __( 'Button label', 'aft' ), __( 'Enrol Now', 'aft' ) );
	$text( 'aft_hero2_cta_url', 'aft_hero2', __( 'Button URL', 'aft' ), '', '', 'url' );
	$text( 'aft_hero_interval', 'aft_hero2', __( 'Seconds between heroes', 'aft' ), '7',
		__( 'How long each hero stays before cross-fading. Default 7.', 'aft' ) );

	/* -------------------------------------------------- accreditation -- */
	$section( 'aft_accred', __( 'CIMA Accreditation', 'aft' ),
		__( 'Only display accreditation you are currently authorised to use.', 'aft' ), 20 );
	$toggle( 'aft_accred_enabled', 'aft_accred', __( 'Show accreditation sections', 'aft' ), true );
	$image( 'aft_accred_badge', 'aft_accred', __( 'Official badge', 'aft' ),
		__( 'Upload the official CIMA asset. If empty, a clearly marked placeholder slot is shown instead of a claim.', 'aft' ) );
	$text( 'aft_accred_title', 'aft_accred', __( 'Title', 'aft' ), __( 'CIMA Accredited Global Learning Provider', 'aft' ) );
	$text( 'aft_accred_strap', 'aft_accred', __( 'Strapline', 'aft' ), __( 'Trusted accounting education', 'aft' ) );
	$text( 'aft_accred_text', 'aft_accred', __( 'Supporting text', 'aft' ),
		__( 'Professional learning and exam support aligned with globally recognised accounting qualifications.', 'aft' ), '', 'textarea' );
	$text( 'aft_accred_url', 'aft_accred', __( 'Learn more URL', 'aft' ), '', '', 'url' );

	/* -------------------------------------------------------- reviews -- */
	$section( 'aft_reviews', __( 'Google Reviews', 'aft' ),
		__( 'Prefer a real integration. Never enter invented reviews.', 'aft' ), 25 );
	$text( 'aft_reviews_shortcode', 'aft_reviews', __( 'Review widget shortcode', 'aft' ), '',
		__( 'e.g. the Trustindex shortcode already used on the live site. If set, it replaces the built-in cards.', 'aft' ) );
	$text( 'aft_reviews_rating', 'aft_reviews', __( 'Average rating', 'aft' ), '', __( 'Verified figure only, e.g. 4.8', 'aft' ) );
	$text( 'aft_reviews_count', 'aft_reviews', __( 'Review count', 'aft' ), '', __( 'Verified figure only, e.g. 500', 'aft' ) );
	$text( 'aft_reviews_url', 'aft_reviews', __( 'View all reviews URL', 'aft' ), '', '', 'url' );

	/* ---------------------------------------------------------- stats -- */
	$section( 'aft_stats', __( 'Trust Statistics', 'aft' ),
		__( 'Blank values are hidden. The current website ships zeroes — enter verified figures only.', 'aft' ), 30 );
	foreach ( [
		'years'     => __( 'Years of Excellence', 'aft' ),
		'graduates' => __( 'Successful Graduates', 'aft' ),
		'passrate'  => __( 'Pass Rate', 'aft' ),
		'courses'   => __( 'Courses & Resources', 'aft' ),
		'reviews'   => __( 'Student Reviews', 'aft' ),
	] as $k => $lbl ) {
		$text( 'aft_stat_' . $k, 'aft_stats', $lbl, '', __( 'Number only. Blank = hidden.', 'aft' ) );
		$text( 'aft_stat_' . $k . '_suffix', 'aft_stats', $lbl . ' — ' . __( 'suffix', 'aft' ), '', __( 'e.g. + or %', 'aft' ) );
		$text( 'aft_stat_' . $k . '_label', 'aft_stats', $lbl . ' — ' . __( 'label', 'aft' ), $lbl );
	}


	/* ------------------------------------------------ programme logos -- */
	$section( 'aft_prog_logos', __( 'Programme Logos', 'aft' ),
		__( 'Upload only marks you are licensed to display. A card with no logo shows a plain wordmark instead — never a substitute logo.', 'aft' ), 33 );
	foreach ( [
		'cima'  => __( 'CIMA logo', 'aft' ),
		'acca'  => __( 'ACCA logo', 'aft' ),
		'flp'   => __( 'CIMA FLP / CGMA logo', 'aft' ),
		'exams' => __( 'Exam Preparation logo', 'aft' ),
	] as $k => $lbl ) {
		$image( 'aft_prog_logo_' . $k, 'aft_prog_logos', $lbl );
	}

	/* ---------------------------------------------------- exam portal -- */
	$section( 'aft_exam', __( 'Exam Portal', 'aft' ),
		__( 'The portal currently lives on a temporary IP and will move to a production domain.', 'aft' ), 35 );
	$text( 'aft_exam_url', 'aft_exam', __( 'Exam Portal URL', 'aft' ), 'http://16.28.52.213/exam/', '', 'url' );
	$text( 'aft_exam_title', 'aft_exam', __( 'Heading', 'aft' ), __( "Practice Like You're Already in the Exam", 'aft' ) );
	$text( 'aft_exam_text', 'aft_exam', __( 'Text', 'aft' ),
		__( 'Build confidence with realistic mock exams, case studies, written-response practice and performance feedback.', 'aft' ), '', 'textarea' );

	/* ------------------------------------------------------- partners -- */
	$section( 'aft_partners', __( 'Partners', 'aft' ), '', 40 );
	$wp->add_setting( 'aft_partners_json', [ 'default' => '', 'sanitize_callback' => 'wp_kses_post' ] );
	$wp->add_control( 'aft_partners_json', [
		'label'       => __( 'Partners (JSON)', 'aft' ),
		'section'     => 'aft_partners',
		'type'        => 'textarea',
		'description' => __( 'Array of {"name","logo","url","description","category"}. Leave blank to use the defaults pulled from your media library.', 'aft' ),
	] );

	/* -------------------------------------------------------- contact -- */
	$section( 'aft_contact', __( 'Contact & Social', 'aft' ), '', 45 );
	$text( 'aft_phone', 'aft_contact', __( 'Phone', 'aft' ), '' );
	$text( 'aft_email', 'aft_contact', __( 'Email', 'aft' ), '' );
	$text( 'aft_address', 'aft_contact', __( 'Office', 'aft' ), '' );
	foreach ( [ 'facebook', 'instagram', 'linkedin', 'youtube', 'twitter' ] as $n ) {
		$text( 'aft_social_' . $n, 'aft_contact', ucfirst( $n ) . ' URL', '', '', 'url' );
	}

	/* --------------------------------------------------------- footer -- */
	$section( 'aft_footer', __( 'Footer', 'aft' ), '', 50 );
	$text( 'aft_footer_about', 'aft_footer', __( 'About line', 'aft' ),
		__( 'Empowering the accountants of tomorrow.', 'aft' ), '', 'textarea' );
	$text( 'aft_footer_copy', 'aft_footer', __( 'Copyright', 'aft' ), '' );

	/* ------------------------------------------------ homepage toggles -- */
	$section( 'aft_sections', __( 'Homepage Sections', 'aft' ), __( 'Show or hide each block.', 'aft' ), 55 );
	foreach ( [
		'accred'    => __( 'CIMA accreditation strip', 'aft' ),
		'reviews'   => __( 'Google reviews', 'aft' ),
		'stats'     => __( 'Trust statistics', 'aft' ),
		'programmes'=> __( 'Programmes', 'aft' ),
		'journey'   => __( 'CIMA journey', 'aft' ),
		'courses'   => __( 'Featured courses (Tutor LMS)', 'aft' ),
		'exam'      => __( 'Exam portal promotion', 'aft' ),
		'partners'  => __( 'Partners', 'aft' ),
		'accredx'   => __( 'Accreditation feature', 'aft' ),
		'story'     => __( 'Our story timeline', 'aft' ),
		'team'      => __( 'Team', 'aft' ),
		'approach'  => __( 'Our approach', 'aft' ),
		'shop'      => __( 'Shop (WooCommerce)', 'aft' ),
		'posts'     => __( 'Latest articles', 'aft' ),
		'cta'       => __( 'Final CTA', 'aft' ),
	] as $k => $lbl ) {
		$toggle( 'aft_show_' . $k, 'aft_sections', $lbl, true );
	}
}
add_action( 'customize_register', 'aft_customize' );
