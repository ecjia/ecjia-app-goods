<?php

/**
 *  ECJIA 商品管理程序
 */

defined('IN_ECJIA') or exit('No permission resources.');
RC_Loader::load_sys_class('ecjia_admin', false);
class admin extends ecjia_admin
{
	private $db_link_goods;
	private $db_goods;
	private $db_group_goods;
	private $db_goods_article;
	private $db_goods_attr;
	private $db_goods_attr_view;
	private $db_goods_cat;
	private $db_goods_gallery;
	private $db_attribute;
	private $db_products;
	private $tags;
	private $db_brand;
	private $db_category;
	private $db_link_area;
	private $db_term_meta;
	private $db_term_relationship;

	public function __construct()
	{
		parent::__construct();

		RC_Lang::load('goods');

		RC_Script::enqueue_script('goods_list', RC_App::apps_url('statics/js/goods_list.js', __FILE__), array('ecjia-common', 'ecjia-utils', 'smoke', 'jquery-validate', 'jquery-form', 'bootstrap-placeholder', 'jquery-wookmark', 'jquery-imagesloaded', 'jquery-colorbox'));

		RC_Script::enqueue_script('jquery-dropper', RC_Uri::admin_url('/statics/lib/dropper-upload/jquery.fs.dropper.js'), array(), false, true);

		RC_Script::enqueue_script('jquery-chosen');
		RC_Style::enqueue_style('chosen');
		RC_Script::enqueue_script('product', RC_App::apps_url('statics/js/product.js', __FILE__), array());
		RC_Script::enqueue_script('replenish_list', RC_App::apps_url('statics/js/replenish_list.js', __FILE__), array());
		RC_Script::enqueue_script('batch_card_add', RC_App::apps_url('statics/js/batch_card_add.js', __FILE__), array());
		RC_Style::enqueue_style('goods-colorpicker-style', RC_Uri::admin_url() . '/statics/lib/colorpicker/css/colorpicker.css');
		RC_Style::enqueue_style('goods-colorpicker-style', RC_Uri::admin_url() . '/statics/lib/colorpicker/css/colorpicker.css');
		RC_Script::enqueue_script('goods-colorpicker-script', RC_Uri::admin_url('/statics/lib/colorpicker/bootstrap-colorpicker.js'), array());
// 		RC_Script::enqueue_script('article-tinymce-script', RC_Uri::vendor_url() . '/tinymce/tinymce.min.js', array(), false, true);
		RC_Script::enqueue_script('bootstrap-editable-script', RC_Uri::admin_url() . '/statics/lib/x-editable/bootstrap-editable/js/bootstrap-editable.min.js', array(), false, true);
		RC_Style::enqueue_style('bootstrap-editable-css', RC_Uri::admin_url() . '/statics/lib/x-editable/bootstrap-editable/css/bootstrap-editable.css');
		RC_Script::enqueue_script('jquery-uniform');
		RC_Style::enqueue_style('uniform-aristo');
		RC_Script::enqueue_script('jq_quicksearch', RC_Uri::admin_url() . '/statics/lib/multi-select/js/jquery.quicksearch.js', array('jquery'), false, true);

		RC_Style::enqueue_style('goodsapi', RC_Uri::home_url('content/apps/goods/statics/styles/goodsapi.css'));
		RC_Script::enqueue_script('ecjia-region',RC_Uri::admin_url('statics/ecjia.js/ecjia.region.js'), array('jquery'), false, true);


		RC_Loader::load_app_class('goods', 'goods', false);
		RC_Loader::load_app_class('goods_image', 'goods', false);

		RC_Loader::load_app_func('functions');
		RC_Loader::load_app_func('common');
		RC_Loader::load_app_func('system_goods');
		RC_Loader::load_app_func('category');

		$goods_list_jslang = array(
				'user_rank_list'	=> get_user_rank_list(),
				'marketPriceRate'	=> ecjia::config('market_price_rate'),
				'integralPercent'	=> ecjia::config('integral_percent'),
		);
		RC_Script::localize_script( 'goods_list', 'admin_goodsList_lang', $goods_list_jslang );

		$this->db_link_goods = RC_Loader::load_app_model('link_goods_model');
		$this->db_goods = RC_Loader::load_app_model('goods_model');
		$this->db_group_goods = RC_Loader::load_app_model('group_goods_model');
		$this->db_goods_article = RC_Loader::load_app_model('goods_article_model');
		$this->db_goods_attr = RC_Loader::load_app_model('goods_attr_model');
		$this->db_goods_attr_view = RC_Loader::load_app_model('goods_attr_viewmodel');
		$this->db_goods_cat = RC_Loader::load_app_model('goods_cat_model');
		$this->db_goods_gallery = RC_Loader::load_app_model('goods_gallery_model');
		$this->db_attribute = RC_Loader::load_app_model('attribute_model');
		$this->db_products = RC_Loader::load_app_model('products_model');
		$this->db_brand = RC_Loader::load_app_model('brand_model','goods');
		$this->db_category = RC_Loader::load_app_model('category_model');
		$this->db_link_area = RC_Loader::load_app_model('link_area_goods_model');
		$this->db_term_relationship = RC_Loader::load_app_model('term_relationship_model');
		$this->db_term_meta = RC_Loader::load_sys_model('term_meta_model');

		$goods_id = $_REQUEST['goods_id'] ? $_REQUEST['goods_id'] : 0;
		$this->tags = get_goods_info_nav($goods_id);
		$this->tags[ROUTE_A]['active'] = 1;
	}


	/**
	* 商品列表
	*/
	public function init()
	{
	    $this->admin_priv('goods_manage');
		$cat_id = empty($_GET['cat_id']) ? 0 : intval($_GET['cat_id']);
		$code = empty($_GET['extension_code']) ? '' : trim($_GET['extension_code']);

		$ur_here = $code == 'virtual_card' ? RC_Lang::lang('50_virtual_card_list') : RC_Lang::lang('01_goods_list');
		$goods_add_lang = $code == 'virtual_card' ? RC_Lang::lang('51_virtual_card_add') : RC_Lang::lang('02_goods_add');

		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here($ur_here));

		$this->assign('ur_here', $ur_here);
		$this->assign('action_link', array('href' => RC_Uri::url('goods/admin/add', empty($code) ? '' : 'extension_code=' . $code), 'text' => $goods_add_lang));
		$this->assign('code', $code);
		$this->assign('cat_list', cat_list(0, $cat_id));
		$this->assign('brand_list', get_brand_list());
		$this->assign('intro_list', goods::intro_list());

		$use_storage = ecjia::config('use_storage');
		$this->assign('use_storage', empty($use_storage) ? 0 : 1);

		$goods_list = goods::goods_list(0,($code == '') ? 1 : 0);

		$this->assign('goods_list', $goods_list);

		$specifications = get_goods_type_specifications();


		ecjia_screen::get_current_screen()->add_help_tab( array(
		'id'		=> 'overview',
		'title'		=> __('概述'),
		'content'	=>
		'<p>' . __('欢迎访问ECJia智能后台商品列表页面，系统中所有的商品都会显示在此列表中。') . '</p>'
		) );

		ecjia_screen::get_current_screen()->set_help_sidebar(
		'<p><strong>' . __('更多信息:') . '</strong></p>' .
		'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:商品列表" target="_blank">关于商品列表帮助文档</a>') . '</p>'
		);

		$this->assign('specifications', $specifications);
		$this->assign('form_action', RC_Uri::url('goods/admin/batch', "extension_code=$code"));

		$this->assign_lang();
		$this->display("goods_list.dwt");
	}


	/**
	 * 预览
	 */
	public function preview()
	{
		$this->admin_priv('goods_manage');
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商品预览')));

		$goods_id = trim($_GET['id']);
		$goods = $this->db_goods->where("goods_id='".$goods_id."' or goods_sn='".$goods_id."'")->find();

		if (empty($goods)) {
			$this->showmessage('未检测到此商品！', ecjia::MSGSTAT_ERROR | ecjia::MSGTYPE_HTML, array('links' => array(array('text'=>'返回上一页','href'=>'javascript:history.go(-1)'))));
		}

		$cat_name   = $this->db_category->where(array('cat_id' => $goods['cat_id']))->get_field('cat_name');
		$brand_name = $this->db_brand->where(array('brand_id' => $goods['brand_id']))->get_field('brand_name');

		if (!file_exists(RC_Upload::upload_path($goods['goods_thumb'])) || empty($goods['goods_thumb'])) {
			$goods['goods_thumb'] = RC_Uri::admin_url('statics/images/nopic.png');
			$goods['goods_img'] = RC_Uri::admin_url('statics/images/nopic.png');
		} else {
			$goods['goods_thumb'] = RC_Upload::upload_url($goods['goods_thumb']);
			$goods['goods_img'] = RC_Upload::upload_url($goods['goods_img']);
		}

		ecjia_screen::get_current_screen()->add_help_tab( array(
		'id'		=> 'overview',
		'title'		=> __('概述'),
		'content'	=>
		'<p>' . __('欢迎访问ECJia智能后台商品预览页面，可以在此页面预览对应的商品信息。') . '</p>'
		) );

		ecjia_screen::get_current_screen()->set_help_sidebar(
		'<p><strong>' . __('更多信息:') . '</strong></p>' .
		'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:商品列表#.E9.A2.84.E8.A7.88.E5.95.86.E5.93.81" target="_blank">关于商品预览帮助文档</a>') . '</p>'
		);

		/* 取得分类、品牌 */
		$this->assign('goods_cat_list', cat_list());
		$this->assign('brand_list',     get_brand_list());

		$this->assign('goods',      $goods);
		$this->assign('cat_name',   $cat_name);
		$this->assign('brand_name', $brand_name);

		$this->assign('ur_here',    '商品预览');
		$this->assign('action_linkedit', array('text' => '商品编辑', 'href' => RC_Uri::url('goods/admin/edit', array('goods_id' => $goods_id))));
		$this->assign('action_link',     array('text' => '商品列表', 'href' => RC_Uri::url('goods/admin/init')));

		$this->assign_lang();
		$this->display('preview.dwt');
	}

	/**
	* 商品回收站
	*/
	public function trash()	{
        $this->admin_priv('goods_manage');

	    ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商品回收站')));
		$ur_here = RC_Lang::lang('11_goods_trash');
		$this->assign('ur_here', $ur_here);

		$action_link = array('href' => RC_Uri::url('goods/admin/init'), 'text' => RC_Lang::lang('01_goods_list'));
		$this->assign('action_link', $action_link);

		$goods_list = goods::goods_list(1, -1);

		ecjia_screen::get_current_screen()->add_help_tab( array(
		'id'		=> 'overview',
		'title'		=> __('概述'),
		'content'	=>
		'<p>' . __('欢迎访问ECJia智能后台商品回收站页面，在商品列表中进行删除的商品会放入此回收站中，在该页面可以对商品进行彻底删除或者还原操作。') . '</p>'
		) );

		ecjia_screen::get_current_screen()->set_help_sidebar(
		'<p><strong>' . __('更多信息:') . '</strong></p>' .
		'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:商品回收站#.E5.95.86.E5.93.81.E5.9B.9E.E6.94.B6.E7.AB.99.E5.88.97.E8.A1.A8" target="_blank">关于商品回收站帮助文档</a>') . '</p>'
		);

		$this->assign('goods_list', $goods_list);
		$this->assign('form_action', 		RC_Uri::url('goods/admin/batch'));

		$this->assign_lang();
		$this->display("goods_trash.dwt");

		//$this->assign('action', 			'batch');
	}

	/**
	* 添加新商品
	*/
	public function add() {
	    $code = empty($_GET['extension_code']) ? '' : trim($_GET['extension_code']);
		$code = trim($code) == 'virtual_card' ? 'virtual_card' : '';
		if ($code == 'virtual_card') {
			$this->admin_priv('virualcard'); // 检查权限
			ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::lang('50_virtual_card_list'), RC_Uri::url('goods/admin/init', 'extension_code=virtual_card')));
			ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::lang('51_virtual_card_add')));
			$this->assign('ur_here', RC_Lang::lang('51_virtual_card_add'));
			$this->assign('action_link', array('href' => RC_Uri::url('goods/admin/init', 'extension_code=virtual_card'), 'text' => RC_Lang::lang('50_virtual_card_list')));
			$this->assign('code', $code);
		} else {
			$this->admin_priv('goods_manage'); // 检查权限
			ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::lang('01_goods_list'),  RC_Uri::url('goods/admin/init')));
			ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::lang('02_goods_add')));
			$this->assign('ur_here', RC_Lang::lang('02_goods_add'));
			$this->assign('action_link', array('href' =>  RC_Uri::url('goods/admin/init'), 'text' => RC_Lang::lang('01_goods_list')));
		}

// 		if (empty($code)) {
// 			ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::lang('01_goods_list'),  RC_Uri::url('goods/admin/init')));
// 			ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::lang('02_goods_add')));

// 			$this->assign('ur_here', RC_Lang::lang('02_goods_add'));
// 			$this->assign('action_link', array('href' =>  RC_Uri::url('goods/admin/init'), 'text' => RC_Lang::lang('01_goods_list')));
// 		} else {
// 			ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::lang('50_virtual_card_list'), RC_Uri::url('goods/admin/init', 'extension_code=virtual_card')));
// 			ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::lang('51_virtual_card_add')));

// 			$this->assign('ur_here', RC_Lang::lang('51_virtual_card_add'));
// 			$this->assign('action_link', array('href' => RC_Uri::url('goods/admin/init', 'extension_code=virtual_card'), 'text' => RC_Lang::lang('50_virtual_card_list')));
// 		}

// 		if (ini_get('safe_mode') == 1 && (!file_exists(RC_Upload::upload_path() . IMAGE_DIR . '/' . date('Ym')) || !is_dir('../' . IMAGE_DIR . '/' . date('Ym')))) {
// 			if (@!mkdir(RC_Upload::upload_path() . IMAGE_DIR . '/' . date('Ym'), 0777)) {
// 				$warning = sprintf(RC_Lang::lang('safe_mode_warning'), RC_Upload::upload_path() . IMAGE_DIR . '/' . date('Ym'));
// 				$this->assign('warning', $warning);
// 			}
// 		} /* 如果目录存在但不可写，提示用户 */
// 		elseif (file_exists(RC_Upload::upload_path() . IMAGE_DIR . '/' . date('Ym')) && RC_File::file_mode_info('../' . IMAGE_DIR . '/' . date('Ym')) < 2) {
// 			$warning = sprintf(RC_Lang::lang('not_writable_warning'), RC_Upload::upload_path() . IMAGE_DIR . '/' . date('Ym'));
// 			$this->assign('warning', $warning);
// 		}

		/* 默认值 */
		//TODO
		$last_choose = array(0, 0);
		if (!empty($_COOKIE['ECSCP']['last_choose'])) {
			$last_choose = explode('|', $_COOKIE['ECSCP']['last_choose']);
		}
		$goods = array(
			'goods_id'				=> 0,
			'goods_desc'			=> '',
			'cat_id'				=> $last_choose[0],
			'brand_id'				=> $last_choose[1],
			'is_on_sale'			=> '1',
			'is_alone_sale'			=> '1',
			'is_shipping'			=> '0',
			'other_cat'				=> array(), // 扩展分类
			'goods_type'			=> 0, // 商品类型
			'shop_price'			=> 0,
			'promote_price'			=> 0,
			'market_price'			=> 0,
			'integral'				=> 0,
			'goods_number'			=> ecjia::config('default_storage'),
			'warn_number'			=> 1,
			'promote_start_date'	=> RC_Time::local_date('Y-m-d'),
			'promote_end_date'		=> RC_Time::local_date('Y-m-d', RC_Time::local_strtotime('+1 month')),
			'goods_weight'			=> 0,
			'give_integral'			=> -1,
			'rank_integral'			=> -1
			);

		if ($code != '') {
			$goods['goods_number'] = 0;
		}
		/* 商品名称样式 */
		$goods_name_style = $goods['goods_name_style'];

		/* 模板赋值 */
		$this->assign('tags', array('edit' => array('name' => _('通用信息'), 'active' => 1, 'pjax' => 1, 'href' => RC_Uri::url('goods/admin/add'))));


		ecjia_screen::get_current_screen()->add_help_tab( array(
		'id'		=> 'overview',
		'title'		=> __('概述'),
		'content'	=>
		'<p>' . __('欢迎访问ECJia智能后台添加商品页面，可以在此页面添加商品信息。') . '</p>'
		) );

		ecjia_screen::get_current_screen()->set_help_sidebar(
		'<p><strong>' . __('更多信息:') . '</strong></p>' .
		'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:添加商品" target="_blank">关于商品添加帮助文档</a>') . '</p>'
		);

		$this->assign('goods', $goods);
		$this->assign('goods_name_color', $goods_name_style[0]);
		$this->assign('cat_list', cat_list(0, $goods['cat_id'], false));
		$this->assign('brand_list', get_brand_list());
		$this->assign('unit_list',  goods::unit_list());
		$this->assign('user_rank_list', get_user_rank_list());
		$this->assign('cfg', ecjia::config());
		$this->assign('goods_attr_html', build_attr_html($goods['goods_type'], $goods['goods_id']));
		$volume_price_list = '';
		if (isset($_REQUEST['goods_id'])) {
			$volume_price_list = get_volume_price_list($_REQUEST['goods_id']);
		}
		if (empty($volume_price_list)) {
			$volume_price_list = array('0' => array('number' => '', 'price' => ''));
		}
		$this->assign('volume_price_list', $volume_price_list);
		$this->assign('form_action', RC_Uri::url('goods/admin/insert'));

		/* 图片列表 */
		// 		$img_list = array();
// 		$this->assign('img_list', $img_list);
// 		$this->assign('form_act', RC_Uri::url('goods/admin/add'));
// 		$this->assign('thumb_width', ecjia::config('thumb_width'));
// 		$this->assign('thumb_height', ecjia::config('thumb_height'));
// 		$this->assign('gd', RC_ENV::gd_version());
// 		$this->assign('goods_type_list', goods_type_list($goods['goods_type']));
		/* 显示商品信息页面 */
		$this->assign_lang();
		$this->display('goods_info.dwt');
	}

	/**
	 * 编辑商品
	 */
	public function edit()
	{
	    $code = empty($_GET['extension_code']) ? '' : trim($_GET['extension_code']);
		$code = $code == 'virtual_card' ? 'virtual_card' : '';
		if ($code == 'virtual_card') {
			$this->admin_priv('virualcard'); // 检查权限
			ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::lang('50_virtual_card_list'), RC_Uri::url('goods/admin/init', 'extension_code=virtual_card')));
			ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('编辑虚拟商品')));
			$this->assign('ur_here', __('编辑虚拟商品'));
			$this->assign('action_link', array('href' => RC_Uri::url('goods/admin/init', 'extension_code=virtual_card'), 'text' => RC_Lang::lang('50_virtual_card_list')));
		} else {
			$this->admin_priv('goods_manage'); // 检查权限
			ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::lang('01_goods_list'),  RC_Uri::url('goods/admin/init')));
			ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('编辑商品')));
			$this->assign('ur_here', __('编辑商品'));
			$this->assign('action_link', array('href' =>  RC_Uri::url('goods/admin/init'), 'text' => RC_Lang::lang('01_goods_list')));
		}

		/* 获取商品类型存在规格的类型 */
		$specifications = get_goods_type_specifications();
		$goods['specifications_id'] = $specifications[$goods['goods_type']];
		$_attribute = get_goods_specifications_list($goods['goods_id']);
		$goods['_attribute'] = empty($_attribute) ? '' : 1;

		/* 商品信息 */
		$goods = $this->db_goods->find(array('goods_id' => $_REQUEST[goods_id]));
		if (empty($goods) === true) {
			/* 默认值 */
			$goods = array(
				'goods_id'				=> 0,
				'goods_desc'			=> '',
				'cat_id'				=> 0,
				'is_on_sale'			=> '1',
				'is_alone_sale'			=> '1',
				'is_shipping'			=> '0',
				'other_cat'				=> array(), // 扩展分类
//				'goods_type'			=>	0,	   // 商品类型
				'shop_price'			=> 0,
				'promote_price'			=> 0,
				'market_price'			=> 0,
				'integral'				=> 0,
				'goods_number'			=> 1,
				'warn_number'			=> 1,
				'promote_start_date'	=> RC_Time::local_date('Y-m-d'),
				'promote_end_date'		=> RC_Time::local_date('Y-m-d', RC_Time::gmstr2time('+1 month')),
				'goods_weight'			=> 0,
				'give_integral'			=> -1,
				'rank_integral'			=> -1
			);
		}
		/* 根据商品重量的单位重新计算 */
		if ($goods['goods_weight'] > 0) {
			$goods['goods_weight_by_unit'] = ($goods['goods_weight'] >= 1) ? $goods['goods_weight'] : ($goods['goods_weight'] / 0.001);
		}

		if (!empty($goods['goods_brief'])) {
			$goods['goods_brief'] = $goods['goods_brief'];
		}
		if (!empty($goods['keywords'])) {
			$goods['keywords'] = $goods['keywords'];
		}

		/* 如果不是促销，处理促销日期 */
		if (isset($goods['is_promote']) && $goods['is_promote'] == '0') {
			unset($goods['promote_start_date']);
			unset($goods['promote_end_date']);
		} else {
			$goods['promote_start_date'] = RC_Time::local_date('Y-m-d', $goods['promote_start_date']);
			$goods['promote_end_date'] = RC_Time::local_date('Y-m-d', $goods['promote_end_date']);
		}
		/* 扩展分类 */
		$getCol = $this->db_goods_cat->field('cat_id')->where(array('goods_id' => $_REQUEST['goods_id']))->select();
		if (!empty($getCol) && is_array($getCol)) {
			foreach ($getCol as $value) {
				$goods['other_cat'][] = $value['cat_id'];
			}
		}

		/* 商品图片路径 */
		if (!empty($goods['goods_img'])) {
				$goods['goods_img'] = goods_image::get_absolute_url($goods['goods_img']);
				$goods['goods_thumb'] = goods_image::get_absolute_url($goods['goods_thumb']);
				$goods['original_img'] = goods_image::get_absolute_url($goods['original_img']);
		}

		/* 拆分商品名称样式 */
		$goods_name_style = explode('+', empty($goods['goods_name_style']) ? '+' : $goods['goods_name_style']);
		$upfile = RC_Upload::upload_url();

		$cat_list = cat_list(0, $goods['cat_id'], false);

		foreach ($cat_list as $k => $v) {
			if(!empty($goods['other_cat']) && is_array($goods['other_cat'])){
				if (in_array($v['cat_id'], $goods['other_cat'])) {
					$cat_list[$k]['is_other_cat'] = 1;
				}
			}
		}

		$data_term_meta = $this->db_term_meta->field('meta_id, meta_key, meta_value')->where(array('object_id' => $goods['goods_id'], 'object_type' => 'ecjia.goods', 'object_group'	=> 'goods'))->select();
		$term_meta_key_list = $this->db_term_meta->field('meta_key')->where(array('object_id' => $goods['goods_id'], 'object_type' => 'ecjia.goods', 'object_group'	=> 'goods'))->group('meta_key')->select();

		ecjia_screen::get_current_screen()->add_help_tab( array(
		'id'		=> 'overview',
		'title'		=> __('概述'),
		'content'	=>
		'<p>' . __('欢迎访问ECJia智能后台编辑商品页面，可以在此页面对相对应的商品信息进行编辑。') . '</p>'
		) );

		ecjia_screen::get_current_screen()->set_help_sidebar(
		'<p><strong>' . __('更多信息:') . '</strong></p>' .
		'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:商品列表#.E5.95.86.E5.93.81.E7.BC.96.E8.BE.91" target="_blank">关于商品编辑帮助文档</a>') . '</p>'
		);

		//设置选中状态,并分配标签导航
		$this->assign('action',		ROUTE_A);
		$this->assign('tags'				, $this->tags);
		$this->assign('data_term_meta'		, $data_term_meta);
		$this->assign('term_meta_key_list'	, $term_meta_key_list);
		$this->assign('code'				, $code);/* 模板赋值 */
		$this->assign('goods'				, $goods);
		$this->assign('goods_name_color'	, $goods_name_style[0]);
		$this->assign('cat_list'			, $cat_list);
		$this->assign('merchant_cat_list'	, merchant_cat_list(0, $goods['cat_id'], false));
		$this->assign('brand_list'			, get_brand_list());
		$this->assign('unit_list'			, goods::unit_list());
		$this->assign('user_rank_list'		, get_user_rank_list());
		$this->assign('weight_unit'			, $goods['goods_weight'] >= 1 ? '1' : '0.001');
		$this->assign('cfg'					, ecjia::config());
		$this->assign('form_act'			, RC_Uri::url('goods/admin/edit'));
		$this->assign('member_price_list'	, get_member_price_list($_REQUEST['goods_id']));
		$this->assign('form_tab'			, 'edit');
		$this->assign('upfile'				, $upfile);
		$this->assign('gd'					, RC_ENV::gd_version());
		$this->assign('thumb_width'			, ecjia::config('thumb_width'));
		$this->assign('thumb_height'		, ecjia::config('thumb_height'));

		$volume_price_list = '';
		if (isset($_REQUEST['goods_id'])) {
			$volume_price_list = get_volume_price_list($_REQUEST['goods_id']);
		}
		if (empty($volume_price_list)) {
			$volume_price_list = array('0' => array('number' => '', 'price' => ''));
		}
		$this->assign('volume_price_list', $volume_price_list);
		/* 显示商品信息页面 */
		$this->assign('form_action', RC_Uri::url('goods/admin/update'));

		$this->assign_lang();
		$this->display('goods_info.dwt');




// 		_dump($goods,1);

		// 		if (ini_get('safe_mode') == 1 && (!file_exists(RC_Upload::upload_path() . IMAGE_DIR . '/' . date('Ym')) || !is_dir(RC_Upload::upload_path() . IMAGE_DIR . '/' . date('Ym')))) {
		// 			if (@!mkdir(RC_Upload::upload_path() . IMAGE_DIR . '/' . date('Ym'), 0777)) {
		// 				$warning = sprintf(RC_Lang::lang('safe_mode_warning'), RC_Upload::upload_path() . IMAGE_DIR . '/' . date('Ym'));
		// 				$this->assign('warning', $warning);
		// 			}
		// 		} /* 如果目录存在但不可写，提示用户 */
		// 		elseif (file_exists(RC_Upload::upload_path() . IMAGE_DIR . '/' . date('Ym')) && RC_File::file_mode_info(RC_Upload::upload_path() . IMAGE_DIR . '/' . date('Ym')) < 2) {
		// 			$warning = sprintf(RC_Lang::lang('not_writable_warning'), RC_Upload::upload_path() . IMAGE_DIR . '/' . date('Ym'));
		// 			$this->assign('warning', $warning);
		// 		}
		/* 取得商品信息 */
		// 		$other_cat_list = array();
		// 		$sql = "SELECT cat_id FROM " . $ecs->table('goods_cat') . " WHERE goods_id = '$_REQUEST[goods_id]'";
		//  	$goods['other_cat'] = $db->getCol($sql);
		// 		_dump($getCol,1);
		/* 图片列表 */
		// 			$sql = "SELECT * FROM " . $ecs->table('goods_gallery') . " WHERE goods_id = '$goods[goods_id]'";
		// 			$img_list = $db->getAll($sql);
// 		$img_list = $this->db_goods_gallery->where(array('goods_id' => $goods['goods_id']))->select();
// 		/* 格式化相册图片路径 */
// 		//TODO: 图片更新方法
// 		if (isset($GLOBALS['shop_id']) && ($GLOBALS['shop_id'] > 0)) {

// 			foreach ($img_list as $key => $gallery_img) {
// 				$gallery_img[$key]['img_url'] = get_image_path($gallery_img['goods_id'], $gallery_img['img_original'], false, 'gallery');
// 				$gallery_img[$key]['thumb_url'] = get_image_path($gallery_img['goods_id'], $gallery_img['img_original'], true, 'gallery');
// 			}
// 		} else {
// 			foreach ($img_list as $key => $gallery_img) {
// 				$gallery_img[$key]['thumb_url'] = str_replace('/', '\\', RC_Upload::upload_path() . (empty($gallery_img['thumb_url']) ? $gallery_img['img_url'] : $gallery_img['thumb_url']));
// 			}
// 		}
		// 		$this->tags = array_slice($this->tags,1,5);
// 		$ur_here = empty($code) ? __('编辑商品') :__('编辑虚拟商品');
// 		$list_url = empty($code) ? RC_Lang::lang('01_goods_list'): RC_Lang::lang('50_virtual_card_list');
// 		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__($list_url), RC_Uri::url('goods/admin/init')));
// 		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__($ur_here)));
// 		$action_link = empty($code) ? array('href' => RC_Uri::url('goods/admin/init'), 'text' => __('商品列表')) : array('href' => RC_Uri::url('goods/admin/init'), 'text' => __('虚拟商品列表'));
// 		$this->assign('ur_here'				, $ur_here);
// 		$this->assign('action_link'			, $action_link);
// 		$this->assign('img_list'			, $img_list);
// 		$this->assign('goods_name_style', $goods_name_style[1]);
// 		$this->assign('link_goods_list', $link_goods_list);
// 		$this->assign('group_goods_list', $group_goods_list);
// 		$this->assign('goods_article_list', $goods_article_list);
// 		$this->assign('goods_type_list', goods_type_list($goods['goods_type']));
// 		$this->assign('goods_attr_html', build_attr_html($goods['goods_type'], $goods['goods_id']));
// 		$this->assign('action_link', list_link($is_add, $code));
	}

	/**
	* 复制商品
	*/
	public function copy()
	{
	    $code = empty($_GET['extension_code']) ? '' : trim($_GET['extension_code']);
	    $code = $code == 'virtual_card' ? 'virtual_card' : '';
		if ($code == 'virtual_card') {
			$this->admin_priv('virualcard'); // 检查权限
// 			__('复制商品') :__('复制虚拟商品');
			ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::lang('50_virtual_card_list'), RC_Uri::url('goods/admin/init', 'extension_code=virtual_card')));
			ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('复制虚拟商品')));
			$this->assign('ur_here', __('复制虚拟商品'));
			$this->assign('action_link', array('href' => RC_Uri::url('goods/admin/init', 'extension_code=virtual_card'), 'text' => RC_Lang::lang('50_virtual_card_list')));
			$this->assign('code', $code);
		} else {
			$this->admin_priv('goods_manage'); // 检查权限
			ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::lang('01_goods_list'),  RC_Uri::url('goods/admin/init')));
			ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('复制商品')));
			$this->assign('ur_here', __('复制商品'));
			$this->assign('action_link', array('href' =>  RC_Uri::url('goods/admin/init'), 'text' => RC_Lang::lang('01_goods_list')));
		}
		/* 如果是安全模式，检查目录是否存在 */
		if (ini_get('safe_mode') == 1 && (!file_exists(RC_Upload::upload_path() . IMAGE_DIR . '/' . date('Ym')) || !is_dir(RC_Upload::upload_path() . IMAGE_DIR . '/' . date('Ym')))) {
			if (@!mkdir(RC_Upload::upload_path() . IMAGE_DIR . '/' . date('Ym'), 0777)) {
				$warning = sprintf(RC_Lang::lang('safe_mode_warning'), RC_Upload::upload_path() . IMAGE_DIR . '/' . date('Ym'));
				$this->assign('warning', $warning);
			}
		} /* 如果目录存在但不可写，提示用户 */
		elseif (file_exists(RC_Upload::upload_path() . IMAGE_DIR . '/' . date('Ym')) && RC_File::file_mode_info(RC_Upload::upload_path() . IMAGE_DIR . '/' . date('Ym')) < 2) {
			$warning = sprintf(RC_Lang::lang('not_writable_warning'), RC_Upload::upload_path() . IMAGE_DIR . '/' . date('Ym'));
			$this->assign('warning', $warning);
		}
		/* 商品信息 */
		$goods = $this->db_goods->find(array('goods_id' => $_REQUEST[goods_id]));
		/* 虚拟卡商品复制时, 将其库存置为0*/
		if ($code != '') {
			$goods['goods_number'] = 0;
		}
		if (empty($goods) === true) {
			/* 默认值 */
			$goods = array(
				'goods_id'				=> 0,
				'goods_desc'			=> '',
				'cat_id'				=> 0,
				'is_on_sale'			=> '1',
				'is_alone_sale'			=> '1',
				'is_shipping'			=> '0',
				'other_cat'				=> array(), // 扩展分类
				'goods_type'			=> 0, // 商品类型
				'shop_price'			=> 0,
				'promote_price'			=> 0,
				'market_price'			=> 0,
				'integral'				=> 0,
				'goods_number'			=> 1,
				'warn_number'			=> 1,
				'promote_start_date'	=> RC_Time::local_date('Y-m-d'),
				'promote_end_date'		=> RC_Time::local_date('Y-m-d', RC_Time::gmstr2time('+1 month')),
				'goods_weight'			=> 0,
				'give_integral'			=> -1,
				'rank_integral'			=> -1
			);
		}

		/* 获取商品类型存在规格的类型 */
		$specifications = get_goods_type_specifications();
		$goods['specifications_id'] = $specifications[$goods['goods_type']];
		$_attribute = get_goods_specifications_list($goods['goods_id']);
		$goods['_attribute'] = empty($_attribute) ? '' : 1;

		/* 根据商品重量的单位重新计算 */
		if ($goods['goods_weight'] > 0) {
			$goods['goods_weight_by_unit'] = ($goods['goods_weight'] >= 1) ? $goods['goods_weight'] : ($goods['goods_weight'] / 0.001);
		}
		if (!empty($goods['goods_brief'])) {
			$goods['goods_brief'] = $goods['goods_brief'];
		}
		if (!empty($goods['keywords'])) {
			$goods['keywords'] = $goods['keywords'];
		}
		/* 如果不是促销，处理促销日期 */
		if (isset($goods['is_promote']) && $goods['is_promote'] == '0') {
			unset($goods['promote_start_date']);
			unset($goods['promote_end_date']);
		} else {
			$goods['promote_start_date'] = RC_Time::local_date('Y-m-d', $goods['promote_start_date']);
			$goods['promote_end_date'] = RC_Time::local_date('Y-m-d', $goods['promote_end_date']);
		}

	    // 图片不变
		/* 扩展分类 */
		$other_cat_list = array();

		$getCol = $this->db_goods_cat->field('cat_id')->where(array('goods_id' => $_REQUEST['goods_id']))->select();
// 		_dump($getCol,1);
		foreach ($getCol as $value) {
		    $goods['other_cat'][] = $value['cat_id'];
		}

// 		$getcol = $this->db_goods_cat->where(array('goods_id' => $_REQUEST[goods_id]))->get_field('cat_id');

// 		$goods['other_cat'] = $getcol;
// 		if (is_array($goods['other_cat'])) {
// 			foreach ($goods['other_cat'] AS $cat_id) {
// 				$other_cat_list[$cat_id] = cat_list(0, $cat_id);
// 			}
// 		}
// 		$this->assign('other_cat_list', $other_cat_list);

		/* 商品图片路径 */
		if (isset($GLOBALS['shop_id']) && ($GLOBALS['shop_id'] > 10) && !empty($goods['original_img'])) {
			$goods['goods_img'] = get_image_path($_REQUEST['goods_id'], $goods['goods_img']);
			$goods['goods_thumb'] = get_image_path($_REQUEST['goods_id'], $goods['goods_thumb'], true);
		}

		/* 图片列表 */
		$img_list = $this->db_goods_gallery->where(array('goods_id' => $_REQUEST[goods_id]))->select();
		/* 格式化相册图片路径 */
		if (isset($GLOBALS['shop_id']) && ($GLOBALS['shop_id'] > 0)) {
			foreach ($img_list as $key => $gallery_img) {
				$gallery_img[$key]['img_url'] = get_image_path($gallery_img['goods_id'], $gallery_img['img_original'], false, 'gallery');
				$gallery_img[$key]['thumb_url'] = get_image_path($gallery_img['goods_id'], $gallery_img['img_original'], true, 'gallery');
			}
		} else {
			if (is_array($img_list)) {
				foreach ($img_list as $key => $gallery_img) {
	//	 				$gallery_img[$key]['thumb_url'] = '../' . (empty($gallery_img['thumb_url']) ? $gallery_img['img_url'] : $gallery_img['thumb_url']);
					$gallery_img[$key]['thumb_url'] = RC_Upload::upload_path() . (empty($gallery_img['thumb_url']) ? $gallery_img['img_url'] : $gallery_img['thumb_url']);
				}
			}
		}
		/* 拆分商品名称样式 */
		$goods_name_style = explode('+', empty($goods['goods_name_style']) ? '+' : $goods['goods_name_style']);
		/* 复制商品信息，处理 */
		$goods['goods_copyid'] = $goods['goods_id'];
		$goods['goods_id'] = 0;
		$goods['goods_sn'] = '';
		$goods['goods_name'] = '';
		$goods['goods_img'] = '';
		$goods['goods_thumb'] = '';
		$goods['original_img'] = '';


		$cat_list = cat_list(0, $goods['cat_id'], false);

		foreach ($cat_list as $k => $v) {
			if (in_array($v['cat_id'], $goods['other_cat'])) {
				$cat_list[$k]['is_other_cat'] = 1;
			}
		}

		ecjia_screen::get_current_screen()->add_help_tab( array(
		'id'		=> 'overview',
		'title'		=> __('概述'),
		'content'	=>
		'<p>' . __('欢迎访问ECJia智能后台商品复制页面，可以在此页面对相对应的商品信息进行复制。') . '</p>'
		) );

		ecjia_screen::get_current_screen()->set_help_sidebar(
		'<p><strong>' . __('更多信息:') . '</strong></p>' .
		'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:商品列表#.E5.A4.8D.E5.88.B6.E5.95.86.E5.93.81" target="_blank">关于商品复制帮助文档</a>') . '</p>'
		);

		/* 模板赋值 */
		$this->assign('tags', array('edit' => array('name' => _('通用信息'), 'active' => 1, 'pjax' => 1, 'href' => RC_Uri::url('goods/admin/add'))));
		$this->assign('goods',				$goods);
		$this->assign('goods_name_color',	$goods_name_style[0]);
		$this->assign('cat_list',			$cat_list);
		$this->assign('brand_list',			get_brand_list());
		$this->assign('unit_list',			goods::unit_list());
		$this->assign('user_rank_list',		get_user_rank_list());
		$this->assign('weight_unit',		$goods['goods_weight'] >= 1 ? '1' : '0.001');
		$this->assign('cfg',				ecjia::config());
		$this->assign('img_list',			$img_list);
		$this->assign('goods_type_list',	goods_type_list($goods['goods_type']));
		$this->assign('member_price_list',	get_member_price_list($_REQUEST['goods_id']));
		$this->assign('gd',					RC_ENV::gd_version());
		$this->assign('thumb_width',		ecjia::config('thumb_width'));
		$this->assign('thumb_height',		ecjia::config('thumb_height'));
		$this->assign('goods_attr_html',	build_attr_html($goods['goods_type'], $goods['goods_id']));
		$volume_price_list = '';
		if (isset($_REQUEST['goods_id'])) {
			$volume_price_list = get_volume_price_list($_REQUEST['goods_id']);
		}
		if (empty($volume_price_list)) {
			$volume_price_list = array('0' => array('number' => '', 'price' => ''));
		}
		$this->assign('volume_price_list', $volume_price_list);
		/* 显示商品信息页面 */
		$this->assign('form_action', RC_Uri::url('goods/admin/copy_goods'));

		$this->assign_lang();
		$this->display('goods_info.dwt');


// 		$ur_here = empty($code) ? __('复制商品') :__('复制虚拟商品');
// 		$list_url = empty($code) ? RC_Lang::lang('01_goods_list'): RC_Lang::lang('50_virtual_card_list');
// 		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__($list_url), RC_Uri::url('goods/admin/init')));
// 		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__($ur_here)));
// 		$this->assign('ur_here',			RC_Lang::lang('copy_goods'));
// 		$this->assign('action_link',		list_link($code));
	}


	/**
	* 插入商品
	*/
	public function insert()
	{
		$code = empty($_REQUEST['extension_code']) ? '' : trim($_REQUEST['extension_code']);
		$code = $code == 'virtual_card' ? 'virtual_card' : '';
		/* 是否处理缩略图 */
		if ($code == 'virtual_card') {
			$this->admin_priv('virualcard'); // 检查权限
		} else {
			$this->admin_priv('goods_manage'); // 检查权限
		}

		/* 对插入商品进行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对插入商品进行权限检查  BY：MaLiuWei  END */

		/* 检查货号是否重复 */
		if ($_POST['goods_sn']) {
			$count = $this->db_goods->join(null)->where(array('goods_sn' => $_POST['goods_sn'], 'is_delete' => 0, 'goods_id' => array('neq' => $_POST['goods_id'])))->count();
			if ($count > 0) {
				$this->showmessage(RC_Lang::lang('goods_sn_exists'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
		}


		RC_Loader::load_app_class('goods_image', 'goods', false);

		 /* 处理商品图片 */
		 $goods_img = ''; // 初始化商品图片
		 $goods_thumb = ''; // 初始化商品缩略图
		 $img_original = ''; // 初始化原始图片


		 $upload = RC_Upload::uploader('image', array('save_path' => 'images', 'auto_sub_dirs' => true));

		 /* 是否处理商品图 */
		 $proc_goods_img = true;
		 if (!$upload->check_upload_file($_FILES['goods_img'])) {
		     $proc_goods_img = false;
		 }
		 /* 是否处理缩略图 */
		 $proc_thumb_img = true;
		 if (!$upload->check_upload_file($_FILES['thumb_img'])) {
		     $proc_thumb_img = false;
		 }


		 if ($proc_goods_img) {
		     $image_info = $upload->upload($_FILES['goods_img']);
		     if (empty($image_info)) {
		         $this->showmessage($upload->error(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		     }
		 }
		 if ($proc_thumb_img) {
		     $thumb_info = $upload->upload($_FILES['thumb_img']);
		     if (empty($thumb_info)) {
		         $this->showmessage($upload->error(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		     }
		 }


		/* 如果没有输入商品货号则自动生成一个商品货号 */
		if (empty($_POST['goods_sn'])) {
			$max_id = $this->db_goods->join(null)->field('MAX(goods_id) + 1|max')->find();
			if (empty($max_id['max'])) {
				$goods_sn_bool = true;
				$goods_sn='';
			} else {
				$goods_sn = generate_goods_sn($max_id['max']);
			}
		} else {
			$goods_sn = $_POST['goods_sn'];
		}


		/* 处理商品数据 */
		$shop_price = !empty($_POST['shop_price']) ? $_POST['shop_price'] : 0;
		$market_price = !empty($_POST['market_price']) ? $_POST['market_price'] : 0;
		$promote_price = !empty($_POST['promote_price']) ? floatval($_POST['promote_price']) : 0;
		$is_promote = empty($promote_price) ? 0 : 1;
		$promote_start_date = ($is_promote && !empty($_POST['promote_start_date'])) ? RC_Time::local_strtotime($_POST['promote_start_date']) : 0;
		$promote_end_date = ($is_promote && !empty($_POST['promote_end_date'])) ? RC_Time::local_strtotime($_POST['promote_end_date']) : 0;
		$goods_weight = !empty($_POST['goods_weight']) ? $_POST['goods_weight'] * $_POST['weight_unit'] : 0;
		$is_best = isset($_POST['is_best']) ? 1 : 0;
		$is_new = isset($_POST['is_new']) ? 1 : 0;
		$is_hot = isset($_POST['is_hot']) ? 1 : 0;
		$is_on_sale = isset($_POST['is_on_sale']) ? 1 : 0;
		$is_alone_sale = isset($_POST['is_alone_sale']) ? 1 : 0;
		$is_shipping = isset($_POST['is_shipping']) ? 1 : 0;
		$goods_number = isset($_POST['goods_number']) ? $_POST['goods_number'] : 0;
		$warn_number = isset($_POST['warn_number']) ? $_POST['warn_number'] : 0;
		$goods_type = isset($_POST['goods_type']) ? $_POST['goods_type'] : 0;
		$give_integral = isset($_POST['give_integral']) ? intval($_POST['give_integral']) : '-1';
		$rank_integral = isset($_POST['rank_integral']) ? intval($_POST['rank_integral']) : '-1';
		$suppliers_id = isset($_POST['suppliers_id']) ? intval($_POST['suppliers_id']) : '0';

// 		$goods_name_style	= $_POST['goods_name_color'] . '+' . $_POST['goods_name_style'];
		$goods_name = htmlspecialchars($_POST['goods_name']);
		$goods_name_style = htmlspecialchars($_POST['goods_name_color']);

		$catgory_id = empty($_POST['cat_id']) ? '' : intval($_POST['cat_id']);
		$brand_id = empty($_POST['brand_id']) ? '' : intval($_POST['brand_id']);
// 		$goods_thumb = (empty($goods_thumb) && !empty($_POST['goods_thumb_url']) && goods_parse_url($_POST['goods_thumb_url'])) ? htmlspecialchars(trim($_POST['goods_thumb_url'])) : $goods_thumb;
// 		$goods_thumb = (empty($goods_thumb) && isset($_POST['auto_thumb'])) ? $goods_img : $goods_thumb;

		if (empty($goods_name)) {
			$this->showmessage(__('商品名称不能为空！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}

		/* 入库 */
		if ($code == '') {
			$data = array(
				'goods_name'            => $goods_name,
				'goods_name_style'      => $goods_name_style,
				'goods_sn'              => $goods_sn,
				'cat_id'                => $catgory_id,
				'brand_id'              => $brand_id,
				'shop_price'            => $shop_price,
				'market_price'          => $market_price,
				'is_promote'            => $is_promote,
				'promote_price'         => $promote_price,
				'promote_start_date'    => $promote_start_date,
				'promote_end_date'      => $promote_end_date,
		//		'goods_img' => $goods_img,
		//		'goods_thumb' => $goods_thumb,
		//		'original_img' => $original_img,
				'keywords'              => $_POST['keywords'],
				'goods_brief'           => $_POST['goods_brief'],
				'seller_note'           => $_POST['seller_note'],
				'goods_weight'          => $goods_weight,
				'goods_number'          => $goods_number,
				'warn_number'           => $warn_number,
				'integral'              => $_POST['integral'],
				'give_integral'         => $give_integral,
				'is_best'               => $is_best,
				'is_new'                => $is_new,
				'is_hot'                => $is_hot,
				'is_on_sale'            => $is_on_sale,
				'is_alone_sale'         => $is_alone_sale,
				'is_shipping'           => $is_shipping,
				'goods_desc'            => $_POST['goods_desc'],
				'add_time'              => RC_Time::gmtime(),
				'last_update'           => RC_Time::gmtime(),
				'goods_type'            => $goods_type,
				'rank_integral'         => $rank_integral,
				'suppliers_id'          => $suppliers_id,
				'review_status'			=> 5,
				);
		} else {
		  $data = array(
		     'goods_name'         => $_POST['goods_name'],
		     'goods_name_style'   => $goods_name_style,
		     'goods_sn'           => $goods_sn,
		     'cat_id'             => $catgory_id,
		     'brand_id'           => $brand_id,
		     'shop_price'         => $shop_price,
		     'market_price'       => $market_price,
		     'is_promote'         => $is_promote,
		     'promote_price'      => $promote_price,
		     'promote_start_date' => $promote_start_date,
		     'promote_end_date'   => $promote_end_date,
// 			 'goods_img' => $goods_img,
// 			 'goods_thumb' => $goods_thumb,
// 			 'original_img' => $original_img,
		     'keywords'           => $_POST['keywords'],
		     'goods_brief'        => $_POST['goods_brief'],
		     'seller_note'        => $_POST['seller_note'],
		     'goods_weight'       => $goods_weight,
		     'goods_number'       => $goods_number,
		     'warn_number'        => $warn_number,
		     'integral'           => $_POST['integral'],
		     'give_integral'      => $give_integral,
		     'is_best'            => $is_best,
		     'is_new'             => $is_new,
		     'is_hot'             => $is_hot,
		     'is_real'            => 0,
		     'is_on_sale'         => $is_on_sale,
		     'is_alone_sale'      => $is_alone_sale,
		     'is_shipping'        => $is_shipping,
		     'goods_desc'         => $_POST['goods_desc'],
		     'add_time'           => RC_Time::gmtime(),
		     'last_update'        => RC_Time::gmtime(),
		     'goods_type'         => $goods_type,
		     'extension_code'     => $code,
		     'rank_integral'      => $rank_integral,
			 'review_status'	  => 5,
		     );
		}

		$insert_id = $this->db_goods->join(null)->insert($data);
		/* 商品编号 */
		$goods_id = $insert_id;
		if($goods_sn_bool){
			$goods_sn = generate_goods_sn($goods_id);
			$data = array('goods_sn' => $goods_sn);
			$this->db_goods->where('goods_id='.$goods_id)->update($data);
		}
		/* 记录日志 */
		ecjia_admin::admin_log($_POST['goods_name'], 'add', 'goods');
		/* 处理会员价格 */
		if (isset($_POST['user_rank']) && isset($_POST['user_price'])) {
		  handle_member_price($goods_id, $_POST['user_rank'], $_POST['user_price']);
		}

		/* 处理优惠价格 */
		if (isset($_POST['volume_number']) && isset($_POST['volume_price'])) {
		  $temp_num = array_count_values($_POST['volume_number']);
		  foreach ($temp_num as $v) {
		     if ($v > 1) {
// 				sys_msg($_LANG['volume_number_continuous'], 1, array(), false);
		        $this->showmessage(RC_Lang::lang('volume_number_continuous'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		        break;
		    }
		}
		handle_volume_price($goods_id, $_POST['volume_number'], $_POST['volume_price']);
		}

		/* 处理扩展分类 */
		if (isset($_POST['other_cat'])) {
		  handle_other_cat($goods_id, array_unique($_POST['other_cat']));
		}

		/* 更新上传后的商品图片 */
		if ($proc_goods_img) {
		    $goods_image = new goods_image($image_info);

		    if ($proc_thumb_img) {
		        $goods_image->set_auto_thumb(false);
		    }

		    $result = $goods_image->update_goods($goods_id);
		    if (is_ecjia_error($result)) {
		        $this->showmessage($result->get_error_message(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		    }
		}

		/* 更新上传后的缩略图片 */
		if ($proc_thumb_img) {

		    $thumb_image = new goods_image($thumb_info);
		    $result = $thumb_image->update_thumb($goods_id);
		    if (is_ecjia_error($result)) {
		        $this->showmessage($result->get_error_message(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		    }
		}

		/* 记录上一次选择的分类和品牌 */
		setcookie('ECSCP[last_choose]', $catgory_id . '|' . $brand_id, RC_Time::gmtime() + 86400);
		/* 提示页面 */
		$link = array();
// 		if (check_goods_specifications_exist($goods_id)) {
// 		  $link[0] = array('href' => RC_Uri::url('goods/admin/product_list', 'goods_id=' . $goods_id), 'text' => RC_Lang::lang('product'));
// 		}
		if ($code == 'virtual_card') {
		  $link[1] = array('href' => RC_Uri::url('goods/admin_virtual_card/replenish', 'goods_id=' . $goods_id), 'text' => RC_Lang::lang('add_replenish'));
		}
		$link[2] = add_link($code);
		$link[3] = list_link($code);

		for ($i = 0; $i < count($link); $i++) {
		  $key_array[] = $i;
		}
		krsort($link);
		$link = array_combine($key_array, $link);
		$this->showmessage(RC_Lang::lang('add_goods_ok'),ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS,array('pjaxurl' => RC_Uri::url('goods/admin/edit', "goods_id=$goods_id&extension_code=$code"), 'links' => $link,'max_id' => $goods_id));
	}

	/**
	 * 添加自定义栏目
	 */
	public function insert_term_meta()
	{
	    $code = empty($_REQUEST['extension_code']) ? '' : trim($_REQUEST['extension_code']);
	    if ($code == 'virtual_card') {
	        $this->admin_priv('virualcard'); // 检查权限
	    } else {
	        $this->admin_priv('goods_manage'); // 检查权限
	    }

		$goods_id = intval($_POST['goods_id']);

		$key = htmlspecialchars(trim($_POST['key']));
		$value = htmlspecialchars(trim($_POST['value']));

		/* 商品信息 */
		$goods = $this->db_goods->where(array('goods_id' => $goods_id))->find();

		$data = array(
			'object_id'		=> $goods['goods_id'],
			'object_type'	=> 'ecjia.goods',
			'object_group'	=> 'goods',
			'meta_key'		=> $key,
			'meta_value'	=> $value,
		);

		$this->db_term_meta->insert($data);

		$res = array(
			'key'		=> $key,
			'value'		=> $value,
			'pjaxurl'	=> RC_Uri::url('goods/admin/edit', array('goods_id'=>$goods['goods_id']))
		);

		$this->showmessage(__('添加自定义栏目成功'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, $res);

	}


	/**
	 * 更新自定义栏目
	 */
	public function update_term_meta()
	{
	    $code = empty($_REQUEST['extension_code']) ? '' : trim($_REQUEST['extension_code']);
	    if ($code == 'virtual_card') {
	        $this->admin_priv('virualcard'); // 检查权限
	    } else {
	        $this->admin_priv('goods_manage'); // 检查权限
	    }

	    $goods_id = intval($_POST['goods_id']);

		$meta_id = intval($_POST['meta_id']);

		if (empty($meta_id)) {
			$this->showmessage('缺少关键参数，更新失败！', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}

		$key = htmlspecialchars(trim($_POST['key']));
		$value = htmlspecialchars(trim($_POST['value']));

		/* 商品信息 */
		$goods = $this->db_goods->where(array('goods_id' => $goods_id))->find();

		$data = array(
			'object_id'		=> $goods['goods_id'],
			'object_type'	=> 'ecjia.goods',
			'object_group'	=> 'goods',
			'meta_key'		=> $key,
			'meta_value'	=> $value,
		);

		$this->db_term_meta->where(array('meta_id' => $meta_id))->update($data);

		$res = array(
			'key'		=> $key,
			'value'		=> $value,
			'pjaxurl'	=> RC_Uri::url('goods/admin/edit', array('goods_id'=>$goods['goods_id']))
		);

		$this->showmessage(__('添加自定义栏目成功'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, $res);

	}


	/**
	 * 删除自定义栏目
	 */
	public function remove_term_meta()
	{
		$meta_id = intval($_GET['meta_id']);

		$this->db_term_meta->where(array('meta_id'=>$meta_id))->delete();

		$this->showmessage(__('删除自定义栏目成功'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);

	}

	public function copy_goods() {
		$code = empty($_REQUEST['extension_code']) ? '' : trim($_REQUEST['extension_code']);
		$code = $code == 'virtual_card' ? 'virtual_card' : '';
		/* 是否处理缩略图 */
		if ($code == 'virtual_card') {
			$this->admin_priv('virualcard'); // 检查权限
		} else {
			$this->admin_priv('goods_manage'); // 检查权限
		}
		/* 检查货号是否重复 */
		if ($_POST['goods_sn']) {
			$count = $this->db_goods->join(null)->where(array('goods_sn' => $_POST['goods_sn'], 'is_delete' => 0, 'goods_id' => array('neq' => $_POST['goods_id'])))->count();
			if ($count > 0) {
				$this->showmessage(RC_Lang::lang('goods_sn_exists'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
		}

		/* 处理商品图片 */
		$goods_img = ''; // 初始化商品图片
		$goods_thumb = ''; // 初始化商品缩略图
		$img_original = ''; // 初始化原始图片


		$upload = RC_Upload::uploader('image', array('save_path' => 'images', 'auto_sub_dirs' => true));

		/* 是否处理商品图 */
		$proc_goods_img = true;
		if (!$upload->check_upload_file($_FILES['goods_img'])) {
			$proc_goods_img = false;
		}
		/* 是否处理缩略图 */
		$proc_thumb_img = true;
		if (!$upload->check_upload_file($_FILES['thumb_img'])) {
			$proc_thumb_img = false;
		}


		if ($proc_goods_img) {
			$image_info = $upload->upload($_FILES['goods_img']);
			if (empty($image_info)) {
				$this->showmessage($upload->error(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
		}
		if ($proc_thumb_img) {
			$thumb_info = $upload->upload($_FILES['thumb_img']);
			if (empty($thumb_info)) {
				$this->showmessage($upload->error(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
		}


		/* 如果没有输入商品货号则自动生成一个商品货号 */
		if (empty($_POST['goods_sn'])) {
			$max_id = $this->db_goods->join(null)->field('MAX(goods_id) + 1|max')->find();
			if (empty($max_id['max'])) {
				$goods_sn_bool = true;
				$goods_sn='';
			} else {
				$goods_sn = generate_goods_sn($max_id['max']);
			}
		} else {
			$goods_sn = $_POST['goods_sn'];
		}


		/* 处理商品数据 */
		$shop_price = !empty($_POST['shop_price']) ? $_POST['shop_price'] : 0;
		$market_price = !empty($_POST['market_price']) ? $_POST['market_price'] : 0;
		$promote_price = !empty($_POST['promote_price']) ? floatval($_POST['promote_price']) : 0;
		$is_promote = empty($promote_price) ? 0 : 1;
		$promote_start_date = ($is_promote && !empty($_POST['promote_start_date'])) ? RC_Time::local_strtotime($_POST['promote_start_date']) : 0;
		$promote_end_date = ($is_promote && !empty($_POST['promote_end_date'])) ? RC_Time::local_strtotime($_POST['promote_end_date']) : 0;
		$goods_weight = !empty($_POST['goods_weight']) ? $_POST['goods_weight'] * $_POST['weight_unit'] : 0;
		$is_best = isset($_POST['is_best']) ? 1 : 0;
		$is_new = isset($_POST['is_new']) ? 1 : 0;
		$is_hot = isset($_POST['is_hot']) ? 1 : 0;
		$is_on_sale = isset($_POST['is_on_sale']) ? 1 : 0;
		$is_alone_sale = isset($_POST['is_alone_sale']) ? 1 : 0;
		$is_shipping = isset($_POST['is_shipping']) ? 1 : 0;
		$goods_number = isset($_POST['goods_number']) ? $_POST['goods_number'] : 0;
		$warn_number = isset($_POST['warn_number']) ? $_POST['warn_number'] : 0;
		$goods_type = isset($_POST['goods_type']) ? $_POST['goods_type'] : 0;
		$give_integral = isset($_POST['give_integral']) ? intval($_POST['give_integral']) : '-1';
		$rank_integral = isset($_POST['rank_integral']) ? intval($_POST['rank_integral']) : '-1';
		$suppliers_id = isset($_POST['suppliers_id']) ? intval($_POST['suppliers_id']) : '0';

		// 		$goods_name_style	= $_POST['goods_name_color'] . '+' . $_POST['goods_name_style'];
		$goods_name_style = $_POST['goods_name_color'];

		$catgory_id = empty($_POST['cat_id']) ? '' : intval($_POST['cat_id']);
		$brand_id = empty($_POST['brand_id']) ? '' : intval($_POST['brand_id']);
		// 		$goods_thumb = (empty($goods_thumb) && !empty($_POST['goods_thumb_url']) && goods_parse_url($_POST['goods_thumb_url'])) ? htmlspecialchars(trim($_POST['goods_thumb_url'])) : $goods_thumb;
		// 		$goods_thumb = (empty($goods_thumb) && isset($_POST['auto_thumb'])) ? $goods_img : $goods_thumb;
		/* 入库 */
		if ($code == '') {
			$data = array(
				'goods_name'            => $_POST['goods_name'],
				'goods_name_style'      =>$goods_name_style,
				'goods_sn'              => $goods_sn,
				'cat_id'                => $catgory_id,
				'brand_id'              => $brand_id,
				'shop_price'            => $shop_price,
				'market_price'          => $market_price,
				'is_promote'            => $is_promote,
				'promote_price'         => $promote_price,
				'promote_start_date'    => $promote_start_date,
				'promote_end_date'      => $promote_end_date,
				//		'goods_img' => $goods_img,
				//		'goods_thumb' => $goods_thumb,
				//		'original_img' => $original_img,
				'keywords'              => $_POST['keywords'],
				'goods_brief'           => $_POST['goods_brief'],
				'seller_note'           => $_POST['seller_note'],
				'goods_weight'          => $goods_weight,
				'goods_number'          => $goods_number,
				'warn_number'           => $warn_number,
				'integral'              => $_POST['integral'],
				'give_integral'         => $give_integral,
				'is_best'               => $is_best,
				'is_new'                => $is_new,
				'is_hot'                => $is_hot,
				'is_on_sale'            => $is_on_sale,
				'is_alone_sale'         => $is_alone_sale,
				'is_shipping'           => $is_shipping,
				'goods_desc'            => $_POST['goods_desc'],
				'add_time'              => RC_Time::gmtime(),
				'last_update'           => RC_Time::gmtime(),
				'goods_type'            => $goods_type,
				'rank_integral'         => $rank_integral,
				'suppliers_id'          => $suppliers_id,
			);
		} else {
			$data = array(
				'goods_name'         => $_POST['goods_name'],
				'goods_name_style'   => $goods_name_style,
				'goods_sn'           => $goods_sn,
				'cat_id'             => $catgory_id,
				'brand_id'           => $brand_id,
				'shop_price'         => $shop_price,
				'market_price'       => $market_price,
				'is_promote'         => $is_promote,
				'promote_price'      => $promote_price,
				'promote_start_date' => $promote_start_date,
				'promote_end_date'   => $promote_end_date,
				// 			 'goods_img' => $goods_img,
		// 			 'goods_thumb' => $goods_thumb,
		// 			 'original_img' => $original_img,
				'keywords'           => $_POST['keywords'],
				'goods_brief'        => $_POST['goods_brief'],
				'seller_note'        => $_POST['seller_note'],
				'goods_weight'       => $goods_weight,
				'goods_number'       => $goods_number,
				'warn_number'        => $warn_number,
				'integral'           => $_POST['integral'],
				'give_integral'      => $give_integral,
				'is_best'            => $is_best,
				'is_new'             => $is_new,
				'is_hot'             => $is_hot,
				'is_real'            => 0,
				'is_on_sale'         => $is_on_sale,
				'is_alone_sale'      => $is_alone_sale,
				'is_shipping'        => $is_shipping,
				'goods_desc'         => $_POST['goods_desc'],
				'add_time'           => RC_Time::gmtime(),
				'last_update'        => RC_Time::gmtime(),
				'goods_type'         => $goods_type,
				'extension_code'     => $code,
				'rank_integral'      => $rank_integral,

			);
		}

		$insert_id = $this->db_goods->join(null)->insert($data);
		/* 商品编号 */
		$goods_id = $insert_id;
		if($goods_sn_bool){
			$goods_sn = generate_goods_sn($goods_id);
			$data = array('goods_sn' => $goods_sn);
			$this->db_goods->where('goods_id='.$goods_id)->update($data);
		}

		/* 记录日志 */
		ecjia_admin::admin_log($_POST['goods_name'], 'add', 'goods');
		/* 处理会员价格 */
		if (isset($_POST['user_rank']) && isset($_POST['user_price'])) {
			handle_member_price($goods_id, $_POST['user_rank'], $_POST['user_price']);
		}

		/* 处理优惠价格 */
		if (isset($_POST['volume_number']) && isset($_POST['volume_price'])) {
			$temp_num = array_count_values($_POST['volume_number']);
			foreach ($temp_num as $v) {
				if ($v > 1) {
					// 				sys_msg($_LANG['volume_number_continuous'], 1, array(), false);
					$this->showmessage(RC_Lang::lang('volume_number_continuous'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
					break;
				}
			}
			handle_volume_price($goods_id, $_POST['volume_number'], $_POST['volume_price']);
		}

		/* 处理扩展分类 */
		if (isset($_POST['other_cat'])) {
			handle_other_cat($goods_id, array_unique($_POST['other_cat']));
		}

		/* 更新上传后的商品图片 */
		if ($proc_goods_img) {
			$goods_image = new goods_image($image_info);

			if ($proc_thumb_img) {
				$goods_image->set_auto_thumb(false);
			}

			$result = $goods_image->update_goods($goods_id);
			if (is_ecjia_error($result)) {
				$this->showmessage($result->get_error_message(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
		}

		/* 更新上传后的缩略图片 */
		if ($proc_thumb_img) {

			$thumb_image = new goods_image($thumb_info);
			$result = $thumb_image->update_thumb($goods_id);
			if (is_ecjia_error($result)) {
				$this->showmessage($result->get_error_message(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
		}

		/* 记录上一次选择的分类和品牌 */
		setcookie('ECSCP[last_choose]', $catgory_id . '|' . $brand_id, RC_Time::gmtime() + 86400);


		$goods_copyid = $_POST['goods_copyid'];
		if (!empty($goods_copyid)) {
			/* 关联商品 */
			$res = $this->db_link_goods->where(array('goods_id' => $goods_copyid))->select();
			$data = array();
			if (!empty($res)) {
				foreach ($res as $val) {
					$data[] = array(
						'goods_id'		=> $goods_id,
						'link_goods_id'	=> $val['link_goods_id'],
						'is_double'		=> $val['is_double'],
						'admin_id'		=> $_SESSION['admin_id'],
					);
				}
			}
			$this->db_link_goods->batch_insert($data);

			/* 关联配件 */
			$res = $this->db_group_goods->where(array('parent_id' => $goods_copyid))->select();
			$data = array();
			if (!empty($res)) {
				foreach ($res as $val) {
					$data[] = array(
						'parent_id'		=> $goods_id,
						'goods_id'		=> $val['goods_id'],
						'goods_price'	=> $val['goods_price'],
						'admin_id'		=> $_SESSION['admin_id'],
					);
				}
			}
			$this->db_group_goods->batch_insert($data);

			/* 关联文章 */
			$res = $this->db_goods_article->where(array('goods_id' => $goods_copyid))->select();
			$data = array();
			if (!empty($res)) {
				foreach ($res as $val) {
					$data[] = array(
						'goods_id' => $goods_id,
						'article_id' => $val['article_id'],
						'admin_id' => $_SESSION['admin_id'],
					);
				}
				$this->db_goods_article->batch_insert($data);
			}

			/* 商品属性 */
			$res = $this->db_goods_attr->where(array('goods_id' => $goods_copyid))->select();
			$data_type = $this->db_goods->field('goods_type')->where(array('goods_id' => $goods_copyid))->find();
			$this->db_goods->where(array('goods_id' => $goods_id))->update($data_type);
			$data = array();
			if (!empty($res)) {
				foreach ($res as $val) {
					$data[] = array(
						'attr_id'		=> $val['attr_id'],
						'goods_id'		=> $goods_id,
						'attr_value'	=> $val['attr_value'],
						'attr_price'	=> $val['attr_price'],
					);
				}
				$this->db_goods_attr->batch_insert($data);
			}


		}




		/* 提示页面 */
		$link = array();
		if ($code == 'virtual_card') {
			$link[1] = array('href' => RC_Uri::url('goods/admin_virtual_card/replenish', 'goods_id=' . $goods_id), 'text' => RC_Lang::lang('add_replenish'));
		}
		$link[2] = add_link($code);
		$link[3] = list_link($code);

		for ($i = 0; $i < count($link); $i++) {
			$key_array[] = $i;
		}
		krsort($link);
		$link = array_combine($key_array, $link);
		$this->showmessage(RC_Lang::lang('add_goods_ok'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('goods/admin/edit', "goods_id=$goods_id&extension_code=$code"), 'links' => $link, 'max_id' => $goods_id));

		/* 以下开始复制商品的处理 */

			// 商品信息
// 			$goods['goods_id'] = 0;
// 			$goods['goods_sn'] = '';
// 			$goods['goods_name'] = '';
// 			$goods['goods_img'] = '';
// 			$goods['goods_thumb'] = '';
// 			$goods['original_img'] = '';

			// 扩展分类不变

			// 关联商品
// 			$sql = "DELETE FROM " . $ecs->table('link_goods') .
// 			" WHERE (goods_id = 0 OR link_goods_id = 0)" .
// 			" AND admin_id = '$_SESSION[admin_id]'";
// 			$db->query($sql);

// 			$sql = "SELECT '0' AS goods_id, link_goods_id, is_double, '$_SESSION[admin_id]' AS admin_id" .
// 			" FROM " . $ecs->table('link_goods') .
// 			" WHERE goods_id = '$_REQUEST[goods_id]' ";
// 			$res = $db->query($sql);
// 			while ($row = $db->fetchRow($res))
// 			{
// 				$db->autoExecute($ecs->table('link_goods'), $row, 'INSERT');
// 			}

// 				$sql = "SELECT goods_id, '0' AS link_goods_id, is_double, '$_SESSION[admin_id]' AS admin_id" .
// 				" FROM " . $ecs->table('link_goods') .
// 				" WHERE link_goods_id = '$_REQUEST[goods_id]' ";
// 				$res = $db->query($sql);
// 				while ($row = $db->fetchRow($res))
// 				{
// 					$db->autoExecute($ecs->table('link_goods'), $row, 'INSERT');
// 				}
// 				// 配件
// 				$sql = "DELETE FROM " . $ecs->table('group_goods') .
// 				" WHERE parent_id = 0 AND admin_id = '$_SESSION[admin_id]'";
// 				$db->query($sql);

// 				$sql = "SELECT 0 AS parent_id, goods_id, goods_price, '$_SESSION[admin_id]' AS admin_id " .
// 				"FROM " . $ecs->table('group_goods') .
// 				" WHERE parent_id = '$_REQUEST[goods_id]' ";
// 				$res = $db->query($sql);
// 				while ($row = $db->fetchRow($res))
// 				{
// 					$db->autoExecute($ecs->table('group_goods'), $row, 'INSERT');
// 				}


// 				// 关联文章
// 				$sql = "DELETE FROM " . $ecs->table('goods_article') .
// 				" WHERE goods_id = 0 AND admin_id = '$_SESSION[admin_id]'";
// 				$db->query($sql);

// 				$sql = "SELECT 0 AS goods_id, article_id, '$_SESSION[admin_id]' AS admin_id " .
// 				"FROM " . $ecs->table('goods_article') .
// 				" WHERE goods_id = '$_REQUEST[goods_id]' ";
// 				$res = $db->query($sql);
// 				while ($row = $db->fetchRow($res))
// 				{
// 					$db->autoExecute($ecs->table('goods_article'), $row, 'INSERT');
// 				}

// 				// 图片不变

// 				// 商品属性
// 				$sql = "DELETE FROM " . $ecs->table('goods_attr') . " WHERE goods_id = 0";
// 				$db->query($sql);

// 				$sql = "SELECT 0 AS goods_id, attr_id, attr_value, attr_price " .
// 						"FROM " . $ecs->table('goods_attr') .
// 						" WHERE goods_id = '$_REQUEST[goods_id]' ";
// 				$res = $db->query($sql);
// 				while ($row = $db->fetchRow($res))
// 				{
// 					$db->autoExecute($ecs->table('goods_attr'), addslashes_deep($row), 'INSERT');
// 				}
	}

	/**
	* 编辑商品
	*/
	public function update()
	{
//		global $ecs, $db, $_CFG, $sess;
	    $code = empty($_REQUEST['extension_code']) ? '' : trim($_REQUEST['extension_code']);

	    if ($code == 'virtual_card') {
	        $this->admin_priv('virualcard'); // 检查权限
	    } else {
	        $this->admin_priv('goods_manage'); // 检查权限
	    }

	    /* 对编辑商品进行权限检查  BY：MaLiuWei  START */
	    if (!empty($_SESSION['ru_id'])) {
	    	$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
	    }
	    /* 对编辑商品进行权限检查  BY：MaLiuWei  END */
		// $goods_type = isset($_POST['goods_type']) ? $_POST['goods_type'] : 0;
		$goods_id = $_POST[goods_id];


//		$image = new image(ecjia::config('bgcolor'), null, false);
		/* 是否处理缩略图 */
// 		$proc_thumb = (isset($GLOBALS['shop_id']) && $GLOBALS['shop_id'] > 0) ? false : true;

		/* 检查货号是否重复 */
		if ($_POST['goods_sn']) {
// 					$sql = "SELECT COUNT(*) FROM " . $ecs->table('goods') .
// 					" WHERE goods_sn = '$_POST[goods_sn]' AND is_delete = 0 AND goods_id <> '$_POST[goods_id]'";
			$count = $this->db_goods->where(array('goods_sn' => $_POST['goods_sn'], 'is_delete' => 0, 'goods_id' => array('neq' => $_POST[goods_id])))->count();
// 					if ($db->getOne($sql) > 0)
			if ($count > 0) {
// 				sys_msg($_LANG['goods_sn_exists'], 1, array(), false);
				$this->showmessage(RC_Lang::lang('goods_sn_exists'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
		}

		$upload = RC_Upload::uploader('image', array('save_path' => 'images', 'auto_sub_dirs' => true));

		/* 是否处理商品图 */
		$proc_goods_img = true;
		if (!$upload->check_upload_file($_FILES['goods_img'])) {
		    $proc_goods_img = false;
		}
		/* 是否处理缩略图 */
		$proc_thumb_img = true;
		if (!$upload->check_upload_file($_FILES['thumb_img'])) {
		    $proc_thumb_img = false;
		}

		if ($proc_goods_img) {
		    $image_info = $upload->upload($_FILES['goods_img']);
		    if (empty($image_info)) {
		        $this->showmessage($upload->error(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		    }
		}
		if ($proc_thumb_img) {
		    $thumb_info = $upload->upload($_FILES['thumb_img']);
		    if (empty($thumb_info)) {
		        $this->showmessage($upload->error(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		    }
		}

		/* 处理商品图片 */
		$goods_img = ''; // 初始化商品图片
		$goods_thumb = ''; // 初始化商品缩略图
		$img_original = ''; // 初始化原始图片

		/* 如果没有输入商品货号则自动生成一个商品货号 */
		if (empty($_POST['goods_sn'])) {
			// 				$max_id	 = $is_insert ? $db->getOne("SELECT MAX(goods_id) + 1 FROM ".$this->db_goods) : $_REQUEST['goods_id'];
		  $max_id = $_REQUEST['goods_id'];

		  $goods_sn = generate_goods_sn($max_id);
		} else {
		  $goods_sn = $_POST['goods_sn'];
		}

		/* 处理商品数据 */
		$shop_price = !empty($_POST['shop_price']) ? $_POST['shop_price'] : 0;
		$market_price = !empty($_POST['market_price']) ? $_POST['market_price'] : 0;
		$promote_price = !empty($_POST['promote_price']) ? floatval($_POST['promote_price']) : 0;
		$is_promote = empty($_POST['is_promote']) ? 0 : 1;
		$promote_start_date = ($is_promote && !empty($_POST['promote_start_date'])) ? RC_Time::local_strtotime($_POST['promote_start_date']) : 0;
		$promote_end_date = ($is_promote && !empty($_POST['promote_end_date'])) ? RC_Time::local_strtotime($_POST['promote_end_date']) : 0;
		$goods_weight = !empty($_POST['goods_weight']) ? $_POST['goods_weight'] * $_POST['weight_unit'] : 0;
		$is_best = isset($_POST['is_best']) ? 1 : 0;
		$is_new = isset($_POST['is_new']) ? 1 : 0;
		$is_hot = isset($_POST['is_hot']) ? 1 : 0;
		$is_on_sale = isset($_POST['is_on_sale']) ? 1 : 0;
		$is_alone_sale = isset($_POST['is_alone_sale']) ? 1 : 0;
		$is_shipping = isset($_POST['is_shipping']) ? 1 : 0;
		$goods_number = isset($_POST['goods_number']) ? $_POST['goods_number'] : 0;
		$warn_number = isset($_POST['warn_number']) ? $_POST['warn_number'] : 0;
		// $goods_type = isset($_POST['goods_type']) ? $_POST['goods_type'] : 0;
		$give_integral = isset($_POST['give_integral']) ? intval($_POST['give_integral']) : '-1';
		$rank_integral = isset($_POST['rank_integral']) ? intval($_POST['rank_integral']) : '-1';
		$suppliers_id = isset($_POST['suppliers_id']) ? intval($_POST['suppliers_id']) : '0';

			// 			$goods_name_style = $_POST['goods_name_color'] . '+' . $_POST['goods_name_style'];
		$goods_name = htmlspecialchars($_POST['goods_name']);
		$goods_name_style = htmlspecialchars($_POST['goods_name_color']);
		$catgory_id = empty($_POST['cat_id']) ? '' : intval($_POST['cat_id']);

		$store_category = !empty($_POST['store_category']) ? intval($_POST['store_category']) : 0;
		if($store_category > 0){
			$catgory_id = $store_category;
		}

		$brand_id = empty($_POST['brand_id']) ? '' : intval($_POST['brand_id']);

// 		$goods_thumb = (empty($goods_thumb) && !empty($_POST['goods_thumb_url']) && goods_parse_url($_POST['goods_thumb_url'])) ? htmlspecialchars(trim($_POST['goods_thumb_url'])) : $goods_thumb;
// 		$goods_thumb = (empty($goods_thumb) && isset($_POST['auto_thumb'])) ? $goods_img : $goods_thumb;

		if (empty($goods_name)) {
			$this->showmessage(__('商品名称不能为空！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}

		/* 更新入库 */
		/* 如果有上传图片，删除原来的商品图 */
			// 				$sql = "SELECT goods_thumb, goods_img, original_img " .
			// 						" FROM " . $ecs->table('goods') .
			// 						" WHERE goods_id = '$_REQUEST[goods_id]'";
			// 				$row = $db->getRow($sql);3+6
// 		$row = $this->db_goods->join(null)->field('goods_thumb,goods_img,original_img')->find(array('goods_id' => $_REQUEST['goods_id']));



		if ($code != '') {
		  $sql .= "is_real=0, extension_code='$code', ";
		}
			// 				$sql .= "keywords = '$_POST[keywords]', " .
			// 				"goods_brief = '$_POST[goods_brief]', " .
			// 				"seller_note = '$_POST[seller_note]', " .
			// 				"goods_weight = '$goods_weight'," .
			// 				"goods_number = '$goods_number', " .
			// 				"warn_number = '$warn_number', " .
			// 				"integral = '$_POST[integral]', " .
			// 				"give_integral = '$give_integral', " .
			// 				"rank_integral = '$rank_integral', " .
			// 				"is_best = '$is_best', " .
			// 				"is_new = '$is_new', " .
			// 				"is_hot = '$is_hot', " .
			// 				"is_on_sale = '$is_on_sale', " .
			// 				"is_alone_sale = '$is_alone_sale', " .
			// 				"is_shipping = '$is_shipping', " .
			// 				"goods_desc = '$_POST[goods_desc]', " .
			// 				"last_update = '". gmtime() ."', ".
			// 				"goods_type = '$goods_type' " .
			// 				"WHERE goods_id = '$_REQUEST[goods_id]' LIMIT 1";
		$data = array(
		  'goods_name'				=> $goods_name,
		  'goods_name_style'	  	=> $goods_name_style,
		  'goods_sn'			  	=> $goods_sn,
		  'cat_id'					=> $catgory_id,
		  'brand_id'			  	=> $brand_id,
		  'shop_price'				=> $shop_price,
		  'market_price'		  	=> $market_price,
		  'is_promote'				=> $is_promote,
		  'promote_price'		 	=> $promote_price,
		  'promote_start_date'		=> $promote_start_date,
		  'suppliers_id'		  	=> $suppliers_id,
		  'promote_end_date'	  	=> $promote_end_date,
// 		  'goods_img'			 	=> $goods_img,
// 		  'original_img'		  	=> $original_img,
// 		  'goods_thumb'		   		=> $goods_thumb,
		  'is_real'			   		=> empty($code) ? '1' : '0',
		  'extension_code'			=> $code,
		  'keywords'			  	=> $_POST['keywords'],
		  'goods_brief'		   		=> $_POST['goods_brief'],
		  'seller_note'		   		=> $_POST['seller_note'],
		  'goods_weight'		 	=> $goods_weight,
		  'goods_number'		  	=> $goods_number,
		  'warn_number'		   		=> $warn_number,
		  'integral'			  	=> $_POST['integral'],
		  'give_integral'		 	=> $give_integral,
		  'rank_integral'		 	=> $rank_integral,
		  'is_best'			   		=> $is_best,
		  'is_new'					=> $is_new,
		  'is_hot'					=> $is_hot,
		  'is_on_sale'				=> $is_on_sale,
		  'is_alone_sale'		 	=> $is_alone_sale,
		  'is_shipping'		   		=> $is_shipping,
		  // 'goods_desc'				=> $_POST['goods_desc'],
		  'last_update'		   		=> RC_Time::gmtime(),
		  // 'goods_type'				=> $goods_type,
		  );
		$this->db_goods->join(null)->where(array('goods_id' => $_REQUEST['goods_id']))->update($data);
			//$db->query($sql);
		/* 商品编号 */
		$goods_id = $_REQUEST['goods_id'];
		/* 记录日志 */
		ecjia_admin::admin_log($_POST['goods_name'], 'edit', 'goods');

		/* 处理会员价格 */
		if (isset($_POST['user_rank']) && isset($_POST['user_price'])) {
		  handle_member_price($goods_id, $_POST['user_rank'], $_POST['user_price']);
		}

		/* 处理优惠价格 */
		if (isset($_POST['volume_number']) && isset($_POST['volume_price'])) {
		  $temp_num = array_count_values($_POST['volume_number']);
		  foreach ($temp_num as $v) {
		     if ($v > 1) {
			// 						sys_msg($_LANG['volume_number_continuous'], 1, array(), false);
		        $this->showmessage(RC_Lang::lang('volume_number_continuous'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		        break;
		    }
		}
		handle_volume_price($goods_id, $_POST['volume_number'], $_POST['volume_price']);
		}

		/* 处理扩展分类 */
        handle_other_cat($goods_id, array_unique($_POST['other_cat']));

		/* 更新上传后的商品图片 */
		if ($proc_goods_img) {
		    $goods_image = new goods_image($image_info);

		    if ($proc_thumb_img) {
		        $goods_image->set_auto_thumb(false);
		    }

		    $result = $goods_image->update_goods($goods_id);
		    if (is_ecjia_error($result)) {
		        $this->showmessage($result->get_error_message(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		    }
		}

		/* 更新上传后的缩略图片 */
		if ($proc_thumb_img) {

		    $thumb_image = new goods_image($thumb_info);
		    $result = $thumb_image->update_thumb($goods_id);
		    if (is_ecjia_error($result)) {
		        $this->showmessage($result->get_error_message(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		    }
		}


		/* 记录上一次选择的分类和品牌 */
		setcookie('ECSCP[last_choose]', $catgory_id . '|' . $brand_id, RC_Time::gmtime() + 86400);
		/* 清空缓存 */
		// 			clear_cache_files();
		/* 提示页面 */
		$link = array();
// 		if (check_goods_specifications_exist($goods_id)) {
// 		  $link[0] = array('href' => RC_Uri::url('goods/admin/product_list', 'goods_id=' . $goods_id), 'text' => RC_Lang::lang('product'));
// 		}
		if ($code == 'virtual_card') {
		  $link[1] = array('href' => RC_Uri::url('goods/admin_virtual_card/replenish', 'goods_id=' . $goods_id), 'text' => RC_Lang::lang('add_replenish'));
		}
		$link[3] = list_link($code);


		// 			$key_array = array_keys($link);
		for ($i = 0; $i < count($link); $i++) {
		  $key_array[] = $i;
		}
		krsort($link);
		$link = array_combine($key_array, $link);

			// 			sys_msg($is_insert ? $_LANG['add_goods_ok'] : $_LANG['edit_goods_ok'], 0, $link);
		$this->showmessage(RC_Lang::lang('edit_goods_ok'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('links' => $link,'max_id' => $goods_id));
// 		}
}

	/**
	 * 审核商品
	 */
	public function review()
	{
// 		检查权限
		$code = empty($_REQUEST['extension_code']) ? '' : trim($_REQUEST['extension_code']);
		if ($code == 'virtual_card') {
			$this->admin_priv('virualcard'); // 检查权限
		} else {
			$this->admin_priv('goods_manage'); // 检查权限
		}
		if(empty($_SESSION['ru_id'])) {
			$arr['review_status'] = $_POST['value'];
			$id = intval($_POST['pk']);
			$rs = $this->db_goods->where(array('goods_id'=>$id))->update($arr);
			if($rs){
				$this->showmessage(__('成功切换审核状态'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('goods/admin/init')));
			}
		}else{
			$this->showmessage(__('请进入入住商后台进行操作'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
		}
	}

	/**
	* 批量操作
	*/
	public function batch()
	{
	    $this->admin_priv('goods_manage', ecjia::MSGTYPE_JSON);

		/* 对操作的商品进行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对操作的商品进行权限检查  BY：MaLiuWei  END */

		$code = empty($_REQUEST['extension_code']) ? '' : trim($_REQUEST['extension_code']);

		/* 取得要操作的商品编号 */
		$goods_id = !empty($_POST['checkboxes']) ? $_POST['checkboxes'] : 0;

		if ($_GET['type'] == '') {
			$this->showmessage(__('请选择操作'),ecjia::MSGTYPE_JSON|ecjia::MSGSTAT_ERROR);
		}
		if (isset($_GET['type'])) {
			/* 放入回收站 */
			if ($_GET['type'] == 'trash') {
				/* 检查权限 */
				$this->admin_priv('remove_back');
				update_goods($goods_id, 'is_delete', '1');
				/* 记录日志 */
				ecjia_admin::admin_log('', 'batch_trash', 'goods');
			} /* 上架 */
			elseif ($_GET['type'] == 'on_sale') {
				/* 检查权限 */
				$this->admin_priv('goods_manage');
				update_goods($goods_id, 'is_on_sale', '1');
                $goods_id = explode(',', $goods_id);
                foreach($goods_id as $val){
                    $goodds_name = $this->db_goods->where(array('goods_id' => $goods_id))->get_field('goods_name');
                    ecjia_admin::admin_log($goodds_name, 'batch_trash', 'goods');
                }
			} /* 下架 */
			elseif ($_GET['type'] == 'not_on_sale') {
				/* 检查权限 */
				$this->admin_priv('goods_manage');
				update_goods($goods_id, 'is_on_sale', '0');
			} /* 设为精品 */
			elseif ($_GET['type'] == 'best') {
				/* 检查权限 */
				$this->admin_priv('goods_manage');
				update_goods($goods_id, 'is_best', '1');
			} /* 取消精品 */
			elseif ($_GET['type'] == 'not_best') {
				/* 检查权限 */
				$this->admin_priv('goods_manage');
				update_goods($goods_id, 'is_best', '0');
			} /* 设为新品 */
			elseif ($_GET['type'] == 'new') {
				/* 检查权限 */
				$this->admin_priv('goods_manage');
				update_goods($goods_id, 'is_new', '1');
			} /* 取消新品 */
			elseif ($_GET['type'] == 'not_new') {
				/* 检查权限 */
				$this->admin_priv('goods_manage');
				update_goods($goods_id, 'is_new', '0');
			} /* 设为热销 */
			elseif ($_GET['type'] == 'hot') {
				/* 检查权限 */
				$this->admin_priv('goods_manage');
				update_goods($goods_id, 'is_hot', '1');
			} /* 取消热销 */
			elseif ($_GET['type'] == 'not_hot') {
				/* 检查权限 */
				$this->admin_priv('goods_manage');
				update_goods($goods_id, 'is_hot', '0');
			} /* 转移到分类 */
			elseif ($_GET['type'] == 'move_to') {
				/* 检查权限 */
				$this->admin_priv('goods_manage');
				update_goods($goods_id, 'cat_id', $_GET['target_cat']);
			} /* 还原 */
			elseif ($_GET['type'] == 'restore') {
				/* 检查权限 */
				$this->admin_priv('remove_back');
				update_goods($goods_id, 'is_delete', '0');
				/* 记录日志 */
				ecjia_admin::admin_log('', 'batch_restore', 'goods');
			} /* 删除 */
			elseif ($_GET['type'] == 'drop') {
				/* 检查权限 */
				$this->admin_priv('remove_back');
				 delete_goods($goods_id);
				/* 记录日志 */
				ecjia_admin::admin_log('', 'batch_remove', 'goods');
			}
		}
		$page = empty($_GET['page']) ? '&page=1' : '&page='.$_GET['page'];
		if ($_GET['type'] == 'drop' || $_GET['type'] == 'restore') {
			$pjaxurl = RC_Uri::url('goods/admin/trash' ,$page);
		} else if ($code == 'virtual_card') {
			$pjaxurl = RC_Uri::url('goods/admin/init' ,'extension_code=virtual_card&is_on_sale='.$_GET['is_on_sale'].$page);
		} else {
			$pjaxurl = RC_Uri::url('goods/admin/init' ,'is_on_sale='.$_GET['is_on_sale'].$page);
		}

		$this->showmessage(RC_Lang::lang('batch_handle_ok'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => $pjaxurl));

// 		global $ecs, $db, $_CFG, $sess;
// 		/* 转移到供货商 */
// 		elseif ($_POST['type'] == 'suppliers_move_to') {
// 			/* 检查权限 */
// 			$this->admin_priv('goods_manage');
// 			update_goods($goods_id, 'suppliers_id', $_POST['suppliers_id']);
// 		}
	}

	/**
	* 修改商品名称
	*/
	public function edit_goods_name()
	{
		$this->admin_priv('goods_manage', ecjia::MSGTYPE_JSON);

		/* 对修改商品名称进行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对修改商品名称进行权限检查  BY：MaLiuWei  END */
		$goods_id = intval($_POST['pk']);
		$goods_name = trim($_POST['value']);
        if(!empty($goods_name)) {
        	if ($this->db_goods->where(array('goods_id' => $goods_id))->update(array('goods_name' => $goods_name, 'last_update' => RC_Time::gmtime()))) {
        		$this->showmessage(__('商品名称修改成功！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => stripslashes($goods_name)));
        	}
        } else {
        	$this->showmessage('商品名称不能为空！', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
        }

	}

	/**
	* 修改商品货号
	*/
	public function edit_goods_sn()
	{
		$this->admin_priv('goods_manage', ecjia::MSGTYPE_JSON);

		/* 对修改商品货号进行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对修改商品货号进行权限检查  BY：MaLiuWei  END */
		$goods_id = intval($_POST['pk']);
		$goods_sn = trim($_POST['value']);

		if (empty($goods_sn)) {
			$this->showmessage('商品货号不能为空！', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}

		/* 检查是否重复 */
		if ($goods_sn) {
			$count = $this->db_goods->where(array('goods_sn' => $goods_sn, 'goods_id' => array('neq' => $goods_id)))->count();
			if ($count > 0) {
				$this->showmessage(RC_Lang::lang('goods_sn_exists'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
		}
		$query = $this->db_products->where(array('product_sn' => $goods_sn))->get_field('goods_id');
		if ($query) {
			$this->showmessage(RC_Lang::lang('goods_sn_exists'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		if ($this->db_goods->where(array('goods_id' => $goods_id))->update(array('goods_sn' => $goods_sn, 'last_update' => RC_Time::gmtime()))) {
			$this->showmessage(__('商品货号修改成功！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => stripslashes($goods_sn)));
		}
	}

	/**
	 * 检查商品货号
	 */
	public function check_goods_sn()
	{
		$this->admin_priv('goods_manage', ecjia::MSGTYPE_JSON);

		$goods_id = intval($_REQUEST['goods_id']);
		$goods_sn = htmlspecialchars(trim($_REQUEST['goods_sn']));

		$query_goods_sn = $this->db_goods->where(array('goods_sn' => $goods_sn,'goods_id' => array('neq' => $goods_id)))->get_field('goods_id');
		if ($query_goods_sn) {
			$this->showmessage(RC_Lang::lang('goods_sn_exists'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}

		/* 检查是否重复 */
		if (!empty($goods_sn)) {
			$query = $this->db_products->where(array('product_sn' => $goods_sn))->get_field('goods_id');
			if ($query) {
				$this->showmessage(RC_Lang::lang('goods_sn_exists'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
		}

		$this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => ''));
	}


// 	public function check_products_goods_sn()
// 	{
// 		$this->admin_priv('goods_manage', ecjia::MSGTYPE_JSON);
// 		$goods_id = intval($_REQUEST['goods_id']);
// 		$goods_sn = trim($_REQUEST['goods_sn']);
// 		$products_sn = explode('||', $goods_sn);
// 		if (!is_array($products_sn)) {
// 			$this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => ''));
// 		} else {
// 			foreach ($products_sn as $val) {
// 				if (empty($val)) {
// 					continue;
// 				}
// 				$int_arry[] = $val;
// 				if (is_array($int_arry)) {
// 					if (in_array($val, $int_arry)) {
// 						$this->showmessage($val . RC_Lang::lang('goods_sn_exists'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
// 					}
// 				}
// 				if ($this->db_products->where(array('product_sn' => $val))->get_field('goods_id')) {
// 					$this->showmessage($val . RC_Lang::lang('goods_sn_exists'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
// 				}
// 			}
// 		}

// 	}

	/**
	* 修改商品价格
	*/
	public function edit_goods_price()
	{
		$this->admin_priv('goods_manage', ecjia::MSGTYPE_JSON);

		/* 对修改商品价格进行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对修改商品价格进行权限检查  BY：MaLiuWei  END */
		$goods_id = intval($_POST['pk']);
		$goods_price = floatval($_POST['value']);
		$price_rate = floatval(ecjia::config('market_price_rate') * $goods_price);
		$data = array(
			'shop_price'	=> $goods_price,
			'market_price'  => $price_rate,
			'last_update'   => RC_Time::gmtime()
			);
		if ($goods_price < 0 || $goods_price == 0 && $_POST['val'] != "$goods_price") {
			$this->showmessage(RC_Lang::lang('shop_price_invalid'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		} else {
			if ($this->db_goods->where(array('goods_id' => $goods_id))->update($data)) {
				$this->showmessage(__('商品价格修改成功！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('goods/admin/init'),'content' => number_format($goods_price, 2, '.', '')));
			}
		}
	}


	/**
	 * 修改商品排序
	 */
	public function edit_sort_order()
	{
		$this->admin_priv('goods_manage', ecjia::MSGTYPE_JSON);

		/* 对修改商品排序进行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对修改商品排序进行权限检查  BY：MaLiuWei  END */
		$goods_id = intval($_POST['pk']);
		$sort_order = intval($_POST['value']);
		$data = array(
			'sort_order' => $sort_order,
			'last_update' => RC_Time::gmtime()
		);
		if ($this->db_goods->where(array('goods_id' => $goods_id))->update($data)) {
			$this->showmessage(__('商品排序修改成功！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_uri::url('goods/admin/init'),'content' => $sort_order));
		}
	}

	/**
	* 修改商品库存数量
	*/
	public function edit_goods_number()
	{
		$this->admin_priv('goods_manage', ecjia::MSGTYPE_JSON);

		/* 对修改商品库存数量进行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对修改商品库存数量进行权限检查  BY：MaLiuWei  END */
		$goods_id = intval($_POST['pk']);
		$goods_num = intval($_POST['value']);
		$data = array(
			'goods_number' => $goods_num,
			'last_update' => RC_Time::gmtime()
			);
		if ($goods_num < 0 || $_POST['value'] != $goods_num) {
			$this->showmessage(RC_Lang::lang('goods_number_error'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}

// 		if (check_goods_product_exist($goods_id) == 1) {
// 			$this->showmessage(__('库存已存在货品，不能修改库存'),ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
// 		}

		if ($this->db_goods->where(array('goods_id' => $goods_id))->update($data)) {
			$this->showmessage(__('商品库存修改成功！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_uri::url('goods/admin/init'),'content' => $goods_num));
		}
	}


	/**
	* 修改上架状态
	*/
	public function toggle_on_sale()
	{
		$this->admin_priv('goods_manage', ecjia::MSGTYPE_JSON);

		/* 对修改上架状态进行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对修改上架状态进行权限检查  BY：MaLiuWei  END */
		$code = empty($_REQUEST['extension_code']) ? '' : trim($_REQUEST['extension_code']);
		$pjaxurl = empty($code) ? RC_Uri::url('goods/admin/init') : RC_Uri::url('goods/admin/init','extension_code=virtual_card');
		$goods_id = intval($_POST['id']);
		$on_sale = intval($_POST['val']);

		$data = array(
			'is_on_sale' => $on_sale,
			'last_update' => RC_Time::gmtime()
			);
		/* 记录日志 */
		$goods_name = $this->db_goods->where(array('goods_id' => $goods_id))->get_field('goods_name');
		if($on_sale == '1') {
			ecjia_admin::admin_log('上架商品，'.$goods_name, 'setup', 'goods');
		}else{
			ecjia_admin::admin_log('下架商品，'.$goods_name, 'setup', 'goods');
		}

		if ($this->db_goods->where(array('goods_id' => $goods_id))->update($data)) {
			$this->showmessage(__('已成功切换上架状态！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => $pjaxurl, 'content' => $on_sale));
		}
	}

	/**
	* 修改精品推荐状态
	*/
	public function toggle_best()
	{
		$this->admin_priv('goods_manage', ecjia::MSGTYPE_JSON);

		/* 对修改精品推荐状态进行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对修改精品推荐状态进行权限检查  BY：MaLiuWei  END */
		$goods_id = intval($_POST['id']);
		$is_best = intval($_POST['val']);
		$data = array(
			'is_best' => $is_best,
			'last_update' => RC_Time::gmtime()
			);
		/* 记录日志 */
		$goods_name = $this->db_goods->where(array('goods_id' => $goods_id))->get_field('goods_name');
		if($is_best == '1') {
			ecjia_admin::admin_log('设为精品，'.$goods_name, 'setup', 'goods');
		}else{
			ecjia_admin::admin_log('取消精品，'.$goods_name, 'setup', 'goods');
		}
		if ($this->db_goods->where(array('goods_id' => $goods_id))->update($data)) {
			$this->showmessage(__('已成功切换精品推荐状态！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => $is_best));
		}
	}

	/**
	* 修改新品推荐状态
	*/
	public function toggle_new()
	{
		$this->admin_priv('goods_manage', ecjia::MSGTYPE_JSON);

		/* 对修改新品推荐状态进行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对修改新品推荐状态进行权限检查  BY：MaLiuWei  END */
		$goods_id = intval($_POST['id']);
		$is_new = intval($_POST['val']);
		$data = array(
			'is_new' =>		 $is_new,
			'last_update' =>	RC_Time::gmtime()
			);
		/* 记录日志 */
		$goods_name = $this->db_goods->where(array('goods_id' => $goods_id))->get_field('goods_name');
		if($is_new == '1') {
			ecjia_admin::admin_log('设为新品，'.$goods_name, 'setup', 'goods');
		}else{
			ecjia_admin::admin_log('取消新品，'.$goods_name, 'setup', 'goods');
		}
		if ($this->db_goods->where(array('goods_id' => $goods_id))->update($data)) {
			$this->showmessage(__('已成功切换新品推荐状态！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => $is_new));
		}
	}

	/**
	* 修改热销推荐状态
	*/
	public function toggle_hot()
	{
		$this->admin_priv('goods_manage', ecjia::MSGTYPE_JSON);

		/* 对修改热销推荐状态进行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对修改热销推荐状态进行权限检查  BY：MaLiuWei  END */
		$goods_id = intval($_POST['id']);
		$is_hot = intval($_POST['val']);
		$data = array(
			'is_hot' => $is_hot,
			'last_update' => RC_Time::gmtime()
			);
		/* 记录日志 */
		$goods_name = $this->db_goods->where(array('goods_id' => $goods_id))->get_field('goods_name');
		if($is_hot == '1') {
			ecjia_admin::admin_log('设为热销，'.$goods_name, 'setup', 'goods');
		}else{
			ecjia_admin::admin_log('取消热销，'.$goods_name, 'setup', 'goods');
		}
		if ($this->db_goods->where(array('goods_id' => $goods_id))->update($data)) {
			$this->showmessage(__('已成功切换热销推荐状态！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => $is_hot));
		}
	}



	/**
	* 放入回收站
	*/
	public function remove()
	{
        $this->admin_priv('remove_back', ecjia::MSGTYPE_JSON);

        /* 对放入回收站进行权限检查  BY：MaLiuWei  START */
        if (!empty($_SESSION['ru_id'])) {
        	$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
        }
        /* 对放入回收站进行权限检查  BY：MaLiuWei  END */
    	$goods_id = intval($_GET['id']);
    	if ($this->db_goods->where(array('goods_id' => $goods_id))->update(array('is_delete' => 1))) {
    		$goods_name = $this->db_goods->where(array('goods_id' => $goods_id))->get_field('goods_name');
    		ecjia_admin::admin_log(addslashes($goods_name), 'trash', 'goods'); // 记录日志
    	}
    	$this->showmessage(RC_Lang::lang('drop_success'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
	}

	/**
	* 还原回收站中的商品
	*/
	public function restore_goods()
	{
	    $this->admin_priv('remove_back', ecjia::MSGTYPE_JSON);

	    /* 对还原回收站中的商品进行权限检查  BY：MaLiuWei  START */
	    if (!empty($_SESSION['ru_id'])) {
	    	$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
	    }
	    /* 对还原回收站中的商品进行权限检查  BY：MaLiuWei  END */
		$goods_id = intval($_GET['id']);
		$data = array(
			'is_delete' => 0,
			'add_time' => RC_Time::gmtime()
			);
		$this->db_goods->where(array('goods_id' => $goods_id))->update($data);

		$goods_name = $this->db_goods->where(array('goods_id' => $goods_id))->get_field('goods_name');

		ecjia_admin::admin_log(addslashes($goods_name), 'restore', 'goods'); // 记录日志
		$this->showmessage(__("成功还原[$goods_name]！"), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
	}

	/**
	 * 彻底删除商品
	 */
	public function drop_goods()
	{
		$this->admin_priv('remove_back');

		/* 对彻底删除商品进行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对彻底删除商品进行权限检查  BY：MaLiuWei  END */

		/* 取得要操作的商品编号 */
		$goods_id = !empty($_GET['id']) ? $_GET['id'] : 0;
		/* 取得商品信息 */
		$goods = $this->db_goods->where(array('goods_id' => $goods_id))->field('goods_id, goods_name, is_delete, is_real, goods_thumb,goods_img, original_img')->find();
		delete_goods($goods_id);
		/* 记录日志 */
		ecjia_admin::admin_log(addslashes($goods['goods_name']), 'remove', 'goods');
		$this->showmessage(__("成功彻底删除[" . $goods['goods_name'] . "]"), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('goods/admin/trash')));

	}

	/**
	* 货品列表
	*/
	public function product_list()
	{
		$this->admin_priv('goods_manage');
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商品列表'), RC_Uri::url('goods/admin/init')));
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('货品列表')));

		$goods_id = intval($_GET['goods_id']);
		if (empty($goods_id)) {
			$this->showmessage(__('缺少参数，请重试！'), ecjia::MSGTYPE_ALERT | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('goods/admin/init')));
		}
		// 过滤商品id
        $where = array('goods_id'  => $goods_id);
        if ($_SESSION['ru_id']) $where['user_id'] = $_SESSION['ru_id'];
		$count = $this->db_goods->where($where)->count();
		if(empty($count)){
			$this->showmessage(__('没有找到相对应的数据',ecjia::MSGTYPE_JSON | ecjia_admin::MSGSTAT_ERROR));
		}

		//获取商品的信息
		$goods = $this->db_goods->field('goods_sn, goods_name, goods_type, shop_price')->find(array('goods_id' => $goods_id));
		//获得商品已经添加的规格列表
		$attribute = get_goods_specifications_list($goods_id);

		foreach ($attribute as $attribute_value) {
			$_attribute[$attribute_value['attr_id']]['attr_values'][] = $attribute_value['attr_value'];
			$_attribute[$attribute_value['attr_id']]['attr_id'] = $attribute_value['attr_id'];
			$_attribute[$attribute_value['attr_id']]['attr_name'] = $attribute_value['attr_name'];
		}
		$attribute_count = count($_attribute);

        if (empty($attribute_count)) {
            $this->showmessage(__('请先添加库存属性，再到货品管理中设置货品库存！'), ecjia::MSGTYPE_ALERT | ecjia::MSGSTAT_ERROR, array('url' => RC_Uri::url('goods/admin/edit_goods_attr', "goods_id=$goods_id")));
        }

		/* 取商品的货品 */
		$product = product_list($goods_id, '');

		$this->assign('tags' ,              $this->tags);
		$this->assign('goods_name', 		sprintf(RC_Lang::lang('products_title'), $goods['goods_name']));
		$this->assign('goods_sn', 			sprintf(RC_Lang::lang('products_title_2'), $goods['goods_sn']));
		$this->assign('attribute', 			$_attribute);
		$this->assign('product_sn', 		$goods['goods_sn'] . '_');
		$this->assign('product_number', 	ecjia::config('default_storage'));
		$this->assign('ur_here', 			__('货品管理'));
		$this->assign('action_link', 		array('href' => RC_Uri::url('goods/admin/init'), 'text' => RC_Lang::lang('01_goods_list')));
		$this->assign('product_list', 		$product['product']);
		$this->assign('goods_id', 			$goods_id);
		$this->assign('form_action', 		RC_Uri::url('goods/admin/product_add_execute'));

		$this->assign_lang();
		$this->display('product_info.dwt');
	}



	/**
	* 货品添加 执行
	*/
	public function product_add_execute() {

		$this->admin_priv('goods_manage', ecjia::MSGTYPE_JSON);

		$product['goods_id'] = $_POST['goods_id'];
		$product['temp_attr'] = $_POST['attr'];
		$product['product_sn'] = $_POST['product_sn'];
		$product['product_number'] = $_POST['product_number'];

		/* 是否存在商品id */
		if (empty($product['goods_id'])) {
			$this->showmessage(RC_Lang::lang('sys/wrong') . RC_Lang::lang('cannot_found_goods'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}

		/* 取出商品信息 */
		$goods = $this->db_goods->join(null)->field('goods_sn, goods_name, goods_type, shop_price')->find(array('goods_id' => $product['goods_id']));

		if (empty($goods)) {
			$this->showmessage(RC_Lang::lang('sys/wrong') . RC_Lang::lang('cannot_found_goods'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}

		if (count($product['product_sn']) != count(array_unique($product['product_sn']))) {
			//货号出现重复
			$this->showmessage(__('商品货号重复！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		if (!empty($product['product_sn'])) {
			foreach ($product['product_sn'] as $key => $value) {
				//获取规格在商品属性表中的id
				foreach ($product['temp_attr'] as $attr_key => $attr_value) {
					/* 检测：如果当前所添加的货品规格存在空值或0 */
					if (empty($attr_value[$key])) {
						$this->showmessage(__('属性不能为空！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
					}

					$is_spec_list[$attr_key] = 'true';

					$value_price_list[$attr_key] = $attr_value[$key] . chr(9) . ''; //$key，当前

					$id_list[$attr_key] = $attr_key;
				}
				$goods_attr_id = handle_goods_attr($product['goods_id'], $id_list, $is_spec_list, $value_price_list);

				/* 是否为重复规格的货品 */
				$goods_attr = sort_goods_attr_id_array($goods_attr_id);
				$goods_attr = implode('|', $goods_attr['sort']);
				$product['attr'][$key] = $goods_attr;
			}
		}
		//释放临时数组
		unset($product['temp_attr']);


		if (count($product['attr']) != count(array_unique($product['attr']))) {
			//货号出现重复
			$this->showmessage(__('商品货号重复！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}


		/*取得旧数据*/
		$old_product = product_list($product['goods_id'], '');
		if (empty($old_product['product'])) {
			//没有旧货品信息
			if (!empty($product['product_sn'])) {
				foreach ($product['product_sn'] as $key=>$value) {

					//过滤
					$use_storage = ecjia::config('use_storage');
					$product['product_number'][$key] = empty($product['product_number'][$key]) ? (empty($use_storage) ? 0 : ecjia::config('default_storage')) : trim($product['product_number'][$key]); //库存

					//货品号为空 自动补货品号
					//TODO:: value错误
					$value = empty($value) ? "g_p" . RC_Time::gmtime() . rand(1000,9999)  : $value;

					/* 插入货品表 */
					$data = array(
						'goods_id' => $product['goods_id'],
						'goods_attr' => $product['attr'][$key],
						'product_sn' => $value,
						'product_number' => $product['product_number'][$key]
						);
					$insert_id = $this->db_products->insert($data);

				}
			}
		} else {

			$old_product_attr = array();
			// 判定删除情况
			foreach ($old_product['product'] as $value ) {
				$old_product_attr[] = $value['goods_attr_str'];
				if (!in_array($value['goods_attr_str'], $product['attr'])) {
					//执行删除
					$products_info = $this->db_products->field('product_number, product_id')->where(array('goods_attr'=>$value['goods_attr_str'], 'goods_id'=>$product['goods_id']))->find();
					$product_number = $products_info['product_number'];
					$product_id = $products_info['product_id'];
					$this->db_products->where(array('product_id' => $product_id))->delete();

					/* 修改商品库存 */
					//TODO:: 错误处理
//                    if (update_goods_stock($product['goods_id'], $product_number - $product['product_number'])) {
//                        //记录日志
//                        ecjia_admin::admin_log('', 'update', 'goods');
//                    }
					//记录日志
					ecjia_admin::admin_log('', 'trash', 'products');
				}
			}

			// 判定编辑或增加两种情况
			foreach ($product['attr'] as $key=>$value_new_product ) {
				//添加
				$use_storage = ecjia::config('use_storage');
				$product['product_number'][$key] = empty($product['product_number'][$key]) ? (empty($use_storage) ? 0 : ecjia::config('default_storage')) : trim($product['product_number'][$key]); //库存

				//货品号为空 自动补货品号
				$product_sn = empty($product['product_sn'][$key]) ? "g_p" . RC_Time::gmtime() . rand(1000,9999)  : $product['product_sn'][$key];

				/* 插入货品表 */
				$data = array(
					'goods_id' => $product['goods_id'],
					'goods_attr'=>$value_new_product,
					'product_sn' => $product_sn,
					'product_number' => $product['product_number'][$key]
					);
				if (in_array($value_new_product, $old_product_attr)) {
					$this->db_products->where(array('goods_attr'=>$value_new_product, 'goods_id'=>$product['goods_id']))->update($data);
				} else {
					$this->db_products->insert($data);
				}
			}


		}


		/* 修改商品表库存 */
		$product_count = product_number_count($product['goods_id']);
		if (update_goods($product['goods_id'], 'goods_number', $product_count)) {
			//记录日志
			ecjia_admin::admin_log($product['goods_id'], 'update', 'goods');
		}

		$link[] = array('href' => RC_Uri::url('goods/admin/add'), 'text' => RC_Lang::lang('02_goods_add'));
		$link[] = array('href' => RC_Uri::url('goods/admin/init'), 'text' => RC_Lang::lang('01_goods_list'));
		$this->showmessage(RC_Lang::lang('save_products'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('links' => $link));
	}

	// /**
	//  * 货品删除
	//  */
	// public function product_remove()
	// {
	// 	$this->admin_priv('remove_back', ecjia::MSGTYPE_JSON);

	// 	/* 对货品删除进行权限检查  BY：MaLiuWei  START */
	// 	if (!empty($_SESSION['ru_id'])) {
	// 		$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
	// 	}
	// 	/* 对货品删除进行权限检查  BY：MaLiuWei  END */
	// 	$product_number = intval($_POST['val']);

	// 	if (empty($_REQUEST['id'])) {
	// 		$this->showmessage(RC_Lang::lang('product_id_null'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
	// 	} else {
	// 		$product_id = intval($_REQUEST['id']);
	// 	}
	// 	$product = get_product_info($product_id, 'product_number, goods_id');
	// 	$result = $this->db_products->where(array('product_id' => $product_id))->delete();
	// 	if ($result) {
	// 		/* 修改商品库存 */
	// 		if (update_goods_stock($product['goods_id'], $product_number - $product['product_number'])) {
	// 			//记录日志
	// 			ecjia_admin::admin_log('', 'update', 'goods');
	// 		}
	// 		//记录日志
	// 		ecjia_admin::admin_log('', 'trash', 'products');
	// 	}
	// 	$this->showmessage('删除成功！',ecjia::MSGTYPE_JSON|ecjia::MSGSTAT_SUCCESS);
	// }


	/**
	 * 货品排序、分页、查询
	 */
	public function product_query()
	{
		$this->admin_priv('goods_manage', ecjia::MSGTYPE_JSON);
		/* 是否存在商品id */
		if (empty($_REQUEST['goods_id'])) {

			$this->showmessage(RC_Lang::lang('sys/wrong'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		} else {
			$goods_id = intval($_REQUEST['goods_id']);
		}

		/* 取出商品信息 */
		$goods = $this->db_goods->join(null)->field('goods_sn, goods_name, goods_type, shop_price')->find(array('goods_id' => $goods_id));
		if (empty($goods)) {
			$this->showmessage(RC_Lang::lang('sys/wrong'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		$this->assign('sn', sprintf(RC_Lang::lang('good_goods_sn'), $goods['goods_sn']));
		$this->assign('price', sprintf(RC_Lang::lang('good_shop_price'), $goods['shop_price']));
		$this->assign('goods_name', sprintf(RC_Lang::lang('products_title'), $goods['goods_name']));
		$this->assign('goods_sn', sprintf(RC_Lang::lang('products_title_2'), $goods['goods_sn']));

		/* 获取商品规格列表 */
		$attribute = get_goods_specifications_list($goods_id);
		if (empty($attribute)) {
			$this->showmessage(RC_Lang::lang('sys/wrong'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		foreach ($attribute as $attribute_value) {
			//转换成数组
			$_attribute[$attribute_value['attr_id']]['attr_values'][] = $attribute_value['attr_value'];
			$_attribute[$attribute_value['attr_id']]['attr_id'] = $attribute_value['attr_id'];
			$_attribute[$attribute_value['attr_id']]['attr_name'] = $attribute_value['attr_name'];
		}
		$attribute_count = count($_attribute);

		$this->assign('attribute_count', $attribute_count);
		$this->assign('attribute', $_attribute);
		$this->assign('attribute_count_3', ($attribute_count + 3));
		$this->assign('product_sn', $goods['goods_sn'] . '_');
		$this->assign('product_number', ecjia::config('default_storage'));

		/* 取商品的货品 */
		$product = product_list($goods_id, '');

		$this->assign('ur_here', RC_Lang::lang('18_product_list'));
		$this->assign('action_link', array('href' => RC_Uri::url('goods/admin/init'), 'text' => RC_Lang::lang('01_goods_list')));
		$this->assign('product_list', $product['product']);
		$use_storage = ecjia::config('use_storage');
		$this->assign('use_storage', empty($use_storage) ? 0 : 1);
		$this->assign('goods_id', $goods_id);
		$this->assign('filter', $product['filter']);

		/* 排序标记 */
		$this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => $this->fetch('product_info')));
	}

	/**
	 * 修改货品价格
	 */
	public function edit_product_sn()
	{
		$this->admin_priv('goods_manage', ecjia::MSGTYPE_JSON);

		/* 对修改货品价格进行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对修改货品价格进行权限检查  BY：MaLiuWei  END */
		$product_id = intval($_POST['pk']);
		$product_sn = trim($_POST['value']);
		$product_sn = (RC_Lang::lang('n_a') == $product_sn) ? '' : $product_sn;
		if (check_product_sn_exist($product_sn, $product_id)) {
			$this->showmessage(RC_Lang::lang('sys/wrong') . RC_Lang::lang('exist_same_product_sn'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 修改 */
		$data = array(
				'product_sn' => $product_sn,
		);
		$result = $this->db_products->where(array('product_id' => $product_id))->update($data);
		if ($result) {
			$this->showmessage(__('货品价格修改成功！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => $product_sn));
		}
	}




	/**
	 * 修改货品库存
	 */
	public function edit_product_number()
	{
		$this->admin_priv('goods_manage', ecjia::MSGTYPE_JSON);

		/* 对修改货品库存进行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对修改货品库存进行权限检查  BY：MaLiuWei  END */
		$product_id = intval($_POST['pk']);
		$product_number = trim($_POST['value']);
		/* 货品库存 */
		$product = get_product_info($product_id, 'product_number, goods_id');

		/* 修改货品库存 */
		$data = array(
				'product_number' => $product_number
		);
		$result = $this->db_products->where(array('product_id' => $product_id))->update($data);
		if ($result) {
			/* 修改商品库存 */
			if (update_goods_stock($product['goods_id'], $product_number - $product['product_number'])) {
				$this->showmessage(__('货品库存修改成功！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => $product_number));
			}
		}
	}

	/**
	* 货品批量操作
	*/
	public function batch_product()
	{
       $this->admin_priv('goods_manage', ecjia::MSGTYPE_JSON);
		/* 定义返回 */
		$link[] = array('href' => RC_Uri::url('goods/admin/product_list', 'goods_id=' . $_POST['goods_id']), 'text' => RC_Lang::lang('item_list'));
		/* 批量操作 - 批量删除 */
		if ($_POST['type'] == 'drop') {
	       //检查权限
			$this->admin_priv('remove_back');

	       //取得要操作的商品编号
			$product_id = !empty($_POST['checkboxes']) ? join(',', $_POST['checkboxes']) : 0;
	       //取出货品库存总数
			$sum = 0;
			$goods_id = 0;
			$product_array = $this->db_products->field('product_id, goods_id, product_number')->in(array('product_id' => $product_id))->select();

			if (!empty($product_array)) {
				foreach ($product_array as $value) {
					$sum += $value['product_number'];
				}
				$goods_id = $product_array[0]['goods_id'];

				/* 删除货品 */
				$query = $this->db_products->in(array('product_id ' => $product_id))->delete();
				if ($query) {
	               //记录日志
					ecjia_admin::admin_log('', 'delete', 'products');
				}

				/* 修改商品库存 */
				if (update_goods_stock($goods_id, -$sum)) {
	               //记录日志
					ecjia_admin::admin_log('', 'update', 'goods');
				}

				/* 返回 */
				$this->showmessage(RC_Lang::lang('product_batch_del_success'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('links' => $link));
			} else {
				$this->showmessage(RC_Lang::lang('cannot_found_products'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR, array('links' => $link));
			}
		}
		$this->showmessage(RC_Lang::lang('no_operation'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR, array('links' => $link));
	}

	/**
	 * 商品描述编辑页面
	 */
	public function edit_goods_desc()
	{
	    $this->admin_priv('goods_manage');
		$code = empty($_REQUEST['extension_code']) ? '' : trim($_REQUEST['extension_code']);
		$code = $code == 'virtual_card' ? 'virtual_card' : '';
		if ($code == 'virtual_card') {
			$this->admin_priv('virualcard'); // 检查权限
		} else {
			$this->admin_priv('goods_manage'); // 检查权限
		}

		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商品列表') , RC_Uri::url('goods/admin/init')));
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('编辑商品描述')));

		$goods_id = intval($_REQUEST['goods_id']);
		$goods = $this->db_goods->find(array('goods_id' => $goods_id));
		if (empty($goods) === true) {
			$this->showmessage(sprintf(__('找不到ID为%s的商品！'),$goods_id), ecjia::MSGTYPE_HTML | ecjia::MSGSTAT_ERROR);
		}

		//设置选中状态,并分配标签导航
		$this->tags['edit_goods_desc']['active'] = 1;
		$this->assign('tags',				$this->tags);
		$this->assign('action_link',		array('href' => RC_Uri::url('goods/admin/init'), 'text' => RC_Lang::lang('01_goods_list')));
		$this->assign('ur_here',			__('编辑商品描述'));
		$this->assign('goods',				$goods);
		$this->assign('goods_id',			$goods_id);
		$this->assign('form_action',		RC_Uri::url('goods/admin/update_goods_desc','goods_id='.$goods_id));
		$this->assign_lang();
		$this->display('goods_desc.dwt');
	}

	/**
	 * 商品描述更新
	 */
	public function update_goods_desc()
	{
	    $code = empty($_REQUEST['extension_code']) ? '' : trim($_REQUEST['extension_code']);
	    if ($code == 'virtual_card') {
	        $this->admin_priv('virualcard'); // 检查权限
	    } else {
	        $this->admin_priv('goods_manage'); // 检查权限
	    }

		$goods_type = isset($_POST['goods_type']) ? $_POST['goods_type'] : 0;
		$goods_id = intval($_REQUEST['goods_id']);

		$goods = $this->db_goods->find(array('goods_id' => $goods_id));

		if (empty($goods) === true) {
			$this->showmessage(sprintf(__('找不到ID为%s的商品！'),$goods_id), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}

		$data = array(
		  'goods_desc'			=> $_POST['goods_desc'],
		  'last_update'		   => RC_Time::gmtime(),
		  );
		$this->db_goods->join(null)->where(array('goods_id' => $_REQUEST['goods_id']))->update($data);

		/* 记录日志 */
		ecjia_admin::admin_log($goods['goods_name'], 'edit', 'goods');

		/* 提示页面 */
		$link = array();
		if ($code == 'virtual_card') {
		  $link[1] = array('href' => RC_Uri::url('goods/admin_virtual_card/replenish', 'goods_id=' . $goods_id), 'text' => RC_Lang::lang('add_replenish'));
		}
		$link[3] = list_link($code);
		for ($i = 0; $i < count($link); $i++) {
		  $key_array[] = $i;
		}
		krsort($link);
		$link = array_combine($key_array, $link);
		$this->showmessage(RC_Lang::lang('edit_goods_ok'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('links' => $link,'max_id' => $goods_id));

	}

	/**
	* 商品属性
	*/
	public function edit_goods_attr()
	{
	    $this->admin_priv('goods_manage');
		$code = empty($_REQUEST['extension_code']) ? '' : trim($_REQUEST['extension_code']);
		$code = $code == 'virtual_card' ? 'virtual_card' : '';
		if ($code == 'virtual_card') {
			$this->admin_priv('virualcard'); // 检查权限
		} else {
			$this->admin_priv('goods_manage'); // 检查权限
		}

		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商品列表') , RC_Uri::url('goods/admin/init')));
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('编辑商品属性')));

		$goods_id = $_REQUEST['goods_id'];
		$goods = $this->db_goods->find(array('goods_id' => $goods_id));
		if (empty($goods) === true) {
			$goods = array(
			'goods_type' => 0 // 商品类型
			);
		}
		/* 获取所有属性列表 */
		$attr_list = get_attr_list($goods['goods_type'], $goods_id);
		$specifications = get_goods_type_specifications();
		$goods['specifications_id'] = $specifications[$goods['goods_type']];
		$_attribute = get_goods_specifications_list($goods['goods_id']);
		$goods['_attribute'] = empty($_attribute) ? '' : 1;

		ecjia_screen::get_current_screen()->add_help_tab( array(
		'id'		=> 'overview',
		'title'		=> __('概述'),
		'content'	=>
		'<p>' . __('欢迎访问ECJia智能后台商品属性页面，可以在此页面对商品的有关属性进行编辑。') . '</p>'
		) );

		ecjia_screen::get_current_screen()->set_help_sidebar(
		'<p><strong>' . __('更多信息:') . '</strong></p>' .
		'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:商品列表#.E5.95.86.E5.93.81.E5.B1.9E.E6.80.A7" target="_blank">关于商品属性帮助文档</a>') . '</p>'
		);

		//设置选中状态,并分配标签导航
		$this->tags['edit_goods_attr']['active'] = 1;
		$this->assign('tags',				$this->tags);
		$this->assign('action_link',		array('href' => RC_Uri::url('goods/admin/init'), 'text' => RC_Lang::lang('01_goods_list')));
		$this->assign('goods_type_list',	goods_type_list($goods['goods_type']));
		$this->assign('goods_attr_html',	build_attr_html($goods['goods_type'], $goods_id));
		$this->assign('ur_here',			__('编辑商品属性'));
		$this->assign('goods_id',			$goods_id);
		$this->assign('form_action',		RC_Uri::url('goods/admin/update_goods_attr','goods_id='.$goods_id));
		$this->assign_lang();
		$this->display('goods_attr.dwt');
	}

	/**
	* 商品属性页面 - 切换商品类型时，返回所需的属性菜单
	*/
	public function get_attr()
	{
		$this->admin_priv('goods_manage', ecjia::MSGTYPE_JSON);
		$goods_id = empty($_GET['goods_id']) ? 0 : intval($_GET['goods_id']);
		$goods_type = empty($_GET['goods_type']) ? 0 : intval($_GET['goods_type']);

		$content = build_attr_html($goods_type, $goods_id);
		$this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => $content));
	}

	/**
	* 更新商品属性
	*/
	public function update_goods_attr()
	{
	    $this->admin_priv('goods_manage', ecjia::MSGTYPE_JSON);
		$goods_type = isset($_POST['goods_type']) ? $_POST['goods_type'] : 0;
		$goods_id = intval($_GET['goods_id']);

		if ((isset($_POST['attr_id_list']) && isset($_POST['attr_value_list'])) || (empty($_POST['attr_id_list']) && empty($_POST['attr_value_list']))) {
	       // 取得原有的属性值
			$goods_attr_list = array();
// 			$keywords_arr = explode(" ", $_POST['keywords']);
// 			$keywords_arr = array_flip($keywords_arr);
// 			if (isset($keywords_arr[''])) {
// 				unset($keywords_arr['']);
// 			}
			$data = $this->db_attribute->field('attr_id, attr_index')->where(array('cat_id' => $goods_type))->select();
			$attr_list = array();
			if (is_array($data)) {
				foreach ($data as $key => $row) {
					$attr_list[$row['attr_id']] = $row['attr_index'];
				}
			}
			//调用视图查询
			$this->db_goods_attr_view->view = array(
				'attribute' => array(
					'type'  => Component_Model_View::TYPE_LEFT_JOIN,
					'alias' => 'a',
					'field' => 'ga.*,a.attr_type',
					'on'	=> 'a.attr_id = ga.attr_id'
					)
				);
			$query = $this->db_goods_attr_view->where(array('ga.goods_id' => $goods_id))->select();
			if (is_array($query)) {
				foreach ($query as $key => $row) {
					$goods_attr_list[$row['attr_id']][$row['attr_value']] = array('sign' => 'delete', 'goods_attr_id' => $row['goods_attr_id']);
				}
			}
// 			_dump($goods_attr_list,1);
// 			_dump($_POST);
			// 循环现有的，根据原有的做相应处理
			if (isset($_POST['attr_id_list'])) {
				foreach ($_POST['attr_id_list'] AS $key => $attr_id) {
					$attr_value = $_POST['attr_value_list'][$key];
					$attr_price = $_POST['attr_price_list'][$key];
					if (!empty($attr_value)) {
						if (isset($goods_attr_list[$attr_id][$attr_value])) {
							// 如果原来有，标记为更新
							$goods_attr_list[$attr_id][$attr_value]['sign'] = 'update';
							$goods_attr_list[$attr_id][$attr_value]['attr_price'] = $attr_price;
						} else {
							// 如果原来没有，标记为新增
							$goods_attr_list[$attr_id][$attr_value]['sign'] = 'insert';
							$goods_attr_list[$attr_id][$attr_value]['attr_price'] = $attr_price;
						}
// 						$val_arr = explode(' ', $attr_value);
// 						foreach ($val_arr AS $k => $v) {
// 							if (!isset($keywords_arr[$v]) && $attr_list[$attr_id] == "1") {
// 								$keywords_arr[$v] = $v;
// 							}
// 						}
					}
				}
			}
// 			_dump($goods_attr_list);

// 			$keywords = join(' ', array_flip($keywords_arr));
			$data = array(
// 				'keywords'		=> $keywords,
				'goods_type'	=> $goods_type
				);
			$this->db_goods->join(null)->where(array('goods_id' => $goods_id))->update($data);

			$data_insert = array();
			$data_update = array();
			/* 插入、更新、删除数据 */
			$goods_type = isset($_POST['goods_type']) ? $_POST['goods_type'] : 0;
			foreach ($goods_attr_list as $attr_id => $attr_value_list) {
				foreach ($attr_value_list as $attr_value => $info) {
					if ($info['sign'] == 'insert') {
						$data_insert[] = array(
							'attr_id'		=> $attr_id,
							'goods_id'		=> $goods_id,
							'attr_value'	=> $attr_value,
							'attr_price'	=> $info[attr_price]
							);
// 						$this->db_goods_attr->insert($data);
// 						$this->showmessage(__('属性添加成功'),ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
					} elseif ($info['sign'] == 'update') {
						$data = array(
							'attr_price' => $info[attr_price]
							);
						$this->db_goods_attr->where(array('goods_attr_id' => $info['goods_attr_id']))->update($data);
// 						$this->showmessage(__('属性更新成功'),ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
					} else {
						$this->db_goods_attr->join(null)->where(array('goods_attr_id' => $info['goods_attr_id']))->delete();
					}
				}
			}
			$this->db_goods_attr->batch_insert($data_insert);
			$this->showmessage(__('成功编辑属性！'),ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('goods/admin/edit_goods_attr', "goods_id=$goods_id")));

		}
	}


	/**
	 * 关联商品
	 */
	public function edit_link_goods()
	{
		$this->admin_priv('goods_manage');
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商品列表') , RC_Uri::url('goods/admin/init')));
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('编辑关联商品')));

		$goods_id = intval($_GET['goods_id']);
		$linked_goods = get_linked_goods($goods_id);

		ecjia_screen::get_current_screen()->add_help_tab( array(
		'id'		=> 'overview',
		'title'		=> __('概述'),
		'content'	=>
		'<p>' . __('欢迎访问ECJia智能后台关联商品页面，可以在此页面对相对应的关联商品进行编辑。') . '</p>'
		) );

		ecjia_screen::get_current_screen()->set_help_sidebar(
		'<p><strong>' . __('更多信息:') . '</strong></p>' .
		'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:商品列表#.E5.85.B3.E8.81.94.E5.95.86.E5.93.81" target="_blank">关于关联商品帮助文档</a>') . '</p>'
		);

		//设置选中状态,并分配标签导航
		$this->assign('tags',				$this->tags);
		$this->assign('link_goods_list',	$linked_goods);
		$this->assign('cat_list',			cat_list());
		$this->assign('brand_list',			get_brand_list());
		$this->assign('action_link',		array('href' => RC_Uri::url('goods/admin/init'), 'text' => RC_Lang::lang('01_goods_list')));
		$this->assign('ur_here',			__('编辑关联商品'));
		$this->assign_lang();
		$this->display('link_goods.dwt');
	}

	/**
	 * 搜索关联地区
	 */
	function get_areaRegion_info_list()
	{
		$this->admin_priv('goods_manage');
		$result = ecjia_app::validate_application('warehouse');
		if (!is_ecjia_error($result)) {
			$this->assign('has_warehouse', 'has_warehouse');
		}
		$db_region_view = RC_Loader::load_app_model('merchants_region_info_viewmodel','goods');


		$ra_id = $_POST['ra_id'];
		$where = array();
		if($ra_id > 0) {
			$where['mr.ra_id'] = $ra_id;
		}

		$area = $db_region_view->field('rw.region_id as regionId, rw.region_name')->where($where)->select();
		$this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => $area));
	}



	/**
	 * 关联地区
	 */
	public function edit_link_area()
	{
		$this->admin_priv('goods_manage');
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商品列表') , RC_Uri::url('goods/admin/init')));
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('编辑关联商品')));

		$goods_id = intval($_GET['goods_id']);
		$linked_area = get_linked_area($goods_id);

		$get_arearegion_list = $this->get_areaRegion_list();

		//设置选中状态,并分配标签导航
		$this->assign('tags',				$this->tags);
		$this->assign('action_link',		array('href' => RC_Uri::url('goods/admin/init'), 'text' => RC_Lang::lang('01_goods_list')));
		$this->assign('ur_here',			__('编辑关联地区'));
		$this->assign('link_area', $linked_area);
		$this->assign('areaRegion_list', $get_arearegion_list);
		$this->assign_lang();
		$this->display('link_area.dwt');
	}
	/**
	 * 插入关联地区
	 * @return [type] [description]
	 */
	public function insert_link_area()
	{
		$this->admin_priv('goods_manage');

		$region_id = $_POST['linked_array'];
		$goods_id = $_GET['goods_id'];
		$ru_id = $this->db_goods->where(array('goods_id' => $goods_id))->get_field('user_id');
		$this->db_link_area->where(array('goods_id' => $goods_id))->delete();
		foreach ($region_id as $key => $val){
			$data = array(
				'goods_id' =>$goods_id,
				'region_id' => $val['id'],
				'ru_id' => $ru_id,
			);
			$this->db_link_area->insert($data);
		}
		$this->showmessage(__('更新关联地区成功'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);

	}

	/**
	* 关联配件
	*/
	public function edit_link_parts()
	{
		$this->admin_priv('goods_manage');
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商品列表') , RC_Uri::url('goods/admin/init')));
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('编辑关联配件')));

		$goods_id = intval($_GET['goods_id']);
		$group_goods_list = get_group_goods($goods_id);
		$this->db_goods_attr->join(null)->where(array('goods_id' => 0))->delete();

		ecjia_screen::get_current_screen()->add_help_tab( array(
		'id'		=> 'overview',
		'title'		=> __('概述'),
		'content'	=>
		'<p>' . __('欢迎访问ECJia智能后台关联配件页面，可以在此页面对相对应的关联配件进行编辑。') . '</p>'
		) );

		ecjia_screen::get_current_screen()->set_help_sidebar(
		'<p><strong>' . __('更多信息:') . '</strong></p>' .
		'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:商品列表#.E5.85.B3.E8.81.94.E9.85.8D.E4.BB.B6" target="_blank">关于关联配件帮助文档</a>') . '</p>'
		);

		//设置选中状态,并分配标签导航
		$this->assign('tags',				$this->tags);
		$this->assign('cat_list',			cat_list());
		$this->assign('brand_list',			get_brand_list());
		$this->assign('group_goods_list',	$group_goods_list);
		$this->assign('action_link',		array('href' => RC_Uri::url('goods/admin/init'), 'text' => RC_Lang::lang('01_goods_list')));
		$this->assign('goods_id',			$goods_id);
		$this->assign('ur_here',			__('编辑关联配件'));
		$this->assign_lang();
		$this->display('link_parts.dwt');
	}

	/**
	* 关联文章
	*/
	public function edit_link_article()
	{
		$this->admin_priv('goods_manage');
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商品列表') , RC_Uri::url('goods/admin/init')));
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('编辑关联文章')));

		$goods_id = intval($_GET['goods_id']);
		$goods_article_list = get_goods_articles($goods_id);

		ecjia_screen::get_current_screen()->add_help_tab( array(
		'id'		=> 'overview',
		'title'		=> __('概述'),
		'content'	=>
		'<p>' . __('欢迎访问ECJia智能后台关联文章页面，可以在此页面对相对应的关联文章进行编辑。') . '</p>'
		) );

		ecjia_screen::get_current_screen()->set_help_sidebar(
		'<p><strong>' . __('更多信息:') . '</strong></p>' .
		'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:商品列表#.E5.85.B3.E8.81.94.E6.96.87.E7.AB.A0" target="_blank">关于关联文章帮助文档</a>') . '</p>'
		);

		//设置选中状态,并分配标签导航
		$this->assign('tags',				$this->tags);
		$this->assign('goods_article_list',	$goods_article_list);
		$this->assign('ur_here',			__('编辑关联文章'));
		$this->assign('action_link',		array('href' => RC_Uri::url('goods/admin/init'), 'text' => RC_Lang::lang('01_goods_list')));
		$this->assign_lang();
		$this->display('link_article.dwt');
	}


	/**
	 * 搜索商品，仅返回名称及ID
	 */
	public function get_goods_list()
	{
	    $this->admin_priv('goods_manage', ecjia::MSGTYPE_JSON);
		$filter = $_GET['JSON'];
		$arr = get_goods_list($filter);
		$opt = array();
		foreach ($arr AS $key => $val) {
			$opt[] = array(
					'value' => $val['goods_id'],
					'text'  => $val['goods_name'],
					'data'  => $val['shop_price']
			);
		}
		$this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => $opt));
	}

	/**
	* 搜索文章
	*/
	public function get_article_list()
	{
		$keyword = trim($_GET['article_title']);
		$where = "cat_id > 0 ";
		if(!empty($keyword)) {
			$where .= " AND title LIKE '%" . mysql_like_quote($keyword) . "%' ";
		}

		$db_article = RC_Loader::load_app_model('article_model', 'article');
		$data = $db_article->field('article_id,title')->where($where)->order('article_id DESC')->limit(50)->select();
		$arr = array();
		if (!empty($data)) {
			foreach ($data as $key => $row) {
				$arr[] = array('value' => $row['article_id'], 'text' => $row['title']);
			}
		}
		$this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => $arr));
	}


	/**
	 * 添加商品关联
	 */
	public function add_link_goods()
	{
		$this->admin_priv('goods_manage', ecjia::MSGTYPE_JSON);
		$goods_id		= intval($_GET['goods_id']);
		$linked_array 	= $_GET['linked_array'];

// 		$this->drop_link_goods($goods_id);
		$this->db_link_goods->where(array('link_goods_id' => $goods_id))->update(array('is_double' => 0));
		$this->db_link_goods->where(array('goods_id' => $goods_id))->delete();

		$data = array();
		if(!empty($linked_array)){
			foreach ($linked_array AS $val) {
				$is_double = $val['is_double'] ? 1 : 0;
				if (!empty($is_double)) {
					/* 双向关联,先干掉与本商品关联的商品，再添加关联给与本商品关联的商品 */
					$this->db_link_goods->where(array('goods_id' => $val, 'link_goods_id' => $goods_id))->delete();
					$data[] = array(
							'goods_id'		=> $val['id'],
							'link_goods_id'	=> $goods_id,
							'is_double'		=> $is_double,
							'admin_id'		=> $_SESSION['admin_id'],
					);
					// 				$this->db_link_goods->insert($data);
				}
				$data[] = array(
						'goods_id'		=> $goods_id,
						'link_goods_id'	=> $val['id'],
						'is_double'		=> $is_double,
						'admin_id'		=> $_SESSION['admin_id'],
				);
				// 			$this->db_link_goods->insert($data);
			}
		}
		if(!empty($data)) {
			$this->db_link_goods->batch_insert($data);
		}
		$goods_name = $this->db_goods->where(array('goods_id'=>$goods_id))->get_field('goods_name');
		ecjia_admin::admin_log('增加关联商品，被设置的商品名称是'.$goods_name, 'setup', 'goods');
		$this->showmessage('成功修改关联商品！', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('goods/admin/edit_link_goods', "goods_id=$goods_id")));
	}

	/**
	* 增加一个配件
	*/
	public function add_link_parts()
	{
	    $this->admin_priv('goods_manage', ecjia::MSGTYPE_JSON);

		$goods_id = intval($_GET['goods_id']);
		$linked_array = $_GET['linked_array'];
		$this->db_group_goods->where(array('parent_id' => $goods_id))->delete();
		$this->db_term_relationship->where(array('object_type'=>'ecjia.goods', 'object_group'=>'group_goods', 'object_id'=>$goods_id))->delete();

		$data = array();
		foreach ($linked_array AS $key=>$val) {
			$data[] = array(
				'parent_id'		=> $goods_id,
				'goods_id'		=> $val['id'],
				'goods_price'	=> $val['price'],
				'admin_id'		=> $_SESSION['admin_id'],
			);
			$sort_data[] = array(
				'object_type'		=> 'ecjia.goods',
				'object_group'		=> 'group_goods',
				'object_id'			=> $goods_id,
				'item_key1'			=> 'goods_id',
				'item_value1'		=> $val['id'],
				'item_key2'			=> 'goods_sort',
				'item_value2'		=> $key,
			);
		}

		if(!empty($data)) {
			$this->db_group_goods->batch_insert($data);
			$this->db_term_relationship->batch_insert($sort_data);
		}

		$this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('goods/admin/edit_link_parts', "goods_id=$goods_id")));
	}

	/**
	* 添加关联文章
	*/
	public function add_link_article()
	{
		$this->admin_priv('goods_manage', ecjia::MSGTYPE_JSON);
		$goods_id = intval($_GET['goods_id']);
		$linked_array = $_GET['linked_array'];

		$this->db_goods_article->where(array('goods_id' => $goods_id))->delete();

		$data = array();
		foreach ($linked_array AS $val) {
			$data[] = array(
				'goods_id' => $goods_id,
				'article_id' => $val['article_id'],
				'admin_id' => $_SESSION['admin_id'],
				);
// 			$this->db_goods_article->insert($data);
		}
		if(!empty($data)) {
			$this->db_goods_article->batch_insert($data);
		}
		$this->showmessage('成功修改关联文章', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('goods/admin/edit_link_article', "goods_id=$goods_id")));
	}

	/**
	 * 区域地区
	 */
	private function get_areaRegion_list() {
		$db_region_area = RC_Loader::load_app_model('merchants_region_area_model', 'warehouse');
		$res = $db_region_area->field(array('ra_id', 'ra_name'))->order(array('ra_sort' => 'ASC'))->select();

		$arr = array();
		foreach ($res as $key => $row) {
			$arr[$key]['ra_id'] = $row['ra_id'];
			$arr[$key]['ra_name'] = $row['ra_name'];
// 			$arr[$key]['area'] = get_areaRegion_info_list($row['ra_id']);
		}

		return $arr;
	}

}



// end
