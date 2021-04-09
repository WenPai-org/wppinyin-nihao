jQuery(document).ready(function($) {

    function wppy_nihao_slug_check_select() {
        var check_el = $('select[name="wppinyin_nihao_two[translator_api]"]').val(),
            check_el2 = $('select[name="wppinyin_nihao_two[type]"]').val(),
            condition_el = $(
                'input[name="wppinyin_nihao_two[baidu_app_id]"], input[name="wppinyin_nihao_two[baidu_api_key]"]').
                parent().
                parent();

        // 根据是否需要发票显示
        if (parseInt(check_el) === 0 || parseInt(check_el2) !== 2) {
            condition_el.hide();
        } else {
            condition_el.show();
        }
    }

    wppy_nihao_slug_check_select();

    $('select[name="wppinyin_nihao_two[translator_api]"]').change(function() {
        wppy_nihao_slug_check_select();
    });

    $('select[name="wppinyin_nihao_two[type]"]').change(function() {
        wppy_nihao_slug_check_select();
    });

});