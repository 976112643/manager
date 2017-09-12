//初始化
function Super_level(data,type,options){
	if(!this instanceof Super_level){
		return new Super_level(data,type,options);
	}
	
	if(typeof data !== 'object') data = {};
	type = type || 'select';
	this.obj = {};
	//根据类型实例化对应设置，默认select
	switch(type){
		case 'select':
			//配置下拉框设置
			this.obj = new Super_level_select(options.set_super);
			break;
		default:
			;
	}
	//初始化树
	this.tree = new Super_tree(data,options.set_tree);
	this.init();
};
Super_level.prototype = {
	//初始化
	init: function(){
		this.init_data_event();
	},
	//根据level获取子集数据
	get_childs_by_level: function(level, set){
		set = set || {};
		var result = '',data = this.tree.get_level(level)._child;
		for(d in data){
			result += this.data_to_tpl(data[d].data,level);
		}
		return result;
	},
	//根据id获取子集数据
	get_childs_by_id: function(id, set){
		var result = '',data = this.tree.search_in_tree(id);
		for(d in data._child){
			result += this.data_to_tpl(data._child[d].data,data.level);
		}
		return result;
	},
	//模版替换
	data_to_tpl: function(data,level,status){
		level = level || 0;
		status = status || 'defaults';
		var result='', tpl = this.obj.options.tpl[status][level]?this.obj.options.tpl[status][level]:this.obj.options.tpl[status][0];
		result = this.obj.format_data(tpl,data);
		result = result.replace('%level',level);
		return result;
	},
	//根据level初始化
	init_by_level: function(level){
		return this.data_to_tpl('',level,'init');
	},
	//初始化事件
	init_data_event: function(){
		this.obj.get_obj().data('super_level',this);
		this.obj.init_data();
	},
}

