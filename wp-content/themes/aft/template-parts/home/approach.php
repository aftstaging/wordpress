<?php defined( 'ABSPATH' ) || exit; ?>
<section class="aft-section">
	<div class="aft-container">
		<div class="aft-head aft-head--center" data-reveal>
			<p class="aft-eyebrow"><?php esc_html_e( 'Our Approach', 'aft' ); ?></p>
			<h2><?php esc_html_e( 'Built Around How You Actually Study', 'aft' ); ?></h2>
		</div>
		<div class="aft-approach">
			<?php foreach ( aft_approach() as $aft_i => $aft_f ) : ?>
				<article class="aft-feat" data-reveal data-delay="<?php echo esc_attr( min( 4, $aft_i ) ); ?>">
					<span class="aft-feat__ico"><?php aft_the_icon( $aft_f['icon'], 24 ); ?></span>
					<h3><?php echo esc_html( $aft_f['title'] ); ?></h3>
					<p><?php echo esc_html( $aft_f['desc'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
