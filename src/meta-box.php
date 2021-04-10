<?php
/**
 * Meta Box注册文件
 *
 * @package WenPai\PinYin
 */

namespace WenPai\PinYin\Src;

use WenPai\Framework\Meta_Box;

Meta_Box::create( WPPY_PREFIX, array(
    'id'          => 'zhuyin',
    'title'       => '自动注音',
    'context'     => 'side',
    'screens'     => array( 'post', 'page' ),
    'fields'      => array(
        array(
            'name'    => 'zhuyin_status',
            'type'    => 'radio',
            'desc'    => '文章加载时是否自动添加注音？',
            'default' => 'on',
            'options' => array(
                'on' => '开启',
                'off'  => '关闭',
            ),
        ),
    ),
) );
