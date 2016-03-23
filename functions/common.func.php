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
	$db = RC_Loader::load_app_model ('brand_model', 'goods');

	$res = $db->field('brand_id, brand_name')->order('sort_order asc')->select();
	$brand_list = array ();
	if (! empty ( $res )) {
		foreach ( $res as $row ) {
			$brand_list[$row ['brand_id']]= addslashes($row ['brand_name']);
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
 	$db = RC_Loader::load_app_model ('brand_viewmodel', 'goods');
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
 	
 	// 	global $page_libs;
 	//  	$sql = "SELECT b.brand_id, b.brand_name, b.brand_logo, b.brand_desc, COUNT(*) AS goods_num, IF(b.brand_logo > '', '1', '0') AS tag " . "FROM ecs_brand AS b, " . "ecs_goods AS g " . "WHERE g.brand_id = b.brand_id $children AND is_show = 1 " . " AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 " . "GROUP BY b.brand_id HAVING goods_num > 0 ORDER BY tag DESC, b.sort_order ASC";
 	// 	include_once (ROOT_PATH . ADMIN_PATH . '/includes/lib_template.php'); 	
 }






/**
 * 记录帐户变动
 *
 * @param int $user_id
 *        	用户id
 * @param float $user_money
 *        	可用余额变动
 * @param float $frozen_money
 *        	冻结余额变动
 * @param int $rank_points
 *        	等级积分变动
 * @param int $pay_points
 *        	消费积分变动
 * @param string $change_desc
 *        	变动说明
 * @param int $change_type
 *        	变动类型：参见常量文件
 * @return void
 */
function log_account_change($user_id, $user_money = 0, $frozen_money = 0, $rank_points = 0, $pay_points = 0, $change_desc = '', $change_type = ACT_OTHER) {
	// 链接数据库
	$db_account_log = RC_Loader::load_app_model ( "account_log_model", "user" );
	$db_users = RC_Loader::load_app_model ( "users_model", "user" );
	/* 插入帐户变动记录 */
	$account_log = array (
			'user_id'		=> $user_id,
			'user_money'	=> $user_money,
			'frozen_money'	=> $frozen_money,
			'rank_points'	=> $rank_points,
			'pay_points'	=> $pay_points,
			'change_time'	=> RC_Time::gmtime(),
			'change_desc'	=> $change_desc,
			'change_type'	=> $change_type 
	);
	$db_account_log->insert ( $account_log );
	
	/* 更新用户信息 */
// 	TODO: 暂时先恢复之前的写法

// 	$sql = "UPDATE  ecs_users  SET user_money = user_money + ('$user_money')," .
// 	" frozen_money = frozen_money + ('$frozen_money')," .
// 	" rank_points = rank_points + ('$rank_points')," .
// 	" pay_points = pay_points + ('$pay_points')" .
// 	" WHERE user_id = '$user_id' LIMIT 1";
// 	$db_users->query($sql);
	$step = $user_money.", frozen_money = frozen_money + ('$frozen_money')," .
	" rank_points = rank_points + ('$rank_points')," .
	" pay_points = pay_points + ('$pay_points')";
	
	$db_users->inc('user_money' , 'user_id='.$user_id , $step);
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
//     $url = empty($image) ?  RC_Config::system('CUSTOM_UPLOAD_SITE_URL').ecjia::config('no_picture') : RC_Config::system('CUSTOM_UPLOAD_SITE_URL') . '/' . $image;
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
	$db = RC_Loader::load_app_model ( 'volume_price_model', 'goods' );
	$volume_price = array ();
	$temp_index = '0';

	$res = $db->field ('`volume_number` , `volume_price`')->where(array('goods_id' => $goods_id, 'price_type' => $price_type))->order ('`volume_number` asc')->select();
	if (! empty ( $res )) {
		foreach ( $res as $k => $v ) {
			$volume_price [$temp_index] = array ();
			$volume_price [$temp_index] ['number'] = $v ['volume_number'];
			$volume_price [$temp_index] ['price'] = $v ['volume_price'];
			$volume_price [$temp_index] ['format_price'] = price_format ( $v ['volume_price'] );
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
    $dbview = RC_Loader::load_app_model('sys_attribute_viewmodel', 'goods');
    
    if (empty($goods_attr_id_array)) {
        return $goods_attr_id_array;
    }
    
    // 重新排序
    $row = $dbview->join('goods_attr')->in(array('v.goods_attr_id' => $goods_attr_id_array))->order(array('a.attr_id' => $sort))->select();
    
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
 * 虚拟卡发货
 *
 * @access public
 * @param string $goods
 *        	商品详情数组
 * @param string $order_sn
 *        	本次操作的订单
 * @param string $msg
 *        	返回信息
 * @param string $process
 *        	设定当前流程：split，发货分单流程；other，其他，默认。
 *
 * @return boolen
 */
function virtual_card_shipping($goods, $order_sn, &$msg, $process = 'other') {
    $db = RC_Loader::load_app_model ( 'virtual_card_model', 'goods' );
    $db_goods = RC_Loader::load_app_model ( 'goods_model', 'goods' );
    $db_order_goods = RC_Loader::load_app_model ( 'order_goods_model', 'orders' );
    $db_order_info = RC_Loader::load_app_model ( 'order_info_model', 'orders' );

    $num = $db->where(array('goods_id' => $goods['goods_id'], 'is_saled' => 0))->count();

    if ($num < $goods ['num']) {
        $msg .= sprintf(RC_Lang::lang('virtual_card_oos'),$goods['goods_name']);
        return false;
    }

    $arr = $db->field('card_id, card_sn, card_password, end_date, crc32')->where(array('goods_id' => $goods [goods_id], 'is_saled' => 0))->limit($goods['num'])->select();

    $card_ids = array ();
    $cards = array ();
    /* 读取商店配置的加密串密钥 */
    $auth_key = ecjia_config::instance()->read_config('auth_key');
    foreach ( $arr as $virtual_card ) {
        $card_info = array ();
        /* 卡号和密码解密 */
        if ($virtual_card ['crc32'] == 0 || $virtual_card ['crc32'] == crc32 ( $auth_key )) {
            $card_info ['card_sn'] = RC_Crypt::decrypt ($virtual_card ['card_sn'],$auth_key);
            $card_info ['card_password'] = RC_Crypt::decrypt ($virtual_card ['card_password'],$auth_key);
        } elseif ($virtual_card ['crc32'] == crc32 ( OLD_AUTH_KEY )) {
            $card_info ['card_sn'] = RC_Crypt::decrypt ( $virtual_card ['card_sn'], OLD_AUTH_KEY );
            $card_info ['card_password'] = RC_Crypt::decrypt ( $virtual_card ['card_password'], OLD_AUTH_KEY );
        } else {
            $msg .= 'error key';
            return false;
        }
        $card_info ['end_date'] = date ( ecjia::config('date_format'), $virtual_card ['end_date'] );
        $card_ids [] = $virtual_card ['card_id'];
        $cards [] = $card_info;
    }


    $data = array (
        'is_saled' => 1,
        'order_sn' => $order_sn
    );
    $query = $db->in(array('card_id' => $card_ids))->update($data);

    if (!$query) {
        $msg .= $db->error();
        return false;
    }

    /* 更新库存 */
    $db_goods->dec('goods_number' , 'goods_id='.$goods['goods_id'] , $goods[num]);
    if (true) {
        /* 获取订单信息 */
        $order = $db_order_info->field('order_id, order_sn, consignee, email')->find(array('order_sn' => $order_sn));

        /* 更新订单信息 */
        if ($process == 'split') {

            $data = array (
                'send_number' => send_number + $goods [num]
            );
            	
            $query = $db_order_goods->inc('send_number','order_id='.$order['order_id'].' and goods_id='.$goods['goods_id'],$goods['num']);//(array('order_id' => $order [order_id],'goods_id' => $goods ['goods_id']))->update($data);
        } else {
            $data = array (
                'send_number' => $goods [num]
            );
            $query = $db_order_goods->where(array('order_id' => $order [order_id],'goods_id' => $goods ['goods_id']))->update($data);
        }
        if (!$query) {
            $msg .= $db->error();
            return false;
        }
    }

    /* 发送邮件 */
    ecjia_admin::$controller->assign ( 'virtual_card', $cards );
    ecjia_admin::$controller->assign ( 'order', $order );
    ecjia_admin::$controller->assign ( 'goods', $goods );

    ecjia_admin::$controller->assign ( 'send_time', date ( 'Y-m-d H:i:s' ));
    ecjia_admin::$controller->assign ( 'shop_name', ecjia::config('shop_name'));
    ecjia_admin::$controller->assign ( 'send_date', date ( 'Y-m-d' ) );
    ecjia_admin::$controller->assign ( 'sent_date', date ( 'Y-m-d' ) );

    $tpl_name = 'virtual_card';
    $tpl   = RC_Api::api('mail', 'mail_template', $tpl_name);
    $content = ecjia_admin::$controller->fetch_string($tpl['template_content']);
    RC_Mail::send_mail( $order ['consignee'], $order ['email'], $tpl ['template_subject'], $content, $tpl ['is_html'] );

    return true;
}
/*------------------------------------------------------ */
/*-- TODO 会员使用到的方法-------------------------------------*/
/*------------------------------------------------------ */
// /**
//  * 初始化会员数据整合类
//  *
//  * @access public
//  * @return object
//  */
// function &init_users() {
//     static $cls = null;
//     if ($cls != null) {
//         return $cls;
//     }

//     RC_Loader::load_app_module ( ecjia::config ( 'integrate_code' ), 'user', false );

//     $cfg = unserialize ( ecjia::config ( 'integrate_config' ) );
//     $cls_name = ecjia::config ( 'integrate_code' );
//     $cls = new $cls_name ( $cfg );

//     return $cls;
// }

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
    $dbview = RC_Loader::load_app_model ('sys_snatch_log_viewmodel',"snatch");
    $db_goods_activity = RC_Loader::load_app_model ('goods_activity_model', "goods" );
    $db_order_info = RC_Loader::load_app_model ( 'order_info_model', "orders" );

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
    $db = RC_Loader::load_app_model ( 'order_goods_model', 'orders' );
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
/*------------------------------------------------------ */
/*-- TODO API使用方法----------------------------------*/
/*------------------------------------------------------ */

// /**
//  * 调用使用UCenter插件时的函数
//  *
//  * @param string $func
//  * @param array $params
//  *
//  * @return mixed
//  */
// function user_uc_call($func, $params = null)
// {
//     if (ecjia::config('integrate_code' && 'integrate_code' == 'ucenter', ecjia::CONFIG_CHECK)) {
//         restore_error_handler();
//         if (! function_exists($func)) {
//             RC_Loader::load_sys_func('uc');
//         }

//         $res = call_user_func_array($func, $params);

//         set_error_handler('exception_handler');

//         return $res;
//     } else {
//         return;
//     }
// }
/*------------------------------------------------------ */
/*-- TODO 支付方式和API使用方法----------------------------------*/
/*------------------------------------------------------ */
/**
 * 虚拟商品发货
 *
 * @access public
 * @param array $virtual_goods
 *        	虚拟商品数组
 * @param string $msg
 *        	错误信息
 * @param string $order_sn
 *        	订单号。
 * @param string $process
 *        	设定当前流程：split，发货分单流程；other，其他，默认。
 *
 * @return bool
 */
function virtual_goods_ship(&$virtual_goods, &$msg, $order_sn, $return_result = false, $process = 'other') {
    $virtual_card = array ();
    foreach ( $virtual_goods as $code => $goods_list ) {
        /* 只处理虚拟卡 */
        if ($code == 'virtual_card') {
            foreach ( $goods_list as $goods ) {
                if (virtual_card_shipping ( $goods, $order_sn, $msg, $process )) {
                    if ($return_result) {
                        $virtual_card [] = array (
                            'goods_id' => $goods ['goods_id'],
                            'goods_name' => $goods ['goods_name'],
                            'info' => virtual_card_result ( $order_sn, $goods )
                        );
                    }
                } else {
                    return false;
                }
            }
            ecjia::$view_object->assign ( 'virtual_card', $virtual_card );
        }
    }

    return true;
}
/*------------------------------------------------------ */
/*-- TODO 商品、订单、API使用方法--------------------------------*/
/*------------------------------------------------------ */
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
    $dbview = RC_Loader::load_app_model ( 'sys_attribute_viewmodel', 'goods' );

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
    $dbview = RC_Loader::load_app_model ( 'sys_goods_member_viewmodel', 'goods' );
    RC_Loader::load_app_func ( 'goods', 'goods' );
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
    $field = "wg.w_id, g.goods_name, g.goods_sn, g.is_on_sale, g.is_real, g.user_id as ru_id, g.model_inventory, g.model_attr, ".
    		"wg.warehouse_price, wg.warehouse_promote_price, wg.region_number as wg_number, wag.region_price, wag.region_promote_price, wag.region_number as wag_number, g.model_price, g.model_attr, ".
    		"g.market_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) AS org_price, ".
    		"IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)) as promote_price, ".
    		" g.promote_start_date, g.promote_end_date, g.goods_weight, g.integral, g.extension_code, g.goods_number, g.is_alone_sale, g.is_shipping, ".
    		"IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * '$_SESSION[discount]') AS shop_price ";
    /* 取得商品信息 */
    $dbview->view = array(
    		'warehouse_goods' => array(
    				'type'  => Component_Model_View::TYPE_LEFT_JOIN,
    				'alias' => 'wg',
    				'on'   	=> "g.goods_id = wg.goods_id and wg.region_id = '$warehouse_id'"
    		),
    		'warehouse_area_goods' => array(
    				'type'  => Component_Model_View::TYPE_LEFT_JOIN,
    				'alias' => 'wag',
    				'on'   	=> "g.goods_id = wag.goods_id and wag.region_id = '$area_id'"
    		),
    		'member_price' => array(
    				'type'  => Component_Model_View::TYPE_LEFT_JOIN,
    				'alias' => 'mp',
    				'on'   	=> "mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]'"
    		)
    );
    // 取得商品促销价格列表
    $goods = $dbview->field($field)->join (array('warehouse_goods', 'warehouse_area_goods', 'member_price'))->find (array('g.goods_id' => $goods_id, 'g.is_delete' => 0));
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
    $mobilebuy_db = RC_Loader::load_app_model('goods_activity_model', 'goods');
    $mobilebuy_ext_info = array();
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
    
    $warehouse_area['warehouse_id'] = $warehouse_id;
    $warehouse_area['area_id'] = $area_id;
    // 如果需要加入规格价格
    if ($is_spec_price) {
        if (! empty ( $spec )) {
            $spec_price = spec_price ( $spec , $goods_id, $warehouse_area);
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
function get_package_goods($package_id)
{
    $dbview = RC_Loader::load_app_model ( 'package_goods_viewmodel', 'goods' );
    $db_attr = RC_Loader::load_app_model ( 'goods_attr_model', 'goods' );

    if ($package_id == 0) {
        $where = " AND pg.admin_id = '$_SESSION[admin_id]'";
    }

    $dbview->view = array (
        'goods' => array (
            'type' 	=> Component_Model_View::TYPE_LEFT_JOIN,
            'alias' => 'g',
            'field' => "pg.goods_id, g.goods_name, pg.goods_number, p.goods_attr, p.product_number, p.product_id",
            'on' 	=> 'pg.goods_id = g.goods_id'
        ),
        'products' => array (
            'type' 	=> Component_Model_View::TYPE_LEFT_JOIN,
            'alias' => 'p',
            'on' 	=> 'pg.product_id = p.product_id'
        )
    );

    $resource = $dbview->where('pg.package_id = '.$package_id.''.$where)->select();
    if (! $resource) {
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

        $result_goods_attr = $db_attr->field ( 'goods_attr_id, attr_value' )->in ( array ('goods_id' => $good_product_str) )->select ();

        $_goods_attr = array ();
        foreach ( $result_goods_attr as $value ) {
            $_goods_attr [$value ['goods_attr_id']] = $value ['attr_value'];
        }
    }

    /* 过滤货品 */
    $format [0] = '%s[%s]';
    $format [1] = '%s';
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

    return $row;
}

/**
 * 所有的促销活动信息
 *
 * @access public
 * @return array
 */
function get_promotion_info($goods_id = '') {
    $db_goods_activity = RC_Loader::load_app_model ("goods_activity_model", "goods");
    $db_goods = RC_Loader::load_app_model ('goods_model', 'goods');

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
    $db = RC_Loader::load_app_model ( 'goods_activity_model', 'goods' );
    $dbview = RC_Loader::load_app_model ( 'sys_package_goods_viewmodel', 'goods' );

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
    $db_products = RC_Loader::load_app_model ( "products_model", "goods" );
    $db_goods_attr = RC_Loader::load_app_model ( "goods_attr_model", "goods" );
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
    $dbview = RC_Loader::load_app_model ( 'sys_goods_attribute_viewmodel', 'goods' );
    $result = $dbview->join ( 'attribute' )->where ( 'ga.goods_id = ' . $goods_id . '' . $conditions )->select ();
    $return_array = array ();
    foreach ( $result as $value ) {
        $return_array [$value ['goods_attr_id']] = $value;
    }

    return $return_array;
}


/*返回商品详情页面的导航条数组*/
function get_goods_info_nav($goods_id=0) {
    return array(
        'edit'                  => array('name' => _('通用信息'), 'pjax' => 1, 'href' => RC_Uri::url('goods/admin/edit', "goods_id=$goods_id")),
        'edit_goods_desc'       => array('name' => _('商品描述'), 'pjax' => 1, 'href' => RC_Uri::url('goods/admin/edit_goods_desc', "goods_id=$goods_id")),
        'edit_goods_attr'       => array('name' => _('商品属性'), 'pjax' => 1, 'href' => RC_Uri::url('goods/admin/edit_goods_attr', "goods_id=$goods_id")),
        'edit_goods_photo'      => array('name' => _('商品相册'), 'pjax' => 1, 'href' => RC_Uri::url('goods/admin_gallery/init', "goods_id=$goods_id")),
        'edit_link_goods'       => array('name' => _('关联商品'), 'pjax' => 1, 'href' => RC_Uri::url('goods/admin/edit_link_goods', "goods_id=$goods_id")),
        'edit_link_parts'       => array('name' => _('关联配件'), 'pjax' => 1, 'href' => RC_Uri::url('goods/admin/edit_link_parts', "goods_id=$goods_id")),
        'edit_link_article'     => array('name' => _('关联文章'), 'pjax' => 1, 'href' => RC_Uri::url('goods/admin/edit_link_article', "goods_id=$goods_id")),
        'edit_link_area'        => array('name' => _('关联地区'), 'pjax' => 1, 'href' => RC_Uri::url('goods/admin/edit_link_area', "goods_id=$goods_id")),
        'product_list'          => array('name' => _('货品管理'), 'pjax' => 1, 'href' => RC_Uri::url('goods/admin/product_list', "goods_id=$goods_id")),
    );
}

// end