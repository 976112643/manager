//select2的扩展
//4.0版本只支持select元素的绑定


//自定义扩展
$.super_select2 = function (options, element) {
	options = options || {};
	var opt = {};
	//父级元素
	opt.element = $(element);
	//标记不同的select2
	var key = $(element).attr('data-select2-key');
	opt.key = key;
	//标记父级块，用于回调
	opt.parent = $(element).attr('data-select2-parent');
	//传递参数给后端
	opt.parm_set = '?operate=ajax_select2&ajax_select2=set_' + key;
	opt.parm_get = '?operate=ajax_select2&ajax_select2=get_' + key;
	//配置最少输入字符数
	if($(element).attr('data-select2-limit')){
		opt.limit = $(element).attr('data-select2-limit');
	}
	this.init($.extend(true,options,opt));
	return this;
};
$.super_select2.defaults = {
	element: '.js_super_select2',
	parent: '',
	child: '',
	key: '',
	parm_get: '',
	parm_set: '',
	limit: 3
};
$.super_select2.prototype = {
	//初始化
	init: function(options){
		var options = this.options = $.extend(true, {}, $.super_select2.defaults, options);
		this.bind_select2(options.element);
	},
	FormatResult: function (repo) {
		return repo.text;
	},
	FormatSelection: function (repo) {
		return repo.text;
	},
	update : function (key) {
		if ($.isPlainObject(key)) {
			$.extend(true, this, key);
		}
	},
	
	//绑定select2
	bind_select2: function (obj) {
		var _this = this;
		var p = obj.parent('.controls');
		if(obj.data('select2-key')){
			obj.select2({
				//语言
				language: "zh-CN",
				//默认值
				placeholder: '请选择',
				//是否显示清空按钮，需和placeholder配合使用
				allowClear: true,
				//最小需要输入多少个字符才进行查询，与之相关的maximumSelectionLength表示最大输入限制
				minimumInputLength: _this.options.limit,
				ajax: {
					url: location.href + _this.options.parm_get,
					dataType: 'json',
					quietMillis: 250,
					data: function (term, page) {
						return {
							q: term.term, //search term
							pagesize: 10, // page size
							page: page, // page number
							apikey: "ju6z9mjyajq2djue3gbvv26t"
						};
					},
					type:'post',
					//失败处理
					error: function (){
						//window.location.href=window.location.href;
					},
					//请求结果格式化，默认json格式为[{id:1,text:'text'},{id:2,text:'text'}]
					processResults:function(data){
						return {
							results: data
						}
					},
				},
				//返回结果回调
				//templateResult: _this.FormatResult,
				//选中项回调
				//templateSelection: _this.FormatSelection, 
				/*
				//字符转义处理
				escapeMarkup: function (data) {
					/*var p = obj.closest(_this.options.parent);
					var child = 'data-select2-' + _this.options.key;
					var result = obj.select2("data");
					if(!data || !result){
						return data;
					}
					p.find('input[' + child + '],select[' + child + '],div[' + child + '],span[' + child + ']').each(function(i,o){
						var name = $(o).attr(child);
						if(eval('result.'+name) == undefined){
							return '';
						}
						var value = eval('result.'+name);
						switch($(o).attr('data-select2-change')){
							case 'html':
								$(o).html(value);
								break;
							default:
								$(o).val(value);
						}
					});
					if(typeof(_this.options.after_select) == "function") {
						_this.options.after_select(result);
					}
					return data;
				},
				formatInputTooShort: function (input, min) {
					var n = min - input.length;
					var content = search_info.replace("%x%", n);
					if(n !== 1){
						content += search_infos;
					}
					return content;
				},*/
			});
		}
		else{
			obj.select2({
				//语言
				language: "zh-CN",
				//默认值
				placeholder: '请选择',
				//是否显示清空按钮，需和placeholder配合使用
				allowClear: true,
				createSearchChoice:function(term, data) {
						console.log(term);
					if ($(data).filter(function() {
						console.log(this);
						return this.text.localeCompare(term)===0; 
					}).length===0) {
						return {
							id:term,
							text:term
						};
					} 
				},
			});
		}
	}
}
$.fn.super_select2 = function (options) {
	this.each(function () {
		var instance = $(this).data('super_select2');
		if (instance) {
			instance.update(options);
		} else {
			$(this).data('super_select2', new $.super_select2(options, this));
		}
	});
	return this;
};