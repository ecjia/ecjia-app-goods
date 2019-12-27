<?php
//
//    ______         ______           __         __         ______
//   /\  ___\       /\  ___\         /\_\       /\_\       /\  __ \
//   \/\  __\       \/\ \____        \/\_\      \/\_\      \/\ \_\ \
//    \/\_____\      \/\_____\     /\_\/\_\      \/\_\      \/\_\ \_\
//     \/_____/       \/_____/     \/__\/_/       \/_/       \/_/ /_/
//
//   上海商创网络科技有限公司
//
//  ---------------------------------------------------------------------------------
//
//   一、协议的许可和权利
//
//    1. 您可以在完全遵守本协议的基础上，将本软件应用于商业用途；
//    2. 您可以在协议规定的约束和限制范围内修改本产品源代码或界面风格以适应您的要求；
//    3. 您拥有使用本产品中的全部内容资料、商品信息及其他信息的所有权，并独立承担与其内容相关的
//       法律义务；
//    4. 获得商业授权之后，您可以将本软件应用于商业用途，自授权时刻起，在技术支持期限内拥有通过
//       指定的方式获得指定范围内的技术支持服务；
//
//   二、协议的约束和限制
//
//    1. 未获商业授权之前，禁止将本软件用于商业用途（包括但不限于企业法人经营的产品、经营性产品
//       以及以盈利为目的或实现盈利产品）；
//    2. 未获商业授权之前，禁止在本产品的整体或在任何部分基础上发展任何派生版本、修改版本或第三
//       方版本用于重新开发；
//    3. 如果您未能遵守本协议的条款，您的授权将被终止，所被许可的权利将被收回并承担相应法律责任；
//
//   三、有限担保和免责声明
//
//    1. 本软件及所附带的文件是作为不提供任何明确的或隐含的赔偿或担保的形式提供的；
//    2. 用户出于自愿而使用本软件，您必须了解使用本软件的风险，在尚未获得商业授权之前，我们不承
//       诺提供任何形式的技术支持、使用担保，也不承担任何因使用本软件而产生问题的相关责任；
//    3. 上海商创网络科技有限公司不对使用本产品构建的商城中的内容信息承担责任，但在不侵犯用户隐
//       私信息的前提下，保留以任何方式获取用户信息及商品信息的权利；
//
//   有关本产品最终用户授权协议、商业授权与技术服务的详细内容，均由上海商创网络科技有限公司独家
//   提供。上海商创网络科技有限公司拥有在不事先通知的情况下，修改授权协议的权力，修改后的协议对
//   改变之日起的新授权用户生效。电子文本形式的授权协议如同双方书面签署的协议一样，具有完全的和
//   等同的法律效力。您一旦开始修改、安装或使用本产品，即被视为完全理解并接受本协议的各项条款，
//   在享有上述条款授予的权力的同时，受到相关的约束和限制。协议许可范围以外的行为，将直接违反本
//   授权协议并构成侵权，我们有权随时终止授权，责令停止损害，并保留追究相关责任的权力。
//
//  ---------------------------------------------------------------------------------
//
defined('IN_ECJIA') or exit('No permission resources.');

/**
 * ECJIA 商品分类管理程序
 */
class admin_category extends ecjia_admin {
	public function __construct() {
		parent::__construct();
		
		ini_set('memory_limit', -1);
		
		RC_Script::enqueue_script('jquery-chosen');
		RC_Script::enqueue_script('jquery-validate');
		RC_Script::enqueue_script('jquery-form');
		RC_Style::enqueue_style('chosen');

		RC_Script::enqueue_script('smoke');
		RC_Script::enqueue_script('jquery-uniform');
		RC_Script::enqueue_script('bootstrap-placeholder');
		RC_Style::enqueue_style('uniform-aristo');
		RC_Script::enqueue_script('bootstrap-editable-script', RC_Uri::admin_url() . '/statics/lib/x-editable/bootstrap-editable/js/bootstrap-editable.min.js', array(), false, 1);
		RC_Style::enqueue_style('bootstrap-editable-css', RC_Uri::admin_url() . '/statics/lib/x-editable/bootstrap-editable/css/bootstrap-editable.css');
		// RC_Script::enqueue_script('ecjia-common');
		
		RC_Script::enqueue_script('clipboard.min', RC_App::apps_url('statics/js/clipboard.min.js', __FILE__), array(), false, 1);
		
		RC_Script::enqueue_script('goods_category_list', RC_App::apps_url('statics/js/goods_category_list.js',__FILE__), array(), false, 1);
		RC_Script::localize_script('goods_category_list', 'js_lang', config('app-goods::jslang.category_page'));
		RC_Style::enqueue_style('goods_category', RC_App::apps_url('statics/styles/goods_category.css', __FILE__), array());

		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商品分类', 'goods'), RC_Uri::url('goods/admin_category/init')));

        RC_Loader::load_app_func('admin_goods');
        RC_Loader::load_app_func('admin_category');
        RC_Loader::load_app_func('global');
	}

	/**
	 * 商品分类列表
	 */
	public function init() {
		$this->admin_priv('category_manage');
		
		$this->assign('ur_here', __('商品分类', 'goods'));
		ecjia_screen::get_current_screen()->remove_last_nav_here();
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商品分类', 'goods')));

		ecjia_screen::get_current_screen()->add_help_tab(array(
			'id'		=> 'overview',
			'title'		=> __('概述', 'goods'),
			'content'	=> '<p>' . __('欢迎访问ECJia智能后台商品分类列表页面，系统中所有的商品分类都会显示在此列表中，列表中切换分类是否显示可切换前台商品分类的显示与隐藏。', 'goods') . '</p>'
		));

		ecjia_screen::get_current_screen()->set_help_sidebar(
			'<p><strong>' . __('更多信息：', 'goods') . '</strong></p>' .
			'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:商品分类#.E5.95.86.E5.93.81.E5.88.86.E7.B1.BB.E5.88.97.E8.A1.A8" target="_blank">关于商品分类列表帮助文档</a>', 'goods') . '</p>'
		);
		
		$cat_id = intval($this->request->input('cat_id', 0));
		$this->assign('cat_id', $cat_id);

        $cat_list = (new \Ecjia\App\Goods\Category\CategoryCollection($cat_id))->getCategories();
		$cat_list = collect($cat_list)->all();

		$this->assign('cat_list', $cat_list);
		
		$cat_info = get_cat_info($cat_id);
		if (!empty($cat_info)) {
			ecjia_screen::get_current_screen()->remove_last_nav_here();
			ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商品分类', 'goods'), RC_Uri::url('goods/admin_category/init')));
			
			$cat_html = $this->get_cat_html($cat_id);
			ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here($cat_html));
			
			$arr = array();
			if (!empty($cat_info['parent_id'])) {
				$arr['cat_id'] = $cat_info['parent_id'];
			}
			$this->assign('back_link', array('href' => RC_Uri::url('goods/admin_category/init', $arr), 'text' => '上级分类'));
		} else {
			$this->assign('exchange_link', array('href' => RC_Uri::url('goods/admin_category/move'), 'text' => __('转移商品', 'goods')));
		}

		$add_arr = array();
		if (!empty($cat_id)) {
			$add_arr = array('parent_id' => $cat_id);
		}
		$this->assign('action_link', array('href' => RC_Uri::url('goods/admin_category/add', $add_arr), 'text' => __('添加商品分类', 'goods')));
		
		return $this->display('category_list.dwt');
	}

	/**
	 * 添加商品分类
	 */
	public function add() {
	    $this->admin_priv('category_update');

	    $parent_id = !empty($_GET['parent_id']) ? intval($_GET['parent_id']) : 0;
		RC_Script::enqueue_script('goods_category_list', RC_App::apps_url('statics/js/goods_category_info.js',__FILE__), array(), false, false);

		$this->assign('ur_here', __('添加商品分类', 'goods'));
		$action_link_arr = array();
		if (!empty($parent_id)) {
			$action_link_arr = array('cat_id' => $parent_id);
			
			ecjia_screen::get_current_screen()->remove_last_nav_here();
			ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商品分类', 'goods'), RC_Uri::url('goods/admin_category/init')));
			
			$cat_html = $this->get_cat_html($parent_id, '', 'add');
			ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here($cat_html));	
		} else {
			ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('添加商品分类', 'goods')));
		}
		$this->assign('action_link', array('href' => RC_Uri::url('goods/admin_category/init', $action_link_arr), 'text' => __('商品分类', 'goods')));
		
		$this->assign('goods_type_list', goods_type_list(0)); // 取得商品类型
		$this->assign('attr_list', get_category_attr_list()); // 取得商品属性
		
		$this->assign('cat_select', cat_list(0, $parent_id, true));
		$this->assign('cat_info', array('is_show' => 1));
		$this->assign('form_action', RC_Uri::url('goods/admin_category/insert'));
		
		$specification_template_list = Ecjia\App\Goods\GoodsAttr::goods_type_select_list(0, 'specification', 1);
		$parameter_template_list     = Ecjia\App\Goods\GoodsAttr::goods_type_select_list(0, 'parameter', 1);
		$this->assign('specification_template_list', $specification_template_list);
		$this->assign('parameter_template_list', $parameter_template_list);

		return $this->display('category_info.dwt');
	}

	/**
	 * 商品分类添加时的处理
	 */
	public function insert() {
		$this->admin_priv('category_update', ecjia::MSGTYPE_JSON);

		$cat['cat_id']       = !empty($_POST['cat_id'])       ? intval($_POST['cat_id'])     : 0;
		$cat['parent_id']    = !empty($_POST['parent_id'])    ? intval($_POST['parent_id'])  : 0;
		$cat['sort_order']   = !empty($_POST['sort_order'])   ? intval($_POST['sort_order']) : 0;
		$cat['keywords']     = !empty($_POST['keywords'])     ? trim($_POST['keywords'])     : '';
		$cat['cat_desc']     = !empty($_POST['cat_desc'])     ? $_POST['cat_desc']           : '';
		$cat['measure_unit'] = !empty($_POST['measure_unit']) ? trim($_POST['measure_unit']) : '';
		$cat['cat_name']     = !empty($_POST['cat_name'])     ? trim($_POST['cat_name'])     : '';
		$cat['show_in_nav']  = !empty($_POST['show_in_nav'])  ? intval($_POST['show_in_nav']): 0;
// 		$cat['style']        = !empty($_POST['style'])        ? trim($_POST['style'])        : '';
		$cat['is_show']      = !empty($_POST['is_show'])      ? intval($_POST['is_show'])    : 0;
		$cat['grade']        = !empty($_POST['grade'])        ? intval($_POST['grade'])      : 0;
		$cat['filter_attr']  = !empty($_POST['filter_attr'])  ? implode(',', array_unique(array_diff($_POST['filter_attr'], array(0)))) : 0;
		$cat['specification_id']      = !empty($_POST['specification_id'])      ? intval($_POST['specification_id'])    : 0;
		$cat['parameter_id']      = !empty($_POST['parameter_id'])      ? intval($_POST['parameter_id'])    : 0;
		if (cat_exists($cat['cat_name'], $cat['parent_id'])) {
		    return $this->showmessage(__('已存在相同的分类名称！', 'goods'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}

		if ($cat['grade'] > 10 || $cat['grade'] < 0) {
			return $this->showmessage(__('价格分级数量只能是0-10之内的整数', 'goods'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}

		/* 上传分类图片 */
		$upload = RC_Upload::uploader('image', array('save_path' => 'data/category', 'auto_sub_dirs' => true));
		if (isset($_FILES['cat_img']) && $upload->check_upload_file($_FILES['cat_img'])) {
			$image_info = $upload->upload($_FILES['cat_img']);
			if (empty($image_info)) {
				return $this->showmessage($upload->error(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
			$cat['category_img'] = $upload->get_position($image_info);
		}

		/* 入库的操作 */
		$insert_id = RC_DB::table('category')->insertGetId($cat);
		
		if ($insert_id) {
			$cat_id = $insert_id;
			if ($cat['show_in_nav'] == 1) {
// 				$vieworder = $this->db_nav->where('type = "middle"')->max('vieworder');
				$vieworder = RC_DB::table('nav')->where('type', 'middle')->max('vieworder');
				$vieworder += 2;
				//显示在自定义导航栏中
				$data = array(
					'name' 		=> $cat['cat_name'],
					'ctype' 	=> 'c',
					'cid' 		=> $cat_id,
					'ifshow' 	=> '1',
					'vieworder'	=> $vieworder,
					'opennew' 	=> '0',
					'url' 		=> build_uri('category', array('cid'=> $cat_id), $cat['cat_name']),
					'type' 		=> 'middle',
				);
				RC_DB::table('nav')->insert($data);
			}
			$cat['cat_recommend']  = !empty($_POST['cat_recommend'])  ? $_POST['cat_recommend'] : array();
			insert_cat_recommend($cat['cat_recommend'], $cat_id);
			
			//存储广告
			if(!empty($_POST['category_ad'])) {
			    self::update_category_ad($insert_id, $_POST['category_ad']);
			}

			ecjia_admin::admin_log($_POST['cat_name'], 'add', 'category');   // 记录管理员操作

            //分类证件 start
            $dt_list = isset($_POST['document_title'])? $_POST['document_title'] : array();
            
            //分类证件 end
            RC_Cache::app_cache_delete('cat_pid_releate', 'goods');
            RC_Cache::app_cache_delete('cat_option_static', 'goods');

            $url_add = $url_back = array();
            if (!empty($cat['parent_id'])) {
            	$url_add = array('parent_id' => $cat['parent_id']);
            	$url_back = array('cat_id' => $cat['parent_id']);
            }
			/*添加链接*/
            $link[0]['text'] = __('返回分类列表', 'goods');
            $link[0]['href'] = RC_Uri::url('goods/admin_category/init', $url_back);
            
			$link[1]['text'] = __('继续添加分类', 'goods');
			$link[1]['href'] = RC_Uri::url('goods/admin_category/add', $url_add);
			RC_Cache::app_cache_delete('admin_category_list', 'goods');
			
			/* 释放app缓存 */
			$category_db = RC_Model::model('goods/orm_category_model');
			$cache_key = sprintf('%X', crc32('category-'. $cat['parent_id']));
			$category_db->delete_cache_item($cache_key);
			$cache_key = sprintf('%X', crc32('category-children-'.$cat['parent_id']));
			$category_db->delete_cache_item($cache_key);
			
			$pjaxurl = RC_Uri::url('goods/admin_category/edit', array('cat_id' => $cat_id));
			return $this->showmessage(__('新商品分类添加成功！', 'goods'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('links' => $link, 'pjaxurl' => $pjaxurl));
		}
	}

	/**
	 * 编辑商品分类信息
	 */
	public function edit() {
		$this->admin_priv('category_update');
		RC_Script::enqueue_script('goods_category_list', RC_App::apps_url('statics/js/goods_category_info.js',__FILE__), array(), false, false);

		$cat_id = intval($_GET['cat_id']);
		$cat_info = get_cat_info($cat_id);  // 查询分类信息数据
		$filter_attr_list = array();
		$attr_list = array();
		
		if ($cat_info['filter_attr']) {
			$filter_attr = explode(",", $cat_info['filter_attr']);  //把多个筛选属性放到数组中
			foreach ($filter_attr AS $k => $v) {
				$attr_cat_id = RC_DB::table('attribute')->where('attr_id', intval($v))->value('cat_id');

				$filter_attr_list[$k]['goods_type_list'] = goods_type_list($attr_cat_id);  //取得每个属性的商品类型
				$filter_attr_list[$k]['filter_attr'] = $v;
				$attr_option = array();
				$_REQUEST['cat_id'] = $attr_cat_id;
				$attr_list = get_attr_list();
				
				if (!empty($attr_list['item'])) {
					foreach ($attr_list['item'] as $val) {
						$attr_option[$val['attr_id']] = $val['attr_name'];
					}
				}
				$filter_attr_list[$k]['option'] = $attr_option;
			}
			$this->assign('filter_attr_list', $filter_attr_list);
		} else {
			$attr_cat_id = 0;
		}

		/* 模板赋值 */
		$this->assign('attr_list', $attr_list); // 取得商品属性
		$this->assign('attr_cat_id', $attr_cat_id);

		$this->assign('ur_here', __('编辑商品分类', 'goods'));
		
		if (!empty($cat_id)) {
			$action_link_arr = array('cat_id' => $cat_id);
				
			ecjia_screen::get_current_screen()->remove_last_nav_here();
			ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商品分类', 'goods'), RC_Uri::url('goods/admin_category/init')));
				
			$cat_html = $this->get_cat_html($cat_id, '');
			ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here($cat_html));
		} else {
			ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('编辑商品分类', 'goods')));
		}
		
		$arr = array();
		if (!empty($cat_info['parent_id'])) {
			$arr['cat_id'] = $cat_info['parent_id'];
		}
		$this->assign('action_link', array('text' => __('商品分类', 'goods'), 'href' => RC_Uri::url('goods/admin_category/init', $arr)));

		$res = RC_DB::table('cat_recommend')->select('recommend_type')->where('cat_id', $cat_id)->get();
		if (!empty($res)) {
			$cat_recommend = array();
			foreach($res as $data) {
				$cat_recommend[$data['recommend_type']] = 1;
			}
			$this->assign('cat_recommend', $cat_recommend);
		}
		$cat_info['category_img'] = !empty($cat_info['category_img']) ? RC_Upload::upload_url($cat_info['category_img']) : '';
		
		$category_ad = self::get_category_ad($cat_info['cat_id']);
		$this->assign('category_ad', $category_ad);
		$this->assign('cat_info', $cat_info);
		$this->assign('cat_select', cat_list(0, $cat_info['parent_id'], true));
		$this->assign('goods_type_list', goods_type_list(0)); // 取得商品类型
		$this->assign('form_action', RC_Uri::url('goods/admin_category/update'));

		
		$specification_template_list = Ecjia\App\Goods\GoodsAttr::goods_type_select_list($cat_info['specification_id'], 'specification', 1);
		$parameter_template_list     = Ecjia\App\Goods\GoodsAttr::goods_type_select_list($cat_info['parameter_id'], 'parameter', 1);
		$this->assign('specification_template_list', $specification_template_list);
		$this->assign('parameter_template_list', $parameter_template_list);
		
		
		return $this->display('category_info.dwt');
	}
	
	public function choose_goods_type() {
		$attr_list = get_attr_list();
		return $this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('attr_list' => $attr_list));
	}

	public function add_category() {
	    $this->admin_priv('category_update', ecjia::MSGTYPE_JSON);
	    
		$parent_id 	= empty($_REQUEST['parent_id'])	? 0		: intval($_REQUEST['parent_id']);
		$category 	= empty($_REQUEST['cat']) 		? '' 	: trim($_REQUEST['cat']);
		
		if (cat_exists($category, $parent_id)) {
			return $this->showmessage(__('已存在相同的分类名称！', 'goods'));
		} else {
			$data = array(
				'cat_name' 	=> $category,
				'parent_id'	=> $parent_id,
				'is_show' 	=> '1',
			);
			$category_id = RC_DB::table('category')->insertGetId($data);
			
			$arr = array("parent_id" => $parent_id, "id" => $category_id, "cat" => $category);
			return $this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => $arr));
		}
	}

	/**
	 * 编辑商品分类信息
	 */
	public function update() {
		$this->admin_priv('category_update', ecjia::MSGTYPE_JSON);

		$cat_id              	= !empty($_POST['cat_id'])       ? intval($_POST['cat_id'])     : 0;
		$old_cat_name        	= $_POST['old_cat_name'];
		$cat['parent_id']    	= !empty($_POST['parent_id'])    ? intval($_POST['parent_id'])  : 0;
		$cat['sort_order']   	= !empty($_POST['sort_order'])   ? intval($_POST['sort_order']) : 0;
		$cat['keywords']     	= !empty($_POST['keywords'])     ? trim($_POST['keywords'])     : '';
		$cat['cat_desc']     	= !empty($_POST['cat_desc'])     ? $_POST['cat_desc']           : '';
		$cat['measure_unit'] 	= !empty($_POST['measure_unit']) ? trim($_POST['measure_unit']) : '';
		$cat['cat_name']     	= !empty($_POST['cat_name'])     ? trim($_POST['cat_name'])     : '';
		$cat['is_show']      	= !empty($_POST['is_show'])      ? intval($_POST['is_show'])    : 0;
		$cat['show_in_nav']  	= !empty($_POST['show_in_nav'])  ? intval($_POST['show_in_nav']): 0;
		//$cat['style']        	= !empty($_POST['style'])        ? trim($_POST['style'])        : '';
		$cat['grade']       	= !empty($_POST['grade'])        ? intval($_POST['grade'])      : 0;
		$cat['filter_attr']		= !empty($_POST['filter_attr'])  ? implode(',', array_unique(array_diff($_POST['filter_attr'], array(0)))) : 0;
		$cat['specification_id']    = !empty($_POST['specification_id'])      ? intval($_POST['specification_id'])    : 0;
		$cat['parameter_id']      	= !empty($_POST['parameter_id'])      ? intval($_POST['parameter_id'])    : 0;

		/* 判断分类名是否重复 */
		if ($cat['cat_name'] != $old_cat_name) {
			if (cat_exists($cat['cat_name'], $cat['parent_id'], $cat_id)) {
				$link[] = array('text' => __('返回上一页', 'goods'), 'href' => 'javascript:history.back(-1)');
				return $this->showmessage(__('已存在相同的分类名称！', 'goods'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR, array('links' => $link));
			}
		}

		/* 判断上级目录是否合法 */
		$children = array_keys(cat_list($cat_id, 0, false));     // 获得当前分类的所有下级分类
		if (in_array($cat['parent_id'], $children)) {
			/* 选定的父类是当前分类或当前分类的下级分类 */
			$link[] = array('text' => __('返回上一页', 'goods'), 'href' => 'javascript:history.back(-1)');
			return $this->showmessage(__('所选择的上级分类不能是当前分类或者当前分类的下级分类！', 'goods'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR, array('links' => $link));
		}

		if ($cat['grade'] > 10 || $cat['grade'] < 0) {
			/* 价格区间数超过范围 */
			$link[] = array('text' => __('返回上一页', 'goods'), 'href' => 'javascript:history.back(-1)');
			return $this->showmessage(__('价格分级数量只能是0-10之内的整数', 'goods'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR, array('links' => $link));
		}
		
		/* 更新分类图片 */
		$upload = RC_Upload::uploader('image', array('save_path' => 'data/category', 'auto_sub_dirs' => true));

		if (isset($_FILES['cat_img']) && $upload->check_upload_file($_FILES['cat_img'])) {
			$image_info = $upload->upload($_FILES['cat_img']);
			if (empty($image_info)) {
				return $this->showmessage($upload->error(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
			$cat['category_img'] = $upload->get_position($image_info);
			$logo = RC_DB::table('category')->where('cat_id', $cat_id)->value('category_img');
				
			if (!empty($logo)) {
				$disk = RC_Filesystem::disk();
				$disk->delete(RC_Upload::upload_path() . $logo);
			}
		}

		$info = RC_DB::table('category')->select('cat_name', 'parent_id', 'show_in_nav')->where('cat_id', $cat_id)->first();
		
		RC_DB::table('category')->where('cat_id', $cat_id)->update($cat);
		//存储广告
		if(!empty($_POST['category_ad'])) {
		    self::update_category_ad($cat_id, $_POST['category_ad']);
		}
		
		if ($cat['cat_name'] != $info['cat_name']) {
			/* 如果分类名称发生了改变 */
			$data = array(
				'name' => $cat['cat_name'],
			);
			RC_DB::table('nav')->where('ctype', 'c')->where('cid', $cat_id)->where('type', 'middle')->update($data);
		}

    	//分类证件 start
       	$dt_list = isset($_POST['document_title']) ? $_POST['document_title'] : array();
     	$dt_id = isset($_POST['dt_id'])? $_POST['dt_id'] : array();
      	//分类证件 end

		if ($cat['show_in_nav'] != $info['show_in_nav']) {
			/* 是否显示于导航栏发生了变化 */
			if ($cat['show_in_nav'] == 1) {
				/* 显示 */
				$nid = RC_DB::table('nav')->select('id')->where('ctype', 'c')->where('cid', $cat_id)->where('type', 'middle')->first();
				
				if (empty($nid)) {
					/* 不存在 */
					$vieworder = RC_DB::table('nav')->where('type', 'middle')->max('vieworder');
					
					$vieworder += 2;
					$uri = build_uri('category', array('cid'=> $cat_id), $cat['cat_name']);
					$data = array(
						'name' 		=> $cat['cat_name'],
						'ctype' 	=> 'c',
						'cid' 		=> $cat_id,
						'ifshow' 	=> '1',
						'vieworder'	=> $vieworder,
						'opennew' 	=> '0',
						'url' 		=> $uri,
						'type' 		=> 'middle',
					);
					RC_DB::table('nav')->insert($data);
				} else {
					$data = array(
						'ifshow' => '1'
					);
					RC_DB::table('nav')->where('ctype', 'c')->where('cid', $cat_id)->where('type', 'middle')->update($data);
				}
			} else {
				/* 去除 */
				$data = array(
					'ifshow' => '0'
				);
				RC_DB::table('nav')->where('ctype', 'c')->where('cid', $cat_id)->where('type', 'middle')->update($data);
			}
		}

		/* 更新首页推荐 */
		$cat['cat_recommend'] = !empty($_POST['cat_recommend'])  ? $_POST['cat_recommend'] : array();
		insert_cat_recommend($cat['cat_recommend'], $cat_id);

		ecjia_admin::admin_log($_POST['cat_name'], 'edit', 'category');
		RC_Cache::app_cache_delete('admin_category_list', 'goods');
		
		/* 释放app缓存 */
		$category_db = RC_Model::model('goods/orm_category_model');
		$cache_key = sprintf('%X', crc32('category-'. $cat['parent_id']));
		$category_db->delete_cache_item($cache_key);
		$cache_key = sprintf('%X', crc32('category-children-'.$cat['parent_id']));
		$category_db->delete_cache_item($cache_key);
		if ($cat['parent_id'] != $info['parent_id']) {
			$cache_key = sprintf('%X', crc32('category-'. $info['parent_id']));
			$category_db->delete_cache_item($cache_key);
			$cache_key = sprintf('%X', crc32('category-children-'.$info['parent_id']));
			$category_db->delete_cache_item($cache_key);
		}
		return $this->showmessage(__('商品分类编辑成功！', 'goods'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('goods/admin_category/edit', array('cat_id' => $cat_id))));
	}

	/**
	 * 批量转移商品分类页面
	 */
	public function move() {
		$this->admin_priv('category_move');

		$cat_id = !empty($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('转移商品', 'goods')));
		ecjia_screen::get_current_screen()->add_help_tab(array(
			'id'		=> 'overview',
			'title'		=> __('概述', 'goods'),
			'content'	=> '<p>' . __('欢迎访问ECJia智能后台转移商品分类页面，可以在此页面进行转移商品分类操作。', 'goods') . '</p>'
		));
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
			'<p><strong>' . __('更多信息：', 'goods') . '</strong></p>' .
			'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:商品分类#.E8.BD.AC.E7.A7.BB.E5.95.86.E5.93.81" target="_blank">关于转移商品分类帮助文档</a>', 'goods') . '</p>'
		);
		
		$this->assign('ur_here', __('转移商品', 'goods'));
		$this->assign('action_link', array('href' => RC_Uri::url('goods/admin_category/init'), 'text' => __('商品分类', 'goods')));

		$this->assign('cat_select', cat_list(0, $cat_id, true));
		$this->assign('form_action', RC_Uri::url('goods/admin_category/move_cat'));

		return $this->display('category_move.dwt');
	}

	/**
	 * 处理批量转移商品分类的处理程序
	 */
	public function move_cat() {
		$this->admin_priv('category_move', ecjia::MSGTYPE_JSON);

		$cat_id        = !empty($_POST['cat_id'])        ? intval($_POST['cat_id'])        : 0;
		$target_cat_id = !empty($_POST['target_cat_id']) ? intval($_POST['target_cat_id']) : 0;

		/* 商品分类不允许为空 */
		if ($cat_id == 0 || $target_cat_id == 0) {
			return $this->showmessage(__('你没有正确选择商品分类！', 'goods'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 更新商品分类 */
		$data = array('cat_id' => $target_cat_id);
		
		$new_cat_name = RC_DB::table('category')->where('cat_id', $target_cat_id)->value('cat_name');
		$old_cat_name = RC_DB::table('category')->where('cat_id', $cat_id)->value('cat_name');
		
		RC_DB::table('goods')->where('cat_id', $cat_id)->update($data);
		
		RC_Cache::app_cache_delete('admin_category_list', 'goods');
		return $this->showmessage(__('转移商品分类已成功完成！', 'goods'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
	}

	/**
	 * 编辑排序序号
	 */
	public function edit_sort_order() {
		$this->admin_priv('category_update', ecjia::MSGTYPE_JSON);
		
		$id = intval($_POST['pk']);
		$val = intval($_POST['value']);

		if (cat_update($id, array('sort_order' => $val))) {
			$info = RC_DB::table('category')->where('cat_id', $id)->first();
			/* 释放app缓存 */
			$category_db = RC_Model::model('goods/orm_category_model');
			$cache_key = sprintf('%X', crc32('category-'. $info['parent_id']));
			$category_db->delete_cache_item($cache_key);
			
			$pjaxurl = RC_Uri::url('goods/admin_category/init');
			if (!empty($info['parent_id'])) {
				$pjaxurl = RC_Uri::url('goods/admin_category/init', array('cat_id' => $info['parent_id']));
			}
			return $this->showmessage(__('排序序号编辑成功', 'goods'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => $pjaxurl));
		} else {
			return $this->showmessage(__('排序序号编辑失败', 'goods'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}

	/**
	 * 编辑数量单位
	 */
	public function edit_measure_unit() {
		$this->admin_priv('category_update', ecjia::MSGTYPE_JSON);
		
		$id = intval($_POST['pk']);
		$val = $_POST['value'];
		
		if (cat_update($id, array('measure_unit' => $val))) {
			return $this->showmessage(__('数量单位编辑成功', 'goods'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => $val));
		} else {
			return $this->showmessage(__('数量单位编辑失败', 'goods'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}

	/**
	 * 编辑价格分级
	 */
	public function edit_grade() {
		$this->admin_priv('category_update', ecjia::MSGTYPE_JSON);
		
		$id = intval($_POST['pk']);
		$val = !empty($_POST['value']) ? intval($_POST['value']) : 0;

		if ($val > 10 || $val < 0) {
			/* 价格区间数超过范围 */
			return $this->showmessage(__('价格分级数量只能是0-10之内的整数', 'goods'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		if (cat_update($id, array('grade' => $val))) {
			return $this->showmessage(__('价格分级编辑成功', 'goods'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => $val));
		} else {
			return $this->showmessage(__('价格分级编辑失败', 'goods'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}

	/**
	 * 切换是否显示
	 */
	public function toggle_is_show() {
		$this->admin_priv('category_update', ecjia::MSGTYPE_JSON);
		
		$id = intval($_POST['id']);
		$val = intval($_POST['val']);

		$info = RC_DB::table('category')->where('cat_id', $id)->first();
		$name = $info['cat_name'];
		
		if (cat_update($id, array('is_show' => $val))) {
			/* 释放app缓存 */
			$category_db = RC_Model::model('goods/orm_category_model');
			$cache_key = sprintf('%X', crc32('category-'. $info['parent_id']));
			$category_db->delete_cache_item($cache_key);
			
			$cache_key = sprintf('%X', crc32('category-children-'.$info['parent_id']));
			$category_db->delete_cache_item($cache_key);
			return $this->showmessage(__('价格是否显示切换成功', 'goods'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => $val));
		} else {
			return $this->showmessage(__('价格是否显示切换失败', 'goods'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}

	/**
	 * 删除商品分类
	 */
	public function remove() {
    	$this->admin_priv('category_delete', ecjia::MSGTYPE_JSON);
		
		$cat_id = intval($_GET['id']);
		
		$info = RC_DB::table('category')->where('cat_id', $cat_id)->first();
		$cat_name = $info['cat_name'];
		$cat_count = RC_DB::table('category')->where('parent_id', $cat_id)->count();
		$goods_count = RC_DB::table('goods')->where('cat_id', $cat_id)->where('is_delete', 0)->count();
		
		if (empty($cat_count) && empty($goods_count)) {
			$old_logo = $info['category_img'];
			
			if (!empty($old_logo)) {
				$disk = RC_Filesystem::disk();
				$disk->delete(RC_Upload::upload_path() . $old_logo);
			}
			RC_DB::table('category')->where('cat_id', $cat_id)->delete();
			
			RC_DB::table('nav')->where('ctype', 'c')->where('cid', $cat_id)->where('type', 'middle')->delete();
			ecjia_admin::admin_log($cat_name, 'remove', 'category');
			
			/* 释放app缓存 */
			$category_db = RC_Model::model('goods/orm_category_model');
			$cache_key = sprintf('%X', crc32('category-'. $info['parent_id']));
			$category_db->delete_cache_item($cache_key);
			
			$cache_key = sprintf('%X', crc32('category-children-'.$info['parent_id']));
			$category_db->delete_cache_item($cache_key);
			
			return $this->showmessage(__('商品分类删除成功！', 'goods'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
		} else {
			return $this->showmessage($cat_name .' '. __('不是末级分类或者此分类/回收站下还存在商品，无法删除！', 'goods'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}

	/**
	 * 删除商品分类图片
	 */
	public function remove_logo() {
		$this->admin_priv('category_update', ecjia::MSGTYPE_JSON);
		
		$cat_id = isset($_GET['cat_id']) ? intval($_GET['cat_id']) : 0;
		
		$info = RC_DB::table('category')->where('cat_id', $cat_id)->first();
		$logo = $info['category_img'];
		if (!empty($logo)) {
			$disk = RC_Filesystem::disk();
			$disk->delete(RC_Upload::upload_path() . $logo);
		}
		
		RC_DB::table('category')->where('cat_id', $cat_id)->update(array('category_img' => ''));
		
		/* 释放app缓存 */
		$category_db = RC_Model::model('goods/orm_category_model');
		$cache_key = sprintf('%X', crc32('category-'. $info['parent_id']));
		$category_db->delete_cache_item($cache_key);
		
		return $this->showmessage(__('删除分类图片成功', 'goods'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('goods/admin_category/edit', array('cat_id' => $cat_id))));
	}
	
	/**
	 * 删除商品分类广告
	 */
	public function remove_ad() {
	    $this->admin_priv('category_update', ecjia::MSGTYPE_JSON);
	
	    $cat_id = isset($_GET['cat_id']) ? intval($_GET['cat_id']) : 0;
	    if (empty($cat_id)) {
	        return $this->showmessage(__('参数错误', 'goods'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
	    }
	
	    $info = RC_DB::table('category')->where('cat_id', $cat_id)->first();
	    $this->remove_category_ad($cat_id);
	
	    /* 释放app缓存 */
	    $category_db = RC_Model::model('goods/orm_category_model');
	    $cache_key = sprintf('%X', crc32('category-'. $info['parent_id']));
	    $category_db->delete_cache_item($cache_key);
	
	    return $this->showmessage(__('操作成功', 'goods'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('goods/admin_category/edit', array('cat_id' => $cat_id))));
	}
	
	public function search_ad() {

	    $filter = [
	    	'keywords' => $_POST['keywords'],
	    ];
	    $result = RC_Api::api('adsense', 'adsense_position_list', $filter);
	    
	    $list = array();
	    if (!empty($result)) {
	        foreach ($result as $val) {
	            $list[] = array(
	                'id' 	=> $val['position_id'],
	                'code' 	=> $val['position_code'],
	                'name' 	=> $val['position_name']
	            );
	        }
	    }
	    
	    return $this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => $list));
	}
	
	private function update_category_ad($category_id, $ad_position_id) {
	    
	    if (empty($category_id)) {
	        return false;
	    }
	    if (RC_DB::table('term_meta')->where('object_type', 'ecjia.goods')->where('object_group', 'category')->where('object_id', $category_id)->where('meta_key', 'category_ad')->count()) {
	        RC_DB::table('term_meta')->where('object_type', 'ecjia.goods')->where('object_group', 'category')->where('object_id', $category_id)->where('meta_key', 'category_ad')
	        ->update(array('meta_value' => $ad_position_id));
	    } else {
	        $data = array(
	            'object_type' => 'ecjia.goods',
	            'object_group' => 'category',
	            'object_id' => $category_id,
	            'meta_key' =>'category_ad',
	            'meta_value' => $ad_position_id
	        );
	        RC_DB::table('term_meta')->insert($data);
	    }
	    
	    return true;
	}
	
	private function get_category_ad($category_id) {
	    if (empty($category_id)) {
	        return false;
	    }
        $ad_position_id = RC_DB::table('term_meta')->where('object_type', 'ecjia.goods')->where('object_group', 'category')->where('object_id', $category_id)->where('meta_key', 'category_ad')->value('meta_value');
        if ($ad_position_id) {
            $ad_info = RC_DB::table('ad_position')->where('position_id', $ad_position_id)->first();
            return $ad_info;
        } else {
            return false;
        }
	}
	
	private function remove_category_ad($category_id) {
	    if (empty($category_id)) {
	        return false;
	    }
	    
        return RC_DB::table('term_meta')
        	->where('object_type', 'ecjia.goods')
        	->where('object_group', 'category')
        	->where('object_id', $category_id)
        	->where('meta_key', 'category_ad')
        	->delete();
	}
	
	private function get_cat_list($cat_id = 0) {
		return RC_DB::table('category')->where('parent_id', $cat_id)->orderBy('parent_id', 'asc')->orderBy('sort_order', 'asc')->get();
	}
	
	private function get_cat_html($cat_id = 0, $array = array(), $type = 'init') {
		$cat_info = get_cat_info($cat_id);
		if (!empty($cat_info)) {
			$array[$cat_id] = $cat_info['cat_name'];
		}
		if (!empty($cat_info['parent_id'])) {
			return $this->get_cat_html($cat_info['parent_id'], $array);
		}
		ksort($array);
		$html = '';
		$i = 1;
		foreach ($array as $k => $v) {
			if ($i == count($array) && $type == 'init') {
				$html .= '<li class="last">'.$v.'</li>';
			} else {
				$html .= '<li><a href="'.RC_Uri::url('goods/admin_category/init', array('cat_id' => $k)).'">'.$v.'</a></li>';
			}
			$i++;
		}
		if ($type == 'add') {
			$html .= '<li class="last">'. __('添加商品分类', 'goods') .'</li>';
		}
		return $html;
	}
}

// end