$(function(){
	/* 权限管理 －S */
	$(".js_check_all_access").click(function(){
		$(".js_access_table").find("input").prop("checked",this.checked);
	})
	$(".js_top_ids").click(function(){
		var all=$(this).parents("tr").next(".child").find(".ids").prop("checked",this.checked);
		if(this.checked && $(".ids:checked").length==$(".ids").length){
			$(".js_check_all_access").prop("checked",true);
		}else{
			$(".js_check_all_access").prop("checked",false);
		}
	})
	$(".js_two_ids").click(function(){
		var all=$(this).parents("table");
		$(all[0]).find(".ids").prop("checked",this.checked);
		if(this.checked){
			if($(".ids:checked").length==$(".ids").length){
				$(".js_check_all_access").prop("checked",true);
			}
			$(this).parents("tr.child").prev().find(".ids").prop("checked",true);
			$(all[0]).parent().parent().find(".js_top_ids").prop("checked",true);
		}else{
			$(this).parents("table").find(".js_check_all_access").prop("checked",false);
		}
	})
	$(".js_three_ids").click(function(){
		var all=$(this).parents("table");
		if(this.checked){
			if($(".ids:checked").length==$(".ids").length){
				$(".js_check_all_access").prop("checked",true);
			}
			$(all[0]).find(".ids").prop("checked",true);
			$(all[1]).find(".js_two_ids").prop("checked",true);
			$(this).parents("tr.child").prev().find(".ids").prop("checked",true);
			$(all[1]).parent().parent().find(".js_top_ids").prop("checked",true);
		}else{
			$(this).parentsUntil('table').next().find("input").prop("checked",false);
			//$(this).parents("tr").find(".js_top_ids").prop("checked",false);
			$(this).parents("table").find(".js_check_all_access").prop("checked",false);
		}
	})
	$(".js_three_ids").click(function(){
		var all=$(this).parents("table");
		if(this.checked){
			if($(".ids:checked").length==$(".ids").length){
				$(".js_check_all_access").prop("checked",true);
			}
			$(all[0]).find(".ids").prop("checked",true);
			$(all[1]).find(".js_two_ids").prop("checked",true);
			$(this).parents("tr.child").prev().find(".ids").prop("checked",true);
			$(all[1]).parent().parent().find(".js_top_ids").prop("checked",true);
		}else{
			$(this).parentsUntil('table').next().find("input").prop("checked",false);
			//$(this).parents("tr").find(".js_top_ids").prop("checked",false);
			$(this).parents("table").find(".js_check_all_access").prop("checked",false);
		}
	})
	$(".js_foure_ids").click(function(){
		if(this.checked){
			var all=$(this).parents("table");
			if($(".ids:checked").length==$(".ids").length){
				$(".js_check_all_access").prop("checked",true);
			}
			$(all[0]).find(".js_foure_ids").prop("checked",true);
			$(all[1]).find(".js_three_ids").prop("checked",true);
			$(all[2]).find(".js_two_ids").prop("checked",true);
			$(this).parents("tr.child").prev().find(".ids").prop("checked",true);
			$(all[1]).parent().parent().find(".js_top_ids").prop("checked",true);
		}else{
		    if($(".ids:checked").length==1){
		        var all=$(this).parents("table");
		        $(all[0]).find(".js_foure_ids").prop("checked",false);
		        $(all[1]).find(".js_three_ids").prop("checked",false);
		        $(all[2]).find(".js_two_ids").prop("checked",false);
		        $(this).parents("tr.child").prev().find(".ids").prop("checked",false);
		    }
			$(".js_check_all_access").prop("checked",false);
		}
	})
	$(".js_last_ids").click(function(){
		if(this.checked){
			var all=$(this).parents("table");
			if($(".ids:checked").length==$(".ids").length){
				$(".js_check_all_access").prop("checked",true);
			}
			$(all[0]).find(".js_foure_ids").prop("checked",true);
			$(all[1]).find(".js_three_ids").prop("checked",true);
			$(all[2]).find(".js_two_ids").prop("checked",true);
			$(this).parents("tr.child").prev().find(".ids").prop("checked",true);
			$(all[1]).parent().parent().find(".js_top_ids").prop("checked",true);
		}else{
			$(".js_check_all_access").prop("checked",false);
		}
	})
	/* 权限管理 －E */
	$(".js_display_access").on("click",function(){
		if($(this).hasClass('access_open')){
			$(this).removeClass("access_open").html('<img src="'+_IMG_+'/access_display.png" alt="">收起');
			var parentObj=$(this).parent().parent().next("tr");
			parentObj.show();
			if(parentObj.find(".js_set_height").length>0){
				var box_height=new Array();
				parentObj.find(".js_set_box_height").each(function(){
					box_height.push($(this).height());
				})
				parentObj.find(".js_set_height").height(Math.max.apply(null,box_height));
			}
		}else{
			$(this).addClass("access_open").html('<img src="'+_IMG_+'/access_display.png" alt="">查看详细');
			$(this).parent().parent().next("tr").hide();
		}
	})
})