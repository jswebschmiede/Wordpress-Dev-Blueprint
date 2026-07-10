<?php

declare( strict_types=1 );

/**
 * Template for the example block.
 *
 * @var array<string, mixed> $attributes Block attributes.
 *
 * @package BoilerplateTheme
 */

defined( 'ABSPATH' ) || exit;

$title       = isset( $attributes['title'] ) && is_scalar( $attributes['title'] ) ? (string) $attributes['title'] : '';
$description = isset( $attributes['description'] ) && is_scalar( $attributes['description'] ) ? (string) $attributes['description'] : '';
$url         = isset( $attributes['url'] ) && is_scalar( $attributes['url'] ) ? trim( (string) $attributes['url'] ) : '';
$class_name  = isset( $attributes['className'] ) && is_scalar( $attributes['className'] ) ? (string) $attributes['className'] : '';

$wrapper_classes = array( 'example-block not-prose' );

if ( '' !== $class_name ) {
	$wrapper_classes[] = $class_name;
}

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => implode( ' ', $wrapper_classes ),
	)
);
?>

<section <?php echo wp_kses_data( $wrapper_attributes ); ?>>
	<div class="example-block__inner">
		<?php if ( '' !== $title ) : ?>
			<h2 class="example-block__title"><?php echo esc_html( $title ); ?></h2>
		<?php endif; ?>

		<?php if ( '' !== $description ) : ?>
			<p class="example-block__description"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>

		<?php if ( '' !== $url ) : ?>
			<a class="example-block__link" href="<?php echo esc_url( $url ); ?>">
				<?php echo esc_html__( 'Read more', 'boilerplate-theme' ); ?>
			</a>
		<?php endif; ?>
	</div>
</section>
