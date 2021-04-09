<?php
/**
 * 公共函数
 *
 * @package WenPai\PinYin
 */

if ( ! function_exists( 'wppy_get_option' ) ) {
    function wppy_get_option( string $option, string $section, $default = '' ) {
        $options = is_multisite() ? get_site_option( WPPY_PREFIX . "_{$section}" ) : get_option( WPPY_PREFIX . "_{$section}" );

        if ( isset( $options[ $option ] ) ) {
            return $options[ $option ];
        }

        return $default;
    }
}

if( ! function_exists( 'wppy_get_registered_post_types' ) ) {
    function wppy_get_registered_post_types() {
        $core_label = array();

        $args = array( 'public' => true );
        $post_types = get_post_types( $args , 'objects' );
        foreach ( $post_types as $post_type ) {
            $core_label[$post_type->name] = $post_type->label;
        }

        return $core_label;
    }
}
