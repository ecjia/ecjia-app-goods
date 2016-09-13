<?php
/**
 * ECJIA 属性规格管理
 */
defined('IN_ECJIA') or exit('No permission resources.');

class admin_attribute extends ecjia_admin {
	private $db_attribute;
	private $db_goods_type;
	private $db_goods_attr;
	
	public function __construct() {
		parent::__construct();

		RC_Loader::load_app_func('goods');
		RC_Loader::load_app_func('functions');
		
		RC_Script::enqueue_script('jquery-validate');
		RC_Script::enqueue_script('jquery-form');
		RC_Script::enqueue_script('smoke');
		RC_Script::enqueue_script('jquery-uniform');
		RC_Style::enqueue_style('uniform-aristo');
		RC_Script::enqueue_script('jquery-chosen');
		RC_Style::enqueue_style('chosen');
		
		RC_Script::enqueue_script('bootstrap-editable-script', RC_Uri::admin_url('statics/lib/x-editable/bootstrap-editable/js/bootstrap-editable.min.js'), array(), false, true);
        RC_Style::enqueue_style('bootstrap-editable-css', RC_Uri::admin_url('statics/lib/x-editable/bootstrap-editable/css/bootstrap-editable.css'));
		RC_Script::enqueue_script('goods_attribute', RC_App::apps_url('statics/js/goods_attribute.js', __FILE__), array(), false, true);

		$this->db_goods_type = RC_Model::model('goods/goods_type_model');
        $this->db_attribute  = RC_Model::model('goods/attribute_model');
        $this->db_goods_attr = RC_Model::model('goods/goods_attr_model');

         ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::get('goods::goods_type.goods_type_list'), RC_Uri::url('goods/admin_goods_type/init')));
	}

	/**
	 * 属性列表
	 */
	public function init() {
		$this->admin_priv('attr_manage', ecjia::MSGTYPE_JSON);
		
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::get('goods::attribute.goods_attribute')));
		ecjia_screen::get_current_screen()->add_help_tab(array(
			'id'		=> 'overview',
			'title'		=> RC_Lang::get('goods::attribute.overview'),
			'content'	=> '<p>' . RC_Lang::get('goods::attribute.goods_attribute_help') . '</p>'
		));
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
			'<p><strong>' . RC_Lang::get('goods::attribute.more_info') . '</strong></p>' .
			'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:商品类型#.E5.B1.9E.E6.80.A7.E5.88.97.E8.A1.A8" target="_blank">'. RC_Lang::get('goods::attribute.about_goods_attribute') .'</a>') . '</p>'
		);
		
		$goods_type	= isset($_GET['cat_id']) ? intval($_GET['cat_id']) : 0;
		$attr_list	= get_attr_list();
		
		$this->assign('ur_here', RC_Lang::get('goods::attribute.goods_attribute'));
		$this->assign('action_link', array('href' => RC_Uri::url('goods/admin_attribute/add', array('cat_id' => $goods_type)), 'text' => RC_Lang::get('system::system.10_attribute_add')));
		$this->assign('action_link2', array('text' => RC_Lang::get('goods::goods_type.goods_type_list'), 'href' => RC_Uri::url('goods/admin_goods_type/init')));
		
		$this->assign('goods_type_list', goods_type_list($goods_type));
		$this->assign('attr_list', $attr_list);
		$this->assign('cat_id', $goods_type);
		$this->assign('form_action', RC_Uri::url('goods/admin_attribute/batch'));
		
		$this->display('attribute_list.dwt');		
	}
	
	/**
	 * 添加/编辑属性
	 */
	public function add() {
		$this->admin_priv('attr_update', ecjia::MSGTYPE_JSON);

		/* 取得属性信息 */
		$cat_id = isset($_GET['cat_id']) ? intval($_GET['cat_id']) : 0;
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::get('goods::attribute.goods_attribute'), RC_Uri::url('goods/admin_attribute/init', 'cat_id='.$cat_id)));
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::get('goods::attribute.add_attribute')));
	
		ecjia_screen::get_current_screen()->add_help_tab(array(
			'id'		=> 'overview',
			'title'		=> RC_Lang::get('goods::attribute.overview'),
			'content'	=> '<p>' . RC_Lang::get('goods::attribute.add_attribute_help') . '</p>'
		));
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
			'<p><strong>' . RC_Lang::get('goods::attribute.more_info') . '</strong></p>' .
			'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:商品类型#.E6.B7.BB.E5.8A.A0.E5.B1.9E.E6.80.A7" target="_blank">'. RC_Lang::get('goods::attribute.about_add_attribute') .'</a>') . '</p>'
		);
		
		/* 取得商品分类列表 */
		$this->assign('goods_type_list', goods_type_list($cat_id));
		$this->assign('ur_here', RC_Lang::get('goods::attribute.add_attribute'));
		$this->assign('action_link', array('href' => RC_Uri::url('goods/admin_attribute/init', 'cat_id='.$cat_id), 'text' => RC_Lang::get('goods::attribute.goods_attribute')));
		$this->assign('form_action', RC_Uri::url('goods/admin_attribute/insert'));
	
		$this->display('attribute_info.dwt');
	}

	/**
	 * 插入/更新属性
	 */
	public function insert() {
		$this->admin_priv('attr_update', ecjia::MSGTYPE_JSON);
	
		/* 检查该类型下名称是否重复 */
		$cat_id = isset($_POST['cat_id']) ? intval($_POST['cat_id']) : 0;
		
		if (empty($cat_id)) {
			$this->showmessage(RC_Lang::get('goods::attribute.cat_not_empty'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		$count = $this->db_attribute->where(array('attr_name' => $_POST['attr_name'], 'cat_id' => $cat_id ))->count();
		if ($count) {
			$this->showmessage(RC_Lang::get('goods::attribute.name_exist'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 取得属性信息 */
		$attr = array(
			'cat_id'			=> $_POST['cat_id'],
			'attr_name'			=> $_POST['attr_name'],
			'attr_index'		=> $_POST['attr_index'],
			'attr_input_type'	=> $_POST['attr_input_type'],
			'is_linked'			=> $_POST['is_linked'],
			'attr_values'       => isset($_POST['attr_values']) ? $_POST['attr_values'] : '',
			'attr_type'			=> empty($_POST['attr_type']) ? '0' : intval($_POST['attr_type']),
			'attr_group'		=> isset($_POST['attr_group']) ? intval($_POST['attr_group']) : 0
		);
		
		/* 入库、记录日志、提示信息 */
		$attr_id = $this->db_attribute->insert($attr);
		
		if ($attr_id) {
			ecjia_admin::admin_log($_POST['attr_name'], 'add', 'attribute');
			$links = array(
				array('text' => RC_Lang::get('goods::attribute.back_list'), 'href' => RC_Uri::url('goods/admin_attribute/init', array('cat_id' => $cat_id))),
				array('text' => RC_Lang::get('goods::attribute.add_next'), 'href' => RC_Uri::url('goods/admin_attribute/add', array('cat_id' => $cat_id))),
			);
			$this->showmessage(sprintf(RC_Lang::get('goods::attribute.add_ok'), $attr['attr_name']), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('goods/admin_attribute/edit', array('attr_id' => $attr_id)), 'links' => $links));
		}
	}

	/**
	 * 添加/编辑属性
	 */
	public function edit() {
		$this->admin_priv('attr_update', ecjia::MSGTYPE_JSON);
		
		$is_add = !empty($_GET['act']) ? 'add' : '';
		$this->assign('form_act', $is_add ? 'insert' : 'update');
		/* 取得属性信息 */
		if ($is_add) {
			$goods_type = isset($_GET['goods_type']) ? intval($_GET['goods_type']) : 0;
			$attr = array(
				'attr_id' 			=> 0,
				'cat_id' 			=> $goods_type,
				'attr_name' 		=> '',
				'attr_input_type'	=> 0,
				'attr_index'  		=> 0,
				'attr_values' 		=> '',
				'attr_type' 		=> 0,
				'is_linked' 		=> 0,
			);
		} else {
			$attr = $this->db_attribute->find(array('attr_id' => $_GET['attr_id']));
		}
		$this->assign('attr', $attr);
		$this->assign('attr_groups', get_attr_groups($attr['cat_id']));
		/* 取得商品分类列表 */
		$this->assign('goods_type_list', goods_type_list($attr['cat_id']));
	
		/* 模板赋值 */
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__($is_add ? RC_Lang::get('system::system.10_attribute_add') : RC_Lang::get('system::system.52_attribute_add'))));
		$this->assign('ur_here', $is_add ? RC_Lang::get('system::system.10_attribute_add') : RC_Lang::get('system::system.52_attribute_add'));
		$this->assign('action_link', array('href' => RC_Uri::url('goods/admin_attribute/init', array('cat_id' => $goods_type)), 'text' => RC_Lang::get('goods::attribute.goods_attribute')));
		$this->assign('form_action', RC_Uri::url('goods/admin_attribute/update'));
		
		ecjia_screen::get_current_screen()->add_help_tab(array(
			'id'		=> 'overview',
			'title'		=> RC_Lang::get('goods::attribute.overview'),
			'content'	=> '<p>' . RC_Lang::get('goods::attribute.edit_attribute_help') . '</p>'
		));
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
			'<p><strong>' . RC_Lang::get('goods::attribute.more_info') . '</strong></p>' .
			'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:商品类型#.E7.BC.96.E8.BE.91.E5.B1.9E.E6.80.A7" target="_blank">'. RC_Lang::get('goods::attribute.about_edit_attribute') .'</a>') . '</p>'
		);
		
		$this->display('attribute_info.dwt');
	}
	
	/**
	 * 插入/更新属性
	 */
	public function update() {
		$this->admin_priv('attr_update', ecjia::MSGTYPE_JSON);
	
		$cat_id = isset($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;
		$attr_id = isset($_POST['attr_id']) ? intval($_POST['attr_id']) : 0;
		/* 检查名称是否重复 */
		if ($this->db_attribute->attribute_count(array('cat_id' => $cat_id, 'attr_name' => $_POST['attr_name'], 'attr_id' => array('neq' => $_POST['attr_id'])))) {
			$this->showmessage(RC_Lang::get('goods::attribute.name_exist'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
			
		/* 取得属性信息 */
		$attr = array(
			'cat_id'			=> $cat_id,
			'attr_name'			=> $_POST['attr_name'],
			'attr_index'		=> $_POST['attr_index'],
			'attr_input_type'	=> $_POST['attr_input_type'],
			'is_linked'			=> $_POST['is_linked'],
			'attr_values'       => isset($_POST['attr_values']) ? $_POST['attr_values'] : '',
			'attr_type'			=> empty($_POST['attr_type']) ? '0' : intval($_POST['attr_type']),
			'attr_group'		=> isset($_POST['attr_group']) ? intval($_POST['attr_group']) : 0
		);
	
		/* 入库、记录日志、提示信息 */
		$this->db_attribute->attribute_manage($attr);
		ecjia_admin::admin_log($_POST['attr_name'], 'edit', 'attribute');
		
		$links = array(
			array('text' => RC_Lang::get('goods::attribute.back_list'), 'href' => RC_Uri::url('goods/admin_attribute/init', array('cat_id' => $cat_id))),
		);
		$this->showmessage(sprintf(RC_Lang::get('goods::attribute.edit_ok'), $attr['attr_name']), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('links' => $links, 'pjaxurl' => RC_Uri::url('goods/admin_attribute/edit', array('attr_id' => $attr_id))));
	}
	
	/**
	 * 删除商品属性
	 */
	public function remove() {
		$this->admin_priv('attr_delete', ecjia::MSGTYPE_JSON);
		

		$id = intval($_GET['id']);
		$this->db_attribute->where(array('attr_id' => $id))->delete();
		$this->db_goods_attr->where(array('attr_id' => $id))->delete();
		$this->showmessage(RC_Lang::get('goods::attribute.drop_success'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
	}

	/**
	 * 删除属性(一个或多个)
	 */
	public function batch() {
		$this->admin_priv('attr_delete', ecjia::MSGTYPE_JSON);
		
		/* 取得要操作的编号 */
		if (isset($_POST['checkboxes'])) {
			$count = count(explode(',', $_POST['checkboxes']));
			$ids	= isset($_POST['checkboxes']) ? $_POST['checkboxes'] : 0;
			$this->db_attribute->in(array('attr_id' => $ids))->delete();
			$this->db_goods_attr->in(array('attr_id' => $ids))->delete();
			/* 记录日志 */
			ecjia_admin::admin_log('', 'batch_remove', 'attribute');
			$this->showmessage(sprintf(RC_Lang::get('goods::attribute.drop_ok'), $count), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('goods/admin_attribute/init')));
		}
	}
	
	/**
	 * 编辑属性名称
	 */
	public function edit_attr_name() {
		$this->admin_priv('attr_update', ecjia::MSGTYPE_JSON);
	
		$id = intval($_POST['pk']);
		$val = trim($_POST['value']);
	
		/* 取得该属性所属商品属性id */
		$cat_id= $this->db_attribute->where(array('attr_id' => $id))->get_field('cat_id');
		/* 检查名称是否重复 */
		if (!empty($val)) {
			if ($val != $attr['attr_name']) {
				if ($this->db_attribute->attribute_count(array('attr_name' => $val, 'cat_id' => $attr['cat_id'])) != 0) {
					$this->showmessage(RC_Lang::get('goods::attribute.name_exist'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
				}
			}
			$data = array(
				'attr_id' 	=> $id,
				'attr_name' => $val
			);
			if ($this->db_attribute->attribute_manage($data)) {
				ecjia_admin::admin_log($val, 'edit', 'attribute');
				$this->showmessage(RC_Lang::get('goods::attribute.edit_success'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => $val));
			}
		} else {
			$this->showmessage(RC_Lang::get('goods::attribute.name_not_null'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}
	
	/**
	 * 编辑排序序号
	 */
	public function edit_sort_order() {
		$this->admin_priv('attr_update', ecjia::MSGTYPE_JSON);
	
		$id = intval($_POST['pk']);
		$val = trim($_POST['value']);
	
		/* 验证参数有效性 */
		if (!is_numeric($val) || $val < 0 || strpos($val, '.') > 0) {
			$this->showmessage(RC_Lang::get('goods::attribute.format_error'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	
		$data = array(
			'attr_id' 		=> $id,
			'sort_order' 	=> $val
		);
		if ($this->db_attribute->attribute_manage($data)) {
			$this->showmessage(RC_Lang::get('goods::attribute.edit_success'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => $val));
		}
	}
}

// end