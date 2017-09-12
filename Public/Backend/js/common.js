$(function(){
	$(".js_offcanvas").on("click",function(){
		$("body").toggleClass('sidebar_open');
	})
	$(".js_collapse").on("click",function(){
		var next=$(this).next();
		if(next.hasClass('open')){
			next.slideUp(100,function(){
				next.removeClass("open");
			});
		}else{
			next.fadeIn(200,function(){
				next.addClass("open");
			});
		}
	})
	/* 鼠标点击展开下拉菜单 －S */
	$(".js_dropdown[data-event=click]").on("click",function(){
		dropdown(this);
	})
	/* 鼠标点击展开下拉菜单 －E */

	/* 广告管理 －S */
	$(document).on("click",".js_add_adspace",function(){
		var obj=$(this).parents(".js_set_adspace");
		var html=$("tr:eq(1)",obj).clone();
		$(html).find("input").val('1');

		$(html).find("td:first").html((obj.find("tr").length-1));
		$(html).find("td:last").html('<div class="delLine js_delLine">删除</div>');
		obj.find("tr:last").before(html);
	})
	$(document).on("click",".js_delLine",function(){
		$(this).parent().parent().remove();
		var trLength=$(".js_set_adspace tr").length;
		for(var num=0;num<trLength;num++){
			if(num!=0 && trLength!=(num+1)){
				$(".js_set_adspace tr:eq("+num+") td:first").html(num);
			}
		};
	})
	/* 广告管理 －E */
	/* 提示消息 －S */
	$(".js_tip_msg_box").on("click",function(){
		$(".js_tip_msg").fadeIn(200);
	})
	$(".js_colse_dialog").on("click",function(){
		$(".js_tip_msg").fadeOut(200);
	})
	/* 提示消息 －E */
	/* 右上角消息通知 -S */
	$(".js_message_btn").on("click",function(){
		if($(".js_message_box").is(":hidden")){
			$(".js_message_box").slideDown(100);
			$(".js_message_box").prev().addClass('active');
			$(".js_message_bg").fadeIn(100);
		}else{
			$(".js_message_box").slideUp(100);
			$(".js_message_box").prev().removeClass('active');
			$(".js_message_bg").fadeOut(100);
		}
	})
	$(document).on("click",function(event){
		if(!$(".js_message_box").is(":hidden")){
			//取消冒泡
		    if($(event.target).hasClass("js_message_btn") || $(event.target).parents(".js_message_box").length>0 || $(event.target).parents(".js_message_btn").length>0){
		        event.cancelBubble = true;
		    }else{
			    $(".js_message_box").slideUp(100);
			    $(".js_message_box").prev().removeClass('active');
			    $(".js_message_bg").fadeOut(100);
		        event.stopPropagation();
		    }
		}
	})
	/* 右上角消息通知 -E */

	resetContentSize();
	$(window).resize(function(){
		resetContentSize();
	});
})
function resetContentSize(){
	$(".js_content_wrapper").css("min-height",$(window).height()-$(".main-header").height());
	if($(window).height()<=$("body").height()){
		var bg_height=$("body").height()-$(".main-header").height();
	}else{
		var bg_height=$(window).height()-$(".main-header").height();
	}
	$(".js_message_bg").css({"height":bg_height,"top":$(".main-header").height()});
}
function dropdown(obj){
	if($(obj).data("obj")!=undefined){
		var nextObj=$("."+$(obj).data("obj"));
	}else{
		var nextObj=$(obj).next();
	}
	if(nextObj.is(":hidden")){
		$(obj).addClass("active");
		nextObj.stop().slideDown(100);
	}else{
		$(obj).removeClass("active");
		nextObj.stop().slideUp(100);
	}
}
$(function(){
	//解决百度编辑器全屏问题
	$(document).on('click','.edui-editor',function(){
		var _this = $(this);
		var winW = $(window).width();
		if(winW <= _this.width()){
			$('.main-header').css({'z-index':'-1'});
			$('.main-sidebar').css({'z-index':'-1'});
		}else{
			$('.main-header').css({'z-index':'820'});
			$('.main-sidebar').css({'z-index':'810'});
		}
	});
});