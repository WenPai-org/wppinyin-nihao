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
            'default' => 'auto',
            'options' => array(
                'on' => '开启',
                'off'  => '关闭',
                'auto'  => '随全局设置',
            ),
        ),
    ),
) );
