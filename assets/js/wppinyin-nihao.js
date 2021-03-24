jQuery(document).ready(function($) {
    var url_status = GetQueryString('zhuyin');
    var id = $('.wppy_nihao_status').attr('id');
    var cookie_status = getCookie(id);

    if ( cookie_status == '' )
        cookie_status = 'on';

    if ( url_status == '' || url_status == null )
        url_status = 'on';

    // alert( cookie_status +'1111111111'+ url_status);
    if ( cookie_status == 'off' && url_status != 'off' ){
        window.location.href = '?zhuyin=off';
        return false;
    }

    if( url_status == 'off' ){
        $('.wppy_nihao_status').attr('checked',false);
    }else{
        $('.wppy_nihao_status').attr('checked',true);
    }
    setCookie(id, cookie_status , 30);   

    $('.wppy_nihao_label').on('click',function(){
        if( url_status == 'off' && cookie_status == 'off' ){
            // alert('我要开启1111');
            setCookie(id, 'on', 30);   
            window.location.href = '?';
            return false;
        }else if( url_status == 'on' && cookie_status == 'off' ){
            // alert('我要开启2222');
            setCookie(id, 'on', 30);   
            window.location.href = '?';
            return false;
        }else if (url_status == 'off' && cookie_status == 'on' ){
            // alert('我要关闭3333');
            setCookie(id, 'off', 30);   
            window.location.href = '?zhuyin=off';
            return false;
        }else if (url_status == 'on' && cookie_status == 'on' ){
            // alert('我要关闭444');
            setCookie(id, 'off', 30);   
            window.location.href = '?zhuyin=off';
            return false;
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
