//构造函数
function Super_tree(data,options){
	if(!this instanceof Super_tree){
		return new Super_tree(data,options);
	}
	
	this.options = Super_tree.defaults;
	//基础数据
	this.data = (typeof data == 'object') ? data : {};
	this.init_tree();
	return this;
};

//默认配置
Super_tree.defaults = {
	//生成的树
	tree: {
		//层级索引，初始0级
		level: [
			{
				ids:[0],
				count:1,
				_child:[]
			}
		],
		//节点数据，默认挂载ID为0的父节点
		data: {
			level: 0,
			data: {
				id: '0'
			},
			_child:{}
		}
	},
}
//原型定义
Super_tree.prototype = {
	//获取树
	get_tree: function(){
		return this.options.tree;
	},
	//循环初始化树
	init_tree: function(p_node){
		if(!p_node){
			this.get_tree().level[0]._child.push(this.get_tree().data);
		}
		//默认从根节点开始
		p_node = p_node || this.get_tree().data;
		if(!p_node.data || !this.data) return false;
		//挂载所有子节点
		this.set_node(p_node,this.data);
		//递归挂载所有子节点的子节点
		for(t in p_node._child){
			if(!this.data) return false;
			arguments.callee(p_node._child[t]);
		}
	},
	//根据父节点，自动寻找子节点并挂载
	set_node: function(p_node,data){
		if(!p_node.data) return false;
		for(d in data){
			if(data[d].pid == p_node.data.id){
				this.add_node(p_node,data[d]);
				//挂载后删除数据，减少遍历次数
				delete data[d];
			}
			else{
				temp_node = this.search_in_tree(data[d].pid);
				if(temp_node){
					this.add_node(temp_node,data[d]);
					//挂载后删除数据，减少遍历次数
					delete data[d];
					
				}
			}
		}
	},
	//将data挂在p_node上
	add_node: function(p_node,data){
		var node = {
			level: 0,
			data: data,
			_child:{}
		}
		var level = p_node.level+1, id = node.data.id;
		var tree_level = this.get_level(level);
		node.level = level;
		p_node._child[id] = node;
		tree_level.ids.push(id);
		tree_level.count++;
		tree_level._child[id] = node;
		
	},
	//获取层级（没有就自动生成）
	get_level: function(level){
		if(!level){
			return this.get_tree().level;
		}
		if(!this.get_tree().level[level]){
			this.get_tree().level.push({ids:[],count:0,_child:[]});
		}
		return this.get_tree().level[level];
	},
	//根据层级获取数据
	get_level_data: function(level){
		var tree_level = this.get_level(level);
		return tree_level._child[id];
	},
	//根据ID在树内搜索
	search_in_tree: function(id){
		var level = this.get_tree().level;
		//按层级遍历
		for(t in level){
			if(level[t].ids.indexOf(id)>-1){
				return level[t]._child[id];
			}
		}
		console.log(id);
		console.log(level[0].ids.indexOf(0));
		return false;
	}
}
