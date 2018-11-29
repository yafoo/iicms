//author: yusi  QQ: 331406669
(function($){
$.fn.ajaxFile = function(options){
	var defaults = {
		upurl:"",
		upname:"",
		uptype:"file",
		callback:null
	}
	var options = $.extend(defaults, options);
	this.each(function(){
		var o = options;
		var that = $(this);
		var showBox = $(that.data("box"));
		if(that.data('upurl')) o.upurl = that.data('upurl');
		if(that.data('upname')) o.upname = that.data('upname');
		if(that.data('uptype')) o.uptype = that.data('uptype');
		
		showBox.css("position","relative").append('<div class="bar progress" style="position:absolute;bottom:0px;left:0px;width:100%;height:0px;background:rgba(0, 0, 0, 0.7) !important;"></div><span class="bar percent" style="display:inline-block;width:50px;line-height:14px;position:absolute;top:50%;left:50%;margin-left:-25px;margin-top:-7px;border:1px solid #390;font-size:12px;text-align:center;border-radius:3px;background-color:green;font-size:12px;color:#fff;">0%</span><input type="hidden" name="'+that.data("iptname")+'">');
		if(o.type == "file") showBox.prepend('<span class="filesrc"><span/>');
		else showBox.prepend('<img class="imgsrc" style="width:100%;height:100%;"/>');
		var bar = showBox.find(".bar");
		var progress = showBox.find(".progress");
		var percent = showBox.find(".percent");console.log(percent.text());
		bar.hide();
		
		var formHtml = '<form action="'+o.upurl+'" method="post" enctype="multipart/form-data" style="display:none;"><input type="file" name="'+o.upname+'"></form>';
		
		that.click(function(){
			if($(this).next("form").length == 0) $(this).after(formHtml);
			var who = $(this).next();
			who.children("input").change(function(){
				imgSave(who);
			}).click();
		});
		
		function imgSave(me){
			me.ajaxSubmit({
				dataType: 'json',
				beforeSend: function(){
					bar.show();
					var percentVal = '0%';
					progress.height(percentVal);
					percent.html(percentVal);
				},
				uploadProgress: function(event, position, total, percentComplete){
					var percentVal = percentComplete + '%';
					progress.height(percentVal);
					percent.html(percentVal);
				},
				success: function(data){
					bar.hide();
					me.remove();
					if(data.state==1 && data.url!='') srcSave(data.url);
					else alert(data.msg);
				},
				error: function(xhr){
					bar.hide();
					me.remove();
					alert("ÉÏ´«Ê§°Ü:"+xhr.responseText);
				},
				clearForm: true
			});
		}
		
		function srcSave(url){
			showBox.find("input").val(url);
			if(o.uptype == "file") showBox.find(".filesrc").text(url);
			else showBox.find(".imgsrc").attr('src',url);
			if(typeof(o.callback) == "function") o.callback(url);
		}
		
	});
};
})(jQuery);