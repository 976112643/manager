//下拉框配置
function Super_level_select(options){
	this.options = Super_level_select.defaults;
	switch(options.type){
		case 'area':
			this.options.tpl.init[1] = '<option value="" >请选择省</option>';
			this.options.tpl.init[2] = '<option value="" >请选择市</option>';
			this.options.tpl.init[3] = '<option value="" >请选择区</option>';
			break;
		default:
			;
	}
	this.$obj = options.$obj;
	this.init();
	return this;
};
Super_level_select.defaults = {
	//模版，根据层级可自行扩展，默认使用0级模版
	tpl: {
		//默认
		defaults: ['<option value="%value" data-level="%level" data-pid="%pid">%title</option>'],
		//选中
		selected: ['<option value="%value" data-level="%level" selected="selected" data-pid="%pid">%title</option>'],
		//只读
		readonly: ['<option value="%value" data-level="%level" readonly="readonly" data-pid="%pid">%title</option>'],
		//初始
		init: ['<option value="" >请选择</option>'],
	},
},
Super_level_select.prototype = {
	get_obj: function(){
		return this.$obj;
	},
	init: function(){
		this.bind_event();
	},
	init_data: function(){
		$.each(this.$obj,function(i,o){
			var opt = $(o).data('super_level');
			var level = $(o).data('level');
			var html = opt.init_by_level(level);
			if(level == '1'){
				$(o).html(html + opt.get_childs_by_id(0));
			}
			else{
				$(o).html(html);
			}
		});
		$.each(this.$obj,function(i,o){
			var value = $(o).data('default');
			if(value){
				$(o).val(value).trigger('change');
			}
		});
		
	},
	bind_event: function(){
		var $obj = this.$obj;
		$obj.on('change',function(){
			var opt = $(this).data('super_level');
			var level = $(this).data('level');
			var value = $(this).val();
			$.each($obj,function(i,o){
				if($(o).data('level') == level + 1){
					$(o).html('').append(opt.init_by_level($(o).data('level')))
							.append(opt.get_childs_by_id(value));
				}
				if($(o).data('level') > level + 1){
					$(o).html('').append(opt.init_by_level($(o).data('level')));
				}
			});
		});
	},
	//数据格式化
	format_data: function(tpl, data){
		var result = tpl,info = {
			value: data.id,
			pid: data.pid,
			title: data.title,
		};
		for(i in info){
			result = result.replace('%' + i,info[i]);
		}
		return result;
	},
}
