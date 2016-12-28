<?php
defined('IN_ECJIA') or exit('No permission resources.');

/**
 * 添加管理员操作对象
 *
 */
function assign_adminlog_content() {
	ecjia_admin_log::instance()->add_object('virtual_card', '虚拟卡');
	ecjia_admin_log::instance()->add_object('encryption', '加密串');
	ecjia_admin_log::instance()->add_object('merchant_brand', '商家品牌');
	ecjia_admin_log::instance()->add_object('goods_booking', '缺货登记');
    ecjia_admin_log::instance()->add_object('brands', '品牌');
    ecjia_admin_log::instance()->add_object('link_goods', '关联商品');
    
    ecjia_admin_log::instance()->add_action('move', '转移');
    ecjia_admin_log::instance()->add_object('category_goods', '分类商品');
}

/*------------------------------------------------------ */
/*-- admin.php控制器调用方法----------------------------------*/
/*------------------------------------------------------ */
/**
* 添加链接
* @param   string $extension_code 虚拟商品扩展代码，实体商品为空
* @return  array('href' => $href, 'text' => $text)
*/
function add_link($extension_code = '') {
	$pathinfo = 'goods/admin/add';
	$args = array();
	if (!empty($extension_code)) {
		$args['extension_code'] = $extension_code;
	}
	if ($extension_code == 'virtual_card') {
		$text = RC_Lang::get('system::system.51_virtual_card_add');
	} else {
		$text = RC_Lang::get('system::system.02_goods_add');
	}
	return array(
		'href' => RC_Uri::url($pathinfo, $args),
		'text' => $text
	);
}

/**
 * 添加商家链接
 * @param   string $extension_code 虚拟商品扩展代码，实体商品为空
 * @return  array('href' => $href, 'text' => $text)
 */
function add_merchant_link($extension_code = '') {
	$pathinfo = 'goods/merchant/add';
	$args = array();
	if (!empty($extension_code)) {
		$args['extension_code'] = $extension_code;
	}
	if ($extension_code == 'virtual_card') {
		$text = RC_Lang::get('system::system.51_virtual_card_add');
	} else {
		$text = RC_Lang::get('system::system.02_goods_add');
	}
	return array(
			'href' => RC_Uri::url($pathinfo, $args),
			'text' => $text
	);
}



/**
* 检查图片网址是否合法
* @param string $url 网址
*
* @return boolean
*/
function goods_parse_url($url) {
	$parse_url = @parse_url($url);
	return (!empty($parse_url['scheme']) && !empty($parse_url['host']));
}

/**
* 保存某商品的优惠价格
* @param   int $goods_id 商品编号
* @param   array $number_list 优惠数量列表
* @param   array $price_list 价格列表
* @return  void
*/
function handle_volume_price($goods_id, $number_list, $price_list) {
// 	$db = RC_Model::model('goods/volume_price_model');
// 	$db->where(array('price_type' => 1, 'goods_id' => $goods_id))->delete();
	
	RC_DB::table('volume_price')->where('price_type', 1)->where('goods_id', $goods_id)->delete();
	/* 循环处理每个优惠价格 */
	foreach ($price_list AS $key => $price) {
		/* 价格对应的数量上下限 */
		$volume_number = $number_list[$key];
		if (!empty($price)) {
			$data = array(
				'price_type'	=> 1,
				'goods_id'		=> $goods_id,
				'volume_number' => $volume_number,
				'volume_price'  => $price,
			);
// 			$db->insert($data);
			RC_DB::table('volume_price')->insert($data);
		}
	}
}

/**
* 修改商品库存
* @param   string $goods_id 商品编号，可以为多个，用 ',' 隔开
* @param   string $value 字段值
* @return  bool
*/
function update_goods_stock($goods_id, $value) {
	$db = RC_Model::model('goods/goods_model');
// 	RC_Loader::load_app_func('common', 'goods');
	
	if ($goods_id) {
// 		$data = array(
// 			'goods_number'  => goods_number + $value,
// 			'last_update'   => RC_Time::gmtime(),
// 		);
// 		$result = $db->where(array('goods_id' => $goods_id))->update($data);
// 		return $result;
		return $db->inc('goods_number', 'goods_id  ='.$goods_id, "'".$value."', last_update=".RC_Time::gmtime());
	} else {
		return false;
	}
}

/**
* 列表链接
* @param   bool $is_add 是否添加（插入）
* @param   string $extension_code 虚拟商品扩展代码，实体商品为空
* @return  array('href' => $href, 'text' => $text)
*/
function list_link($extension_code = '') {
	$pathinfo = 'goods/admin/init';
	$args = array();
	if (!empty($extension_code)) {
		$args['extension_code'] = $extension_code;
	}
	if ($extension_code == 'virtual_card') {
		$text = RC_Lang::get('system::system.50_virtual_card_list');
	} else {
		$text = RC_Lang::get('system::system.01_goods_list');
	}

	return array(
		'href' => RC_Uri::url($pathinfo, $args),
		'text' => $text
	);
}

/**
 * 列表链接
 * @param   bool $is_add 是否添加（插入）
 * @param   string $extension_code 虚拟商品扩展代码，实体商品为空
 * @return  array('href' => $href, 'text' => $text)
 */
function list_merchant_link($extension_code = '') {
	$pathinfo = 'goods/merchant/init';
	$args = array();
	if (!empty($extension_code)) {
		$args['extension_code'] = $extension_code;
	}
	if ($extension_code == 'virtual_card') {
		$text = RC_Lang::get('system::system.50_virtual_card_list');
	} else {
		$text = RC_Lang::get('system::system.01_goods_list');
	}

	return array(
			'href' => RC_Uri::url($pathinfo, $args),
			'text' => $text
	);
}

/*------------------------------------------------------ */
/*-- admin_brand.php品牌管理控制器调用方法 ----------------------*/
/*------------------------------------------------------ */

/**
 * 获取品牌列表
 *
 * @access  public
 * @return  array
 */
function get_merchants_brandlist() {
	$dbview = RC_Model::model('goods/merchants_shop_brand_viewmodel');
	$db = RC_Model::model('goods/brand_model');
	
	$keywords = isset($_GET['keywords']) ? trim($_GET['keywords']) : '';
	$where = array();
	if ($keywords) {
		$where[] = "ssi.shop_name LIKE '%" . mysql_like_quote($keywords) . "%'";
	}
	$count = $dbview->where($where)->count();
	
	$page = new ecjia_page ($count, 10, 5);
	$field = 'mb.*, ssi.shop_name';

	$data = $dbview->field($field)->where($where)->order('sort_order asc')->limit($page->limit())->select();
	
	if (!empty($data)) {
		foreach ($data as $key => $val) {
			$data[$key]['shop_name'] = $val['shop_name'];
			$logo_url = RC_Upload::upload_url($val['brandLogo']);
			if (empty($val['brandLogo'])) {
				$logo_url = RC_Uri::admin_url('statics/images/nopic.png');
				$data[$key]['brandLogo'] = "<img src='" . $logo_url . "' style='width:100px;height:100px;' />";
			} else {
				$logo_url = file_exists(RC_Upload::upload_path($val['brandLogo'])) ? $logo_url : RC_Uri::admin_url('statics/images/nopic.png');
				$data[$key]['brandLogo'] = "<img src='" . $logo_url . "' style='width:100px;height:100px;' />";
			}
		}
	}
	return $data;
}

/*------------------------------------------------------------- */
/*-- admin_category.php品牌分类管理控制器调用方法 ------------------------*/
/*------------------------------------------------------------- */

/**
 * 获得商品分类的所有信息
 *
 * @param   integer     $cat_id     指定的分类ID
 *
 * @return  mix
 */
function get_cat_info($cat_id) {
	return RC_DB::table('category')->where('cat_id', $cat_id)->first();
}

/**
 * 获得商家商品分类的所有信息
 *
 * @param   integer     $cat_id     指定的分类ID
 *
 * @return  mix
 */
function get_merchant_cat_info($cat_id) {
	return RC_DB::table('merchants_category')->where('cat_id', $cat_id)->where('store_id', $_SESSION['store_id'])->first();
}

/**
 * 添加商品分类
 *
 * @param   integer $cat_id
 * @param   array   $args
 *
 * @return  mix
 */
function cat_update($cat_id, $args) {
	if (empty($args) || empty($cat_id)) {
		return false;
	}
	return RC_DB::table('category')->where('cat_id', $cat_id)->update($args);
}

/**
 * 添加商家商品分类
 *
 * @param   integer $cat_id
 * @param   array   $args
 *
 * @return  mix
 */
function merchant_cat_update($cat_id, $args) {
	if (empty($args) || empty($cat_id)) {
		return false;
	}
	return RC_DB::table('merchants_category')->where('cat_id', $cat_id)->where('store_id', $_SESSION['store_id'])->update($args);
}

/**
 * 获取属性列表
 *
 * @access  public
 * @param
 *
 * @return void
 */
function get_category_attr_list() {
// 	$db = RC_Model::model('goods/attribute_goods_viewmodel');
// 	$arr = $db->join('goods_type')->where("gt.enabled = 1")->order(array('a.cat_id' => 'asc','a.sort_order' => 'asc'))->select();
	
	$arr = RC_DB::table('attribute as a')
		->leftJoin('goods_type as gt', RC_DB::raw('a.cat_id'), '=', RC_DB::raw('gt.cat_id'))
		->selectRaw('a.attr_id')
		->where(RC_DB::raw('gt.enabled'), 1)
		->orderBy(RC_DB::raw('a.cat_id'), 'asc')
		->orderBy(RC_DB::raw('a.sort_order'), 'asc')
		->get();
	
	$list = array();
	if (!empty($arr)) {
		foreach ($arr as $val) {
			if (isset($val['cat_id'])) {
				$list[$val['cat_id']][] = array($val['attr_id'] => $val['attr_name']);
			}
		}
	}
	return $list;
}

/**
 * 插入首页推荐扩展分类
 *
 * @access  public
 * @param   array   $recommend_type 推荐类型
 * @param   integer $cat_id     分类ID
 *
 * @return void
 */
function insert_cat_recommend($recommend_type, $cat_id) {
// 	$db = RC_Model::model('goods/cat_recommend_model');
	/* 检查分类是否为首页推荐 */
	if (!empty($recommend_type)) {
		/* 取得之前的分类 */
// 		$recommend_res = $db->field('recommend_type')->where(array('cat_id' => $cat_id))->select();
		$recommend_res = RC_DB::table('cat_recommend')->select('recommend_type')->where('cat_id', $cat_id)->get();
		
		if (empty($recommend_res)) {
			foreach($recommend_type as $data) {
				$data = intval($data);
				$query = array(
					'cat_id' 			=> $cat_id,
					'recommend_type' 	=> $data
				);
// 				$db->insert($query);
				RC_DB::table('cat_recommend')->insert($query);
			}
		} else {
			$old_data = array();
			foreach ($recommend_res as $data) {
				$old_data[] = $data['recommend_type'];
			}
			$delete_array = array_diff($old_data, $recommend_type);
			if (!empty($delete_array)) {
// 				$db->where(array('cat_id' => $cat_id))->in(array('recommend_type' => $delete_array))->delete();
				RC_DB::table('cat_recommend')->where('cat_id', $cat_id)->whereIn('recommend_type', $delete_array)->delete();
			}
			$insert_array = array_diff($recommend_type, $old_data);
			if (!empty($insert_array)) {
				foreach($insert_array as $data) {
					$data = intval($data);
					$query = array(
						'cat_id'          => $cat_id,
						'recommend_type'  => $data
					);
// 					$db->insert($query);
					RC_DB::table('cat_recommend')->insert($query);
				}
			}
		}
	} else {
// 		$db->where(array('cat_id' => $cat_id))->delete();
		RC_DB::table('cat_recommend')->where('cat_id', $cat_id)->delete();
	}
}

/**
 * 取得商品列表：用于把商品添加到组合、关联类、赠品类
 * @param   object  $filters    过滤条件
 */
function get_goods_list($filter) {
	$db = RC_Model::model('goods/goods_auto_viewmodel');
	$filter = (object)$filter;
	$filter->keyword = $filter->keyword;
	//TODO 过滤条件为对象获取方式，后期换回数组
	$where = get_where_sql($filter); // 取得过滤条件
	/* 取得数据 */
	$row = $db->join(null)->field('goods_id, goods_name, shop_price')->where($where)->limit(50)->select();
	return $row;
}

/**
 * 取得商家商品列表：用于把商品添加到组合、关联类、赠品类
 * @param   object  $filters    过滤条件
 */
function get_merchant_goods_list($filter) {
	$db = RC_Model::model('goods/goods_auto_viewmodel');
	$filter = (object)$filter;
	$filter->keyword = $filter->keyword;
	//TODO 过滤条件为对象获取方式，后期换回数组
	$where = get_merchant_where_sql($filter); // 取得过滤条件
	/* 取得数据 */
	$row = $db->join(null)->field('goods_id, goods_name, shop_price')->where($where)->limit(50)->select();
	return $row;
}

/**
 * 获得店铺商品类型的列表
 *
 * @access  public
 * @param   integer     $selected   选定的类型编号
 * @return  string
 */
function goods_type_list($selected, $store_id = 0) {
// 	$db = RC_Model::model('goods/goods_type_model');
// 	$data = $db->field('cat_id, cat_name')->where(array('enabled' => 1))->select();
	$db_goods_type = RC_DB::table('goods_type')->select('cat_id', 'cat_name')->where('enabled', 1);

	if (!empty($store_id)) {
		$db_goods_type->where('store_id', $store_id);
	}
	$data = $db_goods_type->get();
	
	$opt = '';
	if (!empty($data)) {
		foreach ($data as $row){
			$opt .= "<option value='$row[cat_id]'";
			$opt .= ($selected == $row['cat_id']) ? ' selected="true"' : '';
			$opt .= '>' . htmlspecialchars($row['cat_name']). '</option>';
		}
	}
	return $opt;
}

/**
 * 获得是否启用的商品类型列表
 *
 * @access  public
 * @param   integer     $selected   选定的类型编号
 * @return  string
 */
function goods_enable_type_list($selected, $enabled = false) {
	$db_goods_type = RC_DB::table('goods_type');

	if ($enabled) {
		$db_goods_type->where('enabled', 1);
	}
	$data = $db_goods_type->select('cat_id', 'cat_name')->where('store_id', $_SESSION['store_id'])->get();

	$opt = '';
	if (!empty($data)) {
		foreach ($data as $row){
			$opt .= "<option value='$row[cat_id]'";
			$opt .= ($selected == $row['cat_id']) ? ' selected="true"' : '';
			$opt .= '>' . htmlspecialchars($row['cat_name']). '</option>';
		}
	}
	return $opt;
}

/**
 * 获得指定的商品类型下所有的属性分组
 *
 * @param   integer     $cat_id     商品类型ID
 *
 * @return  array
 */
function get_attr_groups($cat_id) {
// 	$db = RC_Model::model('goods/goods_type_model');
// 	$data = $db->where(array('cat_id' => $cat_id))->get_field('attr_group');
	$data = RC_DB::table('goods_type')->where('cat_id', $cat_id)->pluck('attr_group');

	$grp = str_replace("\r", '', $data);
	if ($grp) {
		return explode("\n", $grp);
	} else {
		return array();
	}
}

function brand_exists($brand_name) {
	$db = RC_Model::model('goods/brand_model');
	return ($db->where('brand_name = "'. $brand_name .'" ')->count() > 0 ) ? true : false;
}


/**
 * 生成过滤条件：用于 get_goodslist 和 get_goods_list
 * @param   object  $filter
 * @return  string
 */
function get_where_sql($filter) {
	$time = date('Y-m-d');

	$where  = isset($filter->is_delete) && $filter->is_delete == '1' ?
	' is_delete = 1 ' : ' is_delete = 0 ';
	$where .= (isset($filter->real_goods) && ($filter->real_goods > -1)) ? ' AND is_real = ' . intval($filter->real_goods) : '';
	$where .= isset($filter->cat_id) && $filter->cat_id > 0 ? ' AND ' . get_children($filter->cat_id) : '';
	$where .= isset($filter->brand_id) && $filter->brand_id > 0 ? " AND brand_id = '" . $filter->brand_id . "'" : '';
	$where .= isset($filter->intro_type) && $filter->intro_type != '0' ? ' AND ' . $filter->intro_type . " = '1'" : '';
	$where .= isset($filter->intro_type) && $filter->intro_type == 'is_promote' ?
	" AND promote_start_date <= '$time' AND promote_end_date >= '$time' " : '';
	$where .= isset($filter->keyword) && trim($filter->keyword) != '' ?
	" AND (goods_name LIKE '%" . mysql_like_quote($filter->keyword) . "%' OR goods_sn LIKE '%" . mysql_like_quote($filter->keyword) . "%' OR goods_id LIKE '%" . mysql_like_quote($filter->keyword) . "%') " : '';
	$where .= isset($filter->suppliers_id) && trim($filter->suppliers_id) != '' ?
	" AND (suppliers_id = '" . $filter->suppliers_id . "') " : '';

	$where .= isset($filter->in_ids) ? ' AND goods_id ' . db_create_in($filter->in_ids) : '';
	$where .= isset($filter->exclude) ? ' AND goods_id NOT ' . db_create_in($filter->exclude) : '';
	$where .= isset($filter->stock_warning) ? ' AND goods_number <= warn_number' : '';
	return $where;
}

/**
 * 生成商家过滤条件：用于 get_goodslist 和 get_goods_list
 * @param   object  $filter
 * @return  string
 */
function get_merchant_where_sql($filter) {
	$time = date('Y-m-d');

	$where  = isset($filter->is_delete) && $filter->is_delete == '1' ?
	' is_delete = 1 ' : ' is_delete = 0 ';
	$where .= (isset($filter->real_goods) && ($filter->real_goods > -1)) ? ' AND is_real = ' . intval($filter->real_goods) : '';
	$where .= isset($filter->cat_id) && $filter->cat_id > 0 ? ' AND ' . merchant_get_children($filter->cat_id) : '';
	$where .= isset($filter->brand_id) && $filter->brand_id > 0 ? " AND brand_id = '" . $filter->brand_id . "'" : '';
	$where .= isset($filter->intro_type) && $filter->intro_type != '0' ? ' AND ' . $filter->intro_type . " = '1'" : '';
	$where .= isset($filter->intro_type) && $filter->intro_type == 'is_promote' ?
	" AND promote_start_date <= '$time' AND promote_end_date >= '$time' " : '';
	$where .= isset($filter->keyword) && trim($filter->keyword) != '' ?
	" AND (goods_name LIKE '%" . mysql_like_quote($filter->keyword) . "%' OR goods_sn LIKE '%" . mysql_like_quote($filter->keyword) . "%' OR goods_id LIKE '%" . mysql_like_quote($filter->keyword) . "%') " : '';
	$where .= isset($filter->suppliers_id) && trim($filter->suppliers_id) != '' ?
	" AND (suppliers_id = '" . $filter->suppliers_id . "') " : '';

	$where .= isset($filter->in_ids) ? ' AND goods_id ' . db_create_in($filter->in_ids) : '';
	$where .= isset($filter->exclude) ? ' AND goods_id NOT ' . db_create_in($filter->exclude) : '';
	$where .= isset($filter->stock_warning) ? ' AND goods_number <= warn_number' : '';
	/*商家条件*/
	$where .= isset($filter->store_id) ? ' AND store_id = '.intval($filter->store_id) : '';
	return $where;
}

/**
 * admin_goods_booking.php
 */

/**
 * 获取订购信息
 *
 * @access  public
 *
 * @return array
 */
function get_bookinglist() {
	$args = $_GET;
	/* 查询条件 */
	$filter['keywords']		= empty($args['keywords'])		? '' : trim($args['keywords']);
	$filter['dispose']		= empty($args['dispose'])		? 0 : intval($args['dispose']);
	$filter['sort_by']		= empty($args['sort_by'])		? 'g.sort_order' : trim($args['sort_by']);
	$filter['sort_order']	= empty($args['sort_order'])	? 'DESC' : trim($args['sort_order']);
	
	$where = array();
	(!empty($args['keywords'])) ? $where['g.goods_name'] = array('like' => '%' . mysql_like_quote($filter['keywords']) . '%') : '';
	(!empty($args['dispose'])) ? $where['bg.is_dispose'] = $filter['dispose'] : '';

	$dbview = RC_Model::model('goods/order_booking_goods_viewmodel');
	$count = $dbview->join('goods')->where($where)->count();
	$filter['record_count'] = $count;

	//实例化分页
	$page = new ecjia_page($count, 15, 6);

	/* 获取缺货登记数据 */
	$dbview->view = array(
		'goods' => array(
			'type'	=> Component_Model_View::TYPE_LEFT_JOIN,
			'alias'	=> 'g',
			'on'	=> 'bg.goods_id = g.goods_id',
		),
// 		'merchants_shop_information' => array(
// 			'type'  => Component_Model_View::TYPE_LEFT_JOIN,
// 			'alias'	=> 'ms',
// 			'field'	=> 'bg.rec_id, bg.link_man, g.goods_id, g.goods_name, bg.goods_number, bg.booking_time, bg.is_dispose, g.user_id, ms.shoprz_brandName, ms.shopNameSuffix',
// 			'on'    => 'ms.user_id = g.user_id',
// 		),
		'seller_shopinfo' => array(
				'type'  => Component_Model_View::TYPE_LEFT_JOIN,
				'alias'	=> 'ssi',
				'field'	=> 'bg.rec_id, bg.link_man, g.goods_id, g.goods_name, bg.goods_number, bg.booking_time, bg.is_dispose, g.seller_id, ssi.shop_name',
				'on'    => 'ssi.id = g.seller_id',
		)
	);
	
	$row = $dbview->join('goods,seller_shopinfo')->where($where)->order(array($filter['sort_by'] => $filter['sort_order']))->limit($page->limit())->select();

	if (!empty($row)) {
		foreach ($row AS $key => $val) {
			$row[$key]['booking_time'] = RC_Time::local_date(ecjia::config('time_format'), $val['booking_time']);
			$row[$key]['shop_name'] = $val['seller_id'] == 0 ? '' : $val['shop_name'];
		}
	}
	$filter['keywords'] = stripslashes($filter['keywords']);
	$arr = array('item' => $row, 'filter' => $filter, 'page' => $page->show(15), 'desc' => $page->page_desc());

	return $arr;
}

/**
 * 根据id获得缺货登记的详细信息
 *
 * @param   integer     $id
 *
 * @return  array
 */
function get_booking_info($id) {
	$db = RC_Model::model('goods/goods_booking_viewmodel');
	$res = $db->join(array('goods','users'))->find(array('bg.rec_id' => $id));

	/* 格式化时间 */
	$res['booking_time'] = RC_Time::local_date(ecjia::config('time_format'),$res['booking_time']);
	if (!empty($res['dispose_time'])) {
		$res['dispose_time'] = RC_Time::local_date(ecjia::config('time_format'),$res['dispose_time']);
	}

	return $res;
}

/**
 * 获取指定分类的str 例：分类1,分类2,分类3
 */
function get_cat_str($cat_id = 0) {
	if (empty($cat_id)) {
		return '';
	}
	$cat_info = RC_DB::table('category')->where('cat_id', $cat_id)->select('parent_id', 'cat_name')->first();
	$str = $cat_info['cat_name'];

	if (!empty($cat_info['parent_id'])) {
		$html_tmp = get_cat_str($cat_info['parent_id']);
		if (!empty($html_tmp)) {
			$str .= ','.$html_tmp;
		}
	}
	return $str;
}

/**
 * 获取指定分类的html 例：分类1>>分类2>>分类3
 */
function get_cat_html($str) {
	$cat_list = explode(',', $str);

	$html = '';
	foreach (array_reverse($cat_list) as $k => $v) {
		if ($k == 0) {
			$html .= $v;
		} else {
			$html .= '>>'.$v;
		}
	}
	return $html;
}

/**
 * 获取审核状态
 */
function get_review_status() {
	$review_status = 1;
	if (ecjia::config('review_goods') == 0) {
		$review_status = 5;
	} else {
		if (isset($_SESSION['store_id']) && $_SESSION['store_id'] > 0) {
			$shop_review_goods = RC_DB::table('merchants_config')->where('store_id', $_SESSION['store_id'])->where('code', 'shop_review_goods')->pluck('value');
			if ($shop_review_goods == 0) {
				$review_status = 5;
			}
		} else {
			$review_status = 5;
		}
	}
	return $review_status;
}

//end