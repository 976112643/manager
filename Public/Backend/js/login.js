$(function(){
	var verifyimg = $(".js_verifyimg").attr("src");
	$(".js_verifyimg").on("click",function(){
		if( verifyimg.indexOf('?')>0){
			$(".js_verifyimg").attr("src", verifyimg+'&random='+Math.random());
		}else{
			$(".js_verifyimg").attr("src", verifyimg.replace(/\?.*$/,'')+'?'+Math.random());
		}
	});
})
function afterCallbackLogin(curform,data){
	if(data.status!=1){
		$(".js_verifyimg").click();
	}
	return true;
}