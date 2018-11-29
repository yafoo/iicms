 function toggleLayer(whichLayer)
{
	if (document.getElementById)
	{
		// this is the way the standards work
		var style2 = document.getElementById(whichLayer).style;
		style2.display = style2.display? "":"block";
	}
	else if (document.all)
	{
		// this is the way old msie versions work
		var style2 = document.all[whichLayer].style;
		style2.display = style2.display? "":"block";
	}
	else if (document.layers)
	{
		// this is the way nn4 works
		var style2 = document.layers[whichLayer].style;
		style2.display = style2.display? "":"block";
	}
}

$(function(){
	$(".tab").on("mouseover",function(event){
		$(this).addClass("active").siblings().each(function(){
			$(this).removeClass("active");
			$($(this).data("tab")).hide();
		});
		$($(this).data("tab")).show();
	}).on("click",function(event){
		$(this).addClass("active").siblings().each(function(){
			$(this).removeClass("active");
			$($(this).data("tab")).hide();
		});
		$($(this).data("tab")).show();
	})
});