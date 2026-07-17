<?php

declare( strict_types=1 );

/**
 * Template for the Redux demo shortcode.
 *
 * @var string                                                         $headline Headline from Redux options.
 * @var string                                                         $intro    Intro text from Redux options.
 * @var array<int, array{attachment_id: int, url: string, caption: string}> $gallery Gallery items.
 *
 * @package BoilerplatePlugin
 */

\defined( 'ABSPATH' ) || exit;

$headline = isset( $headline ) && is_string( $headline ) ? $headline : '';
$intro    = isset( $intro ) && is_string( $intro ) ? $intro : '';
$gallery  = isset( $gallery ) && is_array( $gallery ) ? $gallery : array();
?>

<div class="boilerplate-plugin-shortcode">
	<?php if ( '' !== $headline ) : ?>
		<h2 class="boilerplate-plugin-shortcode__title"><?php echo esc_html( $headline ); ?></h2>
	<?php endif; ?>

	<?php if ( '' !== $intro ) : ?>
		<p class="boilerplate-plugin-shortcode__intro"><?php echo esc_html( $intro ); ?></p>
	<?php endif; ?>

	<?php if ( ! empty( $gallery ) ) : ?>
		<ul class="boilerplate-plugin-shortcode__gallery">
			<?php foreach ( $gallery as $item ) : ?>
				<?php
				$attachment_id = isset( $item['attachment_id'] ) ? absint( $item['attachment_id'] ) : 0;
				$url           = isset( $item['url'] ) && is_string( $item['url'] ) ? $item['url'] : '';
				$caption       = isset( $item['caption'] ) && is_string( $item['caption'] ) ? $item['caption'] : '';
				?>
				<li class="boilerplate-plugin-shortcode__item">
					<figure class="boilerplate-plugin-shortcode__figure">
						<?php if ( $attachment_id > 0 ) : ?>
							<?php
							echo wp_get_attachment_image(
								$attachment_id,
								'medium',
								false,
								array(
									'class' => 'boilerplate-plugin-shortcode__image',
								)
							);
							?>
						<?php elseif ( '' !== $url ) : ?>
							<img
								class="boilerplate-plugin-shortcode__image"
								src="<?php echo esc_url( $url ); ?>"
								alt="<?php echo esc_attr( $caption ); ?>"
								loading="lazy"
							/>
						<?php endif; ?>

						<?php if ( '' !== $caption ) : ?>
							<figcaption class="boilerplate-plugin-shortcode__caption">
								<?php echo esc_html( $caption ); ?>
							</figcaption>
						<?php endif; ?>
					</figure>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
