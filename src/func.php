<?php


/**
 * 返回所有文章页面等类型
 * 
 * @since 1.0.0
 *  
 * @return array
 */
if( ! function_exists('wppy_get_registered_post_types')){
    function wppy_get_registered_post_types() {
        $args = array( 'public' => true );
        $post_types = get_post_types( $args , 'objects' );
        foreach ( $post_types as $post_type ) {
            $core_label[$post_type->name] = $post_type->name; 
        }
        return $core_label;
    }
}

/**
 * 获取设置的值
 * 
 * @since 1.0.0
 *
 * @param string $section 选项所属的设置区域
 * @param string $option  选项名称
 * @param string $default 找不到选项值时的默认值
 *
 * @return mixed
 */
if( ! function_exists('wppy_get_option') ){
    function wppy_get_option($section, $option, $default = ''){
        $options = get_option($section);
        if (isset($options[ $option ])) {
            return $options[ $option ];
        }
        return $default;
    }
}

/**
 * 中文截取
 * 
 * @since 1.0.0
 * 
 * @param string $str 需要截取的字符串
 * @param int $split_length 截取的长度
 * @param $charset 字符集
 * 
 * @return array
 */
if (!function_exists('my_mb_str_split')) {
    function my_mb_str_split( string $str, int $split_length = 1, $charset = "UTF-8") : array  {
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
