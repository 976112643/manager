$(function() {
	$('.wrapper').css('min-height',$(window).height()-$('.header').height()-16);
	$(window).resize(function(event) {
		$('.wrapper').css('min-height',$(window).height()-$('.header').height()-16);
	});

	var val=$(window).width()/7.5;
	$('html').css('font-size',val);
	$(window).resize(function(event) {
		var val=$(window).width()/7.5;
		$('html').css('font-size',val);
	});


	// 複製最後一張圖
$(".js_imglist_3d").prepend($(".js_imglist_3d li").eq($(".js_imglist_3d li").length-1).clone());
$(".js_imglist_3d").append($(".js_imglist_3d li").eq(1).clone());

//首页轮播
var page_main = $('.js_banner_stage').get(0);
var kc_div = $('.js_imglist_3d');
var kc_div_img = $('.js_imglist_3d li');
var banner_btn = $('.js_banner_btn');
var length = kc_div_img.length;
var x, lastX, lastY;
var max_x = 60; //有效划过长度
var kc_now = 0;
var page_now = 0;
var w_w = $(window).width();
// 首页轮播图全屏
$('.js_imglist_3d').width(w_w * $('.js_imglist_3d').find('li').length);
$('.js_imglist_3d li').width(w_w).height(w_w);

page_main.addEventListener("touchstart", start, false);
page_main.addEventListener("touchmove", move, false);
page_main.addEventListener("touchend", back0, false);


function start(evt) {
    var w_w = $(window).width();
    var w_h = $(window).height();
    var otouch = evt.targetTouches[0];
    lastX = otouch.pageX;
    kc_div.css({
        "webkitTransition": ""
    });
  

}

function move(evt) {

    evt.preventDefault(); //取消默认滚动事件
    var otouch = evt.targetTouches[0];
    kc_div.css('marginLeft', otouch.pageX - lastX);
}

function back0(evt) {

    var nowl = parseInt(kc_div.css('marginLeft'));
    
    //向右成功
    if (nowl >= max_x) {
        kc_now--;
        succeed()
    }
    //向左成功	
    if (nowl < -max_x) {
        kc_now++;
        succeed()
    }
    //向右失败

    if (nowl < max_x) {

        faild()
    }
    //向左失败	
    if (nowl > -max_x) {

        faild()
    }

}
// 略缩图点击
var  ban_btns=$('.js_ban_btns img');
ban_btns.click(function(event) {
    kc_now=$(this).index();
     succeed()
});
function faild() {
    kc_div.css({
        "webkitTransition": "all .3s cubic-bezier(.13,.76,.36,.85) 0s",
        "marginLeft": 0
    })
}

function succeed() {
    kc_div.css({
        "webkitTransition": "all .3s cubic-bezier(.13,.76,.36,.85) 0s",
        "left": -kc_div_img.width() * (kc_now + 1),
        "marginLeft": 0
    });

    $('.js_num_btn span').html(kc_now+1);
    if (kc_now >= kc_div_img.length - 2) {
        kc_now = 0;
        $('.js_num_btn span').html(kc_now+1);
        setTimeout(function() {
            kc_div.css({
                "webkitTransition": "",
                "left": -kc_div_img.width() * (kc_now + 1),
                "marginLeft": 0
            });

        }, 600)
    }
    if (kc_now <= -1) {
        kc_now = kc_div_img.length - 3;
        $('.js_num_btn span').html(kc_now+1);
        setTimeout(function() {
            kc_div.css({
                "webkitTransition": "",
                "left": -kc_div_img.width() * (kc_now + 1),
                "marginLeft": 0
            });

        }, 600)
    }
}




})
// 提示弹窗 (提示内容，提示窗类型  1-6)
function tip_show(html,type) {
	if($('.tip_win').length!=0){
		return false
	}
	$('body').append('<div class="tip_win"></div>');
	$('.tip_win').addClass('tip_win'+type);
    $('.tip_win').html(html).css('marginLeft', -$('.tip_win').width() * 0.5 - 15).fadeIn(30, function() {
        setTimeout(function() {
            $('.tip_win').fadeOut(300,function(){
            	$('.tip_win').remove()
            });
           
        }, 1100)
    });
};

