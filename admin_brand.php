<?php
/**
 *  ECJIA 管理中心品牌管理
 */
defined('IN_ECJIA') or exit('No permission resources.');

class admin_brand extends ecjia_admin {
	private $db_brand;
	private $db_goods;
	public function __construct() {
		parent::__construct();
		RC_Lang::load('brand');
		
		/* 加载所全局 js/css */
		RC_Script::enqueue_script('bootstrap-placeholder');
		RC_Script::enqueue_script('jquery-validate');
		RC_Script::enqueue_script('jquery-form');
		RC_Script::enqueue_script('smoke');
		RC_Script::enqueue_script('jquery-uniform');
		RC_Style::enqueue_style('uniform-aristo');
		RC_Script::enqueue_script('goods_brand', RC_Uri::home_url('content/apps/goods/statics/js/goods_brand.js'), array());

		RC_Loader::load_app_func('common');
		RC_Loader::load_app_func('functions');
		assign_adminlog_content();

		$this->db_brand = RC_Model::model('goods/brand_model');
		$this->db_goods = RC_Model::model('goods/goods_model');
	}
	
	/**
	 * 品牌列表
	 */
	public function init() {
	    $this->admin_priv('brand_manage');
		RC_Script::enqueue_script('bootstrap-editable-script', RC_Uri::admin_url() . '/statics/lib/x-editable/bootstrap-editable/js/bootstrap-editable.min.js', array(), false, true);
		RC_Style::enqueue_style('bootstrap-editable-css', RC_Uri::admin_url() . '/statics/lib/x-editable/bootstrap-editable/css/bootstrap-editable.css');
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('自营品牌')));
		
		$this->assign('ur_here', '自营品牌');
		$this->assign('action_link', array('text' => RC_Lang::lang('07_brand_add'), 'href' => RC_Uri::url('goods/admin_brand/add')));
		$brand_list = get_brandlist();
		$this->assign('brand_list', $brand_list);
		
		$this->assign_lang();
		$this->display('brand_list.dwt');
	}
	
	/**
	 * 添加品牌
	 */
	public function add() {
	    $this->admin_priv('brand_manage');
	    ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('自营品牌'), RC_Uri::url('goods/admin_brand/init')));
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('添加品牌')));
		
		$this->assign('ur_here', RC_Lang::lang('07_brand_add'));
		$this->assign('action_link', array('text' => RC_Lang::lang('06_goods_brand_list'), 'href' => RC_Uri::url('goods/admin_brand/init')));
		$this->assign('brand', array('sort_order' => 50, 'is_show' => 1));
		$this->assign('form_action', RC_Uri::url('goods/admin_brand/insert'));
		$this->assign_lang();
		
		$this->display('brand_info.dwt');
	}
	
	/**
	 * 处理添加品牌
	 */
	public function insert() {
    	/*检查品牌名是否重复*/
		$this->admin_priv('brand_manage', ecjia::MSGTYPE_JSON);
		
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		$brand_logo	= '';
		$is_show = isset($_REQUEST['is_show']) ? intval($_REQUEST['is_show']) : 0;
		
		$is_only = $this->db_brand->where(array('brand_name' => $_POST['brand_name']))->count();
		if ($is_only != 0) {
			$this->showmessage(sprintf(RC_Lang::lang('brandname_exist'), stripslashes($_POST['brand_name'])),ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		
		/*对描述处理*/
		if (!empty($_POST['brand_desc'])) {
			$_POST['brand_desc'] = $_POST['brand_desc'];
		}
		
		/* 处理上传的LOGO图片 */
		if ((isset($_FILES['brand_img']['error']) && $_FILES['brand_img']['error'] == 0) || (!isset($_FILES['brand_img']['error']) && isset($_FILES['brand_img']['tmp_name']) && $_FILES['brand_img']['tmp_name'] != 'none')) {
			$upload = RC_Upload::uploader('image', array('save_path' => 'data/brandlogo', 'auto_sub_dirs' => false));
    		$info 	= $upload->upload($_FILES['brand_img']);
    		if (!empty($info)) {
    			$brand_logo = $info['savepath'] . '/' . $info['savename'];
    		} else {
    			$this->showmessage($upload->error(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
    		}
	   }

    	/* 使用远程的LOGO图片 */
    	if (!empty($_POST['url_logo']))	{
    		if (strpos($_POST['url_logo'], 'http://') === false && strpos($_POST['url_logo'], 'https://') === false) {
    			$brand_logo = 'http://' . trim($_POST['url_logo']);
    		} else {
    			$brand_logo = trim($_POST['url_logo']);
    		}
    	}
    
    	/*处理URL*/
    	$site_url = RC_Format::sanitize_url( $_POST['site_url'] );
    	$brandname = htmlspecialchars($_POST['brand_name']);
    	if (empty($brandname)){
    		$this->showmessage(__('请填写商品品牌名称'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
    	}
    	/*插入数据*/
    	$_POST = array(
    		'brand_name'	=> $brandname,
    		'site_url'		=> $site_url,
    		'brand_desc'  	=> $_POST['brand_desc'],
    		'brand_logo'	=> $brand_logo,
    		'is_show'		=> $is_show,
    		'sort_order'	=> $_POST['sort_order'],
    	);
    
    	$brand_id = $this->db_brand->insert();
    	ecjia_admin::admin_log($_POST['brand_name'],'add','brand');
    
    	$link[0]['text'] = RC_Lang::lang('back_list');
    	$link[0]['href'] = RC_Uri::url('goods/admin_brand/init');
    	
    	$link[1]['text'] = RC_Lang::lang('continue_add');
    	$link[1]['href'] = RC_Uri::url('goods/admin_brand/add');
    	$this->showmessage(RC_Lang::lang('brandadd_succed'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('goods/admin_brand/edit', "id=$brand_id"), 'links' => $link, 'max_id' => $brand_id));
    }

	/**
	 * 编辑品牌
	 */
	public function edit() {
	    $this->admin_priv('brand_manage');
	    
	    $this->assign('ur_here', RC_Lang::lang('brand_edit'));
	    ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('自营品牌'), RC_Uri::url('goods/admin_brand/init')));
	    ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('编辑品牌')));
	    
		$brand_id =  intval($_GET['id']);
		/* 取得品牌数据 */
		$brand_arr = $this->db_brand->field('brand_id, brand_name, site_url, brand_logo, brand_desc, brand_logo, is_show, sort_order')->find(array('brand_id' => $_REQUEST['id']));
		/* 标记为图片链接还是文字链接 */
		if (!empty($brand_arr['brand_logo'])) {
			if (strpos($brand_arr['brand_logo'], 'http://') === false) {
				$brand_arr['type']	= 1;
				$brand_arr['url']	= RC_Upload::upload_url($brand_arr['brand_logo']);
			} else {
				$brand_arr['type']	= 0;
				$brand_arr['url'] = $brand_arr['brand_logo'];
			}
		} else {
			$brand_arr['type']	= 0;
		}
		$this->assign('action_link', array('text' => RC_Lang::lang('06_goods_brand_list'), 'href' => RC_Uri::url('goods/admin_brand/init')));
		$this->assign('brand', $brand_arr);
		$this->assign('form_action', RC_Uri::url('goods/admin_brand/update'));
		$this->assign_lang();
		
		$this->display('brand_info.dwt');
	}
	
	/**
	 * 编辑品牌处理
	 */
	public function update() {
	    $this->admin_priv('brand_manage', ecjia::MSGTYPE_JSON);
	    
	    if (!empty($_SESSION['ru_id'])) {
	    	$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
	    }
		$id= (!empty($_REQUEST['id'])) ? intval($_REQUEST['id']) : 0;
		if ($_POST['brand_name'] != $_POST['old_brandname']) {
			/*检查品牌名是否相同*/
			$is_only = $this->db_brand->where(array('brand_name' => $_POST['brand_name'],'brand_id' => $_POST['id']))->count();
			if ($is_only != 0){
				$this->showmessage(sprintf(RC_Lang::lang('brandname_exist'), stripslashes($_POST['brand_name'])), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
		}
		/*对描述处理*/
		if (!empty($_POST['brand_desc'])) {
			$_POST['brand_desc'] = $_POST['brand_desc'];
		}
		
		$is_show = isset($_REQUEST['is_show']) ? intval($_REQUEST['is_show']) : 0;
		/*处理URL*/
		$site_url = RC_Format::sanitize_url( $_POST['site_url'] );
		/* 获取旧的LOGO地址,并删除 */
		$old_logo = $this->db_brand->where(array('brand_id' => $id))->get_field('brand_logo');
		/* 如果有图片LOGO要上传 */
		if ((isset($_FILES['brand_img']['error']) && $_FILES['brand_img']['error'] == 0) || (!isset($_FILES['brand_img']['error']) && isset($_FILES['brand_img']['tmp_name']) && $_FILES['brand_img']['tmp_name'] != 'none')) {
			$upload = RC_Upload::uploader('image', array('save_path' => 'data/brandlogo', 'auto_sub_dirs' => false));
			$info = $upload->upload($_FILES['brand_img']);

			/* 如果要修改链接图片, 删除原来的图片 */
			if (!empty($info)) {
				if ((strpos($old_logo, 'http://') === false) && (strpos($old_logo, 'https://') === false)) {
					$upload->remove($old_logo);
				}
				/* 获取新上传的LOGO的链接地址 */
				$brand_logo = $info['savepath'] . '/' . $info['savename'];
			} else {
				$this->showmessage($upload->error(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
		} elseif (!empty($_POST['url_logo'])) {
			if (strpos($_POST['url_logo'], 'http://') === false && strpos($_POST['url_logo'], 'https://') === false) {
				$brand_logo = 'http://' . $_POST['url_logo'];
			} else {
    			$brand_logo = trim($_POST['url_logo']);
    		}
		} else {
			/* 如果没有修改图片字段，则图片为之前的图片*/
			$brand_logo = $old_logo;
		}
		$brandname = htmlspecialchars($_POST['brand_name']);
		if (empty($brandname)){
		    $this->showmessage(__('请填写商品品牌'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 更新信息 */
		$data = array(
			'brand_name'	=> $brandname,
			'site_url' 		=> $site_url,
			'brand_desc' 	=> $_POST['brand_desc'],
			'is_show' 		=> $is_show,
			'brand_logo' 	=> $brand_logo,
			'sort_order' 	=> !empty($_POST['sort_order']) ? intval($_POST['sort_order']) : 0
		);
		if ($this->db_brand->where(array('brand_id' => $_POST['id']))->update($data)){
			ecjia_admin::admin_log($_POST['brand_name'], 'edit', 'brand');
			$note = vsprintf(RC_Lang::lang('brandedit_succed'), $_POST['brand_name']);

			$this->showmessage($note, ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('max_id' => $id));
		} else {
			$this->showmessage($this->db_brand->error(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
    }

	/**
	 * 编辑品牌名称
	 */
	public function edit_brand_name() {
		$this->admin_priv('brand_manage', ecjia::MSGTYPE_JSON);
		
		/* 对编辑品牌名称进行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对编辑品牌名称进行权限检查  BY：MaLiuWei  END */
		$id     = intval($_POST['pk']);
		$name   = trim($_POST['value']);
		if (empty($name)){
		    $this->showmessage('品牌名称不能为空', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 检查名称是否重复 */
		if ($this->db_brand->where(array('brand_name' => $name,'brand_id' => array('neq' => $id)))->count() != 0) {
			$this->showmessage(__("品牌 $name 已经存在！"), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		} else {
			if ($this->db_brand->where(array('brand_id' => $id))->update(array('brand_name' => $name))) {
				ecjia_admin::admin_log($name,'edit','brand');
				$this->showmessage('品牌名称编辑成功', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content'=> stripslashes($name)));
			} else {
				$this->showmessage('品牌名称编辑失败', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR, array('content'=> stripslashes($name)));
			}
		}
	}
	
	/**
	 * 编辑排序序号
	 */
	public function edit_sort_order() {
		$this->admin_priv('brand_manage', ecjia::MSGTYPE_JSON);
		
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		$id     = intval($_POST['pk']);
		$order  = !empty($_POST['value']) ? intval($_POST['value']) : 0;
		if (empty($order)){
		    $this->showmessage(__('排序不能为空'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		$name   = $this->db_brand->where(array('brand_id' => $id))->get_field('brand_name');
		if ($this->db_brand->where(array('brand_id' => $id))->update(array('sort_order' => $order))) {
			ecjia_admin::admin_log(addslashes($name), 'edit', 'brand');
			$this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('goods/admin_brand/init', array('content'=> $order))));
		} else {
			$this->showmessage(sprintf(RC_Lang::lang('brandedit_fail'), $name), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}
	
	/**
	 * 切换是否显示
	 */
	public function toggle_show() {
		$this->admin_priv('brand_manage', ecjia::MSGTYPE_JSON);
	
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		$id   = intval($_POST['id']);
		$val  = intval($_POST['val']);
		$data = array('is_show' => $val);
		$this->db_brand->where(array('brand_id' => $id))->update($data);
		$name = $this->db_brand->where(array('brand_id' => $id))->get_field('brand_name');
		/*记录日志*/
        if ($val) {
            ecjia_admin::admin_log('显示品牌，品牌名是'.$name, 'setup', 'brands');
        } else {
            ecjia_admin::admin_log('隐藏品牌，品牌名是'.$name, 'setup', 'brands');
        }
		$this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => $val));
	}
	
	public function add_brand() {
	    $this->admin_priv('brand_manage', ecjia::MSGTYPE_JSON);
		$brand = empty($_REQUEST['brand']) ? '' : trim($_REQUEST['brand']);
		if (brand_exists($brand)) {
			$this->showmessage(RC_Lang::lang('brand_name_exist'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		} else {
			$_POST = array(
				'brand_name' => $brand	
			);
			$brand_id = $this->db_brand->insert();
			$arr = array("id" => $brand_id, "brand" => $brand);
			$this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content'=> $arr));
		}
	}
	
	/**
	 * 删除品牌
	 */
	public function remove() {
		$this->admin_priv('brand_manage', ecjia::MSGTYPE_JSON);
		
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		$id = intval($_GET['id']);
		/* 删除该品牌的图标 */
		$logo_name = $this->db_brand->where(array('brand_id' => $id))->get_field('brand_logo');
		if (!empty($logo_name)) {
			$disk = RC_Filesystem::disk();
			$disk->delete(RC_Upload::upload_path() . $logo_name);
		}
		$name = $this->db_brand->where(array('brand_id' => $id))->get_field('brand_name');
		$this->db_brand->where(array('brand_id' => $id))->delete();
		/*记录日志*/
		ecjia_admin::admin_log($name, 'remove', 'brands');

		/* 更新商品的品牌编号 */
		$data = array('brand_id' => '0');
		$this->db_goods->where(array('brand_id' => $id))->update($data);
		$this->showmessage(RC_Lang::lang('drop_success'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
	}
	
	/**
	 * 删除品牌图片
	 */
	public function drop_logo() {
	    $this->admin_priv('brand_manage', ecjia::MSGTYPE_JSON);
	    
	    if (!empty($_SESSION['ru_id'])) {
	    	$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
	    }

	    $brand_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		$logo_name = $this->db_brand->where(array('brand_id' => $brand_id))->get_field('brand_logo');
		if (!empty($logo_name)) {
			$disk = RC_Filesystem::disk();
			$disk->delete(RC_Upload::upload_path() . $logo_name);
			$data = array(
				'brand_logo' => ''
			);
			$this->db_brand->where(array('brand_id' => $brand_id))->update($data);
		}
		$link= array(array('text' => RC_Lang::lang('brand_edit_lnk'), 'href' => RC_Uri::url('goods/admin_brand/edit', 'id='.$brand_id)), array('text' => RC_Lang::lang('brand_list_lnk'), 'href' => RC_Uri::url('goods/admin_brand/init')));
		$this->showmessage(RC_Lang::lang('drop_brand_logo_success'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('links' => $link));
	}
}

// end