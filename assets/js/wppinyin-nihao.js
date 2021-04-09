jQuery(document).ready(function($) {
    var url_status = GetQueryString('zhuyin');
    var id = $('#wppy_zhuyin_submit').attr('data-id');
    var article_status = $('#wppy_zhuyin_submit').attr('data-article-status');
    var cookie_status = cookie_tmp = getCookie(id);

    if( article_status == 'off' && ( url_status == '' || url_status == null ) 
    || ( article_status == 'off' && url_status == 'off' )
    || ( cookie_status == 'off' && url_status == 'off' && article_status == 'on' )
    ){
        $('#wppy_zhuyin_submit').attr('value','Open');
    }else{
        $('#wppy_zhuyin_submit').attr('value','Close');
    }

    $('#wppy_zhuyin_submit').on('click',function(){
        if ( cookie_status == '' && ( url_status == '' || url_status == null ) ){
            window.location.href = '?zhuyin=on';
            setCookie(id, 'on', 30);   
            return false;
        }else if( url_status == 'off' && cookie_status == 'off' ){
            setCookie(id, 'on', 30);   
            window.location.href = '?zhuyin=on';
            return false;
        }else if( url_status == 'on' && cookie_status == 'off' ){
            setCookie(id, 'on', 30);   
            window.location.href = '?zhuyin=on';
            return false;
        }else if (url_status == 'off' && cookie_status == 'on' ){
            setCookie(id, 'off', 30);   
            window.location.href = '?zhuyin=off';
            return false;
        }else if (url_status == 'on' && cookie_status == 'on' ){
            setCookie(id, 'off', 30);   
            window.location.href = '?zhuyin=off';
            return false;
        }else if( cookie_status == 'on' && ( url_status == '' || url_status == null ) ){
            setCookie(id, 'off', 30);   
            window.location.href = '?zhuyin=off';
            return false;            

        }
        
    });

    if ( cookie_status == '' && ( url_status == '' || url_status == null ) ){
        return false;
    }else if ( cookie_status == '' && url_status == 'on' ){
        window.location.href = '?zhuyin=on';
        return false;
    }else if( cookie_status == 'on' &&  ( url_status == '' || url_status == null ) && article_status == 'off' ){
        window.location.href = '?zhuyin=on';
        return false;        
    }

    if( article_status == 'off' && cookie_tmp == '' ){
        cookie_status = 'off';
    }
    
    if ( cookie_status == 'off' && url_status != 'off' ){
        window.location.href = '?zhuyin=off';
        return false;
    }

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
