
var scale_val;

// 提示弹窗
function tip_show(html) {
    $('.tip_win').html(html).css('marginLeft', -$('.tip_win').width() * 0.5 - 15).fadeIn(30, function() {
        setTimeout(function() {
            $('.tip_win').fadeOut(300)
        }, 1100)
    });
};

// 遮罩显示 隐藏
function mark_s(){
    $('.js_mark').fadeIn(300)
}
function mark_h(){
    $('.js_mark').fadeOut(300)
}

//弹窗  显示隐藏

function winshow(obj){
    obj.css('transform', 'scale(' + scale_val + ')');
    obj.css('webkitTransform', 'scale(' + scale_val + ')');
    obj.css('margin-top', '-355px');
    
}
function winhide(obj){
    obj.css('transform', 'scale(' + 0+ ')');
    obj.css('webkitTransform', 'scale(' + 0 + ')');
    obj.css('margin-top', '855px');
    
}


 // 我的评价  星星 

    function star_d(){
        var score_star=$('.js_score_star');
        for(i=0;i<score_star.length;i++){
           // Math.ceil(score_star.eq(i).attr('data'));
           // alert(Math.ceil(score_star.eq(0).attr('data')))
          
           var data=score_star.eq(i).attr('data').split(".")[1];
           var data2=score_star.eq(i).attr('data').split(".")[0];
            // 整数
            if(data=='0') {
              
                if(data2==0){
                    score_star.eq(i).find('.star').removeClass('active');
                    
                }else{
                    score_star.eq(i).find('.star').eq(Math.ceil(score_star.eq(i).attr('data'))-1).prevAll('.star').addClass('active');
                    score_star.eq(i).find('.star').eq(Math.ceil(score_star.eq(i).attr('data'))-1).addClass('active');
                }
                

            }
            // 非整数
            else{
                score_star.eq(i).find('.star').eq(Math.ceil(score_star.eq(i).attr('data'))-1).prevAll('.star').addClass('active');
                score_star.eq(i).find('.star').eq(Math.ceil(score_star.eq(i).attr('data'))-1).addClass('active_b');
            }
       
        }
    }

 
    //卡券  三位数 
    function num_c(){
        var num_c=$('.js_num_c');
        for(i=0;i<num_c.length;i++){
            if(num_c.eq(i).html().length==3){
                num_c.eq(i).css('font-size','60px')
            }
        }
    } 


/*function fullpage() {
    var w_w = $(window).width();
    scale_val = w_w / $('.wrapper').width();
    $('.wrapper,.foot,.cart_foot,.confirm_end,.js_cart-select,.address_win').css('transform', 'scale(' + scale_val + ')');
    $('.wrapper,.foot,.cart_foot,.confirm_end,.js_cart-select,.address_win').css('msTransform', 'scale(' + scale_val + ')');
    $('.wrapper,.foot,.cart_foot,.confirm_end,.js_cart-select,.address_win').css('oTransform', 'scale(' + scale_val + ')');
    $('.wrapper,.foot,.cart_foot,.confirm_end,.js_cart-select,.address_win').css('mozTransform', 'scale(' + scale_val + ')');
    $('.wrapper,.foot,.cart_foot,.confirm_end,.js_cart-select,.address_win').css('webkitTransform', 'scale(' + scale_val + ')');
}*/

$(function(){
	
	//fullpage();
   
    var rel_w = $('.wrapper').width();
    var rel_h = $(window).height() * $('.wrapper').width() / $(window).width();
	
    // 评价  --店铺 评价
    $('.js_score_div a').on('click', function(event) {
        var index=$(this).index();
        $(this).nextAll('a').removeClass('active');
        $(this).prevAll('a').addClass('active');
        $(this).addClass('active').siblings('input').val(index+1);

    });
    
    $('.js_sele_p a').click(function(event) {
       var index=$(this).index();
       $('input[name="p_id"]').val($(this).attr('data_id'));
       $(this).addClass('box_active').siblings('a').removeClass('box_active')
    });
    
    // 星星显示
    star_d();
   
   // 卡券三位数
   num_c();

   // 大家的评价 
   $('.js_close_btn').click(function(event) {
       $('.address_t').css('top',-100);
	   if($('.js_img_list div').length==0){
		
        $('.js_img_list').css('height','0')
		}
   });

    // 评论图片点击放大 

    /*$(document).on('click','.js_scale_img',function(){
        
        var imgurl=$(this).attr('data-url');
        $('.js_img_win div').html('<img src="'+imgurl+'">');
        // setTimeout(function(){
        // $('.js_img_win div').css('marginTop',-0.5*$('.js_img_win div').height())
        // },300)
        
        $('.js_img_win').css('display','table');

    })

    $('.js_img_win').click(function(event) {
         $('.js_img_win').fadeOut(300);
    });*/

    // 大家评价  三张图片

    var img_list=$('.js_img_list div');
    for(i=0;i<img_list.length;i++){
        if(i%3==2){
            img_list.eq(i).addClass('last');
        }
    }

    if(img_list.length==0){
        $('.js_img_list').css('height','90')
    }


})
