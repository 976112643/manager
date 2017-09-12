<?php
namespace Backend\Controller\Service;
use Backend\Controller\Base\AdminController;
/**
 * 美容保养主类
 * @author  胡尧
 * @time    2016.11.09
 */
class IndexController extends AdminController {
	/**
	 * 生成Html
	 * @param  [type]  $list     [查询的数据]
	 * @param  integer $id       [主ID]
	 * @param  string  $name     [表单name]
	 * @param  string  $class    [表单class]
	 * @param  string  $disabled [是否显示]
	 * @return [type]            [description]
	 */
	public function html($list,$id = 0, $name = 's_type', $class = 'form-control select2 ', $disabled = '') {
		$list = list_to_tree($list);
		$html = '<select name="'.$name.'" class="'.$class.'" datatype="n" errormsg="请选择分类">';
		$html .= '<option value="">|-- 请选择分类</option>';
		foreach($list as $value) {
			$selected = $value['id'] == $id ? ' selected="selected"' : '';
			$html .= '<option value="'.$value['id'].'"'.$selected.$disabled.'>|-- '.$value['title'].'</option>';
			foreach($value['_child'] as $v) {
				$selected = $v['id'] == $id ? ' selected="selected"' : '';
				$html .= '<option value="'.$v['id'].'"'.$selected.$disabled.'>|--|-- '.$v['title'].'</option>';
				foreach($v['_child'] as $row) {
					$selected = $row['id'] == $id ? ' selected="selected"' : '';
					$html .= '<option value="'.$row['id'].'"'.$selected.'>|--|--|-- '.$row['title'].'</option>';
				}
			}
		}
		$html .= '</select>';
		return $html;
	}
}