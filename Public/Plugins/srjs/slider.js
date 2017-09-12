//幻灯片
$(window).ready(function(){
	//文字循环滚动
	//slider.autoPlay('.js_message','top');
	//banner图左右切换
	slider.btnChecked('.js_home_banner',500);
	
	// toutch.sliderEvent('.js_home_banner');
});

var slider = function($){
	var clear;
	function setScrollDirection(obj,direction,time,margin){ 
		var scroll;
		var scroll_l=getSingelWidth(obj,margin);
		var scroll_t=getSingelHeight(obj,margin);
			margin = (margin == undefined) ? 0 : Number(margin);
		switch(direction){
			case 'top':
			case 'bottom':
				scroll=scroll_t;
				if($(obj).attr('isscroll')==undefined || $(obj).attr('isscroll') == 'false'){
					$(obj).attr('isscroll','true');
					$(obj).stop().animate({marginTop:'-'+scroll +"px"},time,function(){
						$(this).css({marginTop:"0px"}).find("li:first").appendTo(this);
						$(obj).attr('isscroll','false');
						callAutoPlay(obj,direction,margin);
					});
				}
				break;
			case 'right':
				scroll = scroll_l;
				if($(obj).attr('isscroll')==undefined || $(obj).attr('isscroll') == 'false'){
					$(obj).attr('isscroll','true');
					setPointCheckedStyle(obj);
					$(obj).stop().animate({marginLeft:'-'+scroll+"px"},time,function(){
						$(this).css({marginLeft:"0px"}).find("li:first").appendTo(this);
						$(obj).attr('isscroll','false');
						callAutoPlay(obj,direction,margin);
					});
				}
				break;
			case 'left':
				scroll = scroll_l;
				if($(obj).attr('isscroll')==undefined || $(obj).attr('isscroll') == 'false'){
					$(obj).attr('isscroll','true');
					$(obj).css({'margin-left':-scroll + 'px'});
					$(obj).prepend($(obj).find('li:last'));
					setPointCheckedStyle(obj);
					$(obj).stop().animate({marginLeft:"0px"},time,function(){
						$(obj).attr('isscroll','false');
						callAutoPlay(obj,direction,margin);
					});
				}
				break;
		}
	};

	function countLiWidth(obj,margin){
		perW = getSingelWidth(obj,margin);
		var len = $(obj).find('li').length;
		$(obj).find('li').width(perW);
		$(obj).width(perW*len);
	};

	function getSingelWidth(obj,margin){
		margin = (margin == undefined) ? 0 : Number(margin);
		var perW = $(obj).find('li').width() + margin;
		return perW;
	};

	function getSingelHeight(obj,margin){
		margin = (margin == undefined) ? 0 : Number(margin);
		var perW = $(obj).find('li').height() + margin;
		return perW;
	};

	function setBannerAutoPlay(obj,direction,margin){
		$(obj).hover(function(){
			$(this).attr("isscroll","true");
		},function(){
			$(this).attr("isscroll","false");
		});
		if($(obj).attr("isscroll") == "false" || $(obj).attr("isscroll") == undefined ){
			var scroll;
			var scroll_l=getSingelWidth(obj,margin);
			var scroll_t=getSingelHeight(obj,margin);
			margin = (margin == undefined) ? 0 : Number(margin);
			switch(direction){
				case 'top':
				case 'bottom':
					scroll=scroll_t;
					$(obj).stop().animate({marginTop:'-'+scroll+"px"},500,function(){
						$(this).css({marginTop:"0px"}).find("li:first").appendTo(this);
					});
				break;
				case 'right':
				case 'left':
					scroll = scroll_l;
					setPointCheckedStyle(obj);
					$(obj).stop().animate({marginLeft:'-'+scroll+"px"},500,function(){
						$(this).css({marginLeft:"0px"}).find("li:first").appendTo(this);
					});
					break;
			}
		}
		clearTimeout(clear);
		clear = setTimeout(function(){
			setBannerAutoPlay(obj,direction,margin);
		},3000);
	};

	function callAutoPlay(obj,direction,margin){
		clearTimeout(clear);
		if($(obj).attr('auto') == 'true'){
			clear = setTimeout(function(){
				setBannerAutoPlay(obj,direction,margin);
			},3000);
		}
	};

	function setListKey(obj){
		var count = 0;
		obj.find('li').each(function(){
			$(this).attr('key',count);
			count ++;
		});
	};

	function setPointCheckedStyle(obj){
		var _index = Number($(obj).find('li:first').next().attr('key'));
		$(obj).next().find('li').eq(_index).addClass('on');
		$(obj).next().find('li').eq(_index).siblings().removeClass('on');
	};

	function setHoverEvent(obj,margin){
		$(obj).on('mouseover',function(){
			$(this).attr("isscroll","true");
		});
		$(obj).on('mouseleave',function(){
			$(this).attr("isscroll","false");
		});
		$(obj).next().on('mouseover','li',function(){
			$(obj).attr('isscroll','true');
			clearTimeout(clear);

			var _that = $(this);
			_that.addClass('on');
			_that.siblings().removeClass('on');
			var scroll_l=getSingelWidth(obj,margin);
			var cur_key = Number($(obj).find('li:first').attr('key'));
			var _index = $(this).index();
			if(cur_key == _index){
				$(obj).attr('isscroll','false');
				return;
			}
			var maxlen = $(obj).find('li').length;
			var num = (_index-1 < 0)?maxlen-1:_index-1;
			if(cur_key > num){
				$(obj).css({'margin-left':'-'+scroll_l + 'px'});
				$(obj).prepend($(obj).find('li[key='+num+']').nextAll());
				$(obj).stop().animate({marginLeft:"0px"},function(){
					$(obj).attr('isscroll','false');
					callAutoPlay(obj,'left',margin);
				});
			}else{
				$(obj).stop().animate({marginLeft:'-'+scroll_l+'px'},function(){
					$(this).css({marginLeft:"0px"});
					$(obj).prepend($(obj).find('li[key='+num+']').nextAll());
					$(obj).attr('isscroll','false');
					callAutoPlay(obj,'left',margin);
				});
			}
		});
	};

	return {
		/*PC端自动轮播
		| add by 陈梦 2016/07/20 
		-----------------------------------------------------
		| description：
		| obj : 幻灯片左右切换的对象（li的父元素）
		| deriction : 轮播方向 left:向左 top:向上
		| obj_margin : 切换单个li的边距值（内外边距的总和）
		------------------------------------------------------
		*/
		autoPlay : function(obj,direction,margin){
			$(obj).attr('isscroll','false');

			if(direction == 'left'){
				countLiWidth($(obj),margin);
			}
			setListKey($(obj));
			$(obj).attr('auto',true);
			setBannerAutoPlay(obj,direction,margin);
		},

		/*PC端左右切换&自动播放
		| add by 陈梦 2016/07/20 
		-----------------------------------------------------
		| description：
		| obj : 幻灯片左右切换的对象（li的父元素）
		| time : 动画切换过渡时间
		| auto : 自动播放 true ：是，false:否
		| obj_margin : 切换单个li的边距值（内外边距的总和）
		------------------------------------------------------
		*/
		btnChecked : function(obj,time,auto,obj_margin){
			var w = $(obj).find('li').width();
			$(obj).find('li').each(function(){
				$(this).css({'width':w+'px!imporant'});
			});
			countLiWidth($(obj),obj_margin);
			auto = (auto == undefined || auto != 'true') ? false : true;
			$(obj).attr('auto',auto);
			if(auto){
				setBannerAutoPlay(obj,'left',obj_margin);
			}
			setListKey($(obj));
			setHoverEvent(obj,obj_margin);

			var $check = $(obj).parent().parent();
			$check.each(function(){
				var _that = $(this);
				_that.on('click','.prev',function(){
					clearTimeout(clear);
					setScrollDirection($(this).parent().parent().find(obj),'left',time,obj_margin);
				});

				_that.on('click','.next',function(){
					clearTimeout(clear);
					setScrollDirection($(this).parent().parent().find(obj),'right',time,obj_margin);
				});
			});
		}
	};
}(jQuery);

var toutch = function($){
	var start=0,move,end;
	var _obj = new Object();
	// var direction;
	function mobileEventLoad(){
		document.addEventListener('touchstart',mobileTouch,false);
        document.addEventListener('touchmove',mobileTouch,false);
        document.addEventListener('touchend',mobileTouch,false);
	};

	function countLiWidth(obj){
		perW = getSingelWidth(obj);
		var len = $(obj).find('li').length;
		$(obj).find('li').width(perW);
		$(obj).width(perW*len);
	};

	function getSingelWidth(obj){
		var perW = $(obj).find('li').width();
		return perW;
	};

	function setScrollDirection(obj,direction,time,margin){ 
		var scroll =getSingelWidth(obj,margin);
			margin = (margin == undefined) ? 0 : Number(margin);
		switch(direction){
			case 'right':
				if($(obj).attr('isscroll')==undefined || $(obj).attr('isscroll') == 'false'){
					$(obj).attr('isscroll','true');
					var key = $(obj).find("li:first").next().attr('key');
					$(obj).next().eq(key).addClass('on').siblings().removeClass('on');
					$(obj).stop().animate({marginLeft:'-'+scroll+"px"},time,function(){
						$(this).css({marginLeft:"0px"}).find("li:first").appendTo(this);
						$(obj).attr('isscroll','false');
						// callAutoPlay(obj,direction,margin);
					});
				}
				break;
			case 'left':
				if($(obj).attr('isscroll')==undefined || $(obj).attr('isscroll') == 'false'){
					$(obj).attr('isscroll','true');
					$(obj).css({'margin-left':-scroll + 'px'});
					$(obj).prepend($(obj).find('li:last'));
					var key = $(obj).find("li:first").attr('key');
					$(obj).next().eq(key).addClass('on').siblings().removeClass('on');
					$(obj).stop().animate({marginLeft:"0px"},time,function(){
						$(obj).attr('isscroll','false');
						// callAutoPlay(obj,direction,margin);
					});
				}
				break;
		}
	};

	function mobileTouch(event){
		var direction;
        var event = event || window.event;
        switch(event.type){
            case 'touchstart':
                start = event.touches[0].clientX;
                break;
            case 'touchmove':
                move = event.changedTouches[0].clientX - start;
                _obj.css({'margin-left':move+'px'});
                event.preventDefault();
                break;
            case 'touchend':
            	end = move;
            	if(end > 0){
            		direction = 'left';
            	}else{
            		direction = 'right';
            	}
            	setScrollDirection(_obj,direction,500);
        }
    };
	return{
		sliderEvent : function(obj){
			_obj = $(obj);
			countLiWidth(_obj);
			window.addEventListener('load',mobileEventLoad,false);
		}
	}
}(jQuery);

var sr_slider=function($){

	return{
		/*
			obj：幻灯片对象
			direction：方向
			auto：true|false 是否自动播放
		*/
		play:function(obj,direction,auto,autoTime){
			//playBanner(obj,direction)
			console.log(obj);
			var autoPlay=setInterval(function(){

			},1000);
		}
	}
}(jQuery);