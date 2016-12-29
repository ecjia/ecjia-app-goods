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
    if (empty($goods_attr_id_array)) {
        return $goods_attr_id_array;
    }
    // 重新排序
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
function get_final_price($goods_id, $goods_num = '1', $is_spec_price = false, $spec = array(), $warehouse_id = 0, $area_id = 0) {
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
function formated_weight($weight) {
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
    if ($package_id == 0) {
        $where = " AND pg.admin_id = '$_SESSION[admin_id]'";
    }
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

// /**
//  * error_handle回调函数
//  * @return
//  */
// function exception_handler($errno, $errstr, $errfile, $errline)
// {
//     return;
// }

// end