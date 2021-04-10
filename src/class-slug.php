<?php
/**
 * Slug转拼音类文件
 *
 * @package WenPai\PinYin
 */

namespace WenPai\PinYin\Src;

use Overtrue\Pinyin\Pinyin;

if ( !class_exists( Slug::class ) ) {

    /**
     * Slug转拼音类
     *
     * @since 1.0.0
     */
    class Slug {

        /**
         * @since 1.0.0
         * @var string
         */
        private $disable_file_convert;

        /**
         * @since 1.0.0
         * @var int
         */
        private $use_translator_api;

        /**
         * @since 1.0.0
         * @var string
         */
        private $divider;

        /**
         * @since 1.0.0
         * @var int
         */
        private $type;

        /**
         * @since 1.0.0
         * @var int
         */
        private $length;

        /**
         * @since 1.0.0
         * @var string
         */
        private $baidu_app_id;

        /**
         * @since 1.0.0
         * @var string
         */
        private $baidu_api_key;

        public function __construct( array $args ) {
            $default = array(
                'disable_file_convert' => 'off',
                'type'                 => 0,
                'divider'              => '-',
                'length'               => 60,
                'baidu_app_id'         => '',
                'baidu_api_key'        => '',
            );
            $args = wp_parse_args( $args, $default );

            $this->disable_file_convert = $args['disable_file_convert'];
            $this->use_translator_api   = $args['type'];
            $this->divider              = $args['divider'];
            $this->type                 = $args['type'];
            $this->length               = $args['length'];
            $this->baidu_app_id         = $args['baidu_app_id'];
            $this->baidu_api_key        = $args['baidu_api_key'];
        }

        public function register_hook() {
            add_filter( 'wp_unique_post_slug', array( $this, 'unique_post_slug' ), 10, 6 );
            add_filter( 'name_save_pre', array( $this, 'name_save_pre' ), 10, 1 );
            add_filter( 'rest_pre_insert_post', array( $this, 'rest_convert_slug' ), 10, 2 );
            add_filter( 'rest_pre_insert_page', array( $this, 'rest_convert_slug' ), 10, 2 );
            add_filter( 'pre_category_nicename', array( $this, 'pre_category_nicename' ), 10, 1 );
            add_filter( 'wp_insert_term_data', array( $this, 'wp_insert_term_data' ), 10, 3 );
            add_filter( 'wp_update_term_data', array( $this, 'wp_update_term_data' ), 10, 4 );
            add_filter( 'sanitize_file_name', array( $this, 'sanitize_file_name' ), 10, 1 );
        }

        /**
         * 文章别名保存之前，如果没有设置，自动转换为拼音
         *
         * @param $slug
         * @param $post_ID
         * @param $post_status
         * @param $post_type
         * @param $post_parent
         * @param $original_slug
         *
         * @return mixed
         * @since 1.0.0
         */
        public function unique_post_slug( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug ) {
            /** 不处理附件别名 */
            if ( $post_type === 'attachment' ) {
                return $slug;
            }

            /** 还原编码前的别名 */
            $decoded_slug = urldecode( $slug );

            if ( preg_match( '/[\x{4e00}-\x{9fa5}]+/u', $decoded_slug ) ) {
                $slug = urlencode( $this->slug_convert( $decoded_slug ) );
            }

            return $slug;
        }

        /**
         * 文章别名保存之前，如果没有设置，自动转换为拼音
         *
         * @param $slug
         *
         * @return mixed
         * @since 1.0.0
         */
        public function name_save_pre( $slug ) {
            /** 手动编辑时，不自动转换为拼音 */
            if ( $slug && $slug !== '' ) {
                return $slug;
            }

            /** 替换文章标题 */
            return $this->slug_convert( $_POST['post_title'], 'post' );
        }

        /**
         * Rest Api 中，文章别名保存之前，如果没有设置，自动转换为拼音
         *
         * @param $prepared_post
         * @param $request
         *
         * @return mixed
         * @since 1.0.0
         *
         */
        public function rest_convert_slug( $prepared_post, $request ) {
            /** 获取文章标题 */
            $post_title = '';
            $saved_post = null;

            /** 获取已保存文章 */
            if ( isset( $request['id'] ) ) {
                $saved_post = get_post( $request['id'] );
            }

            /** 获取标题 */
            if ( isset( $request['title'] ) ) {
                $post_title = $request['title'];
            } elseif ( isset( $request['id'] ) ) {
                $post_title = $saved_post->post_title;
            }

            /**
             * 1. 已发布状态下，如果设置了 slug，说明编辑了 slug，
             * 1.1 如果 slug 为空，说明删除了 slug 需要重新生成
             * 1.2 如果 slug 不为空，说明手动设置了 slug, 使用设置的 slug, 不自动生成
             */
            if ( $request['status'] === 'publish' ) {
                /** 不处理已保存、且文章已有 slug 的情况，避免编辑时修改掉原有中文 slug */
                if ( $saved_post && $saved_post->post_name === '' ) {
                    if ( ! isset( $request['slug'] ) || empty( $request['slug'] ) || $request['slug'] === '' ) {
                        $prepared_post->post_name = $this->slug_convert( $post_title, 'post' );
                    }
                }
            } else {
                /** 如果上一个状态是已发布，说明执行的是 "切换到草稿" 的操作，这种情况下，不自动转换 slug  */
                if ( ! $saved_post || $saved_post->post_status !== 'publish' ) {
                    /** 2. 其他状态下，如果没有设置 slug, 或 slug 为空，自动生成，如果设置了 slug ,依然不自动生成 */
                    if ( ! isset( $request['slug'] ) || empty( $request['slug'] ) ) {
                        $prepared_post->post_name = $this->slug_convert( $post_title, 'post' );
                    }
                }
            }

            return $prepared_post;
        }

        /**
         * 替换分类标题为拼音
         *
         * @param $slug
         *
         * @return mixed
         * @since 1.0.0
         */
        public function pre_category_nicename( string $slug ): string {
            /** 手动编辑时，不自动转换为拼音 */
            if ( $slug ) {
                return $slug;
            }

            $tag_name = isset( $_POST['tag-name'] ) ? $_POST['tag-name'] : false;

            // 替换文章标题
            if ( $tag_name ) {
                $slug = $this->slug_convert( $_POST['tag-name'], 'term' );
            }

            return $slug;
        }

        /**
         * 添加分类时替换分类标题为拼音
         *
         * @param $data     array 需要保存到数据库中的数据
         * @param $term_id  int 分类项目 ID
         * @param $taxonomy string 分类法名称
         * @param $args     array 用户提交的数据
         *
         * @return array 修改后的需要保存到数据库中的数据
         * @since 1.0.0
         */
        public function wp_insert_term_data( $data, $taxonomy, $args ) {
            /** 手动编辑时，不自动转换为拼音 */
            if ( '' === $args['slug'] ) {
                $data['slug'] = wp_unique_term_slug( $this->slug_convert( $data['name'], 'term' ), (object) $args );
            }

            return $data;
        }

        /**
         * 更新分类时分类标题为拼音
         *
         * @param $data     array 需要保存到数据库中的数据
         * @param $term_id  int 分类项目 ID
         * @param $taxonomy string 分类法名称
         * @param $args     array 用户提交的数据
         *
         * @return array 修改后的需要保存到数据库中的数据
         * @since 1.0.0
         */
        public function wp_update_term_data( $data, $term_id, $taxonomy, $args ) {
            /** 手动编辑时，不自动转换为拼音 */
            if ( '' === $args['slug'] ) {
                $data['slug'] = wp_unique_term_slug( $this->slug_convert( $data['name'], 'term' ), (object) $args );
            }

            return $data;
        }

        /**
         * 替换文件名称为拼音
         *
         * @param string $filename
         *
         * @return string
         * @since 1.0.0
         */
        public function sanitize_file_name( string $filename ): string {
            $disable_file_convert = $this->disable_file_convert;
            if ( $disable_file_convert === 'on' ) {
                return $filename;
            }

            /** 手动编辑时，不自动转换为拼音 */
            $parts = explode( '.', $filename );

            /** 没有后缀时，直接返回文件名，不用再加 . 和后缀 */
            if ( count( $parts ) <= 1 ) {
                if ( preg_match( '/[\x{4e00}-\x{9fa5}]+/u', $filename ) ) {
                    return $this->slug_convert( $filename, 'file' );
                }

                return $filename;
            }

            $filename  = array_shift( $parts );
            $extension = array_pop( $parts );

            foreach ( (array) $parts as $part ) {
                $filename .= '.' . $part;
            }

            if ( preg_match( '/[\x{4e00}-\x{9fa5}]+/u', $filename ) ) {
                $filename = $this->slug_convert( $filename, 'file' );
            }

            $filename .= '.' . $extension;

            return $filename;
        }

        /**
         * 转换拼音的通用功能
         *
         * @param $name
         * @param $type
         *
         * @return string 转换后的拼音
         * @since 1.0.0
         */
        public function slug_convert( string $name, string $type = 'post' ) {
            $use_translator_api = $this->use_translator_api;

            if ( $use_translator_api === 2 ) {
                $slug = $this->slug_translator( $name );
            } else {
                $slug = $this->slug_pinyin_convert( $name );
            }

            if ( ! $slug ) {
                return $name;
            }

            return sanitize_title( $slug );
        }

        /**
         * 拼音转换方式
         *
         * @param $name
         *
         * @return bool|string
         * @since 1.0.0
         */
        public function slug_pinyin_convert( $name ) {
            $divider = $this->divider;
            $type    = $this->type;
            $length  = $this->length;

            $pinyin = new Pinyin();
            if ( $type === 0 ) {
                $slug = $pinyin->permalink( $name, $divider, PINYIN_KEEP_ENGLISH );
            } else {
                $slug = $pinyin->abbr( $name, $divider, PINYIN_KEEP_ENGLISH );
            }
            $slug = $this->trim_slug( $slug, $length, $divider );

            return strtolower( $slug );
        }

        /**
         * 百度翻译转换方式
         *
         * @param $name
         *
         * @return string
         * @since 1.0.0
         */
        public function slug_translator( $name ) {
            $length   = $this->length;
            $api_url  = 'https://fanyi-api.baidu.com/api/trans/vip/translate';
            $app_id   = $this->baidu_app_id;
            $app_key  = $this->baidu_api_key;
            $app_salt = rand( 10000, 99999 );

            if ( ! $app_id || ! $app_key ) {
                $result = false;
            } else {
                /** 签名 */
                $str  = $app_id . $name . $app_salt . $app_key;
                $sign = md5( $str );

                /** 请求数据 */
                $args = array(
                    'q'     => $name,
                    'from'  => 'auto',
                    'to'    => 'en',
                    'appid' => $app_id,
                    'salt'  => $app_salt,
                    'sign'  => $sign,
                );

                $parm = array(
                    'method'      => 'POST',
                    'timeout'     => 45,
                    'redirection' => 5,
                    'httpversion' => '1.0',
                    'blocking'    => true,
                    'headers'     => [],
                    'body'        => $args,
                    'cookies'     => [],
                );
                /** 发送请求 */
                $response = wp_remote_post( $api_url, $parm );

                /** 获取返回数据 */
                if ( is_wp_error( $response ) ) {
                    $result = false;
                } else {
                    $data = json_decode( wp_remote_retrieve_body( $response ) );

                    if ( ! isset( $data->error_code ) ) {
                        $divider = $this->divider;

                        $result = $data->trans_result[0]->dst;
                        $result = str_replace( ' ', $divider, $result );
                        $result = $this->trim_slug( $result, $length );
                    } else {
                        $result = false;
                    }
                }
            }

            if ( ! $result ) {
                $result = $this->slug_pinyin_convert( $name );
            }

            return $result;
        }

        /**
         * 裁剪文本
         *
         * @param string $input
         * @param int $length
         * @param string $divider
         * @param bool $strip_html
         *
         * @return bool|string
         * @since 1.0.0
         */
        public function trim_slug( $input, $length, $divider = '-', $strip_html = true ) {
            if ( $strip_html ) {
                $input = strip_tags( $input );
            }

            /** 如果字符串已经比裁剪程度短了，无需再裁剪，直接返回 */
            if ( ! $length || '' === $length || strlen( $input ) <= $length ) {
                return $input;
            }

            $trimmed_text = substr( $input, 0, $length );

            /** 查找最后截取字符串的最后一个分隔符位置 */
            if ( '' !== $divider ) {
                $last_space = strrpos( substr( $input, 0, $length ), $divider );

                if ( $last_space ) {
                    $trimmed_text = substr( $input, 0, $last_space );
                }
            }

            return $trimmed_text;
        }

    }

}
