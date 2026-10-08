<?php
/**
 * Pages — centred institutional layouts plus curated About and Answer Base
 * experiences.
 *
 * @package AFT
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$slug = (string) get_post_field( 'post_name', get_the_ID() );

	if ( 'about' === $slug ) :
		?>
		<section class="aft-page-hero aft-page-hero--about">
			<div class="aft-container aft-page-hero__grid">
				<div class="aft-page-hero__copy" data-reveal>
					<p class="aft-eyebrow">Accountants for Tomorrow <span aria-hidden="true">·</span> Est. 2015</p>
					<h1>Building tomorrow’s accounting leaders.</h1>
					<p class="aft-page-hero__lede">A modern learning partner for CIMA, ACCA and finance professionals — built around expert tuition, practical confidence and a clear path to CGMA success.</p>
					<div class="aft-page-hero__actions">
						<a class="aft-btn aft-btn--primary" href="<?php echo esc_url( home_url( '/courses/' ) ); ?>">Explore courses<?php aft_the_icon( 'arrow-r', 17 ); ?></a>
						<a class="aft-btn aft-btn--ghost" href="#about-story">Our story</a>
					</div>
					<div class="aft-page-hero__proof" aria-label="AFT highlights">
						<div><strong>2015</strong><span>Founded in Johannesburg</span></div>
						<div><strong>1:1</strong><span>Tailor-made support</span></div>
						<div><strong>2</strong><span>Professional pathways</span></div>
					</div>
				</div>

				<div class="aft-page-hero__media" data-reveal>
					<div class="aft-page-hero__image-wrap">
						<img src="<?php echo esc_url( AFT_URI . '/assets/img/aft-student.png' ); ?>" alt="AFT student ready to learn" width="1536" height="1536">
					</div>
					<div class="aft-page-hero__floating-card">
						<span class="aft-page-hero__floating-icon"><?php aft_the_icon( 'award', 20 ); ?></span>
						<span><b>Learning that moves you forward</b><small>Knowledge · confidence · progression</small></span>
					</div>
				</div>
			</div>
		</section>

		<section class="aft-trust-strip" aria-label="Professional learning focus">
			<div class="aft-container aft-trust-strip__in">
				<span>Professional learning for the next generation of finance leaders</span>
				<div class="aft-trust-strip__marks"><b>CIMA</b><i></i><b>ACCA</b><i></i><b>CGMA</b></div>
			</div>
		</section>

		<section class="aft-section aft-about-story" id="about-story">
			<div class="aft-container aft-about-story__grid">
				<div class="aft-about-story__media" data-reveal>
					<img src="<?php echo esc_url( AFT_URI . '/assets/img/hero.jpg' ); ?>" alt="Student learning online with AFT" width="1536" height="1024" loading="lazy">
					<div class="aft-about-story__badge"><strong>10+</strong><span>years of student<br>progress</span></div>
				</div>
				<div class="aft-about-story__copy" data-reveal>
					<p class="aft-eyebrow">Our story</p>
					<h2>Personal support for ambitious professionals.</h2>
					<p>Accountants for Tomorrow was established in 2015 in Kempton Park, Johannesburg, in response to the changing world of professional accounting examinations.</p>
					<p>Founded by Patrick Pfidze after experiencing the CIMA journey first-hand, AFT was created to make complex subjects clearer, preparation more purposeful and every student’s route to success more personal.</p>
					<div class="aft-about-story__points">
						<div><span><?php aft_the_icon( 'check-c', 20 ); ?></span><b>Expert-led tuition</b><small>Clear explanations from people who understand the exams.</small></div>
						<div><span><?php aft_the_icon( 'check-c', 20 ); ?></span><b>Practical confidence</b><small>Question practice, feedback and exam-scenario technique.</small></div>
					</div>
				</div>
			</div>
		</section>

		<section class="aft-section aft-section--soft aft-about-purpose">
			<div class="aft-container">
				<div class="aft-head aft-head--center" data-reveal>
					<p class="aft-eyebrow">Purpose with direction</p>
					<h2>A learning partner, not just another course.</h2>
					<p class="aft-lede">Our mission and vision keep every lesson focused on the person behind the qualification.</p>
				</div>
				<div class="aft-about-purpose__grid">
					<article class="aft-purpose-card aft-purpose-card--mission" data-reveal>
						<span class="aft-purpose-card__icon"><?php aft_the_icon( 'target', 24 ); ?></span>
						<p class="aft-eyebrow">Our mission</p>
						<h3>Make meaningful progress possible.</h3>
						<p>To cultivate the Chartered Global Management Accountants of tomorrow through exceptional tuition, practical skills, engaging learning and a clear commitment to student progression.</p>
					</article>
					<article class="aft-purpose-card aft-purpose-card--vision" data-reveal>
						<span class="aft-purpose-card__icon"><?php aft_the_icon( 'spark', 24 ); ?></span>
						<p class="aft-eyebrow">Our vision</p>
						<h3>Every country. More capable finance leaders.</h3>
						<p>To help train Chartered Global Management Accountants in every country of the world — with learning that is modern, accessible and human.</p>
					</article>
				</div>
			</div>
		</section>

		<section class="aft-section aft-about-values">
			<div class="aft-container">
				<div class="aft-head aft-head--row" data-reveal>
					<div><p class="aft-eyebrow">How we work</p><h2>Values you can feel in every interaction.</h2></div>
					<p class="aft-lede">The standards behind our teaching, our support and our student community.</p>
				</div>
				<div class="aft-about-values__grid">
					<?php
					$values = [
						[ 'Persistent determination', 'We stay with the problem until the path becomes clear.', 'bolt' ],
						[ 'Teamwork & collaboration', 'Progress is stronger when students and tutors work together.', 'users' ],
						[ 'Integrity & empathy', 'We meet every learner with honesty, care and respect.', 'heart' ],
						[ 'Continuous development', 'We keep learning so our students are ready for what comes next.', 'chart' ],
						[ 'Respect for time', 'Focused preparation turns limited time into meaningful progress.', 'clock' ],
						[ 'Responsibility', 'We take ownership of the standards and outcomes we promise.', 'shield-c' ],
					];
					foreach ( $values as $value ) :
						?>
						<article class="aft-value-card" data-reveal>
							<span class="aft-value-card__icon"><?php aft_the_icon( $value[2], 22 ); ?></span>
							<h3><?php echo esc_html( $value[0] ); ?></h3>
							<p><?php echo esc_html( $value[1] ); ?></p>
						</article>
					<?php
					endforeach;
					?>
				</div>
			</div>
		</section>

		<section class="aft-section aft-about-pathways">
			<div class="aft-container aft-about-pathways__grid">
				<div class="aft-about-pathways__media" data-reveal>
					<img src="<?php echo esc_url( AFT_URI . '/assets/img/exam-portal.jpg' ); ?>" alt="AFT digital exam preparation portal" width="1536" height="1024" loading="lazy">
					<div class="aft-about-pathways__caption"><span><?php aft_the_icon( 'monitor', 18 ); ?></span><b>Modern preparation</b><small>Learn, practise and perform with confidence.</small></div>
				</div>
				<div class="aft-about-pathways__copy" data-reveal>
					<p class="aft-eyebrow">Two routes. One career goal.</p>
					<h2>Choose the path that fits your future.</h2>
					<p>Whether you thrive on structured examinations or prefer an integrated digital pathway, AFT gives you the support and resources to move forward.</p>
					<div class="aft-about-pathways__list">
						<a href="<?php echo esc_url( home_url( '/courses/' ) ); ?>"><span class="aft-about-pathways__number">01</span><span><b>Traditional route</b><small>Structured study, rigorous practice and case-study preparation.</small></span><?php aft_the_icon( 'arrow-r', 18 ); ?></a>
						<a href="<?php echo esc_url( home_url( '/cima-financial-leadership-programme-flp/' ) ); ?>"><span class="aft-about-pathways__number">02</span><span><b>Finance Leadership Programme</b><small>Digital-first learning and practical assessment for modern professionals.</small></span><?php aft_the_icon( 'arrow-r', 18 ); ?></a>
					</div>
				</div>
			</div>
		</section>

		<section class="aft-section aft-about-cta">
			<div class="aft-container">
				<div class="aft-about-cta__inner" data-reveal>
					<div><p class="aft-eyebrow">Your next chapter starts here</p><h2>Ready to build your future in accounting?</h2><p>Explore a learning experience designed to help you progress with clarity and confidence.</p></div>
					<div class="aft-about-cta__actions"><a class="aft-btn aft-btn--primary" href="<?php echo esc_url( home_url( '/courses/' ) ); ?>">Explore courses<?php aft_the_icon( 'arrow-r', 17 ); ?></a><a class="aft-btn aft-btn--onDark" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Talk to our team</a></div>
				</div>
			</div>
		</section>
		<?php

	elseif ( 'ai-assistant' === $slug ) :
		?>
		<section class="aft-page-hero aft-page-hero--assistant">
			<div class="aft-container aft-assistant-hero__grid">
				<div class="aft-page-hero__copy" data-reveal>
					<p class="aft-eyebrow">AFT Answer Base</p>
					<h1>Your study companion, whenever you need it.</h1>
					<p class="aft-page-hero__lede">Get clear, practical support for CIMA, ACCA, IFRS and the questions that come up while you study.</p>
					<div class="aft-page-hero__actions"><a class="aft-btn aft-btn--primary" href="#study-assistant">Open the assistant<?php aft_the_icon( 'arrow-r', 17 ); ?></a><a class="aft-btn aft-btn--ghost" href="<?php echo esc_url( home_url( '/courses/' ) ); ?>">Explore courses</a></div>
				</div>
				<div class="aft-assistant-hero__visual" aria-hidden="true" data-reveal>
					<div class="aft-assistant-hero__orb"><span><?php aft_the_icon( 'spark', 34 ); ?></span></div>
					<div class="aft-assistant-hero__mini-card aft-assistant-hero__mini-card--top"><span class="aft-status-dot"></span><b>AFT Study Assistant</b><small>Ready when you are</small></div>
					<div class="aft-assistant-hero__mini-card aft-assistant-hero__mini-card--bottom"><span><?php aft_the_icon( 'check-c', 18 ); ?></span><b>Clearer answers</b><small>More confident study</small></div>
				</div>
			</div>
		</section>

		<section class="aft-section aft-assistant-section" id="study-assistant">
			<div class="aft-container">
				<div class="aft-head aft-head--center" data-reveal>
					<p class="aft-eyebrow">Ask. Learn. Progress.</p>
					<h2>Meet the AFT Study Assistant.</h2>
					<p class="aft-lede">Use the assistant below to explore concepts, clarify tricky topics and get a useful next step for your preparation.</p>
				</div>
				<div class="aft-assistant-embed" data-reveal>
					<div class="aft-assistant-embed__bar"><span class="aft-assistant-embed__brand"><span><?php aft_the_icon( 'spark', 16 ); ?></span><b>AFT Study Assistant</b></span><span class="aft-assistant-embed__status"><i></i> Online study support</span></div>
					<div class="aft-assistant-embed__frame">
						<iframe src="https://studious-helper.lovable.app/embed" title="AFT Study Assistant" loading="lazy" allow="clipboard-write" referrerpolicy="strict-origin-when-cross-origin"></iframe>
					</div>
				</div>
				<div class="aft-assistant-guidance" data-reveal>
					<div><span><?php aft_the_icon( 'book', 21 ); ?></span><b>Ask specific questions</b><small>Include the subject, level or exam you are preparing for.</small></div>
					<div><span><?php aft_the_icon( 'target', 21 ); ?></span><b>Use it to practise</b><small>Ask for examples, explanations and case-study guidance.</small></div>
					<div><span><?php aft_the_icon( 'check-c', 21 ); ?></span><b>Keep learning human</b><small>Use the assistant alongside your tutor, notes and official resources.</small></div>
				</div>
			</div>
		</section>
		<?php

	elseif ( 'cima-courses-4' === $slug ) :
		$cima_levels = [
			[
				'number' => '01',
				'name' => 'Certificate in Business Accounting',
				'copy' => 'CIMA’s entry route to the professional qualification, building a strong foundation in business, economics, management and financial accounting.',
				'award' => 'CIMA Certificate in Business Accounting',
				'subjects' => [ 'BA1 · Fundamentals of Business Economics', 'BA2 · Fundamentals of Management Accounting', 'BA3 · Fundamentals of Financial Accounting', 'BA4 · Fundamentals of Ethics, Corporate Governance and Business Law' ],
				'link' => 'https://visionpluss.co.za/wp-content/uploads/2024/07/AFT-2024-CIMA-Certificate-In-Business-Accounting.pdf',
			],
			[
				'number' => '02',
				'name' => 'Operational Level',
				'copy' => 'Develop the practical accounting and finance skills needed to support decisions and performance in a digital business environment.',
				'award' => 'CIMA Diploma in Management Accounting',
				'subjects' => [ 'E1 · Managing finance in a digital world', 'P1 · Management Accounting', 'F1 · Financial Reporting and Taxation', 'Operational Case Study Exam' ],
				'link' => 'https://visionpluss.co.za/wp-content/uploads/2025/12/AFT2025-CIMA-Operational-Level-Slides-website.pdf',
			],
			[
				'number' => '03',
				'name' => 'Management Level',
				'copy' => 'Learn to communicate strategy, manage performance and apply advanced technical knowledge to real business decisions.',
				'award' => 'CIMA Advanced Diploma in Management Accounting',
				'subjects' => [ 'E2 · Project and Relationship Management', 'P2 · Advanced Management Accounting', 'F2 · Advanced Financial Reporting', 'Management Case Study Exam' ],
				'link' => 'https://visionpluss.co.za/wp-content/uploads/2025/12/A.F.T.-2025-CIMA-Management-Accounting-website-content.pdf',
			],
			[
				'number' => '04',
				'name' => 'Strategic Level',
				'copy' => 'Focus on long-term strategic decisions and the context in which business strategy is shaped, implemented and measured.',
				'award' => 'CGMA designation and CIMA membership',
				'subjects' => [ 'E3 · Strategic Management', 'P3 · Risk Management', 'F3 · Financial Strategy', 'Strategic Case Study Exam' ],
				'link' => 'https://visionpluss.co.za/wp-content/uploads/2025/12/AFT2025-CIMA-Strategic-Level-website-content.pdf',
			],
		];
		?>
		<section class="aft-program-hero aft-program-hero--cima">
			<div class="aft-container aft-program-hero__grid">
				<div class="aft-program-hero__copy" data-reveal>
					<p class="aft-eyebrow">CIMA · CGMA pathway</p>
					<h1>Build the finance career the future needs.</h1>
					<p class="aft-page-hero__lede">CIMA is one of the most relevant qualifications for a career in finance and business. AFT helps you move from your first subject to strategic leadership with expert tuition and purposeful preparation.</p>
					<div class="aft-page-hero__actions"><a class="aft-btn aft-btn--primary" href="<?php echo esc_url( home_url( '/new/student-registration-2/' ) ); ?>">Register now<?php aft_the_icon( 'arrow-r', 17 ); ?></a><a class="aft-btn aft-btn--ghost" href="#cima-pathway">Explore the pathway</a></div>
					<div class="aft-page-hero__proof"><div><strong>4</strong><span>progressive levels</span></div><div><strong>16</strong><span>subjects and case studies</span></div><div><strong>CGMA</strong><span>global designation</span></div></div>
				</div>
				<div class="aft-program-hero__visual" data-reveal><div class="aft-program-hero__visual-card"><img src="<?php echo esc_url( AFT_URI . '/assets/img/hero.jpg' ); ?>" alt="CIMA and CGMA study pathway" loading="eager"><span class="aft-program-hero__visual-badge"><?php aft_the_icon( 'award', 20 ); ?><b>Professional direction</b><small>Study with clarity. Progress with confidence.</small></span></div></div>
			</div>
		</section>
		<section class="aft-program-trust"><div class="aft-container aft-program-trust__in"><span>Structured learning for ambitious finance professionals</span><div class="aft-trust-strip__marks"><b>CIMA</b><i></i><b>CGMA</b><i></i><b>AFT</b></div></div></section>
		<section class="aft-section aft-program-intro" id="cima-pathway"><div class="aft-container aft-program-intro__grid"><div><p class="aft-eyebrow">The qualification journey</p><h2>One clear route from fundamentals to strategic influence.</h2></div><div><p>CIMA combines accounting, finance and business skills so you can contribute beyond the numbers. Each level develops a different layer of capability, with case study exams connecting learning to the workplace.</p><p class="aft-program-intro__note"><span><?php aft_the_icon( 'check-c', 18 ); ?></span> Every stage gives you a practical milestone to work towards.</p></div></div></section>
		<section class="aft-section aft-section--soft aft-program-levels"><div class="aft-container"><div class="aft-head aft-head--row" data-reveal><div><p class="aft-eyebrow">Your CIMA pathway</p><h2>Progress level by level.</h2></div><p class="aft-lede">Explore what you will study, the capability you will build and the award attached to each stage.</p></div><div class="aft-program-levels__grid">
			<?php foreach ( $cima_levels as $level ) : ?>
				<article class="aft-program-level" data-reveal><div class="aft-program-level__top"><span><?php echo esc_html( $level['number'] ); ?></span><b><?php echo esc_html( $level['name'] ); ?></b></div><p><?php echo esc_html( $level['copy'] ); ?></p><ul><?php foreach ( $level['subjects'] as $subject ) : ?><li><?php aft_the_icon( 'check-c', 15 ); ?><?php echo esc_html( $subject ); ?></li><?php endforeach; ?></ul><div class="aft-program-level__award"><small>Award</small><strong><?php echo esc_html( $level['award'] ); ?></strong></div><a class="aft-text-link" href="<?php echo esc_url( $level['link'] ); ?>" target="_blank" rel="noopener">View level guide<?php aft_the_icon( 'arrow-r', 16 ); ?></a></article>
			<?php endforeach; ?>
		</div></div></section>
		<section class="aft-section aft-program-video"><div class="aft-container aft-program-video__inner" data-reveal><div><p class="aft-eyebrow">Prepare with purpose</p><h2>Turn question practice into exam confidence.</h2><p>With the right practice and feedback, every difficult topic becomes a step forward. Explore the CIMA exam-success approach and see how focused preparation can change your performance.</p></div><a class="aft-btn aft-btn--primary" href="https://www.youtube.com/watch?v=q2MqSXJRCeI" target="_blank" rel="noopener">Watch the CIMA guide<?php aft_the_icon( 'arrow-r', 17 ); ?></a></div></section>
		<section class="aft-section aft-program-cta"><div class="aft-container"><div class="aft-program-cta__inner" data-reveal><div><p class="aft-eyebrow">Start your professional journey</p><h2>Ready to take your next step with CIMA?</h2><p>Register today or speak to the AFT team about the level that fits your experience.</p></div><div class="aft-about-cta__actions"><a class="aft-btn aft-btn--primary" href="<?php echo esc_url( home_url( '/new/student-registration-2/' ) ); ?>">Register now<?php aft_the_icon( 'arrow-r', 17 ); ?></a><a class="aft-btn aft-btn--onDark" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Talk to AFT</a></div></div></div></section>
		<?php

	elseif ( 'acca-courses' === $slug ) :
		$acca_levels = [
			[ 'number' => '01', 'name' => 'Applied Knowledge', 'copy' => 'Build a practical foundation in business, management accounting and financial accounting.' ],
			[ 'number' => '02', 'name' => 'Applied Skills', 'copy' => 'Develop the technical skills needed to analyse, report and make sound financial decisions.' ],
			[ 'number' => '03', 'name' => 'Strategic Professional', 'copy' => 'Strengthen strategic thinking, professional judgement and the ability to lead in a changing world.' ],
			[ 'number' => '04', 'name' => 'Practical experience', 'copy' => 'Combine exams, the Professional Ethics module and relevant experience to become an ACCA member.' ],
		];
		$acca_features = [
			[ 'Global mobility', 'A worldwide reputation opens doors to roles and industries across the globe.', 'globe' ],
			[ 'Strategic capability', 'Build technical skills, professional values and the judgement employers look for.', 'target' ],
			[ 'Flexible progression', 'Choose your own path and add academic qualifications as your goals develop.', 'chart' ],
			[ 'Professional network', 'Join a global community of like-minded finance professionals and future leaders.', 'users' ],
		];
		$acca_platform = [
			[ 'AFT Learning Zone', 'A user-friendly platform trusted by students in more than 16 countries.', 'monitor' ],
			[ 'Downloadable study tools', 'Access notes, mind maps and summaries whenever you need them.', 'book' ],
			[ 'Live and recorded lectures', 'Join scheduled sessions or catch up with recordings at a time that works for you.', 'play' ],
			[ 'Practice and mock exams', 'Build confidence with question practice, realistic mocks and feedback.', 'check-c' ],
			[ 'Free BPP materials', 'Supplement your preparation with official BPP learning materials.', 'award' ],
			[ 'Your success team', 'Get guidance from experienced instructors and dedicated support staff.', 'heart' ],
		];
		?>
		<section class="aft-program-hero aft-program-hero--acca"><div class="aft-container aft-program-hero__grid"><div class="aft-program-hero__copy" data-reveal><p class="aft-eyebrow">ACCA · Global accounting qualification</p><h1>Elevate your skillset for a global future.</h1><p class="aft-page-hero__lede">The ACCA qualification builds future-focused professionals with the financial and business skills to create strong careers, organisations and economies.</p><div class="aft-page-hero__actions"><a class="aft-btn aft-btn--primary" href="<?php echo esc_url( home_url( '/new/student-registration-2/' ) ); ?>">Register now<?php aft_the_icon( 'arrow-r', 17 ); ?></a><a class="aft-btn aft-btn--ghost" href="#acca-journey">See the journey</a></div><div class="aft-page-hero__proof"><div><strong>1904</strong><span>ACCA founded</span></div><div><strong>176</strong><span>countries represented</span></div><div><strong>13</strong><span>exams in the full route</span></div></div></div><div class="aft-program-hero__visual" data-reveal><div class="aft-program-hero__visual-card aft-program-hero__visual-card--person"><img src="<?php echo esc_url( AFT_URI . '/assets/img/aft-student.png' ); ?>" alt="AFT student preparing for a professional qualification" loading="eager"><span class="aft-program-hero__visual-badge"><?php aft_the_icon( 'globe', 20 ); ?><b>Globally recognised</b><small>Skills that travel with your career.</small></span></div></div></div></section>
		<section class="aft-program-trust"><div class="aft-container aft-program-trust__in"><span>Professional accounting education with a worldwide outlook</span><div class="aft-trust-strip__marks"><b>ACCA</b><i></i><b>BSc</b><i></i><b>AFT</b></div></div></section>
		<section class="aft-section aft-program-intro"><div class="aft-container aft-program-intro__grid"><div><p class="aft-eyebrow">About ACCA</p><h2>A qualification designed to keep your options open.</h2></div><div><p>Founded in 1904 to break down barriers in the profession, ACCA offers a path to becoming a qualified accountant for people with talent and dedication. Today it connects more than 771,000 members and future members across 176 countries.</p><p>There are 13 exams in the full qualification route, alongside practical experience and the Professional Ethics module. On completion, students can become ACCA members and use the letters ACCA.</p></div></div></section>
		<section class="aft-section aft-section--soft aft-journey-section" id="acca-journey"><div class="aft-container"><div class="aft-head aft-head--row" data-reveal><div><p class="aft-eyebrow">The ACCA journey</p><h2>Build capability in stages.</h2></div><p class="aft-lede">Every level adds a new layer of technical confidence, professional judgement and career opportunity.</p></div><div class="aft-journey-grid"><?php foreach ( $acca_levels as $level ) : ?><article class="aft-journey-card" data-reveal><span><?php echo esc_html( $level['number'] ); ?></span><h3><?php echo esc_html( $level['name'] ); ?></h3><p><?php echo esc_html( $level['copy'] ); ?></p></article><?php endforeach; ?></div></div></section>
		<section class="aft-section aft-features-section"><div class="aft-container"><div class="aft-head aft-head--center" data-reveal><p class="aft-eyebrow">Why choose ACCA?</p><h2>A qualification with room to grow.</h2><p class="aft-lede">Take your career in any direction with a professional qualification that is respected by employers and recognised around the world.</p></div><div class="aft-icon-card-grid"><?php foreach ( $acca_features as $feature ) : ?><article class="aft-icon-card" data-reveal><span><?php aft_the_icon( $feature[2], 22 ); ?></span><h3><?php echo esc_html( $feature[0] ); ?></h3><p><?php echo esc_html( $feature[1] ); ?></p></article><?php endforeach; ?></div></div></section>
		<section class="aft-section aft-section--soft aft-platform-section"><div class="aft-container aft-platform-section__grid"><div class="aft-platform-section__copy" data-reveal><p class="aft-eyebrow">Why ACCA at AFT?</p><h2>Support that keeps your study moving.</h2><p>Our instructors bring real-world experience and guide students from their starting point through to professional membership. Alongside tuition, you get a learning ecosystem designed for flexible, focused progress.</p><a class="aft-text-link" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Speak to the AFT team<?php aft_the_icon( 'arrow-r', 16 ); ?></a></div><div class="aft-platform-list"><?php foreach ( $acca_platform as $item ) : ?><article data-reveal><span><?php aft_the_icon( $item[2], 19 ); ?></span><div><h3><?php echo esc_html( $item[0] ); ?></h3><p><?php echo esc_html( $item[1] ); ?></p></div></article><?php endforeach; ?></div></div></section>
		<section class="aft-section aft-program-cta"><div class="aft-container"><div class="aft-program-cta__inner" data-reveal><div><p class="aft-eyebrow">Make your global move</p><h2>Start your ACCA journey with AFT.</h2><p>Tell us where you are starting from and we will help you understand the next step.</p></div><div class="aft-about-cta__actions"><a class="aft-btn aft-btn--primary" href="<?php echo esc_url( home_url( '/new/student-registration-2/' ) ); ?>">Register now<?php aft_the_icon( 'arrow-r', 17 ); ?></a><a class="aft-btn aft-btn--onDark" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Ask a question</a></div></div></div></section>
		<?php

	elseif ( 'cima-financial-leadership-programme-flp' === $slug ) :
		$flp_steps = [ [ '01', 'Consultation', 'Book a free session to discuss your goals and career path.' ], [ '02', 'Exemptions', 'We help determine your entry point based on prior experience.' ], [ '03', 'Registration', 'Get enrolled on the official CIMA FLP platform with our support.' ], [ '04', 'Success', 'Study, pass your assessments and earn your global qualification.' ] ];
		$flp_roles = [ 'Financial Accountant', 'Management Accountant', 'Financial Analyst', 'Finance Manager', 'Business Analyst', 'Chief Financial Officer' ];
		$flp_faqs = [ [ 'Is this the official CIMA FLP programme?', 'Yes. AFT provides support for the official CIMA Financial Leadership Programme developed by the Chartered Institute of Management Accountants.' ], [ 'Can I work while studying?', 'Absolutely. CIMA FLP is designed for working professionals, students and career changers who need flexible online study.' ], [ 'How long does the programme take?', 'It depends on your entry level, exemptions and study pace. Many students complete all three professional levels in 12–18 months.' ], [ 'What is the estimated cost?', 'The estimated cost is R65,000 to R70,000 per year subscription, with flexible payment support available.' ] ];
		$flp_logos = [ [ 'saqa.png', 'SAQA' ], [ 'Global-Learning-Provider-2026.svg', 'Quality Council' ], [ 'cima-logo.png', 'AFT' ], [ 'logo2.png', 'CIMA' ], [ 'Kiplan.png', 'Kaplan' ], [ 'BPP.png', 'BPP' ], [ 'Aicpa.png', 'AICPA CIMA' ] ];
		?>
		<section class="aft-program-hero aft-program-hero--flp"><div class="aft-container aft-program-hero__grid"><div class="aft-program-hero__copy" data-reveal><p class="aft-eyebrow">CIMA · Financial Leadership Programme</p><h1>The flexible route to global finance leadership.</h1><p class="aft-page-hero__lede">Study towards the CGMA designation online, at your own pace, with an AFT support team that helps you move from your starting point to your next professional milestone.</p><div class="aft-page-hero__actions"><a class="aft-btn aft-btn--primary" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Book your consultation<?php aft_the_icon( 'arrow-r', 17 ); ?></a><a class="aft-btn aft-btn--ghost" href="#flp-benefits">Explore FLP</a></div><div class="aft-page-hero__proof"><div><strong>Online</strong><span>digital-first learning</span></div><div><strong>12–18</strong><span>months for many students</span></div><div><strong>CGMA</strong><span>professional designation</span></div></div></div><div class="aft-program-hero__visual" data-reveal><div class="aft-program-hero__visual-card aft-program-hero__visual-card--flp"><img src="<?php echo esc_url( AFT_URI . '/assets/img/exam-portal.jpg' ); ?>" alt="AFT student support for CIMA FLP" loading="eager"><span class="aft-program-hero__visual-badge"><?php aft_the_icon( 'spark', 20 ); ?><b>Built for working professionals</b><small>Flexible study. Practical progress.</small></span></div></div></div></section>
		<section class="aft-program-trust"><div class="aft-container aft-program-trust__in"><span>Supported by a connected learning ecosystem</span><div class="aft-flp-logos"><?php foreach ( $flp_logos as $logo ) : ?><img src="<?php echo esc_url( AFT_URI . '/assets/img/partners/' . $logo[0] ); ?>" alt="<?php echo esc_attr( $logo[1] ); ?>" loading="lazy"><?php endforeach; ?></div></div></section>
		<section class="aft-section aft-flp-benefits" id="flp-benefits"><div class="aft-container"><div class="aft-head aft-head--center" data-reveal><p class="aft-eyebrow">Why choose CIMA FLP?</p><h2>A modern programme for a modern professional.</h2><p class="aft-lede">The Financial Leadership Programme streamlines the journey to qualification while keeping learning practical, flexible and connected to real business.</p></div><div class="aft-icon-card-grid aft-icon-card-grid--three"><article class="aft-icon-card" data-reveal><span><?php aft_the_icon( 'clock', 22 ); ?></span><h3>Flexible learning</h3><p>Study anytime, anywhere. The digital-first platform adapts to your schedule and working life.</p></article><article class="aft-icon-card" data-reveal><span><?php aft_the_icon( 'bolt', 22 ); ?></span><h3>Faster progression</h3><p>Move through the levels at your own pace and make your previous experience work for you.</p></article><article class="aft-icon-card" data-reveal><span><?php aft_the_icon( 'target', 22 ); ?></span><h3>Real business skills</h3><p>Focus on practical application and competency-based learning that employers value.</p></article></div></div></section>
		<section class="aft-section aft-section--soft aft-flp-support"><div class="aft-container aft-flp-support__grid"><div class="aft-flp-support__media" data-reveal><img src="<?php echo esc_url( AFT_URI . '/assets/img/hero.jpg' ); ?>" alt="AFT support team helping students" loading="lazy"><span><b>Human support behind digital learning</b><small>From exemptions to exam preparation.</small></span></div><div class="aft-flp-support__copy" data-reveal><p class="aft-eyebrow">Comprehensive support</p><h2>You get the platform — and a team around you.</h2><p>At AFT, we do not just give you access to the platform. Our experts support you from the initial exemption check through to final assessment preparation, helping you keep momentum when the route feels complex.</p><div class="aft-check-list"><div><?php aft_the_icon( 'check-c', 17 ); ?><span>Guidance on your entry point and exemptions</span></div><div><?php aft_the_icon( 'check-c', 17 ); ?><span>Practical tuition and assessment preparation</span></div><div><?php aft_the_icon( 'check-c', 17 ); ?><span>Responsive support from a team that knows the journey</span></div></div><a class="aft-text-link" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Learn more about support<?php aft_the_icon( 'arrow-r', 16 ); ?></a></div></div></section>
		<section class="aft-section aft-flp-steps"><div class="aft-container"><div class="aft-head aft-head--row" data-reveal><div><p class="aft-eyebrow">How it works</p><h2>Four simple steps to get started.</h2></div><p class="aft-lede">A clear beginning makes it easier to keep moving.</p></div><div class="aft-steps-grid"><?php foreach ( $flp_steps as $step ) : ?><article data-reveal><span><?php echo esc_html( $step[0] ); ?></span><h3><?php echo esc_html( $step[1] ); ?></h3><p><?php echo esc_html( $step[2] ); ?></p></article><?php endforeach; ?></div></div></section>
		<section class="aft-section aft-section--soft aft-flp-careers"><div class="aft-container aft-flp-careers__grid"><div><p class="aft-eyebrow">Career opportunities</p><h2>Take your place in the finance function.</h2><p>A CIMA qualification is recognised globally by employers across major industries and opens doors to roles with increasing influence.</p></div><div class="aft-role-cloud"><?php foreach ( $flp_roles as $role ) : ?><span><?php echo esc_html( $role ); ?></span><?php endforeach; ?></div></div></section>
		<section class="aft-section aft-faq-section"><div class="aft-container aft-faq-grid"><div><p class="aft-eyebrow">Questions, answered</p><h2>Make your next decision with confidence.</h2><p>Still deciding whether FLP is right for you? We are happy to talk through your goals, entry point and study options.</p><a class="aft-text-link" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Ask the AFT team<?php aft_the_icon( 'arrow-r', 16 ); ?></a></div><div class="aft-faq-list"><?php foreach ( $flp_faqs as $faq ) : ?><details><summary><?php echo esc_html( $faq[0] ); ?><span>+</span></summary><p><?php echo esc_html( $faq[1] ); ?></p></details><?php endforeach; ?></div></div></section>
		<section class="aft-section aft-program-cta"><div class="aft-container"><div class="aft-program-cta__inner" data-reveal><div><p class="aft-eyebrow">Become a qualified management accountant</p><h2>Build your leadership path with CIMA FLP.</h2><p>Estimated investment: R65,000–R70,000 per year subscription. Flexible payment support available.</p></div><div class="aft-about-cta__actions"><a class="aft-btn aft-btn--primary" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Book consultation<?php aft_the_icon( 'arrow-r', 17 ); ?></a><a class="aft-btn aft-btn--onDark" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Check exemptions</a></div></div></div></section>
		<?php

	elseif ( 'company-profile' === $slug ) :
		$advantages = [ [ 'Personalised approach', 'Tailored classes are designed around individual needs, goals and starting points.', 'users' ], [ 'Innovative teaching', 'Technology and modern teaching techniques create engaging, immersive learning.', 'spark' ], [ 'Enhanced student experience', 'Our portal tools and resources help students take control of their education.', 'monitor' ], [ 'Flexibility and convenience', 'Custom study plans fit different schedules, responsibilities and learning pace.', 'clock' ], [ 'Expert lecturers', 'Experienced instructors bring real-world industry expertise into online learning.', 'award' ], [ 'Continuous improvement', 'Student feedback helps us keep strengthening our programmes and services.', 'chart' ] ];
		$markets = [ [ 'Private and public sector organisations', 'We support current and aspiring management specialists across industries as demand for CIMA-accredited professionals grows.', 'https://visionpluss.co.za/wp-content/uploads/2024/05/Fundamentals-of-Financial-Accounting-150x150.jpg' ], [ 'High school students and recent graduates', 'We support new entrants, international students and people looking for a new route into professional finance.', 'https://visionpluss.co.za/wp-content/uploads/2024/04/about-students-150x150.png' ], [ 'Undergraduate and postgraduate students', 'We provide tutoring, guidance and mentorship for students pursuing professional qualifications after their degrees.', 'https://visionpluss.co.za/wp-content/uploads/2024/04/graduating-student-150x150.jpg' ], [ 'Other professionals', 'We help people with diverse qualifications or relevant experience transition into CIMA and professional accounting.', 'https://visionpluss.co.za/wp-content/uploads/2024/05/Financial-Strategy-150x150.jpg' ] ];
		$partners = [ [ 'Erongo Marine Enterprises PTY LTD', 'A trusted Namibia-based partner investing in employee education and tailored CIMA tuition solutions.', 'silas.kambata@erongo.co.za' ], [ 'ABSA Group', 'A partner empowering employees with high-quality CIMA tuition for career advancement.', 'nomvula.nonjabe@absa.africa' ], [ 'Sibanye Gold Academy PTY LTD', 'A partner investing in comprehensive tuition solutions and a stronger finance workforce.', 'ashtonsidneybrierley@gmail.com' ] ];
		?>
		<section class="aft-profile-hero"><div class="aft-container aft-profile-hero__grid"><div data-reveal><p class="aft-eyebrow">Accountants for Tomorrow · Est. 2015</p><h1>Built to make professional progress more personal.</h1><p class="aft-page-hero__lede">AFT began in response to a changing CIMA landscape. Today, we help ambitious learners build the skills, confidence and direction to thrive in finance.</p><div class="aft-page-hero__actions"><a class="aft-btn aft-btn--primary" href="#profile-story">Our story<?php aft_the_icon( 'arrow-r', 17 ); ?></a><a class="aft-btn aft-btn--ghost" href="<?php echo esc_url( home_url( '/our-team/' ) ); ?>">Meet our team</a></div><div class="aft-page-hero__proof"><div><strong>2015</strong><span>founded in Johannesburg</span></div><div><strong>10+</strong><span>years of learning support</span></div><div><strong>16+</strong><span>countries reached through learning</span></div></div></div><div class="aft-profile-hero__visual" data-reveal><img src="<?php echo esc_url( AFT_URI . '/assets/img/hero.jpg' ); ?>" alt="Professional learning at AFT" loading="eager"><span><b>Learn with purpose</b><small>Expert tuition · practical confidence</small></span></div></div></section>
		<section class="aft-program-trust"><div class="aft-container aft-program-trust__in"><span>A learning partner for the next generation of finance leaders</span><div class="aft-trust-strip__marks"><b>CIMA</b><i></i><b>ACCA</b><i></i><b>CGMA</b></div></div></section>
		<section class="aft-section aft-profile-story" id="profile-story"><div class="aft-container aft-profile-story__grid"><div class="aft-profile-story__media" data-reveal><img src="<?php echo esc_url( AFT_URI . '/assets/img/hero.jpg' ); ?>" alt="AFT learning environment" loading="lazy"><span><strong>2015</strong><small>Founded in Kempton Park, Johannesburg</small></span></div><div data-reveal><p class="aft-eyebrow">Our history</p><h2>Born from a change in the exam room.</h2><p>Founded in 2015 in Johannesburg’s Kempton Park area, Accountants for Tomorrow emerged in response to significant changes introduced by CIMA: online examinations, new Objective Tests and online Case Study Exams under the 2015 syllabus.</p><p>The move to 100% online examinations and higher pass marks created new challenges for students. AFT was built to make the transition clearer, the preparation more focused and the learning experience more supportive.</p></div></div></section>
		<section class="aft-section aft-section--soft aft-advantage-section"><div class="aft-container"><div class="aft-head aft-head--center" data-reveal><p class="aft-eyebrow">Our competitive advantage</p><h2>More than tuition. A complete learning experience.</h2><p class="aft-lede">We combine expert people, practical tools and a commitment to continuous improvement.</p></div><div class="aft-icon-card-grid aft-icon-card-grid--three"><?php foreach ( $advantages as $item ) : ?><article class="aft-icon-card" data-reveal><span><?php aft_the_icon( $item[2], 22 ); ?></span><h3><?php echo esc_html( $item[0] ); ?></h3><p><?php echo esc_html( $item[1] ); ?></p></article><?php endforeach; ?></div></div></section>
		<section class="aft-section aft-market-section"><div class="aft-container"><div class="aft-head aft-head--row" data-reveal><div><p class="aft-eyebrow">Who we serve</p><h2>Learning designed around real starting points.</h2></div><p class="aft-lede">From school leavers to senior professionals, we help people find a practical route into the finance career they want.</p></div><div class="aft-market-grid"><?php foreach ( $markets as $market ) : ?><article data-reveal><img src="<?php echo esc_url( $market[2] ); ?>" alt="<?php echo esc_attr( $market[0] ); ?>" loading="lazy"><div><h3><?php echo esc_html( $market[0] ); ?></h3><p><?php echo esc_html( $market[1] ); ?></p></div></article><?php endforeach; ?></div></div></section>
		<section class="aft-section aft-section--soft aft-culture-section"><div class="aft-container aft-culture-section__grid"><div class="aft-culture-section__media" data-reveal><img src="https://visionpluss.co.za/wp-content/uploads/2024/07/mimi-thian-737711-unsplash.jpg" alt="Collaborative AFT team culture" loading="lazy"><span><b>Democratic and participative</b><small>Teamwork, flexibility and open communication.</small></span></div><div data-reveal><p class="aft-eyebrow">Company culture</p><h2>A culture that helps people do their best work.</h2><div class="aft-check-list"><div><?php aft_the_icon( 'users', 17 ); ?><span>We encourage teamwork, collaboration and collective problem-solving.</span></div><div><?php aft_the_icon( 'heart', 17 ); ?><span>We value ideas and input from every member of the team.</span></div><div><?php aft_the_icon( 'chart', 17 ); ?><span>We support professional development, including CIMA study sponsorship.</span></div><div><?php aft_the_icon( 'message', 17 ); ?><span>We keep communication open, transparent and respectful.</span></div></div></div></div></section>
		<section class="aft-section aft-partners-section"><div class="aft-container"><div class="aft-head aft-head--center" data-reveal><p class="aft-eyebrow">Partnerships</p><h2>Helping organisations grow finance capability.</h2><p class="aft-lede">We collaborate with organisations that prioritise the professional development of their employees.</p></div><div class="aft-partners-grid"><?php foreach ( $partners as $partner ) : ?><article data-reveal><span><?php aft_the_icon( 'users', 20 ); ?></span><h3><?php echo esc_html( $partner[0] ); ?></h3><p><?php echo esc_html( $partner[1] ); ?></p><small>Contact: <?php echo esc_html( $partner[2] ); ?></small></article><?php endforeach; ?></div></div></section>
		<section class="aft-section aft-program-cta"><div class="aft-container"><div class="aft-program-cta__inner" data-reveal><div><p class="aft-eyebrow">Build tomorrow with us</p><h2>Find the learning route that fits your ambition.</h2><p>Explore our programmes or speak to the AFT team about your next step.</p></div><div class="aft-about-cta__actions"><a class="aft-btn aft-btn--primary" href="<?php echo esc_url( home_url( '/courses/' ) ); ?>">Explore courses<?php aft_the_icon( 'arrow-r', 17 ); ?></a><a class="aft-btn aft-btn--onDark" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact AFT</a></div></div></div></section>
		<?php

	elseif ( 'products-services' === $slug ) :
		$services = [
			[ 'CIMA classes', 'Online classes across Certificate, Operational, Management and Strategic levels, led by experienced instructors.', AFT_URI . '/assets/img/hero.jpg', 'https://visionpluss.co.za/new/courses/' ],
			[ 'CIMA textbooks', 'High-quality CIMA textbooks and study materials curated around the latest syllabus and exam requirements.', AFT_URI . '/assets/img/exam-portal.jpg', 'https://visionpluss.co.za/new/product-category/books/' ],
			[ 'CGMA Finance Leadership Programme', 'A digital-first route to the CGMA designation with flexible learning, online assessment and optional tuition support.', AFT_URI . '/assets/img/aft-student.png', 'https://visionpluss.co.za/cima-financial-leadership-programme-flp/' ],
			[ 'CFO Executive Pathway', 'A streamlined pathway for professionals with 10 or more years of senior strategic experience seeking the CGMA designation.', AFT_URI . '/assets/img/hero.jpg', 'https://www.aicpa-cima.com/membership/landing/regional-pathway-cima' ],
			[ 'CGMA Senior Executive Programme', 'Designed for professionals with five or more years of senior management experience aiming for the CGMA designation.', AFT_URI . '/assets/img/exam-portal.jpg', 'https://www.aicpa-cima.com/membership/landing/regional-pathway-cima' ],
			[ 'CA(SA) & CGMA pathway', 'An accelerated route that can help eligible professionals work towards both the CA(SA) and CGMA designations.', AFT_URI . '/assets/img/partners/cima-logo.png', 'https://www.aicpa-cima.com/news/article/cima-and-saica-sign-revised-agreement-to-provide-easier-access-to-dual' ],
			[ 'CA(NAM) & CGMA pathway', 'A professional membership pathway connecting ICAN and CIMA members to the qualifications they need.', AFT_URI . '/assets/img/partners/Global-Learning-Provider-2026.svg', 'https://www.aicpa-cima.com/news/article/cima-and-ican-membership-pathway-agreement-sees-first-ever-professional' ],
		];
		?>
		<section class="aft-services-hero"><div class="aft-container aft-services-hero__grid"><div data-reveal><p class="aft-eyebrow">AFT learning ecosystem</p><h1>Everything you need to move further in finance.</h1><p class="aft-page-hero__lede">From online tuition and textbooks to flexible executive pathways, our products and services help you choose the right route for your professional goals.</p><div class="aft-page-hero__actions"><a class="aft-btn aft-btn--primary" href="#services-list">Explore our services<?php aft_the_icon( 'arrow-r', 17 ); ?></a><a class="aft-btn aft-btn--ghost" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Talk to AFT</a></div><div class="aft-page-hero__proof"><div><strong>7</strong><span>routes and services</span></div><div><strong>Online</strong><span>learning wherever you are</span></div><div><strong>Expert</strong><span>support when it matters</span></div></div></div><div class="aft-services-hero__visual" data-reveal><img src="<?php echo esc_url( AFT_URI . '/assets/img/exam-portal.jpg' ); ?>" alt="AFT digital learning and exam preparation" loading="eager"><span><b>Learn. Practise. Progress.</b><small>One ecosystem for your next move.</small></span></div></div></section>
		<section class="aft-program-trust"><div class="aft-container aft-program-trust__in"><span>Professional learning, materials and pathways in one place</span><div class="aft-trust-strip__marks"><b>CIMA</b><i></i><b>CGMA</b><i></i><b>AFT</b></div></div></section>
		<section class="aft-section aft-services-section" id="services-list"><div class="aft-container"><div class="aft-head aft-head--row" data-reveal><div><p class="aft-eyebrow">Products & services</p><h2>Choose the support that fits your next step.</h2></div><p class="aft-lede">Whether you are starting out, preparing for exams or progressing as a senior professional, AFT has a route to explore.</p></div><div class="aft-services-grid"><?php foreach ( $services as $service ) : ?><article class="aft-service-card" data-reveal><div class="aft-service-card__image"><img src="<?php echo esc_url( $service[2] ); ?>" alt="<?php echo esc_attr( $service[0] ); ?>" loading="lazy"><span><?php aft_the_icon( 'arrow-r', 17 ); ?></span></div><div class="aft-service-card__body"><p class="aft-eyebrow">AFT programme</p><h3><?php echo esc_html( $service[0] ); ?></h3><p><?php echo esc_html( $service[1] ); ?></p><a class="aft-text-link" href="<?php echo esc_url( $service[3] ); ?>" target="_blank" rel="noopener">Learn more<?php aft_the_icon( 'arrow-r', 16 ); ?></a></div></article><?php endforeach; ?></div></div></section>
		<section class="aft-section aft-section--soft aft-services-support"><div class="aft-container aft-services-support__inner" data-reveal><div><p class="aft-eyebrow">A flexible learning partner</p><h2>Build your own combination of classes, materials and support.</h2><p>Our services can work together: use tuition to understand the content, study materials to deepen it, question practice to test it and expert support to keep you moving.</p></div><div class="aft-services-support__steps"><span><b>01</b>Learn</span><span><b>02</b>Practise</span><span><b>03</b>Progress</span></div></div></section>
		<section class="aft-section aft-program-cta"><div class="aft-container"><div class="aft-program-cta__inner" data-reveal><div><p class="aft-eyebrow">Your next professional milestone</p><h2>Not sure which service is right for you?</h2><p>Tell us where you are starting from and we will help you compare your options.</p></div><div class="aft-about-cta__actions"><a class="aft-btn aft-btn--primary" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Speak to AFT<?php aft_the_icon( 'arrow-r', 17 ); ?></a><a class="aft-btn aft-btn--onDark" href="<?php echo esc_url( home_url( '/new/student-registration-2/' ) ); ?>">Register now</a></div></div></div></section>
		<?php

	elseif ( 'contact' === $slug ) :
		$contact_details = [ [ 'Office', 'Office 113 Krestel Place', 'Moddercrest Office Park · Modderfontein', 'pin' ], [ 'Call us', '+27 11 970 7354', '+27 83 352 8056', 'phone' ], [ 'Email', 'info@accountantsfortomorrow.co.za', 'patrickp@accountantsfortomorrow.co.za', 'mail' ] ];
		?>
		<section class="aft-contact-hero"><div class="aft-container aft-contact-hero__grid"><div data-reveal><p class="aft-eyebrow">Accountants for Tomorrow</p><h1>Let’s plan your next step.</h1><p class="aft-page-hero__lede">Whether you are choosing a qualification, checking exemptions or looking for the right support, our team is ready to help.</p><div class="aft-page-hero__actions"><a class="aft-btn aft-btn--primary" href="#contact-form">Send us a message<?php aft_the_icon( 'arrow-r', 17 ); ?></a><a class="aft-btn aft-btn--ghost" href="tel:+27119707354">Call the team</a></div><div class="aft-page-hero__proof"><div><strong>1:1</strong><span>helpful guidance</span></div><div><strong>Online</strong><span>support wherever you are</span></div><div><strong>Human</strong><span>answers to real questions</span></div></div></div><div class="aft-contact-hero__visual" data-reveal><div><span><?php aft_the_icon( 'message', 34 ); ?></span><b>We are here to help.</b><small>Courses · exemptions · registration · support</small></div></div></div></section>
		<section class="aft-section aft-contact-details"><div class="aft-container"><div class="aft-head aft-head--center" data-reveal><p class="aft-eyebrow">Keep in touch</p><h2>Choose the easiest way to reach us.</h2><p class="aft-lede">Send a message through the form or use the details below to contact the AFT team directly.</p></div><div class="aft-contact-details__grid"><?php foreach ( $contact_details as $detail ) : ?><article data-reveal><span><?php aft_the_icon( $detail[3], 21 ); ?></span><p class="aft-eyebrow"><?php echo esc_html( $detail[0] ); ?></p><strong><?php echo esc_html( $detail[1] ); ?></strong><small><?php echo esc_html( $detail[2] ); ?></small></article><?php endforeach; ?></div></div></section>
		<section class="aft-section aft-section--soft aft-contact-form-section" id="contact-form"><div class="aft-container aft-contact-form__grid"><div data-reveal><p class="aft-eyebrow">Send a message</p><h2>Tell us what you need help with.</h2><p>Leave a message and one of our administrators will get in touch. You can ask about courses, entry points, exemptions, study materials or registration.</p><div class="aft-contact-form__promise"><span><?php aft_the_icon( 'check-c', 18 ); ?></span><div><b>A thoughtful response</b><small>We will route your enquiry to the right person.</small></div></div></div><div class="aft-contact-form__box" data-reveal><?php $contact_form = do_shortcode( '[fluentform id="4"]' ); if ( '' !== trim( wp_strip_all_tags( $contact_form ) ) && '[fluentform id="4"]' !== trim( wp_strip_all_tags( $contact_form ) ) ) : echo $contact_form; else : ?><div class="aft-contact-form__fallback"><span><?php aft_the_icon( 'mail', 24 ); ?></span><p class="aft-eyebrow">Email us directly</p><h3>Tell us how we can help.</h3><p>Send your enquiry to the AFT team and include the qualification, level or support you are asking about.</p><a class="aft-btn aft-btn--primary" href="mailto:info@accountantsfortomorrow.co.za?subject=AFT%20website%20enquiry">Email AFT<?php aft_the_icon( 'arrow-r', 17 ); ?></a></div><?php endif; ?></div></div></section>
		<section class="aft-section aft-contact-map"><div class="aft-container aft-contact-map__inner"><div><p class="aft-eyebrow">Find our office</p><h2>Moddercrest Office Park, Modderfontein.</h2><p>Office 113 Krestel Place. Contact us before visiting so we can make sure the right member of the team is available.</p><a class="aft-text-link" href="https://maps.google.com/?q=Moddercrest+Office+Park+Modderfontein" target="_blank" rel="noopener">Open in Google Maps<?php aft_the_icon( 'arrow-r', 16 ); ?></a></div><div class="aft-contact-map__visual"><span><?php aft_the_icon( 'pin', 28 ); ?></span><b>AFT office</b><small>Moddercrest Office Park<br>Modderfontein</small></div></div></section>
		<?php

	elseif ( 'our-team' === $slug ) :
		$operations = [
			[
				'name'  => 'Stanley Mukarati',
				'role'  => 'Administration & finance',
				'cred'  => 'BSc Honours Accountancy',
				'image' => 'https://accountantsfortomorrow.co.za/wp-content/uploads/elementor/thumbs/Stanley-qqwbbeupiv9qzud5dt79ak30n4y0iesrc1fl73odlc.png',
				'copy'  => 'Stanley supports the administration and financial operations that keep the AFT learning experience organised and dependable.',
			],
			[
				'name'  => 'Millicent Pfidze',
				'role'  => 'Student services & operations',
				'cred'  => 'CIMA studies in progress',
				'image' => 'https://visionpluss.co.za/wp-content/uploads/2024/07/Millicent.png',
				'copy'  => 'Millicent guides registrations, subject selection, study materials, payments and day-to-day student support.',
			],
			[
				'name'  => 'Cicilia Lekgetho',
				'role'  => 'Student services & operations',
				'cred'  => 'Diploma & Advanced Diploma in Finance',
				'image' => 'https://visionpluss.co.za/wp-content/uploads/2024/07/Cicilia.png',
				'copy'  => 'Cicilia combines financial-management expertise with a practical, responsive approach to student and lecturer support.',
			],
		];
		$lecturers = [
			[
				'name'  => 'Patrick Pfidze',
				'cred'  => 'ACMA · CGMA · MBA',
				'image' => 'https://visionpluss.co.za/wp-content/uploads/2024/07/Patrick.png',
				'copy'  => 'Founder and CEO with extensive corporate and academic experience, passionate about making complex accounting concepts practical and clear.',
			],
			[
				'name'  => 'Khystyn Angela Gouden',
				'cred'  => 'ACMA · CGMA',
				'image' => 'https://visionpluss.co.za/wp-content/uploads/2024/07/Angelass.webp',
				'copy'  => 'Senior lecturer and finance professional with Big Four, SAP consulting and management-accounting experience.',
			],
			[
				'name'  => 'Lebogang Mogoaneng',
				'cred'  => 'ACMA · CGMA',
				'image' => 'https://visionpluss.co.za/wp-content/uploads/2024/08/Lebo-site-pic.webp',
				'copy'  => 'Brings cost and management-accounting expertise together with practical insight from advanced professional study.',
			],
			[
				'name'  => 'Zvikomborero Nyawungwa',
				'cred'  => 'ACMA · CGMA',
				'image' => 'https://visionpluss.co.za/wp-content/uploads/2024/08/Zvikomborero.webp',
				'copy'  => 'An accounting and finance professional specialising in reporting, reconciliation, business management and IFRS-focused practice.',
			],
			[
				'name'  => 'Sylvester Musademba',
				'cred'  => 'Dip Finance Management · B.Com',
				'image' => 'https://visionpluss.co.za/wp-content/uploads/2025/02/slvesteer.jpg.png',
				'copy'  => 'A dedicated accounting educator who connects university teaching experience with practical financial-management insight.',
			],
			[
				'name'  => 'Olorato Mongake',
				'cred'  => 'ACMA · CGMA',
				'image' => 'https://visionpluss.co.za/wp-content/uploads/2025/02/Olorato-Mongake.jpg.png',
				'copy'  => 'A finance leader with more than a decade of industry experience and a passion for bringing real-world context into learning.',
			],
			[
				'name'  => 'Buhle Khumalo',
				'cred'  => 'CA(SA) · ACMA · CGMA',
				'image' => 'https://visionpluss.co.za/wp-content/uploads/2025/12/Buhle.png',
				'copy'  => 'A results-oriented finance professional committed to lifelong learning, mentorship and lifting others as she rises.',
			],
			[
				'name'  => 'Caroline Hungwe',
				'cred'  => 'ACMA · CGMA',
				'image' => 'https://visionpluss.co.za/wp-content/uploads/2025/02/Hungwe.jpg.png',
				'copy'  => 'An experienced academic and finance professional who makes complex ideas accessible through clear teaching and applied examples.',
			],
		];
		?>
		<section class="aft-team-hero">
			<div class="aft-container aft-team-hero__grid">
				<div class="aft-team-hero__copy" data-reveal>
					<p class="aft-eyebrow">The people behind the progress</p>
					<h1>Meet the team shaping tomorrow’s finance leaders.</h1>
					<p class="aft-page-hero__lede">AFT brings together educators, finance professionals and student-support specialists who believe that exceptional learning is personal, practical and purposeful.</p>
					<div class="aft-page-hero__actions">
						<a class="aft-btn aft-btn--primary" href="<?php echo esc_url( home_url( '/courses/' ) ); ?>">Explore our courses<?php aft_the_icon( 'arrow-r', 17 ); ?></a>
						<a class="aft-btn aft-btn--ghost" href="#lecturing-team">Meet our lecturers</a>
					</div>
					<div class="aft-team-hero__stats"><div><strong>10+</strong><span>years of learning support</span></div><div><strong>1:1</strong><span>student-centred guidance</span></div><div><strong>CGMA</strong><span>professional direction</span></div></div>
				</div>
				<div class="aft-team-hero__visual" data-reveal>
					<div class="aft-team-hero__portrait"><img src="https://visionpluss.co.za/wp-content/uploads/2024/07/Patrick.png" alt="Patrick Pfidze, founder and CEO of Accountants for Tomorrow" width="427" height="240"></div>
					<div class="aft-team-hero__visual-card"><span><?php aft_the_icon( 'users', 20 ); ?></span><b>One team. One purpose.</b><small>Helping every learner move forward.</small></div>
				</div>
			</div>
		</section>

		<section class="aft-team-intro aft-section--tight">
			<div class="aft-container aft-team-intro__in"><span><?php aft_the_icon( 'cap', 25 ); ?></span><p>Our team combines academic knowledge, professional credentials and the lived experience of the accounting journey.</p><div class="aft-trust-strip__marks"><b>CIMA</b><i></i><b>ACCA</b><i></i><b>CGMA</b></div></div>
		</section>

		<section class="aft-section aft-team-founder">
			<div class="aft-container aft-team-founder__grid">
				<div class="aft-team-founder__media" data-reveal><img src="https://visionpluss.co.za/wp-content/uploads/2024/07/Patrick.png" alt="Patrick Pfidze" width="427" height="240"><span class="aft-team-founder__badge"><b>Founder & CEO</b><small>ACMA · CGMA · MBA</small></span></div>
				<div class="aft-team-founder__copy" data-reveal><p class="aft-eyebrow">Leadership</p><h2>Experience that understands the journey.</h2><p>Patrick Pfidze founded Accountants for Tomorrow after experiencing the challenges of the changing CIMA examination system first-hand. That experience shaped AFT’s approach: make difficult subjects clearer, make preparation more purposeful and never lose sight of the person behind the qualification.</p><p>Today, Patrick leads the organisation and remains closely involved in teaching, mentoring and building a learning environment where students can ask questions, practise deliberately and progress with confidence.</p><div class="aft-team-founder__credentials"><span><?php aft_the_icon( 'award', 18 ); ?> MBA · Edinburgh Business School</span><span><?php aft_the_icon( 'check-c', 18 ); ?> ACMA · CGMA</span><span><?php aft_the_icon( 'book', 18 ); ?> Economics & Accounting</span></div></div>
			</div>
		</section>

		<section class="aft-section aft-section--soft aft-team-operations">
			<div class="aft-container"><div class="aft-head aft-head--center" data-reveal><p class="aft-eyebrow">Student experience</p><h2>The support team behind every smooth start.</h2><p class="aft-lede">From registration to study materials and day-to-day guidance, our operations team keeps students connected and supported.</p></div><div class="aft-team-operations__grid">
				<?php foreach ( $operations as $member ) : ?>
					<article class="aft-team-card aft-team-card--operations<?php echo 'Stanley Mukarati' === $member['name'] ? ' aft-team-card--stanley' : ''; ?>" data-reveal><div class="aft-team-card__image"><?php if ( $member['image'] ) : ?><img src="<?php echo esc_url( $member['image'] ); ?>" alt="<?php echo esc_attr( $member['name'] ); ?>" loading="lazy"><?php else : ?><span class="aft-team-card__placeholder"><?php aft_the_icon( 'users', 42 ); ?><small>Student support</small></span><?php endif; ?></div><div class="aft-team-card__body"><p class="aft-eyebrow"><?php echo esc_html( $member['role'] ); ?></p><h3><?php echo esc_html( $member['name'] ); ?></h3><span class="aft-team-card__cred"><?php echo esc_html( $member['cred'] ); ?></span><p><?php echo esc_html( $member['copy'] ); ?></p></div></article>
				<?php endforeach; ?>
			</div></div>
		</section>

		<section class="aft-section aft-team-lecturers" id="lecturing-team">
			<div class="aft-container"><div class="aft-head aft-head--row" data-reveal><div><p class="aft-eyebrow">Academic team</p><h2>Lecturers who bring the profession into the classroom.</h2></div><p class="aft-lede">Learn from people who understand the theory, the exam and the workplace.</p></div><div class="aft-team-lecturers__grid">
				<?php foreach ( $lecturers as $member ) : ?>
					<article class="aft-team-card aft-team-card--lecturer" data-reveal><div class="aft-team-card__image"><img src="<?php echo esc_url( $member['image'] ); ?>" alt="<?php echo esc_attr( $member['name'] ); ?>" loading="lazy"></div><div class="aft-team-card__body"><h3><?php echo esc_html( $member['name'] ); ?></h3><span class="aft-team-card__cred"><?php echo esc_html( $member['cred'] ); ?></span><p><?php echo esc_html( $member['copy'] ); ?></p></div></article>
				<?php endforeach; ?>
			</div></div>
		</section>

		<section class="aft-section aft-team-cta"><div class="aft-container"><div class="aft-team-cta__inner" data-reveal><div><p class="aft-eyebrow">Learn with the people who care</p><h2>Find the right path for your next professional milestone.</h2><p>Explore our programmes or speak to the AFT team about where to begin.</p></div><div class="aft-about-cta__actions"><a class="aft-btn aft-btn--primary" href="<?php echo esc_url( home_url( '/courses/' ) ); ?>">Explore courses<?php aft_the_icon( 'arrow-r', 17 ); ?></a><a class="aft-btn aft-btn--onDark" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact AFT</a></div></div></div></section>
		<?php

	else :
		?>
		<section class="aft-page-hero aft-page-hero--generic">
			<div class="aft-container">
				<div class="aft-page-hero__copy" data-reveal>
					<p class="aft-eyebrow">Accountants for Tomorrow</p>
					<h1><?php the_title(); ?></h1>
					<?php if ( has_excerpt() ) : ?><p class="aft-page-hero__lede"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
				</div>
			</div>
		</section>
		<section class="aft-section aft-page-content-section">
			<div class="aft-container">
				<article <?php post_class( 'aft-page-content' ); ?>>
					<div class="aft-prose"><?php the_content(); ?></div>
				</article>
			</div>
		</section>
		<?php
	endif;
endwhile;

get_footer();
