<?php
/**
 * ECJIA 商品类型管理程序
*/
defined('IN_ECJIA') or exit('No permission resources.');

class admin_goods_type extends ecjia_admin {
	private $db_attribute;
	private $db_attribute_view;
	private $db_goods_type;
	private $db_goods_attr;
	
	public function __construct() {
		parent::__construct();

		$this->db_attribute = RC_Model::model('goods/attribute_model');
		$this->db_goods_type = RC_Model::model('goods/goods_type_model');
		$this->db_goods_attr = RC_Model::model('goods/goods_attr_model');
		$this->db_attribute_view = RC_Model::model('goods/attribute_goods_viewmodel');
		
		RC_Loader::load_app_func('goods');
		RC_Loader::load_app_func('functions');
		
		RC_Script::enqueue_script('jquery-validate');
		RC_Script::enqueue_script('jquery-form');
		RC_Script::enqueue_script('smoke');
		RC_Script::enqueue_script('jquery-chosen');
		RC_Style::enqueue_style('chosen');
		RC_Style::enqueue_style('uniform-aristo');
		RC_Script::enqueue_script('jquery-uniform');
		
		RC_Script::enqueue_script('goods_attribute', RC_App::apps_url('statics/js/goods_attribute.js', __FILE__) , array() , false, true);
		RC_Script::enqueue_script('adsense-bootstrap-editable-script', RC_Uri::admin_url() . '/statics/lib/x-editable/bootstrap-editable/js/bootstrap-editable.min.js', array(), false, true);
		RC_Style::enqueue_style('adsense-bootstrap-editable-style', RC_Uri::admin_url() . '/statics/lib/x-editable/bootstrap-editable/css/bootstrap-editable.css');
		
		RC_Script::localize_script('goods_attribute', 'js_lang', RC_Lang::get('goods::goods.js_lang'));
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::get('goods::goods_type.goods_type_list'), RC_Uri::url('goods/admin_goods_type/init')));
	}
	
	/**
	 * 管理界面
	 */
	public function init() {
		$this->admin_priv('attr_manage',ecjia::MSGTYPE_JSON);
		
		ecjia_screen::get_current_screen()->remove_last_nav_here();
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::get('goods::goods_type.goods_type_list')));
		ecjia_screen::get_current_screen()->add_help_tab(array(
			'id'		=> 'overview',
			'title'		=> RC_Lang::get('goods::goods_type.overview'),
			'content'	=> '<p>' . RC_Lang::get('goods::goods_type.goods_type_help') . '</p>'
		));
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
			'<p><strong>' . RC_Lang::get('goods::goods_type.more_info') . '</strong></p>' .
			'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:商品类型#.E5.95.86.E5.93.81.E7.B1.BB.E5.9E.8B.E5.88.97.E8.A1.A8" target="_blank">'. RC_Lang::get('goods::goods_type.about_goods_type') .'</a>') . '</p>'
		);
		
		$type = !empty($_GET['type']) ? $_GET['type'] : '';
 
		$good_type_arr = get_goods_type();
		$good_in_type = '';
		$data = $this->db_attribute_view->join('goods_attr')->group('a.cat_id')->select();
		 
		if (!empty($data)) {
			foreach ($data as $key => $row) {
				$good_in_type[$row['cat_id']] = 1;
			}
		}
		
		$this->assign('good_in_type', 		$good_in_type);
		$this->assign('goods_type_arr',   	$good_type_arr);
		$this->assign('ur_here',          	RC_Lang::get('goods::goods_type.goods_type_list'));
		$this->assign('action_link',      	array('text' => RC_Lang::get('goods::goods_type.add_goods_type'), 'href' => RC_Uri::url('goods/admin_goods_type/add')));
		
		$this->display('goods_type_list.dwt');
	}
	
	/**
	 * 添加商品类型
	 */
	public function add() {
		$this->admin_priv('goods_type_update',ecjia::MSGTYPE_JSON);
		
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::get('goods::goods_type.add_goods_type')));
		ecjia_screen::get_current_screen()->add_help_tab(array(
		'id'		=> 'overview',
		'title'		=> __('概述'),
		'content'	=>
		'<p>' . __('欢迎访问ECJia智能后台添加商品类型页面，可以在此页面添加商品类型信息。') . '</p>'
		));
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
		'<p><strong>' . __('更多信息:') . '</strong></p>' .
		'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:商品类型#.E6.B7.BB.E5.8A.A0.E5.95.86.E5.93.81.E7.B1.BB.E5.9E.8B" target="_blank">关于添加商品类型帮助文档</a>') . '</p>'
		);
		
		
		$this->assign('ur_here', RC_Lang::get('goods::goods_type.add_goods_type'));
		$this->assign('action_link', array('href'=>	RC_Uri::url('goods/admin_goods_type/init'), 'text' => RC_Lang::get('goods::goods_type.goods_type_list')));
		$this->assign('action', 'add');
		$this->assign('goods_type', array('enabled' => 1));
		$this->assign('form_action',  RC_Uri::url('goods/admin_goods_type/insert'));

		$this->display('goods_type_info.dwt');
	}
		
	public function insert() {
		$this->admin_priv('goods_type_update', ecjia::MSGTYPE_JSON);

		$goods_type['cat_name']		= RC_String::sub_str($_POST['cat_name'], 60);
		$goods_type['attr_group']	= RC_String::sub_str($_POST['attr_group'], 255);
		
		$goods_type['enabled']		= intval($_POST['enabled']);
		$count = $this->db_goods_type->where(array('cat_name' => $goods_type['cat_name']))->count();
		if ($count > 0 ){
			$this->showmessage(RC_Lang::get('goods::goods_type.repeat_type_name'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		} else {
			$cat_id = $this->db_goods_type->insert($goods_type);
			if ($cat_id) {
				$links = array(array('href' => RC_Uri::url('goods/admin_goods_type/init'), 'text' => RC_Lang::get('goods::goods_type.back_list')), array('href' => RC_Uri::url('goods/admin_goods_type/add'), 'text' => RC_Lang::get('goods::goods_type.continue_add')));
				$this->showmessage(RC_Lang::get('goods::goods_type.add_goodstype_success'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('goods/admin_goods_type/edit', 'cat_id='.$cat_id), 'links' => $links));
			} else {
				$this->showmessage(RC_Lang::get('goods::goods_type.add_goodstype_failed'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
		}
	}
	
	/**
	 * 编辑商品类型
	 */
	public function edit() {
		$this->admin_priv('goods_type_update', ecjia::MSGTYPE_JSON);
		
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::get('goods::goods_type.edit_goods_type')));
		$goods_type = get_goods_type_info(intval($_GET['cat_id']));
		if (empty($goods_type)) {
			$this->showmessage(RC_Lang::get('goods::goods_type.cannot_found_goodstype'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		ecjia_screen::get_current_screen()->add_help_tab(array(
			'id'		=> 'overview',
			'title'		=> RC_Lang::get('goods::goods_type.overview'),
			'content'	=> '<p>' . RC_Lang::get('goods::goods_type.edit_type_help') . '</p>'
		));
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
			'<p><strong>' . RC_Lang::get('goods::goods_type.more_info') . '</strong></p>' .
			'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:商品类型#.E7.BC.96.E8.BE.91.E5.95.86.E5.93.81.E7.B1.BB.E5.9E.8B" target="_blank">'. RC_Lang::get('goods::goods_type.about_edit_type') .'</a>') . '</p>'
		);
	
		$this->assign('ur_here', RC_Lang::get('goods::goods_type.edit_goods_type'));
		$this->assign('action_link', array('href'=>RC_Uri::url('goods/admin_goods_type/init'), 'text' => RC_Lang::get('goods::goods_type.goods_type_list')));
		$this->assign('goods_type', $goods_type);
		$this->assign('form_action', RC_Uri::url('goods/admin_goods_type/update'));
		
		$this->display('goods_type_info.dwt');
	}
	
	
	public function update() {
		$this->admin_priv('goods_type_update', ecjia::MSGTYPE_JSON);
		
		$goods_type['cat_name']		= RC_String::sub_str($_POST['cat_name'], 60);
		$goods_type['attr_group']	= RC_String::sub_str($_POST['attr_group'], 255);
		$goods_type['enabled']		= intval($_POST['enabled']);
		$cat_id						= intval($_POST['cat_id']);
		$old_groups					= get_attr_groups($cat_id);
		$count 						= $this->db_goods_type->where(array('cat_name' => $goods_type['cat_name'], 'cat_id' => array('neq' => $cat_id)))->count();
		
		if (empty($count)) {
			if ($this->db_goods_type->where(array('cat_id' => $cat_id))->update($goods_type)) {
				/* 对比原来的分组 */
				$new_groups = explode("\n", str_replace("\r", '', $goods_type['attr_group']));  // 新的分组
				if (!empty($old_groups)) {
					foreach ($old_groups AS $key=>$val) {
						$found = array_search($val, $new_groups);
						if ($found === NULL || $found === false) {
							/* 老的分组没有在新的分组中找到 */
							update_attribute_group($cat_id, $key, 0);
						} else {
							/* 老的分组出现在新的分组中了 */
							if ($key != $found) {
								update_attribute_group($cat_id , $key , $found); // 但是分组的key变了,需要更新属性的分组
							}
						}
					}
				}
				$this->showmessage(RC_Lang::get('goods::goods_type.edit_goodstype_success'),ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('goods/admin_goods_type/edit', 'cat_id='.$cat_id)));
			}
		} else {
			$this->showmessage(RC_Lang::get('goods::goods_type.repeat_type_name'),ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}
	
	/**
	 * 删除商品类型
	 */
	public function remove() {
		$this->admin_priv('goods_type_delete', ecjia::MSGTYPE_JSON);
		
		
		$id = intval($_GET['id']);
		$name = $this->db_goods_type->where(array('cat_id' => $id))->get_field('cat_name');
		if ($this->db_goods_type->where(array('cat_id' => $id))->delete()) {
			ecjia_admin::admin_log(addslashes($name), 'remove', 'goods_type');
			/* 清除该类型下的所有属性 */
			$arr = $this->db_attribute->field('attr_id')->find(array('cat_id' => $id));
			if (!empty($arr)) {
				$this->db_attribute->in(array('attr_id' => $arr))->delete();
				$this->db_goods_attr->in(array('attr_id' => $arr))->delete();
			}
			$this->showmessage(RC_Lang::get('goods::goods_type.remove_success'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
		} else {
			$this->showmessage(RC_Lang::get('goods::goods_type.remove_failed'),  ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => RC_Lang::lang('remove_failed')));
		}
	}

	/**
	 * 修改商品类型名称
	 */
	public function edit_type_name() {
		$this->admin_priv('goods_type_update', ecjia::MSGTYPE_JSON);
		$type_id   = !empty($_POST['pk'])  ? intval($_POST['pk']) : 0;
		$type_name = !empty($_POST['value']) ? trim($_POST['value'])  : '';

		/* 检查名称是否重复 */
		if(!empty($type_name)) {
			$is_only = $this->db_goods_type->where(array('cat_name' => $type_name))->count();
			if ($is_only == 0) {
				$this->db_goods_type->where(array('cat_id' => $type_id))->update(array('cat_name' => $type_name));
				ecjia_admin::admin_log($type_name, 'edit', 'goods_type');
				$this->showmessage(RC_Lang::get('goods::goods_type.edit_success'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => stripslashes($type_name)));
			} else {
				$this->showmessage(RC_Lang::get('goods::goods_type.repeat_type_name'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
			
		} else {
			$this->showmessage(RC_Lang::get('goods::goods_type.type_name_empty'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}
	
	/**
	 * 切换启用状态
	 */
	public function toggle_enabled() {
		$this->admin_priv('goods_type', ecjia::MSGTYPE_JSON);
		

		$id		= intval($_POST['id']);				
		$val    = intval($_POST['val']);
		$data 	= array('enabled' => $val);
		
		$this->db_goods_type->where(array('cat_id' => $id))->update($data);
		$this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => $val));
	}
}
