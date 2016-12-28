<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 添加管理员操作对象
 * 
 */
function assign_adminlog_content() {
    ecjia_admin_log::instance()->add_object('virtual_card',		'虚拟卡');
    ecjia_admin_log::instance()->add_object('encryption',		'加密串');
    ecjia_admin_log::instance()->add_object('merchant_brand',	'商家片品牌');
    ecjia_admin_log::instance()->add_object('goods_booking',	'缺货登记');
    ecjia_admin_log::instance()->add_action('batch_setup',	    '批量设置');
    ecjia_admin_log::instance()->add_action('batch_add',	    '批量添加');
}

/*------------------------------------------------------ */
/*-- TODO API和商品使用到的方法---------------------------------*/
/*------------------------------------------------------ */
/**
* 获得分类的信息
*
* @param   integer $cat_id
*
* @return  void
*/
function get_cat_info($cat_id) {
	$db = RC_Model::model('goods/category_model');
	return $db->field('cat_name, keywords, cat_desc, style, grade, filter_attr, parent_id')->find(array('cat_id' => $cat_id));
}

/**
* 获得分类下的商品
*
* @access  public
* @param   string  $children
* @return  array
*/
function category_get_goods($children, $brand, $min, $max, $ext, $size, $page, $sort, $order) {
	/* 获得商品列表 */
	$dbview = RC_Model::model('goods/goods_member_viewmodel');
// 	$display = $GLOBALS['display'];//TODO:列表布局，暂且注释
	$display = '';
	$where = array(
			'g.is_on_sale' => 1,
			'g.is_alone_sale' => 1,
			'g.is_delete' => 0,
			"(".$children ." OR ".get_extension_goods($children).")",
	);
	if(ecjia::config('review_goods') == 1){
		$where['g.review_status'] = array('gt' => 2);
	}
	if ($brand > 0) {
		$where['g.brand_id'] = $brand;
	}
	if ($min > 0) {
		$where[] = "g.shop_price >= $min";
	}
	if ($max > 0) {
		$where[] = "g.shop_price <= $max ";
	}
	if (!empty($ext)) {
		array_push($where, $ext);
	}
	$limit = ($page - 1) * $size;
	/* 获得商品列表 */
	$data = $dbview->join('member_price')->where($where)->order(array($sort => $order))->limit(($page - 1) * $size, $size)->select();

	$arr = array();
	if (! empty($data)) {
		foreach ($data as $row) {
			if ($row['promote_price'] > 0) {
				$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
			} else {
				$promote_price = 0;
			}
			/* 处理商品水印图片 */
			$watermark_img = '';
			if ($promote_price != 0) {
				$watermark_img = "watermark_promote_small";
			} elseif ($row['is_new'] != 0) {
				$watermark_img = "watermark_new_small";
			} elseif ($row['is_best'] != 0) {
				$watermark_img = "watermark_best_small";
			} elseif ($row['is_hot'] != 0) {
				$watermark_img = 'watermark_hot_small';
			}

			if ($watermark_img != '') {
				$arr[$row['goods_id']]['watermark_img'] = $watermark_img;
			}

			$arr[$row['goods_id']]['goods_id'] = $row['goods_id'];
			if ($display == 'grid') {
				$arr[$row['goods_id']]['goods_name'] = ecjia::config('goods_name_length') > 0 ? RC_String::sub_str($row['goods_name'], ecjia::config('goods_name_length')) : $row['goods_name'];
			} else {
				$arr[$row['goods_id']]['goods_name'] = $row['goods_name'];
			}
			$arr[$row['goods_id']]['name'] = $row['goods_name'];
			$arr[$row['goods_id']]['goods_brief'] = $row['goods_brief'];
			$arr[$row['goods_id']]['goods_style_name'] = add_style($row['goods_name'], $row['goods_name_style']);
			$arr[$row['goods_id']]['market_price'] = price_format($row['market_price']);
			$arr[$row['goods_id']]['shop_price'] = price_format($row['shop_price']);
			$arr[$row['goods_id']]['type'] = $row['goods_type'];
			$arr[$row['goods_id']]['promote_price'] = ($promote_price > 0) ? price_format($promote_price) : '';
			$arr[$row['goods_id']]['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
			$arr[$row['goods_id']]['original_img'] = get_image_path($row['goods_id'], $row['original_img'], true);
			$arr[$row['goods_id']]['goods_img'] = get_image_path($row['goods_id'], $row['goods_img']);
			$arr[$row['goods_id']]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
			
			/* 增加返回原始未格式价格  will.chen*/
			$arr[$row['goods_id']]['unformatted_shop_price'] = $row['shop_price'];
			$arr[$row['goods_id']]['unformatted_promote_price'] = $promote_price;
		}
	}
	return $arr;
}
/*------------------------------------------------------ */
/*-- TODO API使用到的方法------------------------------------*/
/*------------------------------------------------------ */
/**
* 获得分类下的商品总数
*
* @access  public
* @param   string     $cat_id
* @return  integer
*/
function get_cagtegory_goods_count($children, $brand = 0, $min = 0, $max = 0, $ext='') {
	$db = RC_Model::model('goods/goods_model');
	$dbview = RC_Model::model('goods/goods_member_viewmodel');
	RC_Loader::load_app_func('goods','goods');
	$where = array(
		'is_on_sale' => 1,
		'is_alone_sale' => 1,
		'is_delete' => 0,
		"(".$children ." OR ".get_extension_goods($children).")",
	);
	if(ecjia::config('review_goods') == 1){
		$where['review_status'] = array('gt' => 2);
	}
	if ($brand > 0) {
		$where['brand_id'] = $brand;
	}
	if ($min > 0) {
		$where[] = "shop_price >= $min";
	}
	if ($max > 0) {
		$where[] = "shop_price <= $max ";
	}
	if (!empty($ext)) {
		array_push($where, $ext);
	}
	/* 返回商品总数 */
	$count = $dbview->join(null)->where($where)->count();
	return  $count;
}

/**
* 取得最近的上级分类的grade值
*
* @access  public
* @param   int     $cat_id    //当前的cat_id
*
* @return int
*/
function get_parent_grade($cat_id) {
	$db = RC_Model::model('category_model','goods');
	static $res = NULL;
	if ($res === NULL) {
		$data = false;
		if ($data === false) {      
			$res =  $db->field('parent_id, cat_id, grade')->select();       
		} else {
			$res = $data;
		}
	}

	if (!$res) {
		return 0;
	}

	$parent_arr = array();
	$grade_arr = array();

	foreach ($res as $val) {
		$parent_arr[$val['cat_id']] = $val['parent_id'];
		$grade_arr[$val['cat_id']] = $val['grade'];
	}

	while ($parent_arr[$cat_id] >0 && $grade_arr[$cat_id] == 0) {
		$cat_id = $parent_arr[$cat_id];
	}

	return $grade_arr[$cat_id];
}

/**
 * 获得指定商品的关联文章
 *
 * @access  public
 * @param   integer     $goods_id
 * @return  void
 */
function get_linked_articles($goods_id) {
    $dbview = RC_Model::model('article/goods_article_viewmodel');
    $data = $dbview->join('article')->where(array('ga.goods_id' => "$goods_id" ,'a.is_open' => '1'))->order(array('a.add_time' =>'DESC'))->select();

    $arr = array();

    foreach ($data as $row) {
        $row['url']         = $row['open_type'] != 1 ?
        build_uri('article', array('aid'=>$row['article_id']), $row['title']) : trim($row['file_url']);
        $row['add_time']    = RC_Time::local_date(ecjia::config('date_format'), $row['add_time']);
        $row['short_title'] = ecjia::config('article_title_length') > 0 ?
        RC_String::sub_str($row['title'], ecjia::config('article_title_length')) : $row['title'];

        $arr[] = $row;
    }

    return $arr;
}

/**
 *
 *
 * @access public
 * @param
 *
 * @return void
 */
function is_not_null($value) {
    if (is_array($value)) {
        return (!empty($value['from'])) || (!empty($value['to']));
    } else {
        return !empty($value);
    }
}
/*------------------------------------------------------ */
/*-- TODO 商品使用到的方法-------------------------------------*/
/*------------------------------------------------------ */
/**
* 获得指定商品的关联商品
*
* @access  public
* @param   integer     $goods_id
* @return  array
*/
function get_linked_goods($goods_id) {
	$db = RC_Model::model('goods/link_goods_viewmodel');
	$data = $db->join(array('goods','member_price'))->where(array('lg.goods_id' => $goods_id, 'g.is_on_sale' => 1, 'g.is_alone_sale' => 1,'g.is_delete' => 0))->limit(ecjia::config('related_goods_number'))->select();
	$arr = array();

	if(!empty($data)) {
		foreach ($data as $row) {
			$arr[$row['goods_id']]['goods_id']     = $row['goods_id'];
			$arr[$row['goods_id']]['goods_name']   = $row['goods_name'];
			$arr[$row['goods_id']]['short_name']   = ecjia::config('goods_name_length') > 0 ? RC_String::sub_str($row['goods_name'], ecjia::config('goods_name_length')) : $row['goods_name'];
			$arr[$row['goods_id']]['goods_thumb']  = get_image_path($row['goods_id'], $row['goods_thumb'], true);
			$arr[$row['goods_id']]['goods_img']    = get_image_path($row['goods_id'], $row['goods_img']);
			$arr[$row['goods_id']]['market_price'] = price_format($row['market_price']);
			$arr[$row['goods_id']]['shop_price']   = price_format($row['shop_price']);
			$arr[$row['goods_id']]['url']          = build_uri('goods', array('gid'=>$row['goods_id']), $row['goods_name']);
			if ($row['promote_price'] > 0) {
				$arr[$row['goods_id']]['promote_price'] = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
				$arr[$row['goods_id']]['formated_promote_price'] = price_format($arr[$row['goods_id']]['promote_price']);
			} else {
				$arr[$row['goods_id']]['promote_price'] = 0;
			}
		}
	}
	return $arr;
}

// /**
// * 获得指定商品的各会员等级对应的价格
// *
// * @access  public
// * @param   integer     $goods_id
// * @return  array
// */
// function get_user_rank_prices($goods_id, $shop_price) {
	

// // 	$sql = "SELECT rank_id, IFNULL(mp.user_price, r.discount * $shop_price / 100) AS price, r.rank_name, r.discount " .
// // 	'FROM ' . $GLOBALS['ecs']->table('user_rank') . ' AS r ' .
// // 	'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . " AS mp ".
// // 	"ON mp.goods_id = '$goods_id' AND mp.user_rank = r.rank_id " .
// // 	"WHERE r.show_price = 1 OR r.rank_id = '$_SESSION[user_rank]'";
// // 	$res = $GLOBALS['db']->query($sql);
// // 		while ($row = $GLOBALS['db']->fetchRow($res))
	
	
	
// 	$dbview = RC_Model::model('user_rank_viewmodel','user');
// 	$dbview->view =array(
// 		'member_price'	=> array(
// 			'type' 	=> Component_Model_View::TYPE_LEFT_JOIN,
// 			'alias' => 'mp',
// 			'field' => "rank_id, IFNULL(mp.user_price, r.discount * $shop_price / 100) AS price, r.rank_name, r.discount",
// 			'on' 	=> "mp.goods_id = '$goods_id' and mp.user_rank = r.rank_id "
// 			),
// 		);

// 	$data = $dbview->where("r.show_price = 1 OR r.rank_id = '$_SESSION[user_rank]'")->select();
// 	$arr = array();

// 	foreach ($data as $row) {

// 		$arr[$row['rank_id']] = array(
// 			'rank_name' => htmlspecialchars($row['rank_name']),
// 			'price'     => price_format($row['price']));
// 	}

// 	return $arr;
// }

/**
* 获得购买过该商品的人还买过的商品
*
* @access  public
* @param   integer     $goods_id
* @return  array
*/
// function get_also_bought($goods_id) {
	
// // 	$sql = 'SELECT COUNT(b.goods_id ) AS num, g.goods_id, g.goods_name, g.goods_thumb, g.goods_img, g.shop_price, g.promote_price, g.promote_start_date, g.promote_end_date ' .
// // 			'FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS a ' .
// // 			'LEFT JOIN ' . $GLOBALS['ecs']->table('order_goods') . ' AS b ON b.order_id = a.order_id ' .
// // 			'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = b.goods_id ' .
// // 			"WHERE a.goods_id = '$goods_id' AND b.goods_id <> '$goods_id' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 " .
// // 			'GROUP BY b.goods_id ' .
// // 			'ORDER BY num DESC ' .
// // 			'LIMIT ' . $GLOBALS['_CFG']['bought_goods'];
// // 	$res = $GLOBALS['db']->query($sql);
// 	// 	while ($row = $GLOBALS['db']->fetchRow($res))
// //  $data = $dbview->join(array('order_goods','goods'))->where("a.goods_id = '$goods_id' AND b.goods_id <> '$goods_id' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0")->group('b.goods_id')->order('num DESC')->limit(ecjia::config('bought_goods'))->select();
	
	
	
// 	$dbview = RC_Model::model('order_goods_viewmodel','goods');
// 	$data = $dbview->join(array('order_goods','goods'))->where(array('a.goods_id' => $goods_id,'b.goods_id' => array('neq' => $goods_id),'g.is_on_sale' => 1,'g.is_alone_sale' => 1,'g.is_delete' => 0))->group('b.goods_id')->order('num DESC')->limit(ecjia::config('bought_goods'))->select();
// 	$key = 0;
// 	$arr = array();

// 	if(!empty($data)) {
// 		foreach ($data as $row) {
// 			$arr[$key]['goods_id']    = $row['goods_id'];
// 			$arr[$key]['goods_name']  = $row['goods_name'];
// 			$arr[$key]['short_name']  = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
// 			RC_String::sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
// 			$arr[$key]['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
// 			$arr[$key]['goods_img']   = get_image_path($row['goods_id'], $row['goods_img']);
// 			$arr[$key]['shop_price']  = price_format($row['shop_price']);
// 			$arr[$key]['url']         = build_uri('goods', array('gid'=>$row['goods_id']), $row['goods_name']);

// 			if ($row['promote_price'] > 0) {
// 				$arr[$key]['promote_price'] = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
// 				$arr[$key]['formated_promote_price'] = price_format($arr[$key]['promote_price']);
// 			} else {
// 				$arr[$key]['promote_price'] = 0;
// 			}

// 			$key++;
// 		}
// 	}
// 	return $arr;
// }

// /**
// * 获得指定商品的销售排名
// *
// * @access  public
// * @param   integer     $goods_id
// * @return  integer
// */
// function get_goods_rank($goods_id)
// {
// 	/* 统计时间段 */
// // 	if ($period == 1) // 一年
// // 	{
// // 		$ext = " AND o.add_time > '" . RC_Time::local_strtotime('-1 years') . "'";
// // 	}
// // 	elseif ($period == 2) // 半年
// // 	{
// // 		$ext = " AND o.add_time > '" . RC_Time::local_strtotime('-6 months') . "'";
// // 	}
// // 	elseif ($period == 3) // 三个月
// // 	{
// // 		$ext = " AND o.add_time > '" . RC_Time::local_strtotime('-3 months') . "'";
// // 	}
// // 	elseif ($period == 4) // 一个月
// // 	{
// // 		$ext = " AND o.add_time > '" . RC_Time::local_strtotime('-1 months') . "'";
// // 	}
// // 	else
// // 	{
// // 		$ext = '';
// // 	}
// 	/* 查询该商品销量 */
// // 	$sql = 'SELECT IFNULL(SUM(g.goods_number), 0) ' .
// // 			'FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o, ' .
// // 			$GLOBALS['ecs']->table('order_goods') . ' AS g ' .
// // 			"WHERE o.order_id = g.order_id " .
// // 			"AND o.order_status = '" . OS_CONFIRMED . "' " .
// // 			"AND o.shipping_status " . db_create_in(array(SS_SHIPPED, SS_RECEIVED)) .
// // 			" AND o.pay_status " . db_create_in(array(PS_PAYED, PS_PAYING)) .
// // 			" AND g.goods_id = '$goods_id'" . $ext;
// // 	$sales_count = $GLOBALS['db']->getOne($sql);
// 	/* 只有在商品销售量大于0时才去计算该商品的排行 */
// // 		$sql = 'SELECT DISTINCT SUM(goods_number) AS num ' .
// // 				'FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o, ' .
// // 				$GLOBALS['ecs']->table('order_goods') . ' AS g ' .
// // 				"WHERE o.order_id = g.order_id " .
// // 				"AND o.order_status = '" . OS_CONFIRMED . "' " .
// // 				"AND o.shipping_status " . db_create_in(array(SS_SHIPPED, SS_RECEIVED)) .
// // 				" AND o.pay_status " . db_create_in(array(PS_PAYED, PS_PAYING)) . $ext .
// // 				" GROUP BY g.goods_id HAVING num > $sales_count";
// // 		$res = $GLOBALS['db']->query($sql);
// // 			$rank = $GLOBALS['db']->num_rows($res) + 1;
	
	
	
// 	$dbview = RC_Model::model('order_info_viewmodel','goods');
// 	/* 统计时间段 */
// 	$period = intval(ecjia::config('top10_time'));
// 	switch ($period) {
// 		case 1: // 一年
// 		$ext = " AND o.add_time > '" . RC_Time::local_strtotime('-1 years') . "'";
// 		break;
// 		case 2: // 半年
// 		$ext = " AND o.add_time > '" . RC_Time::local_strtotime('-6 months') . "'";
// 		break;
// 		case 3: // 三个月
// 		$ext = " AND o.add_time > '" . RC_Time::local_strtotime('-3 months') . "'";
// 		break;
// 		case 4: // 一个月
// 		$ext = " AND o.add_time > '" . RC_Time::local_strtotime('-1 months') . "'";
// 		break;
// 		default:
// 		$ext = '';
// 	}

// 	/* 查询该商品销量 */
// 	$sales_count = $dbview->join('order_goods')->in(array('o.shipping_status' =>array(SS_SHIPPED, SS_RECEIVED)))->find('o.pay_status in(2,1) and o.order_status = '.OS_CONFIRMED.' and g.goods_id = '.$goods_id.$ext.'');

// 	$sales_count = $sales_count[goods_number];

// 	if ($sales_count > 0) {
// 		/* 只有在商品销售量大于0时才去计算该商品的排行 */
// 		$dbview->view =array(
// 			'order_goods'	=> array(
// 				'type' 	=> Component_Model_View::TYPE_LEFT_JOIN,
// 				'alias' => 'g',
// 				'field' => 'DISTINCT SUM(goods_number)|num',
// 				'on' 	=> 'o.order_id = g.order_id '
// 				)
// 			);
// 		$row_num = $dbview->where('o.pay_status in(2,1) and o.order_status = '.OS_CONFIRMED.''.$ext)->in(array('o.shipping_status' =>array(SS_SHIPPED, SS_RECEIVED)))->group('g.goods_id')->having('num > '.$sales_count.'')->select();
// 		$rank = count($row_num) + 1;

// 		if ($rank > 10) {
// 			$rank = 0;
// 		}
// 	} else {
// 		$rank = 0;
// 	}

// 	return $rank;
// }

// /**
// * 获得商品选定的属性的附加总价格
// *
// * @param   integer     $goods_id
// * @param   array       $attr
// *
// * @return  void
// */
// function get_attr_amount($goods_id, $attr)
// {
	
// // 	$sql = "SELECT SUM(attr_price) FROM " . $GLOBALS['ecs']->table('goods_attr') .
// // 	" WHERE goods_id='$goods_id' AND " . db_create_in($attr, 'goods_attr_id');
// // 	return $GLOBALS['db']->getOne($sql);

	
	
// 	$db = RC_Model::model('goods_attr_model');
// 	$query = $db->where(array('goods_id' => $goods_id))->in(array('goods_attr_id' => $attr))->sum('attr_price');
// 	return $query;
// }

// /**
// * 取得跟商品关联的礼包列表
// *
// * @param   string  $goods_id    商品编号
// *
// * @return  礼包列表
// */
// function get_package_goods_list($goods_id)
// {
	
// // 	$sql = "SELECT pg.goods_id, ga.act_id, ga.act_name, ga.act_desc, ga.goods_name, ga.start_time,
// //                    ga.end_time, ga.is_finished, ga.ext_info
// //             FROM " . $GLOBALS['ecs']->table('goods_activity') . " AS ga, " . $GLOBALS['ecs']->table('package_goods') . " AS pg
// //             WHERE pg.package_id = ga.act_id
// //             AND ga.start_time <= '" . $now . "'
// //             AND ga.end_time >= '" . $now . "'
// //             AND pg.goods_id = " . $goods_id . "
// //             GROUP BY ga.act_id
// //             ORDER BY ga.act_id ";
// // 	$res = $GLOBALS['db']->getAll($sql);

// // 		$sql = "SELECT pg.package_id, pg.goods_id, pg.goods_number, pg.admin_id, p.goods_attr, g.goods_sn, g.goods_name, g.market_price, g.goods_thumb, IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS rank_price
// // 		FROM " . $GLOBALS['ecs']->table('package_goods') . " AS pg
// // 				LEFT JOIN ". $GLOBALS['ecs']->table('goods') . " AS g
// // 						ON g.goods_id = pg.goods_id
// //                     LEFT JOIN ". $GLOBALS['ecs']->table('products') . " AS p
// // 								ON p.product_id = pg.product_id
// // 								LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp
// //                         ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]'
// //                         WHERE pg.package_id = " . $value['act_id']. "
// //                         ORDER BY pg.package_id, pg.goods_id";

// //                         $goods_res = $GLOBALS['db']->getAll($sql);
// 	/* 取商品属性 */
// // 	$sql = "SELECT ga.goods_attr_id, ga.attr_value
// // 			FROM " .$GLOBALS['ecs']->table('goods_attr'). " AS ga, " .$GLOBALS['ecs']->table('attribute'). " AS a
// //          WHERE a.attr_id = ga.attr_id
// //          AND a.attr_type = 1
// //          AND " . db_create_in($goods_id_array, 'goods_id');
// //  $result_goods_attr = $GLOBALS['db']->getAll($sql);


	
	
// 	$db_good = RC_Model::model('goods_activity_viewmodel','goods');
// 	$db_package = RC_Model::model('package_goods_viewmodel','goods');
// 	$db_attr = RC_Model::model('goods_attr_viewmodel');
// 	$now = RC_Time::gmtime();
// 	$res = $db_good->join('package_goods')->where(array('ga.start_time' => array('elt' => $now), 'ga.end_time' => array('egt' => $now, 'and pg.goods_id' => $goods_id)))->group('ga.act_id')->order(array('ga.act_id'=>'asc'))->select();

// 	foreach ($res as $tempkey => $value) {
// 		$subtotal = 0;
// 		$row = unserialize($value['ext_info']);
// 		unset($value['ext_info']);
// 		if ($row) {
// 			foreach ($row as $key=>$val) {
// 				$res[$tempkey][$key] = $val;
// 			}
// 		}
// 		$goods_res = $db_package->join(array('goods','products','member_price'))->where(array('pg.package_id' => $value['act_id']))->order(array('pg.package_id '=>'asc','pg.goods_id'=>'asc'));
// 		foreach($goods_res as $key => $val) {
// 			$goods_id_array[] = $val['goods_id'];
// 			$goods_res[$key]['goods_thumb']  = get_image_path($val['goods_id'], $val['goods_thumb'], true);
// 			$goods_res[$key]['market_price'] = price_format($val['market_price']);
// 			$goods_res[$key]['rank_price']   = price_format($val['rank_price']);
// 			$subtotal += $val['rank_price'] * $val['goods_number'];
// 		}

// 		/* 取商品属性 */
// 		$db_attr->view =array(
// 			'attribute' => array(
// 				'type' 		=> Component_Model_View::TYPE_LEFT_JOIN,
// 				'alias' 	=> 'a',
// 				'field' 	=> 'ga.goods_attr_id, ga.attr_value',
// 				'on'		=> 'a.attr_id = ga.attr_id'
// 				)
// 			);
// 		$result_goods_attr = $db_attr->where(array('a.attr_type' => 1))->in(array('goods_id' => $goods_id_array))->select();
// 		$_goods_attr = array();
// 		foreach ($result_goods_attr as $value) {
// 			$_goods_attr[$value['goods_attr_id']] = $value['attr_value'];
// 		}

// 		/* 处理货品 */
// 		$format = '[%s]';
// 		foreach($goods_res as $key => $val)
// 		{
// 			if ($val['goods_attr'] != '') {
// 				$goods_attr_array = explode('|', $val['goods_attr']);

// 				$goods_attr = array();
// 				foreach ($goods_attr_array as $_attr){
// 					$goods_attr[] = $_goods_attr[$_attr];
// 				}

// 				$goods_res[$key]['goods_attr_str'] = sprintf($format, implode('，', $goods_attr));
// 			}
// 		}

// 		$res[$tempkey]['goods_list']    = $goods_res;
// 		$res[$tempkey]['subtotal']      = price_format($subtotal);
// 		$res[$tempkey]['saving']        = price_format(($subtotal - $res[$tempkey]['package_price']));
// 		$res[$tempkey]['package_price'] = price_format($res[$tempkey]['package_price']);
// 	}

// 	return $res;
// }

//search


/**
* 获得可以检索的属性
*
* @access  public
* @params  integer $cat_id
* @return  void
*/
function get_seachable_attributes($cat_id = 0) {
	$db_good = RC_Model::model('goods/goods_type_viewmodel');
	$db_attribute = RC_Model::model('goods/attribute_model');

	$attributes = array(
		'cate' => array(),
		'attr' => array()
	);

	/* 获得可用的商品类型 */
	$db_good->view =array(
		'attribute' => array(
			'type'  => Component_Model_View::TYPE_LEFT_JOIN,
			'alias' => 'a',
			'field' => 'gt.cat_id, cat_name',
			'on'    => 'a.cat_id = gt.cat_id'
		)
	);
	$cat = $db_good->where(array('gt.enabled' => 1, 'a.attr_index' => array('gt' => 0)))->select();

	/* 获取可以检索的属性 */
	if (!empty($cat)) {
		foreach ($cat AS $val) {
			$attributes['cate'][$val['cat_id']] = $val['cat_name'];
		}
		$where = $cat_id > 0 ? ' AND cat_id = ' . $cat_id : " AND cat_id = " . $cat[0]['cat_id'];
		$data = $db_attribute->field('attr_id, attr_name, attr_input_type, attr_type, attr_values, attr_index, sort_order')->where('attr_index > 0'.$where)->order(array('cat_id' =>'asc', 'sort_order'=>'ASC'))->select();
		foreach ($data as $row) {
			if ($row['attr_index'] == 1 && $row['attr_input_type'] == 1) {
				$row['attr_values'] = str_replace("\r", '', $row['attr_values']);
				$options = explode("\n", $row['attr_values']);

				$attr_value = array();
				foreach ($options AS $opt) {
					$attr_value[$opt] = $opt;
				}
				$attributes['attr'][] = array(
					'id'      => $row['attr_id'],
					'attr'    => $row['attr_name'],
					'options' => $attr_value,
					'type'    => 3
				);
			} else {
				$attributes['attr'][] = array(
					'id'   => $row['attr_id'],
					'attr' => $row['attr_name'],
					'type' => $row['attr_index']
				);
			}
		}
	}
	return $attributes;
}



/*------------------------------------------------------ */
//-- PRIVATE FUNCTION品牌表的方法
/*------------------------------------------------------ */

/**
* 获得指定品牌的详细信息
*
* @access  private
* @param   integer $id
* @return  void
*/
function get_brand_info($id) {
	$db = RC_Model::model('goods/brand_model');
	return $db->find(array('brand_id' => $id));
}

/**
* 获得指定品牌下的推荐和促销商品
*
* @access  private
* @param   string  $type
* @param   integer $brand
* @return  array
*/
function brand_recommend_goods($type, $brand, $cat = 0) {
	$db = RC_Model::model('goods/goods_auto_viewmodel');

	static $result = NULL;
	$time = RC_Time::gmtime();

	if ($result === NULL) {
		if ($cat > 0) {
			$cat_where = "AND " . get_children($cat);
		} else {
			$cat_where = '';
		}

		$db->view =array(
			'brand' => array(
				'type' => 	Component_Model_View::TYPE_LEFT_JOIN,
				'alias' => 'b',
				'field' => "g.goods_id, g.goods_name, g.market_price, g.shop_price AS org_price, g.promote_price,IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price,promote_start_date, promote_end_date, g.goods_brief, g.goods_thumb, goods_img,b.brand_name, g.is_best, g.is_new, g.is_hot, g.is_promote",
				'on' 	=> 'b.brand_id = g.brand_id '
			),
			'member_price'	=> array(
				'type'	=>	Component_Model_View::TYPE_LEFT_JOIN,
				'alias' => 'mp',
				'on' 	=> 'mp.goods_id = g.goods_id and mp.user_rank = '.$_SESSION['user_rank'].''
			)	
		);
		$result = $db->where('g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.brand_id = "'.$brand.'" and (g.is_best = 1 OR (g.is_promote = 1 AND promote_start_date <= "'.$time.'" and promote_end_date >= "'.$time.'"))'.$cat_where)->order(array('g.sort_order'=>'asc','g.last_update'=>'desc'))->select();
	}

	/* 取得每一项的数量限制 */
	$num = 0;
	$type2lib = array('best'=>'recommend_best', 'new'=>'recommend_new', 'hot'=>'recommend_hot', 'promote'=>'recommend_promotion');
	$num = get_library_number($type2lib[$type]);

	$idx = 0;
	$goods = array();
	foreach ($result AS $row) {
		if ($idx >= $num) {
			break;
		}

		if (($type == 'best' && $row['is_best'] == 1) || ($type == 'promote' && $row['is_promote'] == 1 && $row['promote_start_date'] <= $time && $row['promote_end_date'] >= $time)) {
			if ($row['promote_price'] > 0) {
				$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
				$goods[$idx]['promote_price'] = $promote_price > 0 ? price_format($promote_price) : '';
			} else {
				$goods[$idx]['promote_price'] = '';
			}

			$goods[$idx]['id']           = $row['goods_id'];
			$goods[$idx]['name']         = $row['goods_name'];
			$goods[$idx]['brief']        = $row['goods_brief'];
			$goods[$idx]['brand_name']   = $row['brand_name'];
			$goods[$idx]['short_style_name']   = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
			RC_String::sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
			$goods[$idx]['market_price'] = price_format($row['market_price']);
			$goods[$idx]['shop_price']   = price_format($row['shop_price']);
			$goods[$idx]['thumb']        = get_image_path($row['goods_id'], $row['goods_thumb'], true);
			$goods[$idx]['goods_img']    = get_image_path($row['goods_id'], $row['goods_img']);
			$goods[$idx]['url']          = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);

			$idx++;
		}
	}

	return $goods;
}

/**
* 获得指定的品牌下的商品总数
*
* @access  private
* @param   integer     $brand_id
* @param   integer     $cate
* @return  integer
*/
function goods_count_by_brand($brand_id, $cate = 0) {
	$db = RC_Model::model('goods/goods_member_viewmodel');
	if ($cate > 0) {
		$query = $db->join(null)->where('brand_id = '.$brand_id.' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND '. get_children($cate).'')->count();
	}
	$query = $db->join(null)->where(array('brand_id' => $brand_id, 'g.is_on_sale' => 1, 'g.is_alone_sale' => 1, 'g.is_delete' => 0))->count();
	return $query;
}

/**
* 获得品牌下的商品
*
* @access  private
* @param   integer  $brand_id
* @return  array
*/
function brand_get_goods($brand_id, $cate, $size, $page, $sort, $order) {
	$dbview = RC_Model::model('goods/goods_member_viewmodel');
	$cate_where = ($cate > 0) ? 'AND ' . get_children($cate) : '';

	/* 获得商品列表 */
	$dbview->view =array(
		'member_price' 	=> array(
			'type' 	=> Component_Model_View::TYPE_LEFT_JOIN,
			'alias' => 'mp',
			'field' => "g.goods_id, g.goods_name, g.market_price, g.shop_price AS org_price,IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, g.promote_price,g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb , g.goods_img",
			'on' 	=> 'mp.goods_id = g.goods_id and mp.user_rank = '.$_SESSION['user_rank'].''
		)
	);
	$data = $dbview->where('g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.brand_id = '.$brand_id.$cate_where.'')->order(array($sort => $order))->limit(($page - 1) * $size,$size)->select();

	$arr = array();
	if(!empty($data)) {
		foreach ($data as $row) {
			if ($row['promote_price'] > 0) {
				$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
			} else {
				$promote_price = 0;
			}

			$arr[$row['goods_id']]['goods_id']      = $row['goods_id'];
			if($GLOBALS['display'] == 'grid') {
				$arr[$row['goods_id']]['goods_name']       = $GLOBALS['_CFG']['goods_name_length'] > 0 ? RC_String::sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
			} else {
				$arr[$row['goods_id']]['goods_name']       = $row['goods_name'];
			}
			$arr[$row['goods_id']]['market_price']  = price_format($row['market_price']);
			$arr[$row['goods_id']]['shop_price']    = price_format($row['shop_price']);
			$arr[$row['goods_id']]['promote_price'] = ($promote_price > 0) ? price_format($promote_price) : '';
			$arr[$row['goods_id']]['goods_brief']   = $row['goods_brief'];
			$arr[$row['goods_id']]['goods_thumb']   = get_image_path($row['goods_id'], $row['goods_thumb'], true);
			$arr[$row['goods_id']]['goods_img']     = get_image_path($row['goods_id'], $row['goods_img']);
			$arr[$row['goods_id']]['url']           = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
		}
	}
	return $arr;
}

/**
* 获得与指定品牌相关的分类
*
* @access  public
* @param   integer $brand
* @return  array
*/
function brand_related_cat($brand) {
	$db = RC_Model::model('goods/category_viewmodel');
	$arr[] = array(
		'cat_id' 	=> 0,
		'cat_name'	=> RC_Lang::lang('all_category'),
		'url'		=> build_uri('brand', array('bid' => $brand), RC_Lang::lang('all_category'))
	);
	$data = $db->join('goods')->where(array('g.brand_id' => $brand))->group('g.cat_id')->select();
	if(!empty($data)) {
		foreach ($data as $row) {
			$row['url'] = build_uri('brand', array('cid' => $row['cat_id'], 'bid' => $brand), $row['cat_name']);
			$arr[] = $row;
		}
	}
	return $arr;
}
/*------------------------------------------------------ */
//-- END PRIVATE FUNCTION品牌表的方法结束
/*------------------------------------------------------ */

/*------------------------------------------------------ */
//-- 所有分类及品牌的方法
/*------------------------------------------------------ */
/**
* 计算指定分类的商品数量
*
* @access public
* @param   integer     $cat_id
*
* @return void
*/
function calculate_goods_num($cat_list, $cat_id) {
	$goods_num = 0;

	foreach ($cat_list AS $cat) {
		if ($cat['parent_id'] == $cat_id && !empty($cat['goods_num'])) {
			$goods_num += $cat['goods_num'];
		}
	}
	return $goods_num;
}

/**
 * 获得商家指定品牌下的推荐和促销商品
 *
 * @access  private
 * @param   string  $type
 * @param   integer $brand
 * @return  array
 */
function merchant_brand_recommend_goods($type, $brand, $cat = 0) {
	$db = RC_Model::model('goods/goods_auto_viewmodel');

	static $result = NULL;
	$time = RC_Time::gmtime();

	if ($result === NULL) {
		if ($cat > 0) {
			$cat_where = "AND " . merchant_get_children($cat);
		} else {
			$cat_where = '';
		}

		$db->view =array(
				'brand' => array(
						'type' => 	Component_Model_View::TYPE_LEFT_JOIN,
						'alias' => 'b',
						'field' => "g.goods_id, g.goods_name, g.market_price, g.shop_price AS org_price, g.promote_price,IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price,promote_start_date, promote_end_date, g.goods_brief, g.goods_thumb, goods_img,b.brand_name, g.is_best, g.is_new, g.is_hot, g.is_promote",
						'on' 	=> 'b.brand_id = g.brand_id '
				),
				'member_price'	=> array(
						'type'	=>	Component_Model_View::TYPE_LEFT_JOIN,
						'alias' => 'mp',
						'on' 	=> 'mp.goods_id = g.goods_id and mp.user_rank = '.$_SESSION['user_rank'].''
				)
		);
		$result = $db->where('g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.brand_id = "'.$brand.'" and (g.is_best = 1 OR (g.is_promote = 1 AND promote_start_date <= "'.$time.'" and promote_end_date >= "'.$time.'"))'.$cat_where)->order(array('g.sort_order'=>'asc','g.last_update'=>'desc'))->select();
	}

	/* 取得每一项的数量限制 */
	$num = 0;
	$type2lib = array('best'=>'recommend_best', 'new'=>'recommend_new', 'hot'=>'recommend_hot', 'promote'=>'recommend_promotion');
	$num = get_library_number($type2lib[$type]);

	$idx = 0;
	$goods = array();
	foreach ($result AS $row) {
		if ($idx >= $num) {
			break;
		}

		if (($type == 'best' && $row['is_best'] == 1) || ($type == 'promote' && $row['is_promote'] == 1 && $row['promote_start_date'] <= $time && $row['promote_end_date'] >= $time)) {
			if ($row['promote_price'] > 0) {
				$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
				$goods[$idx]['promote_price'] = $promote_price > 0 ? price_format($promote_price) : '';
			} else {
				$goods[$idx]['promote_price'] = '';
			}

			$goods[$idx]['id']           = $row['goods_id'];
			$goods[$idx]['name']         = $row['goods_name'];
			$goods[$idx]['brief']        = $row['goods_brief'];
			$goods[$idx]['brand_name']   = $row['brand_name'];
			$goods[$idx]['short_style_name']   = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
			RC_String::sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
			$goods[$idx]['market_price'] = price_format($row['market_price']);
			$goods[$idx]['shop_price']   = price_format($row['shop_price']);
			$goods[$idx]['thumb']        = get_image_path($row['goods_id'], $row['goods_thumb'], true);
			$goods[$idx]['goods_img']    = get_image_path($row['goods_id'], $row['goods_img']);
			$goods[$idx]['url']          = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);

			$idx++;
		}
	}

	return $goods;
}

/**
 * 获得商家指定的品牌下的商品总数
 *
 * @access  private
 * @param   integer     $brand_id
 * @param   integer     $cate
 * @return  integer
 */
function merchant_goods_count_by_brand($brand_id, $cate = 0) {
	$db = RC_Model::model('goods/goods_member_viewmodel');
	if ($cate > 0) {
		$query = $db->join(null)->where('brand_id = '.$brand_id.' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND '. merchant_get_children($cate).'')->count();
	}
	$query = $db->join(null)->where(array('brand_id' => $brand_id, 'g.is_on_sale' => 1, 'g.is_alone_sale' => 1, 'g.is_delete' => 0))->count();
	return $query;
}

/**
 * 获得商家品牌下的商品
 *
 * @access  private
 * @param   integer  $brand_id
 * @return  array
 */
function merchant_brand_get_goods($brand_id, $cate, $size, $page, $sort, $order) {
	$dbview = RC_Model::model('goods/goods_member_viewmodel');
	$cate_where = ($cate > 0) ? 'AND ' . merchant_get_children($cate) : '';

	/* 获得商品列表 */
	$dbview->view =array(
			'member_price' 	=> array(
					'type' 	=> Component_Model_View::TYPE_LEFT_JOIN,
					'alias' => 'mp',
					'field' => "g.goods_id, g.goods_name, g.market_price, g.shop_price AS org_price,IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, g.promote_price,g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb , g.goods_img",
					'on' 	=> 'mp.goods_id = g.goods_id and mp.user_rank = '.$_SESSION['user_rank'].''
			)
	);
	$data = $dbview->where('g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.brand_id = '.$brand_id.$cate_where.'')->order(array($sort => $order))->limit(($page - 1) * $size,$size)->select();

	$arr = array();
	if(!empty($data)) {
		foreach ($data as $row) {
			if ($row['promote_price'] > 0) {
				$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
			} else {
				$promote_price = 0;
			}

			$arr[$row['goods_id']]['goods_id']      = $row['goods_id'];
			if($GLOBALS['display'] == 'grid') {
				$arr[$row['goods_id']]['goods_name']       = $GLOBALS['_CFG']['goods_name_length'] > 0 ? RC_String::sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
			} else {
				$arr[$row['goods_id']]['goods_name']       = $row['goods_name'];
			}
			$arr[$row['goods_id']]['market_price']  = price_format($row['market_price']);
			$arr[$row['goods_id']]['shop_price']    = price_format($row['shop_price']);
			$arr[$row['goods_id']]['promote_price'] = ($promote_price > 0) ? price_format($promote_price) : '';
			$arr[$row['goods_id']]['goods_brief']   = $row['goods_brief'];
			$arr[$row['goods_id']]['goods_thumb']   = get_image_path($row['goods_id'], $row['goods_thumb'], true);
			$arr[$row['goods_id']]['goods_img']     = get_image_path($row['goods_id'], $row['goods_img']);
			$arr[$row['goods_id']]['url']           = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
		}
	}
	return $arr;
}

/*------------------------------------------------------ */
//-- 所有分类及品牌的方法结束
/*------------------------------------------------------ */

// end