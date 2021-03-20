<?php


/**
 * 返回所有文章页面等类型
 * @return array
 */
if( ! function_exists('wppy_get_registered_post_types')):
    function wppy_get_registered_post_types() {
        $post_types = get_post_types( array( 'show_in_rest' => true ), 'objects' );

        foreach ( $post_types as $post_type ) {
            // var_dump($post_type->name);

            $core_label[$post_type->name] = $post_type->name; 
        }
        return $core_label;
    }
endif;

/**
 * 获取设置的值
 *
 * @param string $section 选项所属的设置区域
 * @param string $option  选项名称
 * @param string $default 找不到选项值时的默认值
 *
 * @return mixed
 */
if( ! function_exists('wppy_get_option') ):
    function wppy_get_option($section, $option, $default = ''){
        $options = get_option($section);
        if (isset($options[ $option ])) {
            return $options[ $option ];
        }
        return $default;
    }
endif;


if (!function_exists('my_mb_str_split')) {
    function my_mb_str_split($str, $split_length = 1, $charset = "UTF-8") {
        if (func_num_args() == 1) {
            return preg_split('/(?<!^)(?!$)/u', $str);
        }
        if ($split_length < 1) {
            return false;
        }
        $len = mb_strlen($str, $charset);
        $arr = array();
        for ($i = 0; $i < $len; $i += $split_length) {
            $s     = mb_substr($str, $i, $split_length, $charset);
            $arr[] = $s;
        }

        return $arr;
    }
}
