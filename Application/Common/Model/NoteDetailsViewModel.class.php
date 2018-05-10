<?php
namespace Common\Model;

use Think\Model\ViewModel;

/**
 * 笔记带用户模型
 */
class NoteDetailsViewModel extends ViewModel
{
    /**
     * 模型字段定义
     * @var array
     */
    public $viewFields = array(
        'note' => array(
            '*',
            '_as' => 'a',
            '_type' => 'LEFT'
        ),
        'note_category' => array(
            'title'=>'category_title',
            '_on' => 'a.category_id=note_category.id',
        )
    );
}