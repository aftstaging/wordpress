<?php defined( 'ABSPATH' ) || exit; $aft_stats = aft_stats(); if ( ! $aft_stats ) { return; } ?>
<section class="aft-section aft-section--tight">
	<div class="aft-container">
		<div class="aft-stats" data-reveal>
			<?php foreach ( $aft_stats as $aft_s ) : ?>
				<div class="aft-stat">
					<span class="aft-stat__ico"><?php aft_the_icon( $aft_s['icon'], 21 ); ?></span>
					<div class="aft-stat__num">
						<span data-count="<?php echo esc_attr( preg_replace( '/[^0-9.]/', '', $aft_s['value'] ) ); ?>">0</span><?php echo esc_html( $aft_s['suffix'] ); ?>
					</div>
					<div class="aft-stat__lbl"><?php echo esc_html( $aft_s['label'] ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
