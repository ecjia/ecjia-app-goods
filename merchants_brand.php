<?php
/**
 *  ECJIA 管理中心品牌管理
 */
defined('IN_ECJIA') or exit('No permission resources.');

class merchants_brand extends ecjia_admin {
	private $db_brand;
	private $db_goods;
	private $db_merchants_brand;
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
		
		$this->db_brand = RC_Loader::load_app_model('brand_model','goods');
		$this->db_merchants_brand = RC_Loader::load_app_model('merchants_shop_brand_model','seller');
		$this->db_goods = RC_Loader::load_app_model('goods_model','goods');
	}
	
	/**
	 * 品牌列表
	 */
	public function init() {
	    $this->admin_priv('merchants_brand');
		RC_Script::enqueue_script('bootstrap-editable-script', RC_Uri::admin_url() . '/statics/lib/x-editable/bootstrap-editable/js/bootstrap-editable.min.js', array(), false, true);
		RC_Style::enqueue_style('bootstrap-editable-css', RC_Uri::admin_url() . '/statics/lib/x-editable/bootstrap-editable/css/bootstrap-editable.css');
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商家品牌')));
		
		$this->assign('ur_here', '商家品牌');
		$brand_list = get_merchants_brandlist();
		$this->assign('brand_list', $brand_list);
		
		$this->assign_lang();
		$this->display('merchants_brand_list.dwt');
	}

	/**
	 * 编辑品牌
	 */
	public function edit() {
	    $this->admin_priv('merchants_brand');
	    ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商家品牌') , RC_Uri::url('goods/merchants_brand/init')));
	    ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('编辑商家品牌')));
	    
		$brand_id =  intval($_GET['id']);
		/* 取得品牌数据 */
		$brand_arr = $this->db_merchants_brand->field('bid, brandName, site_url, brandLogo, brand_desc, is_show, sort_order')->find(array('bid' => $_GET['id']));
		/* 标记为图片链接还是文字链接 */
		if (!empty($brand_arr['brandLogo'])) {
			if (strpos($brand_arr['brandLogo'], 'http://') === false) {
				$brand_arr['type']	= 1;
				$brand_arr['url']	= RC_Upload::upload_url($brand_arr['brandLogo']);
			} else {
				$brand_arr['type']	= 0;
				$brand_arr['url'] = $brand_arr['brandLogo'];
			}
		} else {
			$brand_arr['type']	= 0;
		}
		
		$this->assign('ur_here', RC_Lang::lang('brand_edit'));
		$this->assign('action_link', array('text' => '返回商家品牌', 'href' => RC_Uri::url('goods/merchants_brand/init')));
		$this->assign('brand', $brand_arr);
		$this->assign('form_action', RC_Uri::url('goods/merchants_brand/update&id='.$brand_arr['bid']));
		$this->assign_lang();
		
		$this->display('merchants_brand_info.dwt');
	}
	
	/**
	 * 编辑品牌处理
	 */
	public function update() {
	    $this->admin_priv('merchants_brand', ecjia::MSGTYPE_JSON);

		$id= !empty($_GET['id']) ? intval($_GET['id']) : 0;
		$is_show = isset($_GET['is_show']) ? intval($_GET['is_show']) : 0;
		/*处理URL*/
		$site_url = RC_Format::sanitize_url( $_POST['site_url'] );
		/* 获取旧的LOGO地址,并删除 */
		$old_logo = $this->db_merchants_brand->where(array('bid' => $id))->get_field('brandLogo');
		
		/* 如果有图片LOGO要上传 */
		if ((isset($_FILES['brand_img']['error']) && $_FILES['brand_img']['error'] == 0) ||
			(!isset($_FILES['brand_img']['error']) && isset($_FILES['brand_img']['tmp_name']) && $_FILES['brand_img']['tmp_name'] != 'none')) {
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
		$brandname = htmlspecialchars($_POST['brandName']);
		if(empty($brandname)){
			$this->showmessage(__('请填写商品品牌'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 更新信息 */
		$data = array(
			'brandName'		=> $brandname,
			'site_url' 		=> htmlspecialchars($site_url),
			'brand_desc' 	=> htmlspecialchars($_POST['brand_desc']),
			'is_show' 		=> intval($is_show),
			'brandLogo' 	=> $brand_logo,
			'sort_order' 	=> intval($_POST['sort_order']),
		);
		if ($this->db_merchants_brand->where(array('bid' => $id))->update($data)) {
			ecjia_admin::admin_log($_POST['brandName'], 'edit', 'brand');
			
			$link[0]['text'] = RC_Lang::lang('back_list');
			$link[0]['href'] = RC_Uri::url('goods/merchants_brand/init');
			$note = vsprintf(RC_Lang::lang('brandedit_succed'), empty($_POST['brand_name']) ? '' : $_POST['brand_name']);

			$this->showmessage($note, ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('links' => $link, 'max_id' => $id));
		} else {
			$this->showmessage($this->db_brand->error(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
    }

	/**
	 * 编辑品牌名称
	 */
	public function edit_brand_name() {
		$this->admin_priv('merchants_brand', ecjia::MSGTYPE_JSON);

		$id     = intval($_POST['pk']);
		$name   = trim($_POST['value']);
		if (empty($name)) {
		    $this->showmessage('品牌名称不能为空', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		if ($this->db_merchants_brand->where(array('bid' => $id))->update(array('brandName' => $name))) {
			ecjia_admin::admin_log($name,'edit','brand');
			$this->showmessage('品牌名称编辑成功', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content'=> stripslashes($name)));
		} else {
			$this->showmessage('品牌名称编辑失败', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR, array('content'=> stripslashes($name)));
		}
	}
	
	/**
	 * 编辑排序序号
	 */
	public function edit_sort_order() {
		$this->admin_priv('merchants_brand', ecjia::MSGTYPE_JSON);

		$id     = intval($_POST['pk']);
		$order  = intval($_POST['value']);
		if (empty($order)){
		    $this->showmessage(__('排序不能为空'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		$name   = $this->db_merchants_brand->where(array('bid' => $id))->get_field('brandName');
		if ($this->db_merchants_brand->where(array('bid' => $id))->update(array('sort_order' => $order))) {
			ecjia_admin::admin_log(addslashes($name),'edit','brand');
			$this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('goods/merchants_brand/init', array('content'=> $order))));
		} else {
			$this->showmessage(sprintf(RC_Lang::lang('brandedit_fail'), $name), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}
	
	/**
	 * 切换是否显示
	 */
	public function toggle_show() {
		$this->admin_priv('merchants_brand', ecjia::MSGTYPE_JSON);
		
		if (empty($_SESSION['ru_id'])) {
			$id     = intval($_POST['id']);
			$val    = intval($_POST['val']);
			$data = array('is_show' => $val);
			
			$this->db_merchants_brand->where(array('bid' => $id))->update($data);
			$name = $this->db_merchants_brand->where(array('bid' => $id))->get_field('brandName');
		
			ecjia_admin::admin_log($name.'切换显示状态', 'edit', 'merchant_brand');
		}
		$this->showmessage('成功切换显示状态', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => $val));
	}
	
	/**
	 * 删除品牌
	 */
	public function remove() {
		$this->admin_priv('merchants_brand', ecjia::MSGTYPE_JSON);
		
		$id = intval($_GET['id']);
		/* 删除该品牌的图标 */
		$logo_name = $this->db_merchants_brand->where(array('bid' => $id))->get_field('brandLogo');
		if (!empty($logo_name)) {
			$disk = RC_Filesystem::disk();
			$disk->delete(RC_Upload::upload_path() . $logo_name);
		}
		$this->db_merchants_brand->where(array('bid' => $id))->delete();
		$name = $this->db_merchants_brand->where(array('bid' => $id))->get_field('brandName');
		/* 记录日志 */
		ecjia_admin::admin_log($name, 'remove', 'merchant_brand');

		/* 更新商品的品牌编号 */
		$data = array('brand_id' => '0');
		$this->db_goods->where(array('brand_id' => $id))->update($data);
		$this->showmessage(RC_Lang::lang('drop_success'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
	}
	
	/**
	 * 删除品牌图片
	 */
	public function drop_logo()	{
	    $this->admin_priv('merchants_brand', ecjia::MSGTYPE_JSON);
	  
		$brand_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		$logo_name = $this->db_brand->where(array('brand_id' => $brand_id))->get_field('brand_logo');
		if (!empty($logo_name)) {
			$disk = RC_Filesystem::disk();
			$disk->delete(RC_Upload::upload_path() . $logo_name);
			
			$data = array('brand_logo' => '');
			$this->db_brand->where(array('brand_id' => $brand_id))->update($data);
		}
		$link= array(array('text' => RC_Lang::lang('brand_edit_lnk'), 'href' => RC_Uri::url('goods/merchants_brand/edit', 'id='.$brand_id)), array('text' => RC_Lang::lang('brand_list_lnk'), 'href' => RC_Uri::url('goods/merchants_brand/init')));
		$this->showmessage(RC_Lang::lang('drop_brand_logo_success'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('links' => $link));
	}
}

// end