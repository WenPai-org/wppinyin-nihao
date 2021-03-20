jQuery(document).ready(function($) {
    var url_status = GetQueryString('zhuyin');
    var id = $('.wppy_nihao_status').attr('id');
    var cookie_status = getCookie(id);
    if ( cookie_status == 'off' && url_status != 'off' ){
        window.location.href = '?zhuyin=off';
    }
    if( url_status == 'off' ){
        $('.wppy_nihao_status').attr('checked',false);
    }else{
        $('.wppy_nihao_status').attr('checked',true);
    }
    $('.wppy_nihao_label').on('click',function(){
        var cookie_status = $('.wppy_nihao_status').val();
        if ( url_status == cookie_status ){
            cookie_status = 'off';
        }
        if ( cookie_status == 'off' ){
            setCookie(id, cookie_status, 30);   
            window.location.href = '?zhuyin=off';
             
        }else{
            setCookie(id, '', 0);   
            window.location.href = '?zhuyin=on';                      
        }
    });

});

function GetQueryString(name){
    var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if(r!=null) return unescape(r[2]); return null;
}

function setCookie(cname,cvalue,exdays){
    var d = new Date();
    d.setTime(d.getTime()+(exdays*24*60*60*1000));
    var expires = "expires="+d.toGMTString();
    document.cookie = cname+"="+cvalue+"; "+expires;
}

function getCookie(cname){
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i].trim();
        if (c.indexOf(name)==0) { return c.substring(name.length,c.length); }
    }
    return "";
}
