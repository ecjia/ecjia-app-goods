<?php
/**
*  ECJIA 商品模式
*/
defined('IN_ECJIA') or exit('No permission resources.');

class admin_goods_mode extends ecjia_admin {
	private $db_goods;
	private $area_goods;
	private $warehouse_goods;
	private $warehouse_region;
	private $region_warehouse;

	public function __construct() {
		parent::__construct();

		$this->db_goods			= RC_Loader::load_app_model('goods_model');
		$this->area_goods		= RC_Loader::load_app_model('warehouse_area_goods_model');
		$this->warehouse_goods	= RC_Loader::load_app_model('warehouse_goods_model');
		$this->warehouse_region	= RC_Loader::load_app_model('warehouse_region_model');
		$this->region_warehouse	= RC_Loader::load_app_model('region_warehouse_model');

		RC_Loader::load_app_func('warehouse');
	}

	/**
	* 商品模式
	*/
	public function init() {
		$this->admin_priv('goods_manage');

		RC_Script::enqueue_script('goods_list', RC_App::apps_url('statics/js/goods_list.js', __FILE__), array('ecjia-common', 'ecjia-utils', 'smoke', 'jquery-validate', 'jquery-form', 'bootstrap-placeholder', 'jquery-wookmark', 'jquery-imagesloaded', 'jquery-colorbox'));
		RC_Script::enqueue_script('jquery-dropper', RC_Uri::admin_url() . '/statics/lib/dropper-upload/jquery.fs.dropper.js', array(), false, true);

		RC_Style::enqueue_style('jquery-colorbox');
		RC_Script::enqueue_script('jquery-chosen');
		RC_Style::enqueue_style('chosen');
		RC_Script::enqueue_script('replenish_list', RC_App::apps_url('statics/js/replenish_list.js', __FILE__), array());
		RC_Script::enqueue_script('batch_card_add', RC_App::apps_url('statics/js/batch_card_add.js', __FILE__), array());
		RC_Style::enqueue_style('goods-colorpicker-style', RC_Uri::admin_url() . '/statics/lib/colorpicker/css/colorpicker.css');
		RC_Script::enqueue_script('goods-colorpicker-script', RC_Uri::admin_url('/statics/lib/colorpicker/bootstrap-colorpicker.js'), array());
		RC_Script::enqueue_script('bootstrap-editable-script', RC_Uri::admin_url() . '/statics/lib/x-editable/bootstrap-editable/js/bootstrap-editable.min.js', array(), false, true);
		RC_Style::enqueue_style('bootstrap-editable-css', RC_Uri::admin_url() . '/statics/lib/x-editable/bootstrap-editable/css/bootstrap-editable.css');
		RC_Script::enqueue_script('jquery-uniform');
		RC_Style::enqueue_style('uniform-aristo');
		RC_Script::enqueue_script('jq_quicksearch', RC_Uri::admin_url() . '/statics/lib/multi-select/js/jquery.quicksearch.js', array('jquery'), false, true);

		RC_Loader::load_app_func('system_goods');
		$goods_list_jslang = array(
			'user_rank_list'	=> get_user_rank_list(),
			'marketPriceRate'	=> ecjia::config('market_price_rate'),
			'integralPercent'	=> ecjia::config('integral_percent'),
		);
		RC_Script::localize_script( 'goods_list', 'admin_goodsList_lang', $goods_list_jslang );

		RC_Style::enqueue_style('goodsapi', RC_Uri::home_url('content/apps/goods/statics/styles/goodsapi.css'));
		RC_Script::enqueue_script('ecjia-region',RC_Uri::admin_url('statics/ecjia.js/ecjia.region.js'), array('jquery'), false, true);
		
		$goods_id = intval($_GET['goods_id']);
		$price_model = $this->db_goods->where(array('goods_id' => $goods_id))->get_field('model_price');
		$inventory_model = $this->db_goods->where(array('goods_id' => $goods_id))->get_field(' model_inventory');

		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商品列表'), RC_Uri::url('goods/admin/init')));
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('编辑商品模式')));	
		$warehouse_goods = get_warehouse_goods_list($goods_id);
		$area_goods = get_warehouse_area_goods_list($goods_id);
		$warehouse = $this->region_warehouse->where(array('parent_id' => 0, 'region_type' => 0))->select();
		
		$code = isset($_GET['extension_code']) ? 'virtual_card' : '';
		$this->assign('code',				$code);
		$this->assign('repertory', 			$warehouse);
		$this->assign('action_link', 		array('href' =>  RC_Uri::url('goods/admin/init'), 'text' => RC_Lang::lang('01_goods_list')));
		$this->assign('price_model', 		$price_model);
		$this->assign('inventory_model', 	$inventory_model);
		$this->assign('warehouse_goods', 	$warehouse_goods);
		$this->assign('area_goods', 		$area_goods);
		$this->assign('goods_id', 			$goods_id);
		$this->assign('form_insert', 		RC_Uri::url('goods/admin_goods_mode/insert_warehouse_goods&goods_id='.$goods_id));
		$this->assign('form_action2', 		RC_Uri::url('goods/admin_goods_mode/insert_area&goods_id='.$goods_id));
		$this->assign('form_area_url', 		RC_Uri::url('goods/admin_goods_mode/update_area&goods_id='.$goods_id));
		$this->assign('form_edit', 			RC_Uri::url('goods/admin_goods_mode/update_warehouse_goods&goods_id='.$goods_id));
		$this->assign('form', 				RC_Uri::url('goods/admin_goods_mode/update_goodsmodel&goods_id='.$goods_id));
		$this->assign('ur_here',			__('编辑商品模式'));

		$this->display('goods_mode.dwt');
	}

	/**
	* 编辑商品模式  
	*/
	public function update_goodsmodel() {
		$this->admin_priv('goods_manage');
		
		$id = intval($_GET['goods_id']);
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}

		 /*商品模式*/
		$model_price 		= !empty($_POST['model_price']) 	? intval($_POST['model_price']) 	: 0;
		$model_inventory 	= !empty($_POST['model_inventory']) ? intval($_POST['model_inventory']) : 0;
		$goods_data = array(
			'model_price' 		=> $model_price,
			'model_inventory' 	=> $model_inventory,
		);
		$arr = $this->db_goods->where(array('goods_id' => $id))->update($goods_data);
		if ($arr) {
			$this->showmessage(__('成功修改商品模式'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS,  array('pjaxurl' => RC_Uri::url('goods/admin_goods_mode/init&goods_id='.$id)));
		}
	}

	/**
	* 增加商品地区模式
	*/
	public function insert_area() {
		$this->admin_priv('goods_manage');
		
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	
		$region_name			= !empty($_POST['region_name'])		     	? intval($_POST['region_name'])				: 0;
		$warehouse_area_name	= !empty($_POST['warehouse_area_name'])		? intval($_POST['warehouse_area_name'])		: 0;
		$region_number			= !empty($_POST['region_number'])			? intval($_POST['region_number'])			: 0;
		$region_price			= !empty($_POST['region_price'])			? intval($_POST['region_price'])			: 0;
		$region_promote_price	= !empty($_POST['region_promote_price'])	? intval($_POST['region_promote_price'])	: 0;
		
		$id = intval($_GET['goods_id']);
		$ru_id = $this->db_goods->where(array('goods_id' => $id))->get_field('user_id');

		if (empty($warehouse_area_name)) {
			$this->showmessage(__('请选择仓库'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		if (empty($region_name)) {
			$this->showmessage(__('请选择地区'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		if (empty($region_number)) {
			$this->showmessage(__('库存不能为空'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		if (empty($region_price)) {
			$this->showmessage(__('价格不能为空'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/*仓库库存是否存在*/
		$count = $this->area_goods->where(array('goods_id' => $id, 'region_id' => $region_name, 'user_id' => $ru_id))->count();
		if (!empty($count)) {
			$this->showmessage(__('该地区的商品模式已存在'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		$ru_id = $this->db_goods->where(array('goods_id' => $id))->get_field('user_id');
		$warehouse_area_goods = array(
			'goods_id' 					=> $id,
			'region_id' 				=> $region_name,
			'region_number' 			=> $region_number,
			'region_price' 			    => $region_price,
			'region_promote_price' 	    => $region_promote_price,
			'user_id' 					=> $ru_id,
			'add_time' 					=> RC_Time::gmtime(),
		);
		$arr = $this->area_goods->insert($warehouse_area_goods);
		
		if ($arr) {
			$this->showmessage(__('操作成功'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('goods/admin_goods_mode/init&goods_id='.$id)));
		}
	}

	/**
	* 增加商品仓库模式
	*/
	public function insert_warehouse_goods() {	
		$this->admin_priv('goods_manage');

		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}

		$warehouse_name				= !empty($_POST['warehouse_name'])			? intval($_POST['warehouse_name'])			: 0;
		$warehouse_number			= !empty($_POST['warehouse_number'])		? intval($_POST['warehouse_number'])		: 0;
		$warehouse_price			= !empty($_POST['warehouse_price'])			? intval($_POST['warehouse_price'])			: 0;
		$warehouse_promote_price 	= !empty($_POST['warehouse_promote_price'])	? intval($_POST['warehouse_promote_price'])	: 0;
		$id = intval($_GET['goods_id']);

		if (empty($warehouse_name)) {
			$this->showmessage(__('请选择仓库名称'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		if (empty($warehouse_number)) {
			$this->showmessage(__('库存不能为空'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		if (empty($warehouse_price)) {
			$this->showmessage(__('价格不能为空'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}

		/*商品的地区价格是否存在*/
		$count = $this->warehouse_goods->where(array('goods_id' => $id, 'region_id' => $warehouse_name, 'user_id' => $_SESSION['ru_id']))->count();
		if (!empty($count)) {
			$this->showmessage(__('该商品的地区价格已存在'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		
		$ru_id = $this->db_goods->where(array('goods_id' => $id))->get_field('user_id');
		$warehouse_goods = array(
			'goods_id' 				    => $id,
			'region_id' 			    => $warehouse_name,
			'region_number' 		    => $warehouse_number,
			'warehouse_price' 			=> $warehouse_price,
			'warehouse_promote_price' 	=> $warehouse_promote_price,
			'user_id' 				    => $ru_id,
			'add_time' 				    => RC_Time::gmtime(),
		);
			
		$arr = $this->warehouse_goods->insert($warehouse_goods);
		if ($arr) {
			$this->showmessage(__('操作成功'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('goods/admin_goods_mode/init&goods_id='.$id)));
		}
	}

	/**
	* 编辑商品地区模式
	*/
	public function update_area() 
	{
		$this->admin_priv('goods_manage');
		
		/* 对编辑地区名称进行权限检查  BY：JI  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对编辑地区名称进行权限检查  BY：JI END */

        $a_id = intval($_POST['a_id']);
		$region_name				= !empty($_POST['region_name'])		        ? intval($_POST['region_name'])		        : 0;
		$warehouse_area_name		= !empty($_POST['warehouse_area_name'])		? intval($_POST['warehouse_area_name'])		: 0;
		$region_number				= !empty($_POST['region_number'])			? intval($_POST['region_number'])			: 0;
		$region_price				= !empty($_POST['region_price'])			? intval($_POST['region_price'])			: 0;
		$region_promote_price		= !empty($_POST['region_promote_price'])	? intval($_POST['region_promote_price'])	: 0;
		
		$id = intval($_GET['goods_id']);
		$ru_id = $this->db_goods->where(array('goods_id' => $id))->get_field('user_id');
		if (empty($warehouse_area_name)){
			$this->showmessage(__('请选择仓库'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		if (empty($region_name)){
			$this->showmessage(__('请选择地区'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		if (empty($region_number)){
			$this->showmessage(__('库存不能为空'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		if (empty($region_price)){
			$this->showmessage(__('价格不能为空'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		$warehouse_area_goods = array(
			'region_id' 				=> $region_name,
			'region_number' 			=> $region_number,
			'region_price' 			    => $region_price,
			'region_promote_price' 	    => $region_promote_price,
			'user_id' 					=> $ru_id,
			'add_time' 					=> RC_Time::gmtime(),
		);
		
		$arr = $this->area_goods->where(array('a_id' => $a_id))->update($warehouse_area_goods);
		if ($arr) {
			$this->showmessage(__('操作成功'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('goods/admin_goods_mode/init&goods_id='.$id)));
		}

	}

	/**
	* 编辑商品仓库模式
	*/
	public function update_warehouse_goods() {	
		$this->admin_priv('goods_manage');

		if (!empty($_SESSION['ru_id'])) {
		$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}

		$w_id = intval($_POST['w_id']);

		$warehouse_name				= !empty($_POST['warehouse_name'])			? intval($_POST['warehouse_name'])			: 0;
		$warehouse_number			= !empty($_POST['warehouse_number'])		? intval($_POST['warehouse_number'])		: 0;
		$warehouse_price			= !empty($_POST['warehouse_price'])			? intval($_POST['warehouse_price'])			: 0;
		$warehouse_promote_price 	= !empty($_POST['warehouse_promote_price'])	? intval($_POST['warehouse_promote_price'])	: 0;
		$id = $_GET['goods_id'];

		if (empty($warehouse_name)) {
			$this->showmessage(__('请选择仓库名称'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		if (empty($warehouse_number)) {
			$this->showmessage(__('库存不能为空'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		if (empty($warehouse_price)) {
			$this->showmessage(__('价格不能为空'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		$ru_id = $this->db_goods->where(array('goods_id' => $id))->get_field('user_id');

		$warehouse_goods = array(
			'region_id' 			    => $warehouse_name,
			'region_number' 		    => $warehouse_number,
			'warehouse_price' 			=> $warehouse_price,
			'warehouse_promote_price' 	=> $warehouse_promote_price,
			'user_id' 				    => $ru_id,
			'add_time' 				    => RC_Time::gmtime(),
		);
		
		$arr = $this->warehouse_goods->where(array('w_id' => $w_id))->update($warehouse_goods);
		if ($arr) {
			$this->showmessage(__('操作成功'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('goods/admin_goods_mode/init&goods_id='.$id)));
		}
	}

	/**
	* 删除仓库  
	*/
	public function delete_warehouse_goods() {
		$this->admin_priv('goods_manage');

		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}

		$id = intval($_GET['id']);
		$this->warehouse_goods->where(array('w_id' => $id))->delete();
		$this->showmessage(__('删除成功'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
	}

	/**
	* 删除地区  
	*/
	public function delete_area_goods() {
		$this->admin_priv('goods_manage');

		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		
		$id = $_GET['id'];
		$this->area_goods->where(array('a_id' => $id))->delete();
		$this->showmessage(__('删除成功'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
	}

	/**
	* 查询仓库的地区  
	*/
	public function region() {
		$this->admin_priv('goods_manage');

		$warehouse	= $_GET['parent'];
		$type      	= !empty($_GET['type'])   ? intval($_GET['type'])   : 0;
		$target  	= !empty($_GET['target']) ? stripslashes(trim($_GET['target'])) : '';
		$area = array('region', 'target' => $target, 'type' => $type);
		if (empty($warehouse)){
			echo json_encode($area);
		} else {
			$area['regions']	= $this->warehouse_region->field('regionId | region_id, region_name')->where(array('parent_id' => $warehouse))->select();
			$area['target'] 	= $target;
			$area['type']		= $type;
			echo json_encode($area);
		}
	}
}

// end