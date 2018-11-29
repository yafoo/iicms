(function($) {
  'use strict';

  $(function() {
    var $fullText = $('.admin-fullText');
    $('#admin-fullscreen').on('click', function() {
      $.AMUI.fullscreen.toggle();
    });

    $(document).on($.AMUI.fullscreen.raw.fullscreenchange, function() {
      $fullText.text($.AMUI.fullscreen.isFullscreen ? ' 开启全屏' : ' 退出全屏');
    });
  });
})(jQuery);

$(function(){
	$('.aside-title a').click(function(){$('.am-list li a').removeClass("hover");});
	$('.aside-bars').click(function(){$(this).next().toggle(200);});
	$('.am-list li a').click(function(){
		$('.am-list li a').removeClass("hover");
		$(this).addClass("hover");
	});
});

$.AMUI.progress.start(); //加载进度条

$(function(){
	//分页
	$('.admin-page-info').prependTo('.am-pagination');
	
	//上传
	if($(".ajax-file").length > 0) $(".ajax-file").ajaxFile();
});

//删除确认
function delcfm(){
	if (!confirm("确认删除？")) {
		window.event.returnValue = false;
	}
}

//处理确认
function actcfm(){
	if (!confirm("确认操作？")) {
		window.event.returnValue = false;
	}
}



$(function(){
    //ajax提交表单
    $('[data-ajax]:submit').click(function(){
        $(this).addClass('am-disabled').prepend('<span class="am-icon-spinner am-icon-pulse"></span>');
        var $form =$($(this).data('ajax'));
        var url = $form.attr('action');
        var data = $form.serialize();
        pForm(url,data);
        setTimeout("removeSubmitDisabledStatus()",5000) //5秒后移除禁用状态
        return false;
    })
    $(document).on('click','[data-curd]',function(){
        var json = $(this).data('curd');
        var obj = j2o(json);
        pForm(obj.u,{id:obj.id,act:obj.a});
    })

    $(document).on('click','[data-pid]',function(){
        var json = $(this).data('pid');
        var obj  = j2o(json);
        $(obj.form+ ' [name="pid"]').attr('value',obj.id);
        spanel('add');
    })

    $(document).on('click','[data-ed-auth]',function(){
        var json =$(this).data('ed-auth');
        var ob = j2o(json);
        spanel('ed');
        var $e = $('#ed');
        $.post(ob.u,{id:ob.id},function(j){
            var data = j2o(j);
            if(data){
                $e.find('[name="name"]').val(data.name);
                $e.find('[name="title"]').val(data.title);
                $e.find('[name="pid"]').val(data.pid);
                $e.find('[name="sort"]').val(data.sort);
                $e.find('[name="id"]').val(data.id);
                $e.find('[name="icon"]').val(data.icon);
            }
        })
    })
    $.AMUI.progress.done(); //完成进度加载
})
function removeSubmitDisabledStatus(){
    var $e = $('[data-ajax]');
    $e.removeClass('am-disabled');
    $e.find('span').remove();
}
/**
 * post提交数据
 * @param url
 * @param data
 */
function pForm(url,data){
    $.post(url,data,function(json){
        showAlert(json);
    })
}

function spanel(name){
    $('#'+name).show().siblings('.am-panel-default').slideUp();
}
/**
 * get提交数据
 * @param url
 * @param data
 */
function gForm(url,data){
    $.get(url,data,function(json){
        showAlert(json)
    })
}


/**
 * json数据转obj对象
 * @param json
 * @returns {Object}
 */
function j2o(json){
    return eval('(' + json + ')');
}

/**
 * 打开modal 层
 * @param elm id
 */
function md(elm) {
    $('#'+elm).modal('open');
}

//刷新页面
function refresh(){
    window.location.reload();
}
/**
 * 转跳到指定页面
 * @param url
 */
function toUlr(url){
    widows.location.href=url;
}

/**
 * 页面消息提示alert
 * @param status
 * @param msg
 * @param ref
 */
function showAlert(json){
    var data = j2o(json);
    var $success=$('.am-alert-success');
    var $error=$('.am-alert-danger');
    var _alert;
    if(data.status==1){
        _alert=$success;
    }else{
        _alert=$error;
    }
    _alert.html(data.msg).fadeIn().delay(3000).fadeOut();
    if(data.ref && data.url == 0){
        setTimeout('refresh()',data.hold);
    }else if(data.ref && data.url){
        setTimeout("toUlr(data.url)",data.hold);
    }
}


/**
 * 页面遮罩
 */
function popCreat(){
	$("<div id='pop' style='position:absolute;top:0px;left:0px;background-color:rgba(0,0,0,0.7)!important;z-index:9999;'><iframe id='popFrm' name='main' frameborder='0' src='' style='display:block;position:absolute;top:50%;left:50%;margin-left:-200px;margin-top:-150px;width:400px;height:300px;background:#FFF;box-shadow:0px 0px 10px #000000;'></iframe></div>")
	.height($(document).height())
	.width($(document).width()).hide().appendTo("body");
	$("#pop").click(function(){popHide();});
}

function popHide(){
	$("#pop").hide();
}

function popShow(url = ''){
	$("#pop #popFrm").attr("src",url);
	$("#pop").show();
}


$(function(){
	$("#mybtn1").click(function(){window.parent.popShow("http://m.baidu.com");});
	$("#mybtn2").click(function(){window.parent.popShow("http://www.yafu.me/admin/index/dialog.html");});
	$("#mybtn3").click(function(){window.parent.popHide();$("#mybtn1",window.parent.window.frames["main"].document).click();});
});