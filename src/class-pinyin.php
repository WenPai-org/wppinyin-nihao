<?php

namespace WenPai;

use Overtrue\Pinyin\Pinyin as PinYinClass;
use PHPHtmlParser\Dom;
use Wenpai\Framework\Setting;
use WenPai\Slug;
// use Wenpai\Framework\Widget;

if ( !class_exists( 'PinYin' ) ) {

    require_once WPPY_NIHAO_PLUGIN_DIR_PATH . 'wenpai-framework/framework/class-setting.php';
    require_once WPPY_NIHAO_PLUGIN_DIR_PATH . 'wenpai-framework/framework/class-widget.php';
    
    require_once (WPPY_NIHAO_PLUGIN_DIR_PATH . 'vendor/autoload.php' );
    require_once( WPPY_NIHAO_PLUGIN_DIR_PATH . 'src/func.php' );
    
    define( 'WENPAIPINYIN_NIHAO_PREFIX' , 'wppinyin_nihao' );
    define( 'WPPY_NIHAO_PLUGIN_URL' , plugins_url( '' , dirname( __FILE__  ) ) );

    class PinYin {

        public static $global_autoload_py_config;

        public static $auto_zhuyin_ele = 'h1,h2,h3,h4,h5,p,li';

        /**
         * 常量获取
         * 
         * @since 1.0.0
         */
        public function __construct( ){           

            self::$global_autoload_py_config = wppy_get_option( 'wppinyin_nihao_one', 'global_autoload_py' );
            
        }



        public function register_hook()    {

            add_action( 'wp_enqueue_scripts' , array( $this , 'wppinyin_single_js') , 11 );
            add_filter( 'the_content' , array( $this , 'wppinyin_zhuyin' ) );
            add_action( 'plugins_loaded' , array( $this , 'wppinyin_plugin_loaded' ) );
            add_action( 'admin_enqueue_scripts' , array($this , 'wppinyin_admin_scripts') );
            add_action( 'wp_loaded' , array( $this , 'wppinyin_wp_loaded' ) );
            add_shortcode( 'wppinyin_shortcode' , array( $this ,'wppinyin_shortcode') );

        }

        /**
         * shortcode 短代码功能
         * 
         * @since 1.0.0
         */
        public static function wppinyin_shortcode(){
            if ( ! self::check_zhuyin_status() )    return;
            $name = 'wppy_nihao_status_' . get_the_ID();
            printf('<label for="%s" class="wppy_nihao_label">
            <input type="checkbox" class="wppy_nihao_status" id="%s" name="%s" value="on">' . __('开/关注音','wppy_nihao') . '
            </label>',$name,$name,$name);
        }
        
        /**
         * 前端单页面js引用
         * 
         * @since 1.0.0
         */
        public static function wppinyin_single_js(){
            // if( ! is_single() ) return ;
            wp_register_script('wppy_nihao', WPPY_NIHAO_PLUGIN_URL . '/assets/js/wppinyin-nihao.js' , array(), WPPY_VERSION , true);
            wp_enqueue_script('wppy_nihao');
        }

        /**
         * 检测该类型的全局开关状态
         * 
         * @since 1.0.0
         * 
         * @return boolean true/false
         */
        public static function check_zhuyin_status() {
            global $post;
            if ( '' == $post )    return false;
            $post_type = $post->post_type;
            $post_type_options = self::$global_autoload_py_config;
            // var_dump($post_type_options[$post_type]);
            if( isset($post_type_options[$post_type]) && '' !== $post_type_options[$post_type] ){
                return true;
            }
            return false;
        }

        /**
         * 文章内容中自动增加注音
         * 
         * @since 1.0.0
         * @param $content_original
         * @return string
         */
        public static function wppinyin_zhuyin( string $content_original) : string {
            if( isset($_GET['zhuyin']) && $_GET['zhuyin'] == 'off' ){
                return $content_original;
            }else if( ! self::check_zhuyin_status() ){
                return $content_original;
            }
            // 小内存型
            // $pinyin = new Pinyin(); // 默认
            // 内存型
            $pinyin = new PinYinClass('\\Overtrue\\Pinyin\\MemoryFileDictLoader');
            // I/O型
            // $pinyin = new Pinyin('\\Overtrue\\Pinyin\\GeneratorFileDictLoader');

            $html_tags = self::$auto_zhuyin_ele;
            $dom = new Dom;
            $dom->loadStr($content_original);
            
            foreach( explode(',' , $html_tags) as $tag ){
                foreach ($dom->find($tag) as $line) {
                    $ruby_line = '<ruby>';
                    foreach (my_mb_str_split($line->text) as $char) {
                        if (preg_match("/[\x{4e00}-\x{9fa5}]/u", $char)) {
                            $pinyin_str = $pinyin->convert($char, PINYIN_TONE);
                            $pinyin_str = isset($pinyin_str[0]) ? $pinyin_str[0] : '';
                            $ruby_line .= "{$char}<rp>(</rp><rt>{$pinyin_str}</rt><rp>)</rp>";
                        } else {
                            $ruby_line .= '</ruby>';
                            $ruby_line .= $char;
                            $ruby_line .= '<ruby>';
                        }
                    }
                    $ruby_line .= '</ruby>';
                    $line->firstChild()->setText($ruby_line);
                }
            }

            return $dom;
        }




        /**
         * 文章内容中自动增加注音
         * 
         * @since 1.0.0
         */
        public static function wppinyin_plugin_loaded() {

            /**
             * 加载语言
             */  
            load_plugin_textdomain( 'wppy_nihao', false, basename( dirname( __FILE__ ) ) . '/languages' );
            
            /**
             * 加载slug转换主要功能
             */        
            require_once('class-slug.php');
            $PinYinSlug = new Slug(); 
            $PinYinSlug->register_hook();

        
        }


        /**
         * 加载后台JS
         * 
         * @since 1.0.0
         * 
         * @param string $hook
         */
        public static function wppinyin_admin_scripts( string $hook ){
            if( 'post.php' === $hook  ){
                /**
                 * 加载手动注音功能
                 */                
                $deps = array(
                    'wp-element', 'wp-editor', 'wp-i18n',
                    'wp-rich-text', 'wp-compose','wp-components',
                );

                /**
                 * 加载手动注音功能
                 */
                wp_enqueue_script( 'wppy_gutenberg', WPPY_NIHAO_PLUGIN_URL . '/assets/js/wppy-gutenberg.js' , $deps , WPPY_VERSION, true );
            
            }else if ( 'settings_page_wenpai_pinyin_nihao' === $hook ) {
                wp_enqueue_script('wppinyin_nihao_slug_admin_script', WPPY_NIHAO_PLUGIN_URL . '/assets/js/scripts-option.js' , 'jquery' , WPPY_VERSION);
            }
        }

        


        /**
         * 文派框架生成菜单，配置页等
         * 
         * @since 1.0.0
         */
        public static function wppinyin_wp_loaded (){

            /**
             * 创建设置页
             */
            Setting::create_options( WENPAIPINYIN_NIHAO_PREFIX, array(
                'menu_title' => __('文派拼音生成器','wppy_nihao'),
                'menu_slug' => 'wenpai_pinyin_nihao',
            ) );

            /**
             * 创建设置组
             */
            Setting::create_section( WENPAIPINYIN_NIHAO_PREFIX, array(
                array(
                    'id'     => 'one',
                    'title'  => __('选项卡一','wppy_nihao'),
                    'fields' => array(
                        array(
                            'name'  => 'global_autoload_py',
                            'label' => __('加载时增加注音','wppy_nihao'),
                            'desc'  => __('开启后全站所有文章的内容均自动在加载时增加注音，同时需能勾选要生效的文章类型','wppy_nihao'),
                            'type'  => 'checkbox',
                            'default' => array(
                                'one' => 'one',
                                'four' => 'four'
                            ),
                            'options' => wppy_get_registered_post_types(),                
                        ),

                    ),
                ), 
                
                array(
                    'id'     => 'two',
                    'title'  => __('选项卡二','wppy_nihao'),
                    'fields' => array(
                        array(
                            'name'    => 'type',
                            'label'   => __('转换方式','wppy_nihao'),
                            'desc'    => __('全拼、首字母或或百度翻译 (首字母模式下，英文也会取第一个字母)','wppy_nihao'),
                            'type'    => 'select',
                            'default' => 0,
                            'options' => array(
                                0 => '拼音全拼',
                                1 => '拼音首字母',
                                2 => '百度翻译',
                            ),
                        ),

                        array(
                            'name'              => 'divider',
                            'label'             => __('拼音分隔分隔符','wppy_nihao'),
                            'desc'              => __('可以是：_ 或 - 或 . &nbsp; 默认为 “-”，如过不需要分隔符，请留空','wppy_nihao'),
                            'placeholder'       => __('','wppy_nihao'),
                            'default'           => '',
                            'type'              => 'text',
                            'sanitize_callback' => 'sanitize_text_field',
                        ),

                        array(
                            'name'              => 'length',
                            'label'             => __('别名长度限制','wppy_nihao'),
                            'desc'              => __('超过设置的长度后，会按照指定的长度截断转换后的拼音字符串。为保持拼音的完整性，如果设置了分隔符，会在最后一个分隔符后截断','wppy_nihao'),
                            'type'              => 'text',
                            'default'           => '60',
                            'sanitize_callback' => 'sanitize_text_field',
                        ),

                        array(
                            'name'              => 'baidu_app_id',
                            'label'             => __('百度翻译 APP ID','wppy_nihao'),
                            'desc'              => __('请在百度翻译开放平台获取：http://api.fanyi.baidu.com/api/trans/product/index','wppy_nihao'),
                            'type'              => 'text',
                            'sanitize_callback' => 'sanitize_text_field',
                        ),

                        array(
                            'name'              => 'baidu_api_key',
                            'label'             => __('百度翻译密钥','wppy_nihao'),
                            'desc'              => __('请在百度翻译开放平台获取：http://api.fanyi.baidu.com/api/trans/product/index','wppy_nihao'),
                            'type'              => 'text',
                            'sanitize_callback' => 'sanitize_text_field',
                        ),

                        array(
                            'name'              => 'disable_file_convert',
                            'label'             => __('禁用文件名转换','wppy_nihao'),
                            'desc'              => __('不要自动转换文件名','wppy_nihao'),
                            'type'              => 'switcher',
                            'sanitize_callback' => 'sanitize_text_field',
                        ),
                    ),
                ),
            ) );





        }

















    }

}
