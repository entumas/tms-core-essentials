<?php
/**
 * Includes -> API -> Get tax field
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! function_exists( 'tcres_tax_field_get' ) ) :

	/**
	 * Retrieve a taxonomy term custom field value for a post's first assigned term
	 */
	function tcres_tax_field_get( array $args = array() ): string {
		return tcres_tax_field_get_value( $args );
	}


	function tcres_tax_field_get_value( array $args = array() ): string {
		$tax = isset( $args['tax'] )
			? (string) $args['tax']
			: '';
		$post_id = isset( $args['post_id'] )
			? (int) $args['post_id']
			: (int) get_the_ID();
		$field = isset( $args['field'] )
			? (string) $args['field']
			: '';
		$field_num = isset( $args['field_num'] )
			? $args['field_num']
			: '';
		$group = isset( $args['group'] )
			? (string) $args['group']
			: '';
		$group_num = isset( $args['group_num'] )
			? $args['group_num']
			: '';
		$before = isset( $args['before'] )
			? (string) $args['before']
			: '';
		$after = isset( $args['after'] )
			? (string) $args['after']
			: '';
		$before_repeat = isset( $args['before_repeat'] )
			? (string) $args['before_repeat']
			: '';
		$after_repeat = isset( $args['after_repeat'] )
			? (string) $args['after_repeat']
			: '';
		$format = isset( $args['format'] )
			? (string) $args['format']
			: '';
		$output        = '';

		if ( $tax === '' || $field === '' || $post_id <= 0 ) return '';

		$terms = get_the_terms( $post_id, $tax );
		if ( ! is_array( $terms ) || empty( $terms ) ) return '';

		$term_id = (int) $terms[0]->term_id;

		list( $before, $after, $before_repeat, $after_repeat ) = tcres_field_format_wrapper_parts(
			$before,
			$after,
			$before_repeat,
			$after_repeat,
			$format
		);

		if ( $group === '' ) :
			if ( $field_num === '' || $field_num === 0 ) :
				$value = get_term_meta( $term_id, $field, true );
				return tcres_field_build_segment( $before, (string) $value, $after, $format );
			endif;

			$field_num_search = 1;
			$entries          = get_term_meta( $term_id, $field, true );
			foreach ( (array) $entries as $value ) :
				if ( ! empty( $value ) && (int) $field_num === -1 ) :
					$output .= tcres_field_build_segment( $before, (string) $value, $after, $format );
				elseif ( ! empty( $value ) && (int) $field_num_search === (int) $field_num ) :
					return tcres_field_build_segment( $before, (string) $value, $after, $format );
				else :
					$field_num_search++;
				endif;
			endforeach;

			return $before_repeat . $output . $after_repeat;
		endif;

		$group_num_search = 1;
		$entries          = get_term_meta( $term_id, $group, true );
		foreach ( (array) $entries as $value ) :
			if ( ! is_array( $value ) ) continue;

			if ( $field_num === '' || $field_num === 0 ) :
				if ( ! empty( $value[ $field ] ) && ( $group_num === '' || $group_num === 0 ) ) :
					return tcres_field_build_segment( $before, (string) $value[ $field ], $after, $format );
				elseif ( ! empty( $value[ $field ] ) && (int) $group_num_search === (int) $group_num ) :
					return tcres_field_build_segment( $before, (string) $value[ $field ], $after, $format );
				endif;

				$group_num_search++;
				continue;
			endif;

			if ( (int) $group_num_search !== (int) $group_num ) :
				$group_num_search++;
				continue;
			endif;

			$field_num_search = 1;
			foreach ( (array) $value as $key => $nested ) :
				if ( $key !== $field || ! is_array( $nested ) ) :
					continue;
				endif;

				foreach ( (array) $nested as $nested_value ) :
					if ( (int) $field_num === -1 && $nested_value ) :
						$output .= tcres_field_build_segment( $before, (string) $nested_value, $after, $format );
					elseif ( (int) $field_num_search === (int) $field_num ) :
						return tcres_field_build_segment( $before, (string) $nested_value, $after, $format );
					else :
						$field_num_search++;
					endif;
				endforeach;

				return $before_repeat . $output . $after_repeat;
			endforeach;

			$group_num_search++;
		endforeach;

		return '';
	}
endif;
