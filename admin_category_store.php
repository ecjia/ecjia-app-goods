<?php
/**
 * ECJIA 商品分类管理程序
 */
defined('IN_ECJIA') or exit('No permission resources.');
RC_Loader::load_sys_class('ecjia_admin', false);

class admin_category_store extends ecjia_admin
{
	private $db_category;
	private $db_nav;
	private $db_attribute;
	private $db_cat;
	private $db_goods;
	public function __construct()
	{
		parent::__construct();

		RC_Lang::load('category');

		RC_Loader::load_app_func('goods');

		RC_Script::enqueue_script('jquery-chosen');
		RC_Script::enqueue_script('jquery-validate');
		RC_Script::enqueue_script('jquery-form');
		RC_Style::enqueue_style('chosen');

		RC_Script::enqueue_script('smoke');
		RC_Script::enqueue_script('jquery-uniform');
		RC_Script::enqueue_script('bootstrap-placeholder');
		RC_Style::enqueue_style('uniform-aristo');
		RC_Script::enqueue_script('bootstrap-editable-script', RC_Uri::admin_url() . '/statics/lib/x-editable/bootstrap-editable/js/bootstrap-editable.min.js', array(), false, true);
		RC_Style::enqueue_style('bootstrap-editable-css', RC_Uri::admin_url() . '/statics/lib/x-editable/bootstrap-editable/css/bootstrap-editable.css');
		RC_Script::enqueue_script('ecjia-common');
		RC_Script::enqueue_script('goods_category_list', RC_App::apps_url('statics/js/goods_category_list.js',__FILE__), array());

		RC_Loader::load_app_func('category');
		RC_Loader::load_app_func('common');
		RC_Loader::load_app_func('functions');

		$this->db_category = RC_Loader::load_app_model('category_model','goods');
		$this->db_nav = RC_Loader::load_model('nav_model');
		$this->db_attribute = RC_Loader::load_app_model('attribute_model','goods');
		$this->db_cat = RC_Loader::load_app_model('cat_recommend_model','goods');
		$this->db_goods = RC_Loader::load_app_model('goods_model','goods');

		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('店铺商品分类'), RC_Uri::url('goods/admin_category_store/init')));

	}

	/**
	 * 商品分类列表
	 */
	public function init()
	{
	    $this->admin_priv('cat_manage');

//         $cat_list = RC_Cache::userdata_getcache('cat_list', '00false0truetrue', true);
//         if (empty($cat_list)) {
        	$cat_list = cat_list(0, 0, false, 0, true, true);
//             RC_Cache::userdata_setcache('cat_list', $cat_list, '00false0truetrue', true);
//         }

		ecjia_screen::get_current_screen()->remove_last_nav_here();
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('店铺商品分类')));

		$this->assign('ur_here', __('店铺商品分类'));
		$this->assign('action_link1',  array('href' => RC_Uri::url('goods/admin_category/move'), 'text' => RC_Lang::lang('move_goods')));
		$this->assign('store', '1');
		$this->assign('cat_info',     $cat_list);
        $this->assign('quickuri', array(
            'init'                  => RC_Uri::url('goods/admin/init'),
            'edit_measure_unit'     => RC_Uri::url('goods/admin_category/edit_measure_unit'),
            'edit_grade'            => RC_Uri::url('goods/admin_category/edit_grade'),
            'edit_sort_order'       => RC_Uri::url('goods/admin_category/edit_sort_order'),
            'toggle_is_show'        => RC_Uri::url('goods/admin_category/toggle_is_show'),
            'edit'                  => RC_Uri::url('goods/admin_category_store/edit'),
            'remove'                => RC_Uri::url('goods/admin_category_store/remove')
        ));
		$this->assign('shopname', '1');
		$this->assign_lang();
		$this->display('category_list.dwt');
	}

	/**
	 * 添加商品分类
	 */
	public function add()
	{
	    $this->admin_priv('cat_manage');

		RC_Script::enqueue_script('goods_category_list', RC_App::apps_url('statics/js/goods_category_info.js',__FILE__), array(), false, false);

		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('添加商品分类')));
		$this->assign('ur_here', RC_Lang::lang('04_category_add'));
		$this->assign('action_link',  array('href' => RC_Uri::url('goods/admin_category/init'), 'text' => RC_Lang::lang('店铺商品分类')));

		$this->assign('goods_type_list',  goods_type_list(0)); // 取得商品类型
		$this->assign('attr_list',        get_category_attr_list()); // 取得商品属性
		$this->assign('cat_select',   cat_list(0, 0, true));
		$this->assign('cat_info',     array('is_show' => 1));

		$this->assign('form_action',RC_Uri::url('goods/admin_category/insert'));

		$this->assign_lang();
		$this->display('category_info.dwt');
	}

	/**
	 * 商品分类添加时的处理
	 */
	public function insert()
	{
		$this->admin_priv('cat_manage', ecjia::MSGTYPE_JSON);

		/* 对商品分类添加进行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对商品分类添加进行权限检查  BY：MaLiuWei  END */
		$cat['cat_id']       = !empty($_POST['cat_id'])       ? intval($_POST['cat_id'])     : 0;
		$cat['parent_id']    = !empty($_POST['parent_id'])    ? intval($_POST['parent_id'])  : 0;
		$cat['sort_order']   = !empty($_POST['sort_order'])   ? intval($_POST['sort_order']) : 0;
		$cat['keywords']     = !empty($_POST['keywords'])     ? trim($_POST['keywords'])     : '';
		$cat['cat_desc']     = !empty($_POST['cat_desc'])     ? $_POST['cat_desc']           : '';
		$cat['measure_unit'] = !empty($_POST['measure_unit']) ? trim($_POST['measure_unit']) : '';
		$cat['cat_name']     = !empty($_POST['cat_name'])     ? trim($_POST['cat_name'])     : '';
		$cat['show_in_nav']  = !empty($_POST['show_in_nav'])  ? intval($_POST['show_in_nav']): 0;
		$cat['is_show']      = !empty($_POST['is_show'])      ? intval($_POST['is_show'])    : 0;
		$cat['grade']        = !empty($_POST['grade'])        ? intval($_POST['grade'])      : 0;
		$cat['filter_attr']  = !empty($_POST['filter_attr'])  ? implode(',', array_unique(array_diff($_POST['filter_attr'],array(0)))) : 0;

		$cat['cat_recommend']  = !empty($_POST['cat_recommend'])  ? $_POST['cat_recommend'] : array();

		if (cat_exists($cat['cat_name'], $cat['parent_id'])) {
		    $this->showmessage(RC_Lang::lang('catname_exist'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}

		if($cat['grade'] > 10 || $cat['grade'] < 0) {
			$this->showmessage(RC_Lang::lang('grade_error'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}

		/* 上传分类图片 */
		$upload = RC_Upload::uploader('image', array('save_path' => 'data/category', 'auto_sub_dirs' => true));
		if ($upload->check_upload_file($_FILES['cat_img'])) {
			$image_info = $upload->upload($_FILES['cat_img']);
			if (empty($image_info)) {
				$this->showmessage($upload->error(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
			$cat['style'] = $image_info['savepath'] . '/' . $image_info['savename'];
		}

		/* 入库的操作 */
		$insert_id = $this->db_category->insert($cat);
		if($insert_id) {
			$cat_id = $insert_id;
			if($cat['show_in_nav'] == 1) {
				$vieworder = $this->db_nav->where('type = "middle"')->max('vieworder');
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
				$this->db_nav->insert($data);
			}
			insert_cat_recommend($cat['cat_recommend'], $cat_id);

			ecjia_admin::admin_log($_POST['cat_name'], 'add', 'category');   // 记录管理员操作

			/*添加链接*/
			$link[0]['text'] = RC_Lang::lang('continue_add');
			$link[0]['href'] = RC_Uri::url('goods/admin_category/add');

			$link[1]['text'] = RC_Lang::lang('back_list');
			$link[1]['href'] = RC_Uri::url('goods/admin_category/init');
			$this->showmessage(RC_Lang::lang('catadd_succed'),ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS,array('links' => $link,'max_id' => $insert_id));
		}
	}

	/**
	 * 编辑商品分类信息
	 */
	public function edit()
	{
		RC_Script::enqueue_script('goods_category_list', RC_App::apps_url('statics/js/goods_category_info.js',__FILE__), array(), false, false);


		$this->admin_priv('cat_manage');

		$cat_id = intval($_REQUEST['cat_id']);
		$cat_info = get_cat_info($cat_id);  // 查询分类信息数据
		$filter_attr_list = array();

		if ($cat_info['filter_attr']) {
			$filter_attr = explode(",", $cat_info['filter_attr']);  //把多个筛选属性放到数组中
			foreach ($filter_attr AS $k => $v) {
				$attr_cat_id = $this->db_attribute->where(array('attr_id' => intval($v)))->get_field('cat_id');

				$filter_attr_list[$k]['goods_type_list'] = goods_type_list($attr_cat_id[cat_id]);  //取得每个属性的商品类型
				$filter_attr_list[$k]['filter_attr'] = $v;
				$attr_option = array();
				$_REQUEST['cat_id'] = $attr_cat_id;
				$attr_list = get_attr_list();

				foreach ($attr_list['item'] as $val) {
					$attr_option[$val['attr_id']] = $val['attr_name'];
				}

				$filter_attr_list[$k]['option'] = $attr_option;
			}

			$this->assign('filter_attr_list', $filter_attr_list);
		} else {
			$attr_cat_id = 0;
		}

		/* 模板赋值 */
		$this->assign('attr_list',        $attr_list); // 取得商品属性
		$this->assign('attr_cat_id',      $attr_cat_id);

		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('编辑商品分类')));
		$this->assign('ur_here',     RC_Lang::lang('category_edit'));
		$this->assign('action_link', array('text' => '店铺商品分类', 'href' => RC_Uri::url('goods/admin_category_store/init')));

		$res = $this->db_cat->field('recommend_type')->where(array('cat_id' => $cat_id))->select();
		if (!empty($res)) {
			$cat_recommend = array();
			foreach($res as $data) {
				$cat_recommend[$data['recommend_type']] = 1;
			}
			$this->assign('cat_recommend', $cat_recommend);
		}

		$cat_info['style'] = !empty($cat_info['style']) ? RC_Upload::upload_url($cat_info['style']) : $cat_info['style'];
		$this->assign('cat_info',    $cat_info);
		$this->assign('cat_select',  cat_list(0, $cat_info['parent_id'], true));
		$this->assign('goods_type_list',  goods_type_list(0)); // 取得商品类型

		$this->assign('form_action',RC_Uri::url('goods/admin_category_store/update'));

		$this->assign_lang();
		$this->display('category_store_info.dwt');
	}

	public function choose_goods_type()
	{
		$attr_list = get_attr_list();
		$this->showmessage('', ecjia::MSGSTAT_SUCCESS | ecjia::MSGTYPE_JSON, array('attr_list' => $attr_list));
		// 		$category_id = empty($_REQUEST['cat_id']) ? '' : trim($_REQUEST['cat_id']);
	}

	public function add_category()
	{
		$parent_id = empty($_REQUEST['parent_id']) ? 0 : intval($_REQUEST['parent_id']);
		$category = empty($_REQUEST['cat']) ? '' : trim($_REQUEST['cat']);
		if(cat_exists($category, $parent_id)) {
			$this->showmessage(RC_Lang::lang('catname_exist'));
		} else {
			$data =array(
				'cat_name' 	=> $category,
				'parent_id'	=> $parent_id,
				'is_show' 	=> '1',

				);
			$category_id = $this->db_category->insert($data);

			$arr = array("parent_id" => $parent_id, "id" => $category_id, "cat" => $category);

			$this->showmessage('',ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS,array('content' => $arr));
		}
	}

	/**
	 * 编辑商品分类信息
	 */
	public function update()
	{
		$this->admin_priv('cat_manage', ecjia::MSGTYPE_JSON);

		/* 对 编辑商品分类信息进行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对 编辑商品分类信息进行权限检查  BY：MaLiuWei  END */
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
// 		$cat['style']        	= !empty($_POST['style'])        ? trim($_POST['style'])        : '';
		$cat['grade']       	= !empty($_POST['grade'])        ? intval($_POST['grade'])      : 0;
		$cat['filter_attr']		= !empty($_POST['filter_attr'])  ? implode(',', array_unique(array_diff($_POST['filter_attr'],array(0)))) : 0;
		$cat['cat_recommend']	= !empty($_POST['cat_recommend'])  ? $_POST['cat_recommend'] : array();

		/* 判断分类名是否重复 */
		if ($cat['cat_name'] != $old_cat_name) {
			if (cat_exists($cat['cat_name'],$cat['parent_id'], $cat_id)) {
				$link[] = array('text' => RC_Lang::lang('go_back'), 'href' => 'javascript:history.back(-1)');
				$this->showmessage(RC_Lang::lang('catname_exist'),ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR,array('links' => $link));
			}
		}

		/* 判断上级目录是否合法 */
		$children = array_keys(cat_list($cat_id, 0, false));     // 获得当前分类的所有下级分类
		if (in_array($cat['parent_id'], $children)) {
			/* 选定的父类是当前分类或当前分类的下级分类 */
			$link[] = array('text' => RC_Lang::lang('go_back'), 'href' => 'javascript:history.back(-1)');
			$this->showmessage(RC_Lang::lang('is_leaf_error'),ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR,array('links' => $link));
		}

		if($cat['grade'] > 10 || $cat['grade'] < 0) {
			/* 价格区间数超过范围 */
			$link[] = array('text' => RC_Lang::lang('go_back'), 'href' => 'javascript:history.back(-1)');
			$this->showmessage(RC_Lang::lang('grade_error'),ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR,array('links' => $link));
		}

		if (empty($_POST['old_img'])) {
			$file_name = $this->db_category->where(array('cat_id' => $cat_id))->get_field('style');
			$file_path = RC_Upload::upload_path($file_name);
			@unlink($file_path);
			$cat['style'] = '';
		}

		/* 更新分类图片 */
		$upload = RC_Upload::uploader('image', array('save_path' => 'data/category', 'auto_sub_dirs' => true));

		if ($upload->check_upload_file($_FILES['cat_img'])) {
			$image_info = $upload->upload($_FILES['cat_img']);
			if (empty($image_info)) {
				$this->showmessage($upload->error(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
			$cat['style'] = $image_info['savepath'] . '/' . $image_info['savename'];
		}

		$dat = $this->db_category->field('cat_name, show_in_nav')->find(array('cat_id' => $cat_id));
		if ($this->db_category->where(array('cat_id' => $cat_id))->update($cat)) {
			if($cat['cat_name'] != $dat['cat_name']) {
				/* 如果分类名称发生了改变 */
				$data = array(
					'name' => $cat['cat_name'],
					);
				$this->db_nav->where(array('ctype' => 'c', 'cid' => $cat_id, 'type' => 'middle'))->update($data);

			}
			if ($cat['show_in_nav'] != $dat['show_in_nav']) {
				/* 是否显示于导航栏发生了变化 */
				if ($cat['show_in_nav'] == 1) {
					/* 显示 */
					$nid = $this->db_nav->field('id')->find(array('ctype' => 'c','cid' => $cat_id, 'type' => 'middle'));
					if(empty($nid)) {
						/* 不存在 */
						$vieworder = $this->db_nav->where(array('type' => 'middle'))->max('vieworder');
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
						$this->db_nav->insert($data);
					} else {
						$data = array(
							'ifshow' => '1'
							);
						$this->db_nav->where(array('ctype' => 'c','cid' => $cat_id, 'type' => 'middle'))->update($data);
					}

				} else {
					/* 去除 */
					$data = array(
						'ifshow' => '0'
						);
					$this->db_nav->where(array('ctype' => 'c','cid' => $cat_id, 'type' => 'middle'))->update($data);
				}
			}

			/* 更新首页推荐 */
			insert_cat_recommend($cat['cat_recommend'], $cat_id);

			ecjia_admin::admin_log($_POST['cat_name'], 'edit', 'category');

			$link[] = array('text' => RC_Lang::lang('back_list'), 'href' => RC_Uri::url('goods/admin_category_store/init'));
			$this->showmessage(RC_Lang::lang('catedit_succed'),ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS,array('links' => $link,'max_id' => $cat_id));
		}
	}

	/**
	 * 批量转移商品分类页面
	 */
	public function move()
	{
		$this->admin_priv('cat_drop');

		$cat_id = !empty($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;

		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('转移商品')));
		$this->assign('ur_here',     RC_Lang::lang('move_goods'));
		$this->assign('action_link', array('href' => RC_Uri::url('goods/admin_category/init'), 'text' => RC_Lang::lang('03_category_list')));

		$this->assign('cat_select', cat_list(0, $cat_id, true));
		$this->assign('form_action',   RC_Uri::url('goods/admin_category/move_cat'));

		$this->assign_lang();
		$this->display('category_move.dwt');
	}

	/**
	 * 处理批量转移商品分类的处理程序
	 */
	public function move_cat()
	{
		$this->admin_priv('cat_drop');

		/* 对处理批量转移商品分类进行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对处理批量转移商品分类进行权限检查  BY：MaLiuWei  END */
		$cat_id        = !empty($_POST['cat_id'])        ? intval($_POST['cat_id'])        : 0;
		$target_cat_id = !empty($_POST['target_cat_id']) ? intval($_POST['target_cat_id']) : 0;

		/* 商品分类不允许为空 */
		if ($cat_id == 0 || $target_cat_id == 0) {
			$link[] = array('text' => RC_Lang::lang('go_back'), 'href' => RC_Uri::url('goods/admin_category/move'));
			$this->showmessage(RC_Lang::lang('cat_move_empty'),ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS,array('links' => $link));
		}
		/* 更新商品分类 */
		$data =array(
			'cat_id' => $target_cat_id
			);
		$goods_id = $this->db_goods->field('goods_id')->where(array('cat_id' => $cat_id))->select();
		$goods_ids = implode(',', array_column($goods_id, 'goods_id'));
		$new_cat_name = $this->db_category->where(array('cat_id' => $target_cat_id))->get_field('cat_name');
		$old_cat_name = $this->db_category->where(array('cat_id' => $cat_id))->get_field('cat_name');
		$query = $this->db_goods->where(array('cat_id' => $cat_id))->update($data);
		/*管理员记录日志*/
		ecjia_admin::admin_log($old_cat_name.'下商品'.$goods_ids.'转移到'.$new_cat_name, 'edit', 'category');
		if($query) {
			$this->showmessage(RC_Lang::lang('move_cat_success'),ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
		}
	}

	/**
	 * 编辑排序序号
	 */
	public function edit_sort_order()
	{
		$this->admin_priv('cat_manage', ecjia::MSGTYPE_JSON);
		/* 对编辑排序序号进行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对编辑排序序号进行权限检查  BY：MaLiuWei  END */
		$id = intval($_POST['pk']);
		$val = intval($_POST['value']);

		if (cat_update($id, array('sort_order' => $val))) {
			$this->showmessage('排序序号编辑成功！',ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS,array('pjaxurl' => RC_Uri::url('goods/admin_category/init')));
		} else {
			$this->showmessage($this->db_category->error(),ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}

	/**
	 * 编辑数量单位
	 */
	public function edit_measure_unit()
	{
		$this->admin_priv('cat_manage', ecjia::MSGTYPE_JSON);

		/*对编辑数量添加权限	 BY：MaLiuWei  START  */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/*对编辑数量添加权限	 BY：MaLiuWei  END  */
		$id = intval($_POST['pk']);
		$val = $_POST['value'];
		if (cat_update($id, array('measure_unit' => $val))) {
			$this->showmessage('数量单位编辑成功！',ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS,array('content' => $val));
		} else {
			$this->showmessage($this->db_category->error(),ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}

	/**
	 * 编辑价格分级
	 */
	public function edit_grade()
	{
		$this->admin_priv('cat_manage', ecjia::MSGTYPE_JSON);

		/*对编辑数量添加权限	 BY：MaLiuWei  START  */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/*对编辑数量添加权限	 BY：MaLiuWei  END  */
		$id = intval($_POST['pk']);
		$val = intval($_POST['value']);

		if($val > 10 || $val < 0) {
			/* 价格区间数超过范围 */
			$this->showmessage(RC_Lang::lang('grade_error'),ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}

		if (cat_update($id, array('grade' => $val))) {
			$this->showmessage('价格分级编辑成功！',ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS,array('content' => $val));
		} else {
			$this->showmessage($this->db_category->error(),ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}

	/**
	 * 切换是否显示
	 */
	public function toggle_is_show()
	{
		$this->admin_priv('cat_manage', ecjia::MSGTYPE_JSON);

		/*对切换是否显示添加权限	 BY：MaLiuWei  START  */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/*对切换是否显示添加权限	 BY：MaLiuWei  END  */
		$id = intval($_POST['id']);
		$val = intval($_POST['val']);
		$name = $this->db_category->where(array('cat_id' => $id))->get_field('cat_name');
		if (cat_update($id, array('is_show' => $val))) {
			/*增加管理员操作日志*/
			ecjia_admin::admin_log($name."切换显示状态", 'edit', 'category');
			$this->showmessage('是否显示编辑成功！',ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS,array('content' => $val));
		} else {
			$this->showmessage($this->db_category->error(),ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}

	/**
	 * 删除商品分类
	 */
	public function remove()
	{
    	$this->admin_priv('cat_manage', ecjia::MSGTYPE_JSON);

    	/*对删除商品分类添加权限	 BY：MaLiuWei  START  */
    	if (!empty($_SESSION['ru_id'])) {
    		$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
    	}
    	/*对删除商品分类添加权限	 BY：MaLiuWei  END  */

		/* 初始化分类ID并取得分类名称 */
		$cat_id   = intval($_GET['id']);
		$cat_name = $this->db_category->where(array('cat_id' => $cat_id))->get_field('cat_name');
		/* 当前分类下是否有子分类 */
		$cat_count = $this->db_category->where(array('parent_id' => $cat_id))->count();
		/* 当前分类下是否存在商品 */
		$goods_count = $this->db_goods->where(array('cat_id' => $cat_id))->count();
		/* 如果不存在下级子分类和商品，则删除之 */
		if ($cat_count == 0 && $goods_count == 0) {
			/* 删除分类 */
			$query = $this->db_category->where(array('cat_id' => $cat_id))->delete();
			if ($query) {
				$this->db_nav->where(array('ctype' => 'c', 'cid' => $cat_id, 'type' => 'middle'))->delete();
				ecjia_admin::admin_log($cat_name, 'remove', 'category');
				$this->showmessage(__('删除商品分类成功'),ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
			}
		} else {
			$this->showmessage($cat_name .' '. RC_Lang::lang('cat_isleaf'),ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}
}

// end
