<?php
defined( 'ABSPATH' ) || exit;
$aft_p = aft_partners();
if ( ! $aft_p ) { return; }
$aft_loop = array_merge( $aft_p, $aft_p ); // duplicated for a seamless marquee
?>
<section class="aft-section aft-section--soft">
	<div class="aft-container">
		<div class="aft-phead" data-reveal>
			<p class="aft-eyebrow"><?php esc_html_e( 'Our Partners', 'aft' ); ?></p>
			<h2><?php esc_html_e( 'Trusted Partnerships', 'aft' ); ?></h2>
			<p class="aft-phead__text"><?php esc_html_e( 'Connected to organisations and learning resources that support the professional development of tomorrow\'s finance professionals.', 'aft' ); ?></p>
		</div>
	</div>

	<div class="aft-marquee" data-parallax data-speed="0.03">
		<div class="aft-marquee__track aft-marquee__track--ltr">
			<?php foreach ( $aft_loop as $aft_i => $aft_x ) : ?>
				<?php $aft_tag = $aft_x['url'] ? 'a' : 'span'; ?>
				<<?php echo $aft_tag; ?> class="aft-plogo"
					<?php if ( $aft_x['url'] ) : ?>href="<?php echo esc_url( $aft_x['url'] ); ?>" target="_blank" rel="noopener noreferrer"<?php endif; ?>
					<?php echo $aft_i >= count( $aft_p ) ? 'aria-hidden="true" tabindex="-1"' : ''; ?>>
					<img src="<?php echo esc_url( $aft_x['logo'] ); ?>" alt="<?php echo esc_attr( $aft_x['name'] ); ?>" loading="lazy">
				</<?php echo $aft_tag; ?>>
			<?php endforeach; ?>
		</div>
	</div>

</section>
