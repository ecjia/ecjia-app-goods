<?php
/**
 * ECJIA 商品类型管理程序
*/

defined('IN_ROYALCMS') or exit('No permission resources.');
RC_Loader::load_sys_class('ecjia_admin', false);

class admin_goods_type extends ecjia_admin 
{
	private $db_attribute;
	private $db_attribute_view;
	private $db_goods_type;
	private $db_goods_attr;
	
	public function __construct() 
	{
		parent::__construct();
// 		$this->addLang('goods_type');
		RC_Lang::load('goods_type');
		RC_Loader::load_app_func('goods');
		RC_Loader::load_app_func('functions');
		
		RC_Script::enqueue_script('jquery-validate');
		RC_Script::enqueue_script('jquery-form');
		RC_Script::enqueue_script('smoke');

		/* chosen */
		RC_Script::enqueue_script('jquery-chosen');
		RC_Style::enqueue_style('chosen');
		
		RC_Script::enqueue_script('goods_attribute' , RC_App::apps_url('statics/js/goods_attribute.js' , __FILE__) , array() , false, true);
		RC_Script::enqueue_script('adsense-bootstrap-editable-script', RC_Uri::admin_url() . '/statics/lib/x-editable/bootstrap-editable/js/bootstrap-editable.min.js', array(), false, true);
		RC_Style::enqueue_style('adsense-bootstrap-editable-style', RC_Uri::admin_url() . '/statics/lib/x-editable/bootstrap-editable/css/bootstrap-editable.css');
		
		RC_Style::enqueue_style('uniform-aristo');
		RC_Script::enqueue_script('jquery-uniform');
		
		$this->db_attribute = RC_Loader::load_app_model('attribute_model');
		$this->db_goods_type = RC_Loader::load_app_model('goods_type_model');
		$this->db_goods_attr = RC_Loader::load_app_model('goods_attr_model');
		$this->db_attribute_view = RC_Loader::load_app_model('attribute_goods_viewmodel');		
	}
	
	/**
	 * 管理界面
	 */
	public function init() 
	{
		$this->admin_priv('attr_manage');
		
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商品类型列表')));
		$url_here = __('商品类型列表');
		$type = $_GET['type'];

		$good_type_arr = get_goods_type();
		$good_in_type = '';
		$data = $this->db_attribute_view->join('goods_attr')->group('a.cat_id')->select();
		 
		foreach ($data as $key => $row) {
			$good_in_type[$row['cat_id']] = 1;
		}
	
		ecjia_screen::get_current_screen()->add_help_tab( array(
		'id'		=> 'overview',
		'title'		=> __('概述'),
		'content'	=>
		'<p>' . __('欢迎访问ECJia智能后台商品类型列表页面，系统中所有的商品类型都会显示在此列表中。') . '</p>'
		) );
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
		'<p><strong>' . __('更多信息:') . '</strong></p>' .
		'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:商品类型" target="_blank">关于商品类型帮助文档</a>') . '</p>'
		);
		
		$this->assign('good_in_type', 		$good_in_type);
		$this->assign('goods_type_arr',   	$good_type_arr);
		$this->assign('ur_here',          	$url_here);
		$this->assign('action_link',      	array('text' => __('添加商品类型'), 'href' => RC_Uri::url('goods/admin_goods_type/add')));
		$this->assign_lang();
		$this->display('goods_type_list.dwt');
	    
// 		global $ecs, $db, $_CFG, $sess;
// 		$goods_type = get_goods_type_info(intval($_GET['cat_id']));
// 		$exc = new exchange($ecs->table("goods_type"), $db, 'cat_id', 'cat_name');
// 		$this->assign('goods_type',  		$goods_type);
// 		$this->assign('goods_type_arr',   $good_type_list['type']);
// 		$this->assign('filter',       $good_type_list['filter']);
// 		$this->assign('record_count', $good_type_list['record_count']);
// 		$this->assign('page_count',   $good_type_list['page_count']);
// 		$query = $db->query("SELECT a.cat_id FROM " . $ecs->table('attribute') . " AS a RIGHT JOIN " . $ecs->table('goods_attr') . " AS ga ON ga.attr_id = a.attr_id GROUP BY a.cat_id");
//     	while ($row = $db->fetchRow($query))
//		$this->assign('action_link',      array('text' => RC_Lang::lang('new_goods_type'), 'href' => 'index.php?m=goods&c=admin_goods_type&a=add'));
	}
	
// 	/**
// 	 * 获得列表
// 	 */
// 	public function query() {
// 		$good_type_list = get_goodstype();
// 		$this->assign('goods_type_arr',   $good_type_list['type']);
// 		$this->assign('filter',       $good_type_list['filter']);
// 		$this->assign('record_count', $good_type_list['record_count']);
// 		$this->assign('page_count',   $good_type_list['page_count']);
		
// 		make_json_result($this->fetch('goods_type'), '',
// 		array('filter' => $good_type_list['filter'], 'page_count' => $good_type_list['page_count']));
		
// 	}
	
	/**
	 * 添加商品类型
	 */
	public function add() 
	{
// 		global $ecs, $db, $_CFG, $sess;
		$this->admin_priv('goods_type');
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商品类型列表') , RC_Uri::url('goods/admin_goods_type/init')));
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('新建商品类型')));
		$this->assign('ur_here',    RC_Lang::lang('new_goods_type'));
		$this->assign('action_link', array('href'=>	RC_Uri::url('goods/admin_goods_type/init'), 'text' => RC_Lang::lang('goods_type_list')));
		$this->assign('action',      'add');
		$this->assign('goods_type',  array('enabled' => 1));
		$this->assign('form_action',   RC_Uri::url('goods/admin_goods_type/insert'));

		ecjia_screen::get_current_screen()->add_help_tab( array(
		'id'		=> 'overview',
		'title'		=> __('概述'),
		'content'	=>
		'<p>' . __('欢迎访问ECJia智能后台添加商品类型页面，可以在此页面添加商品类型信息。') . '</p>'
		) );
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
		'<p><strong>' . __('更多信息:') . '</strong></p>' .
		'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:商品类型#.E6.B7.BB.E5.8A.A0.E5.95.86.E5.93.81.E7.B1.BB.E5.9E.8B" target="_blank">关于添加商品类型帮助文档</a>') . '</p>'
		);
		
		$this->assign_lang();
		$this->display('goods_type_info.dwt');
	}
		
	public function insert() 
	{
		$this->admin_priv('goods_type', ecjia::MSGTYPE_JSON);

		/* 对添加商品类型进行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对添加商品类型进行权限检查  BY：MaLiuWei  END */
		$goods_type['cat_name']		= RC_String::sub_str($_POST['cat_name'] , 60);
		$goods_type['attr_group']	= RC_String::sub_str($_POST['attr_group'] , 255);
		
		$goods_type['enabled']		= intval($_POST['enabled']);
		$count = $this->db_goods_type->where(array('cat_name' => $goods_type['cat_name']))->count();
		if ($count > 0 ){
			$this->showmessage('商品类型已存在' , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		} else {
			$cat_id = $this->db_goods_type->insert($goods_type);
			if ($cat_id) {
				$links = array(array('href' => RC_Uri::url('goods/admin_goods_type/init'), 'text' => __('返回商品类型列表')),array('href' => RC_Uri::url('goods/admin_goods_type/add'), 'text' => __('继续添加商品类型')));
				$this->showmessage(__('商品类型[' . $goods_type['cat_name'] . ']添加成功！') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS , array('pjaxurl' => RC_Uri::url('goods/admin_goods_type/edit' , 'cat_id='.$cat_id), 'links' => $links));
			} else {
				$this->showmessage(RC_Lang::lang('add_goodstype_failed') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
		}
				
// 		$goods_type['cat_name']   = RC_String::sub_str($_POST['cat_name'], 60);
// 		$goods_type['attr_group'] = RC_String::sub_str($_POST['attr_group'], 255);
// 		$goods_type['enabled']    = intval($_POST['enabled']);
// 		$count = $this->db_goods_type->where(array('cat_name' => $goods_type['cat_name']))->count();
// 		if ($count > 0 ){
// 			$this->showmessage('商品类型已存在', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
// 		} else {
// 			if ($this->db_goods_type->insert($goods_type) !== false) {
// 				$links = array(array('href' => RC_Uri::url('goods/admin_goods_type/init'), 'text' => __('返回商品类型列表')));
// 				$this->showmessage(__('添加商品类型成功！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('goods/admin_goods_type/edit'), 'links' => $links));
// 			} else {
// 				$this->showmessage(__('添加商品类型失败！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
// 			}
// 		}
// 		global $ecs, $db, $_CFG, $sess;
// 		$goods_type['cat_name']   = trim_right(sub_str($_POST['cat_name'], 60));
// 		$goods_type['attr_group'] = trim_right(sub_str($_POST['attr_group'], 255));
// 		if ($db->autoExecute($ecs->table('goods_type'), $goods_type) !== false) {
//		$links = array(array('href' => 'index.php?m=goods&c=admin_goods_type&a=manage', 'text' => RC_Lang::lang('back_list')));
// 		sys_msg($_LANG['add_goodstype_success'], 0, $links);
// 		sys_msg($_LANG['add_goodstype_failed'], 1);
	}
	
	/**
	 * 编辑商品类型
	 */
	public function edit() 
	{
		$this->admin_priv('goods_type', ecjia::MSGTYPE_JSON);
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商品类型列表') , RC_Uri::url('goods/admin_goods_type/init')));
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('编辑商品类型')));
		
		$goods_type = get_goods_type_info(intval($_GET['cat_id']));
		if (empty($goods_type)) {
			$this->showmessage(RC_Lang::lang('cannot_found_goodstype'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		ecjia_screen::get_current_screen()->add_help_tab( array(
		'id'		=> 'overview',
		'title'		=> __('概述'),
		'content'	=>
		'<p>' . __('欢迎访问ECJia智能后台编辑商品类型页面，可以在此页面编辑商品类型信息。') . '</p>'
				) );
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
		'<p><strong>' . __('更多信息:') . '</strong></p>' .
		'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:商品类型#.E7.BC.96.E8.BE.91.E5.95.86.E5.93.81.E7.B1.BB.E5.9E.8B" target="_blank">关于编辑商品类型帮助文档</a>') . '</p>'
				);
	
		$this->assign('ur_here',     RC_Lang::lang('edit_goods_type'));
		$this->assign('action_link', array('href'=>RC_Uri::url('goods/admin_goods_type/init'), 'text' => __('商品类型列表')));
		$this->assign('goods_type',  $goods_type);
		$this->assign('form_action',   RC_Uri::url('goods/admin_goods_type/update'));
		
		$this->assign_lang();
		$this->display('goods_type_info.dwt');
				
// 		global $ecs, $db, $_CFG, $sess;
// 		sys_msg($_LANG['cannot_found_goodstype'], 1);
//		$this->assign('action_link', array('href'=>'index.php?m=goods&c=admin_goods_type&a=manage', 'text' => RC_Lang::lang('goods_type_list')));
	}
	
	
	public function update() 
	{
		$this->admin_priv('goods_type', ecjia::MSGTYPE_JSON);
		
		/* 对编辑商品类型进行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对编辑商品类型进行权限检查  BY：MaLiuWei  END */
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
				$this->showmessage(__('商品类型')."[ ".$_POST['cat_name']." ]".__('编辑成功！') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS,array('pjaxurl' => RC_Uri::url('goods/admin_goods_type/edit' , 'cat_id='.$cat_id)));
				
			}
		} else {
			$this->showmessage(__('商品类型名称已存在') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		
// 		global $ecs, $db, $_CFG, $sess;
// 		if ($db->autoExecute($ecs->table('goods_type'), $goods_type, 'UPDATE', "cat_id='$cat_id'") !== false)
//		$links = array(array('href' => 'index.php?m=goods&c=admin_goods_type&a=manage', 'text' => RC_Lang::lang('back_list')));
// 		sys_msg($_LANG['edit_goodstype_success'], 0, $links);
// 		sys_msg($_LANG['edit_goodstype_failed'], 1);

// 		$goods_type['cat_name']   = RC_String::sub_str($_POST['cat_name'], 60);
// 		$goods_type['attr_group'] = RC_String::sub_str($_POST['attr_group'], 255);
// 		$goods_type['enabled']    = intval($_POST['enabled']);
// 		$cat_id                   = intval($_POST['cat_id']);
// 		$old_groups               = get_attr_groups($cat_id);
// 		$count = $this->db_goods_type->where(array('cat_name' => $goods_type['cat_name']))->count();
// 		if ($_POST['cat_name'] != $this->db_goods_type->where(array('cat_id' => $cat_id))->get_field('cat_name')) {
// 			if ( $count == 0 ) {
// 				if($this->db_goods_type->where(array('cat_id' => $cat_id))->update($goods_type)) {
// 					/* 对比原来的分组 */
// 					$new_groups = explode("\n", str_replace("\r", '', $goods_type['attr_group']));  // 新的分组
// 					foreach ($old_groups AS $key=>$val) {
// 						$found = array_search($val, $new_groups);
// 						if ($found === NULL || $found === false) {
// 							/* 老的分组没有在新的分组中找到 */
// 							update_attribute_group($cat_id, $key, 0);
// 						} else {
// 							/* 老的分组出现在新的分组中了 */
// 							if ($key != $found) {
// 								update_attribute_group($cat_id, $key, $found); // 但是分组的key变了,需要更新属性的分组
// 							}
// 						}
// 					}
// 					$links = array(array('href' => RC_Uri::url('goods/admin_goods_type/init'), 'text' => RC_Lang::lang('back_list')));
// 					$this->showmessage(RC_Lang::lang('edit_goodstype_success'),ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('url' => RC_Uri::url('goods/admin_goods_type/init',"type=edit&cat_id=$cat_id")));
// 				} 
// 			} else {
// 				$this->showmessage('商品类型名称已存在',ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
// 			}
// 		}else {
// 			$this->showmessage(RC_Lang::lang('edit_goodstype_success'),ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS , array('url' => RC_Uri::url('goods/admin_goods_type/init',"type=edit&cat_id=$cat_id")));
// 		}
	}
	
	/**
	 * 删除商品类型
	 */
	public function remove() 
	{
		$this->admin_priv('goods_type', ecjia::MSGTYPE_JSON);
		
		/* 对删除商品类型进行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对删除商品类型进行权限检查  BY：MaLiuWei  END */
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
			$this->showmessage(__('商品类型[' . $name . ']删除成功！') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
		} else {
			$this->showmessage(RC_Lang::lang('remove_failed'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => RC_Lang::lang('remove_failed')));
		}
// 		global $ecs, $db, $_CFG, $sess;
// 		$exc = new exchange($ecs->table("goods_type"), $db, 'cat_id', 'cat_name');
//		check_authz_json('goods_type');
// 		$name = $exc->get_name($id);
// 		if ($exc->drop($id)) {
		/* 清除该类型下的所有属性 */
// 		$sql = "SELECT attr_id FROM " .$ecs->table('attribute'). " WHERE cat_id = '$id'";
// 		$arr = $db->getCol($sql);	
// 		$arr = $this->db_attribute->field('attr_id')->find('cat_id = "'.$id.'"');
// 		$GLOBALS['db']->query("DELETE FROM " .$ecs->table('attribute'). " WHERE attr_id " . db_create_in($arr));
// 		$GLOBALS['db']->query("DELETE FROM " .$ecs->table('goods_attr'). " WHERE attr_id " . db_create_in($arr));
//		$url = 'index.php?m=goods&c=admin_goods_type&a=query&' . str_replace('a=remove', '', $_SERVER['QUERY_STRING']);
// 		$url = RC_Uri::url('goods/admin_goods_type/query',str_replace('a=remove', '', $_SERVER['QUERY_STRING'])) ;
// 		$this->header("Location: $url\n");
// 		exit;
// 		make_json_error(RC_Lang::lang('remove_failed'));		
	}

	/**
	 * 修改商品类型名称
	 */
	public function edit_type_name() 
	{
		$this->admin_priv('goods_type', ecjia::MSGTYPE_JSON);

		/* 对修改商品类型名称进行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对修改商品类型名称进行权限检查  BY：MaLiuWei  END */
		$type_id   = !empty($_POST['pk'])  ? intval($_POST['pk']) : 0;
		$type_name = !empty($_POST['value']) ? trim($_POST['value'])  : '';

		/* 检查名称是否重复 */
		if(!empty($type_name)) {
			$is_only = $this->db_goods_type->where(array('cat_name' => $type_name))->count();
			if ($is_only == 0) {
				$this->db_goods_type->where(array('cat_id' => $type_id))->update(array('cat_name' => $type_name));
				ecjia_admin::admin_log($type_name, 'edit', 'goods_type');
				$this->showmessage('成功修改商品类型名称为：' . $type_name, ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => stripslashes($type_name)));
			} else {
				$this->showmessage(RC_Lang::lang('repeat_type_name'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
			
		} else {
			$this->showmessage('商品类型名称不能为空！', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		
		
// 		global $ecs, $db, $_CFG, $sess;
// 		$exc = new exchange($ecs->table("goods_type"), $db, 'cat_id', 'cat_name');
//		check_authz_json('goods_type');
// 		$type_name = !empty($_POST['val']) ? json_str_iconv(trim($_POST['val']))  : '';
/* 检查名称是否重复 */
// 		$is_only = $exc->is_only('cat_name', $type_name, $type_id);
// 		$exc->edit("cat_name='$type_name'", $type_id);
// 		make_json_result(stripslashes($type_name));
	}
	
	/**
	 * 切换启用状态
	 */
	public function toggle_enabled() 
	{
		$this->admin_priv('goods_type', ecjia::MSGTYPE_JSON);
		
		/* 对切换启用状态进行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对切换启用状态进行权限检查  BY：MaLiuWei  END */
		$id     = intval($_POST['id']);				
		$val    = intval($_POST['val']);
		$data = array('enabled' => $val	);
		$this->db_goods_type->where(array('cat_id' => $id))->update($data);
		$this->showmessage('',ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => $val));
		
// 		global $ecs, $db, $_CFG, $sess;
// 		$exc = new exchange($ecs->table("goods_type"), $db, 'cat_id', 'cat_name');
// 		check_authz_json('goods_type');
// 		$exc->edit("enabled='$val'", $id);
// 		make_json_result($val);
	}
}
