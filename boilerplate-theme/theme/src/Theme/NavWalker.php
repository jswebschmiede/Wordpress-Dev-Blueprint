<?php

declare( strict_types=1 );

namespace SmartMedia24\BoilerplateTheme\Theme;

defined( 'ABSPATH' ) || exit;

/**
 * Custom navigation walker for the primary menu.
 *
 * Extends Walker_Nav_Menu to output BEM-structured HTML
 * compatible with the f-header__ CSS component system.
 *
 * @extends \Walker_Nav_Menu
 */
class NavWalker extends \Walker_Nav_Menu {
	/**
	 * Stores the parent item ID for the next submenu `<ul>` rendering.
	 *
	 * @var int|null
	 */
	private ?int $pending_dropdown_id = null;

	/**
	 * Starts the list before the elements are added.
	 *
	 * @param string    $output Passed by reference. Used to append additional content.
	 * @param int       $depth  Depth of menu item. Used for padding.
	 * @param \stdClass $args   An object of wp_nav_menu() arguments.
	 * @return void
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ): void {
		$indent      = str_repeat( "\t", $depth );
		$dropdown_id = '';
		if ( null !== $this->pending_dropdown_id ) {
			$dropdown_id               = ' id="dropdown-menu-' . esc_attr( (string) $this->pending_dropdown_id ) . '"';
			$this->pending_dropdown_id = null;
		}

		$output .= "\n{$indent}<ul role=\"menu\" class=\"f-header__dropdown\"{$dropdown_id}>\n";
	}

	/**
	 * Starts the element output.
	 *
	 * @param string    $output Passed by reference. Used to append additional content.
	 * @param \WP_Post  $item   Menu item data object.
	 * @param int       $depth  Depth of menu item. Used for padding.
	 * @param \stdClass $args   An object of wp_nav_menu() arguments.
	 * @param int       $id     Current item ID.
	 * @return void
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ): void {
		$indent = $depth ? str_repeat( "\t", $depth ) : '';

		if ( 0 === strcasecmp( $item->attr_title, 'divider' ) && 1 === $depth ) {
			$output .= $indent . '<li role="presentation" class="divider">';
			return;
		}

		if ( 0 === strcasecmp( $item->title, 'divider' ) && 1 === $depth ) {
			$output .= $indent . '<li role="presentation" class="divider">';
			return;
		}

		if ( 0 === strcasecmp( $item->attr_title, 'dropdown-header' ) && 1 === $depth ) {
			$output .= $indent . '<li role="presentation" class="dropdown-header">' . esc_html( $item->title );
			return;
		}

		if ( 0 === strcasecmp( $item->attr_title, 'disabled' ) ) {
			$output .= $indent . '<li role="presentation" class="disabled"><a href="#">' . esc_html( $item->title ) . '</a>';
			return;
		}

		$classes   = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;
		$classes[] = 'f-header__item';

		if ( $args->has_children ) {
			$classes[] = 'menu-item-has-children';
		}

		$class_names = implode( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
		$class_attr  = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

		$item_id    = apply_filters( 'nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args );
		$id_attr    = $item_id ? ' id="' . esc_attr( $item_id ) . '"' : '';
		$is_current = in_array( 'current-menu-item', $classes, true );

		$output .= $indent . '<li itemscope="itemscope" itemtype="https://www.schema.org/SiteNavigationElement"' . $id_attr . $class_attr . '>';

		$is_top_level_dropdown = $args->has_children && 0 === $depth;

		$atts           = array();
		$atts['target'] = ! empty( $item->target ) ? $item->target : '';
		$atts['rel']    = ! empty( $item->xfn ) ? $item->xfn : '';
		$atts['href']   = ! empty( $item->url ) ? $item->url : '';

		if ( $is_current ) {
			$atts['aria-current'] = 'page';
		}

		$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args );

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( '' !== $value ) {
				$value       = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
				$attributes .= ' ' . $attr . '="' . $value . '"';
			}
		}

		$item_output = $args->before;

		if ( $is_top_level_dropdown ) {
			$this->pending_dropdown_id = (int) $item->ID;

			$button_classes = 'text-inherit f-header__dropdown-control js-f-header__dropdown-control';
			$aria_label     = sprintf(
				/* translators: %s is the menu item title. */
				__( 'Untermenü für %s öffnen', 'boilerplate-theme' ),
				wp_strip_all_tags( (string) $item->title )
			);

			$item_output .= '<button type="button" class="' . esc_attr( $button_classes ) . '"';
			$item_output .= ' aria-expanded="false"';
			$item_output .= ' aria-label="' . esc_attr( $aria_label ) . '"';
			$item_output .= ' aria-controls="dropdown-menu-' . esc_attr( (string) $item->ID ) . '"';
			if ( $is_current ) {
				$item_output .= ' aria-current="page"';
			}
			$item_output .= '>';

			$item_output .= '<a class="f-header__link"' . $attributes . '>';
			$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
			$item_output .= '</a>';

			$item_output .= $this->get_dropdown_icon();
			$item_output .= '</button>';
		} else {
			if ( $depth > 0 ) {
				$link_class = 'f-header__dropdown-link';
			} else {
				$link_class = 'f-header__link';
			}

			$item_output .= '<a class="' . esc_attr( $link_class ) . '"' . $attributes . '>';
			$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
			$item_output .= '</a>';
		}

		$item_output .= $args->after;

		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}

	/**
	 * Traverses elements to create list from elements.
	 *
	 * @param object $element           Data object.
	 * @param array  $children_elements List of elements to continue traversing.
	 * @param int    $max_depth         Max depth to traverse.
	 * @param int    $depth             Depth of current element.
	 * @param array  $args              An array of arguments.
	 * @param string $output            Passed by reference.
	 * @return void
	 */
	public function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ): void {
		if ( ! $element ) {
			return;
		}

		$id_field = $this->db_fields['id'];

		if ( is_object( $args[0] ) ) {
			$args[0]->has_children = ! empty( $children_elements[ $element->$id_field ] );
		}

		parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
	}

	/**
	 * Menu fallback output for admins when no menu is assigned.
	 *
	 * @param array<string, mixed> $args Passed from the wp_nav_menu() function.
	 * @return void
	 */
	public static function fallback( array $args ): void {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}

		$container       = $args['container'];
		$container_id    = $args['container_id'];
		$container_class = $args['container_class'];
		$menu_class      = $args['menu_class'];
		$menu_id         = $args['menu_id'];

		if ( $container ) {
			echo '<' . esc_attr( $container );
			if ( $container_id ) {
				echo ' id="' . esc_attr( $container_id ) . '"';
			}
			if ( $container_class ) {
				echo ' class="' . esc_attr( $container_class ) . '"';
			}
			echo '>';
		}

		echo '<ul';
		if ( $menu_id ) {
			echo ' id="' . esc_attr( $menu_id ) . '"';
		}
		if ( $menu_class ) {
			echo ' class="' . esc_attr( $menu_class ) . '"';
		}
		echo '>';
		echo '<li><a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '">'
			. esc_html__( 'Menü hinzufügen', 'boilerplate-theme' )
			. '</a></li>';
		echo '</ul>';

		if ( $container ) {
			echo '</' . esc_attr( $container ) . '>';
		}
	}

	/**
	 * Returns the SVG dropdown indicator icon markup.
	 *
	 * @return string SVG icon HTML with the f-header__dropdown-icon class.
	 */
	private function get_dropdown_icon(): string {
		return '<svg class="f-header__dropdown-icon" aria-hidden="true" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">'
			. '<polyline points="1 4 6 9 11 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>'
			. '</svg>';
	}
}
