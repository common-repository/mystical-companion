<?php
	/** Helper Functions **/

	/** Order List **/
	function mystical_companion_order_list() {
		return array(
			'ASC' => esc_html( 'Ascending', 'mystical-companion' ),
			'DESC' => esc_html( 'descending', 'mystical-companion' ),
		);
	}

	/** Orderby List **/
	function mystical_companion_orderby_list() {
		return array(
			'none' => esc_html( 'None', 'mystical-companion' ),
			'date' => esc_html( 'Date', 'mystical-companion' ),
			'ID' => esc_html( 'ID', 'mystical-companion' ),
			'author' => esc_html( 'Author', 'mystical-companion' ),
			'title' => esc_html( 'Title', 'mystical-companion' ),
			'rand' => esc_html( 'Random', 'mystical-companion' ),
		);
	}

	/** Category Lists **/
	function mystical_companion_category_lists() {
		$categories = get_categories();
		$category_list = array();

		$category_list['all'] = esc_html__( 'All Category', 'mystical-companion' );

		foreach( $categories as $category ) {
			$category_list[$category->slug] = $category->name;
		}

		return $category_list;
	}