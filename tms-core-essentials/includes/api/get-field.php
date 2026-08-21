<?php
/**
 * Includes -> API -> Get field
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! function_exists( 'tcres_field_get' ) ) :

	/**
	 * Retrieve a post custom field value (supports repeatable fields and groups)
	 */
	function tcres_field_get( array $args = array() ): string {
		$format = isset( $args['format'] )
			? (string) $args['format']
			: '';

		return tcres_field_apply_output_format( tcres_field_get_value( $args ), $format );
	}


	function tcres_field_get_value( array $args = array() ): string {
		$post_id = isset( $args['post_id'] )
			? (int) $args['post_id']
			: (int) get_the_ID();
		if ( $post_id <= 0 ) return '';

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
		$output = '';

		if ( $field === '' ) return '';

		if ( $group === '' ) :
			if ( $field_num === '' || $field_num === 0 ) :
				$value = get_post_meta( $post_id, $field, true );
				return $before . tcres_field_apply_value_format( (string) $value ) . $after;
			endif;

			$field_num_search = 1;
			$entries          = get_post_meta( $post_id, $field, true );
			foreach ( (array) $entries as $value ) :
				if ( ! empty( $value ) && (int) $field_num === -1 ) :
					$output .= $before . tcres_field_apply_value_format( (string) $value ) . $after;
				elseif ( ! empty( $value ) && (int) $field_num_search === (int) $field_num ) :
					return $before . tcres_field_apply_value_format( (string) $value ) . $after;
				else :
					$field_num_search++;
				endif;
			endforeach;

			return $before_repeat . $output . $after_repeat;
		endif;

		$group_num_search = 1;
		$entries          = get_post_meta( $post_id, $group, true );
		foreach ( (array) $entries as $value ) :
			if ( ! is_array( $value ) ) continue;

			if ( $field_num === '' || $field_num === 0 ) :
				if ( ! empty( $value[ $field ] ) && ( $group_num === '' || $group_num === 0 ) ) :
					return $before . tcres_field_apply_value_format( (string) $value[ $field ] ) . $after;
				elseif ( ! empty( $value[ $field ] ) && (int) $group_num_search === (int) $group_num ) :
					return $before . tcres_field_apply_value_format( (string) $value[ $field ] ) . $after;
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
				if ( $key !== $field || ! is_array( $nested ) ) continue;

				foreach ( (array) $nested as $nested_value ) :
					if ( (int) $field_num === -1 && $nested_value ) :
						$output .= $before . tcres_field_apply_value_format( (string) $nested_value ) . $after;
					elseif ( (int) $field_num_search === (int) $field_num ) :
						return $before . tcres_field_apply_value_format( (string) $nested_value ) . $after;
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
