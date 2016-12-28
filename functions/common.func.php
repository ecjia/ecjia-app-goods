<?php
defined('IN_ECJIA') or exit('No permission resources.');

/**
 * 公用函数库
 */

/**
 * 创建像这样的查询: "IN('a','b')";
 *
 * @access public
 * @param mix $item_list
 *        	列表数组或字符串
 * @param string $field_name
 *        	字段名称
 *        	
 * @return void
 */
function db_create_in($item_list, $field_name = '') {
	if (empty ( $item_list )) {
		return $field_name . " IN ('') ";
	} else {
		if (! is_array ( $item_list )) {
			$item_list = explode ( ',', $item_list );
		}
		$item_list = array_unique ( $item_list );
		$item_list_tmp = '';
		foreach ( $item_list as $item ) {
			if ($item !== '') {
				$item_list_tmp .= $item_list_tmp ? ",'$item'" : "'$item'";
			}
		}
		if (empty ( $item_list_tmp )) {
			return $field_name . " IN ('') ";
		} else {
			return $field_name . ' IN (' . $item_list_tmp . ') ';
		}
	}
}


/**
 * 取得品牌列表
 *
 * @return array 品牌列表 id => name
 */
function get_brand_list() {
// 	$db = RC_Model::model('goods/brand_model');
// 	$res = $db->field('brand_id, brand_name')->order('sort_order asc')->select();
	$res = RC_DB::table('brand')->select('brand_id', 'brand_name')->orderBy('sort_order', 'asc')->get();
	
	$brand_list = array ();
	if (! empty ( $res )) {
		foreach ( $res as $row ) {
			$brand_list[$row ['brand_id']] = addslashes($row ['brand_name']);
		}
	}
	return $brand_list;
}

 /**
  * 获得某个分类下
  *
  * @access public
  * @param int $cat        	
  * @return array
  */
 function get_brands($cat = 0, $app = 'brand') {
 	$db = RC_Model::model ('goods/brand_viewmodel');
// 	TODO:暂api用，不考虑调用模版配置文件 	
//  	$template = basename (PHP_SELF);
//  	$template = substr ($template, 0, strrpos ( $template, '.' ));

//  	static $static_page_libs = null;
//  	if ($static_page_libs == null) {
//  		$static_page_libs = $page_libs;
//  	}
	
 	$children[] = ($cat > 0) ? get_children ( $cat ) : '';
	$db->view = array (
		'goods' => array(
			'type'  => Component_Model_View::TYPE_LEFT_JOIN,
			'alias' => 'g',
			'field' => "b.brand_id, b.brand_name, b.brand_logo, b.brand_desc, COUNT(*) AS goods_num, IF(b.brand_logo > '', '1', '0') AS tag ",
			'on'   	=> 'g.brand_id = b.brand_id'
		),	
	);
	$where['is_show'] = 1;
	$where['g.is_on_sale'] = 1;
	$where['g.is_alone_sale'] = 1;
	$where['g.is_delete'] = 0;
	array_merge($where,$children);
// 	TODO:暂api用，不考虑调用模版配置文件	
//  	if (isset ( $static_page_libs [$template] ['/library/brands.lbi'] )) {
//  		$num = get_library_number ( "brands" );
//  		$sql .= " LIMIT $num ";
//  	}
 	$row = $db->join('goods')->where($where)->group('b.brand_id')->having('goods_num > 0')->order(array('tag'=>'desc','b.sort_order'=>'asc'))->limit(3)->select();
	
 	if (! empty ( $row )) {
 		foreach ( $row as $key => $val ) {
 			$row [$key] ['url'] = build_uri ( $app, array (
 					'cid' => $cat,
 					'bid' => $val ['brand_id'] 
 			), $val ['brand_name'] );
 			$row [$key] ['brand_desc'] = htmlspecialchars ( $val ['brand_desc'], ENT_QUOTES );
 		}
 	}
 	return $row;
 }

/**
 * 重新获得商品图片与商品相册的地址
 *
 * @param int $goods_id
 *        	商品ID
 * @param string $image
 *        	原商品相册图片地址
 * @param boolean $thumb
 *        	是否为缩略图
 * @param string $call
 *        	调用方法(商品图片还是商品相册)
 * @param boolean $del
 *        	是否删除图片
 *        	
 * @return string $url
 */
function get_image_path($goods_id, $image = '', $thumb = false, $call = 'goods', $del = false)
{
	if (empty($image)) {
		$url = RC_Uri::admin_url('statics/images/nopic.png');
	} else {
		$url = RC_Upload::upload_url() . '/' . $image;
	}
    return $url;   
}
// TODO TODO TODO TODO

/**
 * 取得商品优惠价格列表
 *
 * @param string $goods_id
 *        	商品编号
 * @param string $price_type
 *        	价格类别(0为全店优惠比率，1为商品优惠价格，2为分类优惠比率)
 *        	
 * @return 优惠价格列表
 */
function get_volume_price_list($goods_id, $price_type = '1') {
// 	$db = RC_Model::model('goods/volume_price_model');
// 	$res = $db->field ('`volume_number` , `volume_price`')->where(array('goods_id' => $goods_id, 'price_type' => $price_type))->order ('`volume_number` asc')->select();
	
	$res = RC_DB::table('volume_price')
		->select('volume_number', 'volume_price')
		->where('goods_id', $goods_id)
		->where('price_type', $price_type)
		->orderBy('volume_number', 'asc')
		->get();
	
	$volume_price = array();
	$temp_index = '0';
	if (!empty($res)) {
		foreach ($res as $k => $v) {
			$volume_price[$temp_index] 					= array();
			$volume_price[$temp_index]['number'] 		= $v['volume_number'];
			$volume_price[$temp_index]['price'] 		= $v['volume_price'];
			$volume_price[$temp_index]['format_price'] 	= price_format($v['volume_price']);
			$temp_index ++;
		}
	}
	return $volume_price;
}


/**
 * 将 goods_attr_id 的序列按照 attr_id 重新排序
 *
 * 注意：非规格属性的id会被排除
 *
 * @access public
 * @param array $goods_attr_id_array
 *        	一维数组
 * @param string $sort
 *        	序号：asc|desc，默认为：asc
 *        	
 * @return string
 */
function sort_goods_attr_id_array($goods_attr_id_array, $sort = 'asc') {
//     $dbview = RC_Model::model('goods/sys_attribute_viewmodel');
    
    if (empty($goods_attr_id_array)) {
        return $goods_attr_id_array;
    }
    // 重新排序
//     $row = $dbview->join('goods_attr')->in(array('v.goods_attr_id' => $goods_attr_id_array))->order(array('a.attr_id' => $sort))->select();

    $row = RC_DB::table('attribute as a')
	    ->leftJoin('goods_attr as v', function($join){
	    	$join->on(RC_DB::raw('v.attr_id'), '=', RC_DB::raw('a.attr_id'))->on(RC_DB::raw('a.attr_type'), '=', RC_DB::raw('1'));
	    })
	    ->selectRaw('a.attr_type, v.attr_value, v.goods_attr_id')
	    ->whereIn(RC_DB::raw('v.goods_attr_id'), $goods_attr_id_array)
	    ->orderby(RC_DB::raw('a.attr_id'), $sort)
	    ->get();
	    
    $return_arr = array();
    if (! empty($row)) {
        foreach ($row as $value) {
            $return_arr['sort'][] = $value['goods_attr_id'];
            
            $return_arr['row'][$value['goods_attr_id']] = $value;
        }
    }
    return $return_arr;
}

/**
 * 调用array_combine函数
 *
 * @param array $keys
 * @param array $values
 *
 * @return $combined
 */
if (! function_exists ( 'array_combine' )) {
    function array_combine($keys, $values) {
        if (! is_array ( $keys )) {
            user_error ( 'array_combine() expects parameter 1 to be array, ' . gettype ( $keys ) . ' given', E_USER_WARNING );
            return;
        }

        if (! is_array ( $values )) {
            user_error ( 'array_combine() expects parameter 2 to be array, ' . gettype ( $values ) . ' given', E_USER_WARNING );
            return;
        }

        $key_count = count ( $keys );
        $value_count = count ( $values );
        if ($key_count !== $value_count) {
            user_error ( 'array_combine() Both parameters should have equal number of elements', E_USER_WARNING );
            return false;
        }

        if ($key_count === 0 || $value_count === 0) {
            user_error ( 'array_combine() Both parameters should have number of elements at least 0', E_USER_WARNING );
            return false;
        }

        $keys = array_values ( $keys );
        $values = array_values ( $values );

        $combined = array ();
        for($i = 0; $i < $key_count; $i ++) {
            $combined [$keys [$i]] = $values [$i];
        }

        return $combined;
    }
}

/**
 * 获取指定 id snatch 活动的结果
 *
 * @access public
 * @param int $id
 *        	snatch_id
 *
 * @return array array(user_name, bie_price, bid_time, num)
 *         num通常为1，如果为2表示有2个用户取到最小值，但结果只返回最早出价用户。
 */
function get_snatch_result($id) {
    // 加载数据模型
    $dbview = RC_Model::model ('snatch/sys_snatch_log_viewmodel');
    $db_goods_activity = RC_Model::model ('goods/goods_activity_model');
    $db_order_info = RC_Model::model('orders/order_info_model');

    $rec = $dbview->join('users')->group('lg.bid_price')->order(array('num' => 'asc','lg.bid_price' => 'asc','lg.bid_time' => 'asc'))->find(array('lg.snatch_id' => $id));
    if ($rec) {
        $rec ['bid_time'] = RC_Time::local_date (ecjia::config('time_format'), $rec ['bid_time'] );
        $rec ['formated_bid_price'] = price_format ( $rec ['bid_price'], false );
        /* 活动信息 */
        $row = $db_goods_activity->where(array('act_id' => $id, 'act_type' => GAT_SNATCH))->get_field('ext_info');
        $info = unserialize ( $row ['ext_info'] );

        if (! empty ( $info ['max_price'] )) {
            $rec ['buy_price'] = ($rec ['bid_price'] > $info ['max_price']) ? $info ['max_price'] : $rec ['bid_price'];
        } else {
            $rec ['buy_price'] = $rec ['bid_price'];
        }

        /* 检查订单 */
        $rec ['order_count'] = $db_order_info->in(array('order_status' => array(OS_CONFIRMED,OS_UNCONFIRMED)))->where(array('extension_code' => snatch, 'extension_id' => $id))->count();
    }

    return $rec;
}

/*------------------------------------------------------ */
/*-- TODO 支付方式和会员使用方法----------------------------------*/
/*------------------------------------------------------ */
/**
 * 返回订单中的虚拟商品
 *
 * @access public
 * @param int $order_id
 *        	订单id值
 * @param bool $shipping
 *        	是否已经发货
 *
 * @return array()
 */
function get_virtual_goods($order_id, $shipping = false) {
    $db = RC_Model::model('orders/order_goods_model');
    if ($shipping) {
        $res = $db->field('goods_id, goods_name, send_number|num, extension_code' )->where ( 'order_id = ' . $order_id . ' AND extension_code > " " ' )->select ();
    } else {
        $res = $db->field('goods_id, goods_name, (goods_number - send_number)|num, extension_code' )->where ( 'order_id = ' . $order_id . ' AND is_real = 0 AND (goods_number - send_number) > 0 AND extension_code > " " ' )->select ();
    }

    $virtual_goods = array ();
    if (! empty ( $res )) {
        foreach ( $res as $row ) {
            $virtual_goods [$row ['extension_code']] [] = array (
                'goods_id' => $row ['goods_id'],
                'goods_name' => $row ['goods_name'],
                'num' => $row ['num']
            );
        }
    }
    return $virtual_goods;
}

/**
 *
 * 是否存在规格
 *
 * @access public
 * @param array $goods_attr_id_array
 *        	一维数组
 *
 * @return string
 */
function is_spec($goods_attr_id_array, $sort = 'asc') {
    $dbview = RC_Model::model('goods/sys_attribute_viewmodel');

    if (empty ( $goods_attr_id_array )) {
        return $goods_attr_id_array;
    }

    // 重新排序

    $row = $dbview->join ( 'goods_attr' )->in ( array ('v.goods_attr_id' => $goods_attr_id_array) )->order ( array ('a.attr_id' => $sort) )->select ();

    $return_arr = array ();
    foreach ( $row as $value ) {
        $return_arr ['sort'] [] = $value ['goods_attr_id'];
        $return_arr ['row'] [$value ['goods_attr_id']] = $value;
    }

    if (! empty ( $return_arr )) {
        return true;
    } else {
        return false;
    }
}
/*------------------------------------------------------ */
/*-- TODO API、订单、会员使用的方法-------------------------------*/
/*------------------------------------------------------ */
/**
 * 取得商品最终使用价格
 *
 * @param string $goods_id
 *        	商品编号
 * @param string $goods_num
 *        	购买数量
 * @param boolean $is_spec_price
 *        	是否加入规格价格
 * @param mix $spec
 *        	规格ID的数组或者逗号分隔的字符串
 *
 * @return 商品最终购买价格
 */
function get_final_price($goods_id, $goods_num = '1', $is_spec_price = false, $spec = array(), $warehouse_id = 0, $area_id = 0)
{
    $dbview = RC_Model::model('goods/sys_goods_member_viewmodel');
    RC_Loader::load_app_func('goods', 'goods');
    $final_price = '0'; // 商品最终购买价格
    $volume_price = '0'; // 商品优惠价格
    $promote_price = '0'; // 商品促销价格
    $user_price = '0'; // 商品会员价格

    // 取得商品优惠价格列表
    $price_list = get_volume_price_list ( $goods_id, '1' );

    if (! empty ( $price_list )) {
        foreach ( $price_list as $value ) {
            if ($goods_num >= $value ['number']) {
                $volume_price = $value ['price'];
            }
        }
    }

    // 取得商品促销价格列表
    $goods = $dbview->join ( 'member_price' )->find (array('g.goods_id' => $goods_id, 'g.is_delete' => 0));
    /* 计算商品的促销价格 */
    if ($goods ['promote_price'] > 0) {
        $promote_price = bargain_price ( $goods ['promote_price'], $goods ['promote_start_date'], $goods ['promote_end_date'] );
    } else {
        $promote_price = 0;
    }

    // 取得商品会员价格列表
    $user_price = $goods ['shop_price'];

    // 比较商品的促销价格，会员价格，优惠价格
    if (empty ( $volume_price ) && empty ( $promote_price )) {
        // 如果优惠价格，促销价格都为空则取会员价格
        $final_price = $user_price;
    } elseif (! empty ( $volume_price ) && empty ( $promote_price )) {
        // 如果优惠价格为空时不参加这个比较。
        $final_price = min ( $volume_price, $user_price );
    } elseif (empty ( $volume_price ) && ! empty ( $promote_price )) {
        // 如果促销价格为空时不参加这个比较。
        $final_price = min ( $promote_price, $user_price );
    } elseif (! empty ( $volume_price ) && ! empty ( $promote_price )) {
        // 取促销价格，会员价格，优惠价格最小值
        $final_price = min ( $volume_price, $promote_price, $user_price );
    } else {
        $final_price = $user_price;
    }
    /* 手机专享*/
    $mobilebuy_db = RC_Model::model('goods/goods_activity_model');
    $mobilebuy_ext_info = array('price' => 0);
    $mobilebuy = $mobilebuy_db->find(array(
    	'goods_id'	 => $goods_id,
    	'start_time' => array('elt' => RC_Time::gmtime()),
    	'end_time'	 => array('egt' => RC_Time::gmtime()),
    	'act_type'	 => GAT_MOBILE_BUY,
    ));
    if (!empty($mobilebuy)) {
    	$mobilebuy_ext_info = unserialize($mobilebuy['ext_info']);
    }
    $final_price =  ($final_price > $mobilebuy_ext_info['price'] && !empty($mobilebuy_ext_info['price'])) ? $mobilebuy_ext_info['price'] : $final_price;
    
    // 如果需要加入规格价格
    if ($is_spec_price) {
        if (! empty ( $spec )) {
            $spec_price = spec_price ( $spec );
            $final_price += $spec_price;
        }
    }

    // 返回商品最终购买价格
    return $final_price;
}
/*------------------------------------------------------ */
/*-- TODO 会员和API使用方法----------------------------------*/
/*------------------------------------------------------ */
// /**
//  * 调用UCenter的函数
//  *
//  * @param string $func
//  * @param array $params
//  *
//  * @return mixed
//  */
// function uc_call($func, $params = null)
// {
//     restore_error_handler();
//     if (! function_exists($func)) {
//         include_once (ROOT_PATH . 'uc_client/client.php');
//     }

//     $res = call_user_func_array($func, $params);

//     set_error_handler('exception_handler');

//     return $res;
// }
/*------------------------------------------------------ */
/*-- TODO 订单和API使用方法----------------------------------*/
/*------------------------------------------------------ */
/**
 * 格式化重量：小于1千克用克表示，否则用千克表示
 *
 * @param float $weight
 *        	重量
 * @return string 格式化后的重量
 */
function formated_weight($weight)
{
    $weight = round(floatval($weight), 3);
    if ($weight > 0) {
        if ($weight < 1) {
            /* 小于1千克，用克表示 */
            return intval($weight * 1000) . RC_Lang::lang('gram');
        } else {
            /* 大于1千克，用千克表示 */
            return $weight . RC_Lang::lang('kilogram');
        }
    } else {
        return 0;
    }
}
/**
 * 获得指定礼包的商品
 *
 * @access public
 * @param integer $package_id
 * @return array
 */
function get_package_goods($package_id) {
//     $dbview = RC_Model::model('goods/package_goods_viewmodel');
//     $db_attr = RC_Model::model('goods/goods_attr_model');

    if ($package_id == 0) {
        $where = " AND pg.admin_id = '$_SESSION[admin_id]'";
    }

//     $dbview->view = array (
//         'goods' => array (
//             'type' 	=> Component_Model_View::TYPE_LEFT_JOIN,
//             'alias' => 'g',
//             'field' => "pg.goods_id, g.goods_name, pg.goods_number, p.goods_attr, p.product_number, p.product_id",
//             'on' 	=> 'pg.goods_id = g.goods_id'
//         ),
//         'products' => array (
//             'type' 	=> Component_Model_View::TYPE_LEFT_JOIN,
//             'alias' => 'p',
//             'on' 	=> 'pg.product_id = p.product_id'
//         )
//     );

//     $resource = $dbview->where('pg.package_id = '.$package_id.''.$where)->select();
    
    $resource = RC_DB::table('package_goods as pg')
    	->leftJoin('goods as g', RC_DB::raw('g.goods_id'), '=', RC_DB::raw('pg.goods_id'))
    	->leftJoin('products as p', RC_DB::raw('pg.product_id'), '=', RC_DB::raw('p.product_id'))
    	->selectRaw('pg.goods_id, g.goods_name, pg.goods_number, p.goods_attr, p.product_number, p.product_id')
    	->whereRaw('pg.package_id = '.$package_id.''.$where)
    	->get();
    
    if (!$resource) {
        return array ();
    }

    $row = array ();
    /* 生成结果数组 取存在货品的商品id 组合商品id与货品id */
    $good_product_str = '';

    if (! empty ( $resource )) {
        foreach ( $resource as $_row ) {
            if ($_row ['product_id'] > 0) {
                /* 取存商品id */
                $good_product_str .= ',' . $_row ['goods_id'];

                /* 组合商品id与货品id */
                $_row ['g_p'] = $_row ['goods_id'] . '_' . $_row ['product_id'];
            } else {
                /* 组合商品id与货品id */
                $_row ['g_p'] = $_row ['goods_id'];
            }
            	
            // 生成结果数组
            $row [] = $_row;
        }
    }
    $good_product_str = trim ( $good_product_str, ',' );

    /* 释放空间 */
    unset ( $resource, $_row, $sql );

    /* 取商品属性 */
    if ($good_product_str != '') {

//         $result_goods_attr = $db_attr->field ( 'goods_attr_id, attr_value' )->in ( array ('goods_id' => $good_product_str) )->select ();
        $result_goods_attr = RC_DB::table('goods_attr')->select('goods_attr_id', 'attr_value')->whereIn('goods_id', $good_product_str)->get();

        $_goods_attr = array ();
        foreach ( $result_goods_attr as $value ) {
            $_goods_attr [$value ['goods_attr_id']] = $value ['attr_value'];
        }
    }

    /* 过滤货品 */
    $format [0] = '%s[%s]';
    $format [1] = '%s';
    if (!empty($row)) {
    	foreach ( $row as $key => $value ) {
    		if ($value ['goods_attr'] != '') {
    			$goods_attr_array = explode ( '|', $value ['goods_attr'] );
    			 
    			$goods_attr = array ();
    			foreach ( $goods_attr_array as $_attr ) {
    				$goods_attr [] = $_goods_attr [$_attr];
    			}
    			 
    			$row [$key] ['goods_name'] = sprintf ( $format [0], $value ['goods_name'], implode ( '，', $goods_attr ));
    		} else {
    			$row [$key] ['goods_name'] = sprintf ( $format [1], $value ['goods_name']);
    		}
    	}
    }

    return $row;
}

/**
 * 所有的促销活动信息
 *
 * @access public
 * @return array
 */
function get_promotion_info($goods_id = '') {
    $db_goods_activity = RC_Model::model('goods/goods_activity_model');
    $db_goods = RC_Model::model('goods/goods_model');

    $snatch = array ();
    $group = array ();
    $auction = array ();
    $package = array ();
    $favourable = array ();

    $gmtime = RC_Time::gmtime ();

    $where = "is_finished=0 AND start_time <= " . $gmtime . " AND end_time >= " . $gmtime;
    if (! empty ( $goods_id )) {
        $where .= " AND goods_id = '$goods_id'";
    }

    $res = $db_goods_activity->field('act_id, act_name, act_type, start_time, end_time')->where($where)->select();

    if (!empty($res)) {
        foreach ($res as $data) {
            switch ($data ['act_type']) {
                case GAT_SNATCH : // 夺宝奇兵
                    $snatch [$data ['act_id']] ['act_name'] = $data ['act_name'];
                    $snatch [$data ['act_id']] ['url'] = build_uri ('snatch', array (
                        'sid' => $data ['act_id']
                    ) );
                    $snatch [$data ['act_id']] ['time'] = sprintf(RC_Lang::lang ( 'promotion_time' ), RC_Time::local_date ( 'Y-m-d', $data ['start_time'] ), RC_Time::local_date ( 'Y-m-d', $data ['end_time'] ) );
                    $snatch [$data ['act_id']] ['sort'] = $data ['start_time'];
                    $snatch [$data ['act_id']] ['type'] = 'snatch';
                    break;

                case GAT_GROUP_BUY : // 团购
                    $group [$data ['act_id']] ['act_name'] = $data ['act_name'];
                    $group [$data ['act_id']] ['url'] = build_uri ( 'group_buy', array (
                        'gbid' => $data ['act_id']
                    ) );
                    $group [$data ['act_id']] ['time'] = sprintf ( RC_Lang::lang ( 'promotion_time' ), RC_Time::local_date ( 'Y-m-d', $data ['start_time'] ), RC_Time::local_date ( 'Y-m-d', $data ['end_time'] ) );
                    $group [$data ['act_id']] ['sort'] = $data ['start_time'];
                    $group [$data ['act_id']] ['type'] = 'group_buy';
                    break;

                case GAT_AUCTION : // 拍卖
                    $auction [$data ['act_id']] ['act_name'] = $data ['act_name'];
                    $auction [$data ['act_id']] ['url'] = build_uri ( 'auction', array (
                        'auid' => $data ['act_id']
                    ) );
                    $auction [$data ['act_id']] ['time'] = sprintf ( RC_Lang::lang ( 'promotion_time' ), RC_Time::local_date ( 'Y-m-d', $data ['start_time'] ), RC_Time::local_date ( 'Y-m-d', $data ['end_time'] ) );
                    $auction [$data ['act_id']] ['sort'] = $data ['start_time'];
                    $auction [$data ['act_id']] ['type'] = 'auction';
                    break;

                case GAT_PACKAGE : // 礼包
                    $package [$data ['act_id']] ['act_name'] = $data ['act_name'];
                    $package [$data ['act_id']] ['url'] = 'package.php#' . $data ['act_id'];
                    $package [$data ['act_id']] ['time'] = sprintf ( RC_Lang::lang ( 'promotion_time' ), RC_Time::local_date ( 'Y-m-d', $data ['start_time'] ), RC_Time::local_date ( 'Y-m-d', $data ['end_time'] ) );
                    $package [$data ['act_id']] ['sort'] = $data ['start_time'];
                    $package [$data ['act_id']] ['type'] = 'package';
                    break;
            }
        }
    }
    $user_rank = ',' . $_SESSION ['user_rank'] . ',';
    $favourable = array ();

    $where = " start_time <= '$gmtime' AND end_time >= '$gmtime'";
    if (! empty ( $goods_id )) {

        $where .= " AND CONCAT(',', user_rank, ',') LIKE '%" . $user_rank . "%'";
    }

    if (empty ( $goods_id )) {
        if (!empty($res)) {
            foreach ( $res as $rows ) {
                $favourable [$rows ['act_id']] ['act_name'] = $rows ['act_name'];
                $favourable [$rows ['act_id']] ['url'] = 'activity.php';
                $favourable [$rows ['act_id']] ['time'] = sprintf ( RC_Lang::lang ( 'promotion_time' ), RC_Time::local_date ( 'Y-m-d', $rows ['start_time'] ), RC_Time::local_date ( 'Y-m-d', $rows ['end_time'] ) );
                $favourable [$rows ['act_id']] ['sort'] = $rows ['start_time'];
                $favourable [$rows ['act_id']] ['type'] = 'favourable';
            }
        }
    } else {
        $row = $db_goods->field('cat_id, brand_id')->find(array('goods_id' => $goods_id));
        $category_id = $row ['cat_id'];
        $brand_id = $row ['brand_id'];

        foreach ( $res as $rows ) {
            if ($rows ['act_range'] == FAR_ALL) {
                $favourable [$rows ['act_id']] ['act_name'] = $rows ['act_name'];
                $favourable [$rows ['act_id']] ['url'] = 'activity.php';
                $favourable [$rows ['act_id']] ['time'] = sprintf ( RC_Lang::lang ( 'promotion_time' ), RC_Time::local_date ( 'Y-m-d', $rows ['start_time'] ), RC_Time::local_date ( 'Y-m-d', $rows ['end_time'] ) );
                $favourable [$rows ['act_id']] ['sort'] = $rows ['start_time'];
                $favourable [$rows ['act_id']] ['type'] = 'favourable';
            } elseif ($rows ['act_range'] == FAR_CATEGORY) {
                /* 找出分类id的子分类id */
                $id_list = array ();
                $raw_id_list = explode ( ',', $rows ['act_range_ext'] );
                foreach ( $raw_id_list as $id ) {
                    $id_list = array_merge ( $id_list, array_keys ( cat_list ( $id, 0, false ) ) );
                }
                $ids = join ( ',', array_unique ( $id_list ) );

                if (strpos ( ',' . $ids . ',', ',' . $category_id . ',' ) !== false) {
                    $favourable [$rows ['act_id']] ['act_name'] = $rows ['act_name'];
                    $favourable [$rows ['act_id']] ['url'] = 'activity.php';
                    $favourable [$rows ['act_id']] ['time'] = sprintf ( RC_Lang::lang ( 'promotion_time' ), RC_Time::local_date ( 'Y-m-d', $rows ['start_time'] ), RC_Time::local_date ( 'Y-m-d', $rows ['end_time'] ) );
                    $favourable [$rows ['act_id']] ['sort'] = $rows ['start_time'];
                    $favourable [$rows ['act_id']] ['type'] = 'favourable';
                }
            } elseif ($rows ['act_range'] == FAR_BRAND) {
                if (strpos ( ',' . $rows ['act_range_ext'] . ',', ',' . $brand_id . ',' ) !== false) {
                    $favourable [$rows ['act_id']] ['act_name'] = $rows ['act_name'];
                    $favourable [$rows ['act_id']] ['url'] = 'activity.php';
                    $favourable [$rows ['act_id']] ['time'] = sprintf ( RC_Lang::lang ( 'promotion_time' ), RC_Time::local_date ( 'Y-m-d', $rows ['start_time'] ), RC_Time::local_date ( 'Y-m-d', $rows ['end_time'] ) );
                    $favourable [$rows ['act_id']] ['sort'] = $rows ['start_time'];
                    $favourable [$rows ['act_id']] ['type'] = 'favourable';
                }
            } elseif ($rows ['act_range'] == FAR_GOODS) {
                if (strpos ( ',' . $rows ['act_range_ext'] . ',', ',' . $goods_id . ',' ) !== false) {
                    $favourable [$rows ['act_id']] ['act_name'] = $rows ['act_name'];
                    $favourable [$rows ['act_id']] ['url'] = 'activity.php';
                    $favourable [$rows ['act_id']] ['time'] = sprintf ( RC_Lang::lang ( 'promotion_time' ), RC_Time::local_date ( 'Y-m-d', $rows ['start_time'] ), RC_Time::local_date ( 'Y-m-d', $rows ['end_time'] ) );
                    $favourable [$rows ['act_id']] ['sort'] = $rows ['start_time'];
                    $favourable [$rows ['act_id']] ['type'] = 'favourable';
                }
            }
        }
    }

    $sort_time = array ();
    $arr = array_merge ( $snatch, $group, $auction, $package, $favourable );
    foreach ( $arr as $key => $value ) {
        $sort_time [] = $value ['sort'];
    }
    array_multisort ( $sort_time, SORT_NUMERIC, SORT_DESC, $arr );

    return $arr;
}

/**
 * 页面上调用的js文件
 *
 * @access public
 * @param string $files
 * @return void
 */
function smarty_insert_scripts($args) {
    static $scripts = array ();

    $arr = explode ( ',', str_replace ( ' ', '', $args ['files'] ) );

    $str = '';
    foreach ( $arr as $val ) {
        if (in_array ( $val, $scripts ) == false) {
            $scripts [] = $val;
            if ($val {0} == '.') {
                $str .= '<script type="text/javascript" src="' . $val . '"></script>';
            } else {
                $str .= '<script type="text/javascript" src="js/' . $val . '"></script>';
            }
        }
    }

    return $str;
}
// /**
//  * error_handle回调函数
//  * @return
//  */
// function exception_handler($errno, $errstr, $errfile, $errline)
// {
//     return;
// }
/**
 * 获取指定id package 的信息
 *
 * @access public
 * @param int $id
 *        	package_id
 *
 * @return array array(package_id, package_name, goods_id,start_time, end_time, min_price, integral)
 */
function get_package_info($id) {
    $db = RC_Model::model('goods/goods_activity_model');
    $dbview = RC_Model::model('goods/sys_package_goods_viewmodel');

    $id = is_numeric ( $id ) ? intval ( $id ) : 0;
    $now = RC_Time::gmtime ();

    $package = $db->field ( 'act_id|id,  act_name|package_name, goods_id , goods_name, start_time, end_time, act_desc, ext_info' )->find ( array ('act_id' => $id,'act_type' => GAT_PACKAGE) );
    /* 将时间转成可阅读格式 */
    if ($package ['start_time'] <= $now && $package ['end_time'] >= $now) {
        $package ['is_on_sale'] = "1";
    } else {
        $package ['is_on_sale'] = "0";
    }
    $package ['start_time'] = RC_Time::local_date ( 'Y-m-d H:i', $package ['start_time'] );
    $package ['end_time'] = RC_Time::local_date ( 'Y-m-d H:i', $package ['end_time'] );
    $row = unserialize ( $package ['ext_info'] );
    unset ( $package ['ext_info'] );
    if ($row) {
        foreach ( $row as $key => $val ) {
            $package [$key] = $val;
        }
    }

    $goods_res = $dbview->join ( array ('goods','member_price') )->where ( 'pg.package_id = ' . $id . '' )->order ( array ('pg.package_id' => 'asc','pg.goods_id' => 'asc') )->select ();

    $market_price = 0;
    $real_goods_count = 0;
    $virtual_goods_count = 0;

    foreach ( $goods_res as $key => $val ) {
        $goods_res [$key] ['goods_thumb'] = get_image_path ( $val ['goods_id'], $val ['goods_thumb'], true );
        $goods_res [$key] ['market_price_format'] = price_format ( $val ['market_price'] );
        $goods_res [$key] ['rank_price_format'] = price_format ( $val ['rank_price'] );
        $market_price += $val ['market_price'] * $val ['goods_number'];
        /* 统计实体商品和虚拟商品的个数 */
        if ($val ['is_real']) {
            $real_goods_count ++;
        } else {
            $virtual_goods_count ++;
        }
    }

    if ($real_goods_count > 0) {
        $package ['is_real'] = 1;
    } else {
        $package ['is_real'] = 0;
    }

    $package ['goods_list'] = $goods_res;
    $package ['market_package'] = $market_price;
    $package ['market_package_format'] = price_format ( $market_price );
    $package ['package_price_format'] = price_format ( $package ['package_price'] );

    return $package;
}
/**
 * 取商品的货品列表
 *
 * @param mixed $goods_id
 *        	单个商品id；多个商品id数组；以逗号分隔商品id字符串
 * @param string $conditions
 *        	sql条件
 *
 * @return array
 */
function get_good_products($goods_id, $conditions = '') {
    $db_products = RC_Model::model('goods/products_model');
    $db_goods_attr = RC_Model::model('goods/goods_attr_model');
    if (empty ( $goods_id )) {
        return array ();
    }

    switch (gettype ( $goods_id )) {
        case 'integer' :
            $_goods_id = "goods_id = '" . intval ( $goods_id ) . "'";
            break;
        case 'string' :
        case 'array' :
            $_goods_id = db_create_in ( $goods_id, 'goods_id' );
            break;
    }

    /* 取货品 */
    $result_products = $db_products->where($_goods_id . $conditions )->select();

    /* 取商品属性 */
    $result_goods_attr = $db_goods_attr->field ( 'goods_attr_id, attr_value' )->where ( $_goods_id )->select ();

    $_goods_attr = array ();
    foreach ( $result_goods_attr as $value ) {
        $_goods_attr [$value ['goods_attr_id']] = $value ['attr_value'];
    }

    /* 过滤货品 */
    foreach ( $result_products as $key => $value ) {
        $goods_attr_array = explode ( '|', $value ['goods_attr'] );
        if (is_array ( $goods_attr_array )) {
            $goods_attr = array ();
            foreach ( $goods_attr_array as $_attr ) {
                $goods_attr [] = $_goods_attr [$_attr];
            }
            $goods_attr_str = implode ( '，', $goods_attr );
        }

        $result_products [$key] ['goods_attr_str'] = $goods_attr_str;
    }

    return $result_products;
}
/**
 * 取商品的下拉框Select列表
 *
 * @param int $goods_id
 *        	商品id
 *
 * @return array
 */
function get_good_products_select($goods_id) {
    $return_array = array ();
    $products = get_good_products ( $goods_id );
    if (empty ( $products )) {
        return $return_array;
    }
    foreach ( $products as $value ) {
        $return_array [$value ['product_id']] = $value ['goods_attr_str'];
    }

    return $return_array;
}
/**
 * 取商品的规格列表
 *
 * @param int $goods_id
 *        	商品id
 * @param string $conditions
 *        	sql条件
 *
 * @return array
 */
function get_specifications_list($goods_id, $conditions = '') {
    // 加载数据库
    $dbview = RC_Model::model('goods/sys_goods_attribute_viewmodel');
    $result = $dbview->join ('attribute')->where('ga.goods_id = ' . $goods_id . '' . $conditions )->select();
    $return_array = array ();
    foreach ( $result as $value ) {
        $return_array [$value ['goods_attr_id']] = $value;
    }

    return $return_array;
}


/*返回商品详情页面的导航条数组*/
function get_goods_info_nav($goods_id=0, $extension_code='') {
    return array(
        'edit'                  => array('name' => RC_Lang::get('goods::goods.tab_general'), 'pjax' => 1, 'href' => RC_Uri::url('goods/admin/edit', "goods_id=$goods_id".$extension_code)),
        'edit_goods_desc'       => array('name' => RC_Lang::get('goods::goods.tab_detail'), 'pjax' => 1, 'href' => RC_Uri::url('goods/admin/edit_goods_desc', "goods_id=$goods_id".$extension_code)),
        'edit_goods_attr'       => array('name' => RC_Lang::get('goods::goods.tab_properties'), 'pjax' => 1, 'href' => RC_Uri::url('goods/admin/edit_goods_attr', "goods_id=$goods_id".$extension_code)),
        'edit_goods_photo'      => array('name' => RC_Lang::get('goods::goods.tab_gallery'), 'pjax' => 1, 'href' => RC_Uri::url('goods/admin_gallery/init', "goods_id=$goods_id".$extension_code)),
        'edit_link_goods'       => array('name' => RC_Lang::get('goods::goods.tab_linkgoods'), 'pjax' => 1, 'href' => RC_Uri::url('goods/admin/edit_link_goods', "goods_id=$goods_id".$extension_code)),
//         'edit_link_parts'       => array('name' => RC_Lang::get('goods::goods.tab_groupgoods'), 'pjax' => 1, 'href' => RC_Uri::url('goods/admin/edit_link_parts', "goods_id=$goods_id".$extension_code)),
//         'edit_link_article'     => array('name' => RC_Lang::get('goods::goods.tab_article'), 'pjax' => 1, 'href' => RC_Uri::url('goods/admin/edit_link_article', "goods_id=$goods_id".$extension_code)),
        'product_list'          => array('name' => RC_Lang::get('goods::goods.tab_product'), 'pjax' => 1, 'href' => RC_Uri::url('goods/admin/product_list', "goods_id=$goods_id".$extension_code)),
    );
}

/*返回商家商品详情页面的导航条数组*/
function get_merchant_goods_info_nav($goods_id=0, $extension_code='') {
	return array(
			'edit'                  => array('name' => RC_Lang::get('goods::goods.tab_general'), 'pjax' => 1, 'href' => RC_Uri::url('goods/merchant/edit', "goods_id=$goods_id".$extension_code)),
			'edit_goods_desc'       => array('name' => RC_Lang::get('goods::goods.tab_detail'), 'pjax' => 1, 'href' => RC_Uri::url('goods/merchant/edit_goods_desc', "goods_id=$goods_id".$extension_code)),
			'edit_goods_attr'       => array('name' => RC_Lang::get('goods::goods.tab_properties'), 'pjax' => 1, 'href' => RC_Uri::url('goods/merchant/edit_goods_attr', "goods_id=$goods_id".$extension_code)),
			'edit_goods_photo'      => array('name' => RC_Lang::get('goods::goods.tab_gallery'), 'pjax' => 1, 'href' => RC_Uri::url('goods/mh_gallery/init', "goods_id=$goods_id".$extension_code)),
			'edit_link_goods'       => array('name' => RC_Lang::get('goods::goods.tab_linkgoods'), 'pjax' => 1, 'href' => RC_Uri::url('goods/merchant/edit_link_goods', "goods_id=$goods_id".$extension_code)),
			//         'edit_link_parts'       => array('name' => RC_Lang::get('goods::goods.tab_groupgoods'), 'pjax' => 1, 'href' => RC_Uri::url('goods/merchant/edit_link_parts', "goods_id=$goods_id".$extension_code)),
	//         'edit_link_article'     => array('name' => RC_Lang::get('goods::goods.tab_article'), 'pjax' => 1, 'href' => RC_Uri::url('goods/merchant/edit_link_article', "goods_id=$goods_id".$extension_code)),
			'product_list'          => array('name' => RC_Lang::get('goods::goods.tab_product'), 'pjax' => 1, 'href' => RC_Uri::url('goods/merchant/product_list', "goods_id=$goods_id".$extension_code)),
	);
}

// end