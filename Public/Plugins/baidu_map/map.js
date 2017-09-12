	var map = {},mk=null,searchInfoWindow=null;
	//百度地图API功能
	function set_location(point,info){
		point = new BMap.Point(point.lng,point.lat);
		map.centerAndZoom(point, 15);
		mk = new BMap.Marker(point);
		mk.enableDragging();
		mk.addEventListener("dragend", function(e){ 
			$('#lng').val(e.point.lng);
			$('#lat').val(e.point.lat);  
		});
		if(info){
			searchInfoWindow._content = get_content(info);
			searchInfoWindow.open(mk);
			mk.addEventListener("click", function(e){
				searchInfoWindow.open(mk);
			});
		}
		map.addOverlay(mk);
	}
	function get_content(info){
		var content = '<div style="margin:0;line-height:20px;padding:2px;">' +
                    '地址：' + info.address + '<br/>' +
										'电话：' + info.tel + '<br/>' + 
										'联系人：' + info.people + '' +
                  '</div>';
		return content;
	}
	function init_map(){
		map = new BMap.Map("baidu_map");            // 创建Map实例
		
	
		searchInfoWindow = new BMapLib.SearchInfoWindow(map, '', {
			title  : "部门",      //标题
			width  : 290,             //宽度
			height : 105,              //高度
			panel  : "panel",         //检索结果面板
			enableAutoPan : true,     //自动平移
			searchTypes   :[
				BMAPLIB_TAB_SEARCH,   //周边检索
				BMAPLIB_TAB_TO_HERE,  //到这里去
				BMAPLIB_TAB_FROM_HERE //从这里出发
			]
		});
		//map.centerAndZoom('武汉',15);   // 初始化地图，设置中心点坐标和地图级别                
		map.enableScrollWheelZoom();                 //启用滚轮放大缩小
		var top_left_control = new BMap.ScaleControl({anchor: BMAP_ANCHOR_BOTTOM_LEFT});// 左上角，添加比例尺
		//var top_left_navigation = new BMap.NavigationControl();  //左上角，添加默认缩放平移控件
		var top_right_navigation = new BMap.NavigationControl({
			anchor: BMAP_ANCHOR_BOTTOM_RIGHT,
			type: BMAP_NAVIGATION_CONTROL_SMALL,
			enableGeolocation: true
		}); //右上角，仅包含平移和缩放按钮
		//缩放控件type有四种类型:
		//BMAP_NAVIGATION_CONTROL_SMALL：仅包含平移和缩放按钮；
		//BMAP_NAVIGATION_CONTROL_PAN:仅包含平移按钮；
		//BMAP_NAVIGATION_CONTROL_ZOOM：仅包含缩放按钮
		map.addControl(top_left_control);        
		//map.addControl(top_left_navigation);     
		map.addControl(top_right_navigation);  
		
		var address = '';
			address += $('[name="province_id"]').find("option:selected").text();
			address += $('[name="city_id"]').find("option:selected").text();
			address += $('[name="zone_id"]').find("option:selected").text();
			address += $('[name="address"]').val();
		//优先坐标，然后地址，最后浏览器
		if(Math.abs($('#lng').val())>0 && Math.abs($('#lat').val())>0){
			var point = {lng:$('#lng').val(),lat:$('#lat').val()};
			var info = {address:address,tel:$('[name="contact_tel"]').val(),people:$('[name="contact_people"]').val()};
			set_location(point,info);
		}else{
				
			/*if(address){
				// 创建地址解析器实例
				var myGeo = new BMap.Geocoder();
				// 将地址解析结果显示在地图上,并调整地图视野
				myGeo.getPoint(address, function(point){
					if (point) {
						var info = {address:address,tel:$('[name="contact_tel"]').val(),people:$('[name="contact_people"]').val()};
						set_location(point,info);
						$('#lng').val(point.lng);
						$('#lat').val(point.lat);  
					}else{
						alert("您选择地址没有解析到结果!");
					}
				});
			}else{*/
				var geolocation = new BMap.Geolocation();
				geolocation.getCurrentPosition(function(r){
					if(this.getStatus() == BMAP_STATUS_SUCCESS){
						set_location(r.point);
						$('#lng').val(r.point.lng);
						$('#lat').val(r.point.lat);  
					}
					else {
						alert('获取当前位置失败');
					}        
				},{enableHighAccuracy: true});
			//}
		}
	}
	