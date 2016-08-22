<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 单个商品的信息
 * @author royalwang
 *
 */
class detail_module implements ecjia_interface
{

    public function run(ecjia_api & $api)
    {
        //如果用户登录获取其session
    	EM_Api::authSession(false);
        
        $goods_id = _POST('goods_id', 0);
        $area_id = _POST('area_id', 0);
        
        $rec_type = _POST('rec_type');
        $object_id = _POST('object_id');
        
        /* 获得商品的信息 */
        RC_Loader::load_app_func('goods','goods');
        RC_Loader::load_app_func('category','goods');
        
        if ( $area_id > 0 ) {
        	$db_region = RC_Loader::load_app_model('region_model', 'shipping');
        	$region_result = $db_region->where(array('region_id' => $area_id))->find();
        	
        	if ($region_result['region_type'] > 2) {//定位为区县
        		$region_result = $db_region->where(array('region_id' => $region_result['parent_id']))->find();
        		$area_id = $region_result['parent_id'];
        	} elseif ($region_result['region_type'] == 2) { //定位为市
        		$area_id = $region_result['parent_id'];
        	} else { //定位为省
        		$area_id = $region_result['region_id'];
        	}
        	$warehouse_db = RC_Loader::load_app_model('warehouse_model', 'warehouse');
        	$warehouse = $warehouse_db->where(array('regionId' => $area_id))->find();
        	$area_id = $warehouse['region_id'];
        	$warehouse_id = $warehouse['parent_id'];
        }
        
        $goods = get_goods_info($goods_id, $warehouse_id, $area_id);
        
        if ($goods === false) {
            /* 如果没有找到任何记录则跳回到首页 */
            EM_Api::outPut(13);
        } elseif (empty($goods['goods_id'])) {
            /* 不存在该商品  */
            EM_Api::outPut(13);
        } else {
        	//多店铺开启库存管理以及地区后才会去判断
        	if ( $area_id > 0 ) {
        		$warehouse_db = RC_Loader::load_app_model('warehouse_model', 'warehouse');
        		$warehouse = $warehouse_db->where(array('regionId' => $area_id))->find();
        		$area = $warehouse['region_id'];
        		$warehouse_id = $warehouse['parent_id'];
        		if ($goods['model_inventory'] > 0 || $goods['model_price'] > 0 || $goods['model_attr'] > 0) {
	
        			$warehouse_name = $warehouse_db->where(array('region_id' => $warehouse['parent_id']))->get_field('region_name');
        			$goods['is_warehouse'] = true;
        			$goods['warehouse'] = $warehouse_name;

        		} else {
        			$goods['is_warehouse'] = false;
        		}
        	}
        	
        	
            if ($goods['brand_id'] > 0) {
                $goods['goods_brand_url'] = build_uri('brand', array('bid' => $goods['brand_id']), $goods['goods_brand']);
            }
            /* 加入验证如果价格不存在，则为0 */
            $shop_price = $goods['shop_price'];
            $linked_goods = array();
            
            $goods['goods_style_name'] = add_style($goods['goods_name'], $goods['goods_name_style']);
            
            /* 购买该商品可以得到多少钱的红包 */
            if ($goods['bonus_type_id'] > 0) {
                $time = RC_Time::gmtime();
                $db_bonus_type = RC_Loader::load_app_model('bonus_type_model','bonus');
                $goods['bonus_money'] = $db_bonus_type->where(array('type_id' => $goods['bonus_type_id'] , 'send_type' => SEND_BY_GOODS , 'send_start_date' => array('elt' => $time) , 'send_end_date' => array('egt' => $time)))-> get_field('type_money');
                
                if ($goods['bonus_money'] > 0) {
                    $goods['bonus_money'] = price_format($goods['bonus_money']);
                }
            }

            $db_goods = RC_Loader::load_app_model('goods_model', 'goods');
            RC_Loader::load_app_func('warehousegoods', 'goods');
            $properties = get_goods_properties($goods_id, $warehouse_id, $area); // 获得商品的规格和属性
// 			$properties = warehouse_get_goods_properties($goods_id);
            
            // 获取关联礼包
//             $package_goods_list = get_package_goods_list($goods['goods_id']);
//             $volume_price_list = get_volume_price_list($goods['goods_id'], '1');// 商品优惠价格区间
        }
        
        /* 更新点击次数 */
        $db_goods->inc('click_count','goods_id='.$goods_id,1);
        
        $data = $goods;
        $data['rank_prices']     = !empty($shop_price) ? get_user_rank_prices($goods_id, $shop_price) : 0;
        $data['pictures']        = EM_get_goods_gallery($goods_id);
        $data['properties']      = $properties['pro'];
        $data['specification']   = $properties['spe'];
        $data['collected']       = 0;
        
        $db_favourable = RC_Loader::load_app_model('favourable_activity_model', 'favourable');
        $favourable_result = $db_favourable->where(array('seller_id' => $goods['seller_id'], 'start_time' => array('elt' => RC_Time::gmtime()), 'end_time' => array('egt' => RC_Time::gmtime()), 'act_type' => array('neq' => 0)))->select();
        $favourable_list = array();
        if (empty($rec_type)) {
        	if (!empty($favourable_result)) {
        		foreach ($favourable_result as $val) {
        			if ($val['act_range'] == '0') {
        				$favourable_list[] = array(
        						'name' => $val['act_name'],
        						'type' => $val['act_type'] == '1' ? 'price_reduction' : 'price_discount',
        						'type_label' => $val['act_type'] == '1' ? __('满减') : __('满折'),
        				);
        			} else {
        				$act_range_ext = explode(',', $val['act_range_ext']);
        				switch ($val['act_range']) {
        					case 1 : 
        						if (in_array($goods['cat_id'], $act_range_ext)) {
        							$favourable_list[] = array(
        									'name' => $val['act_name'],
        									'type' => $val['act_type'] == '1' ? 'price_reduction' : 'price_discount',
        									'type_label' => $val['act_type'] == '1' ? __('满减') : __('满折'),
        							);
        						}
        						break;
        					case 2 : 
        						if (in_array($goods['brand_id'], $act_range_ext)) {
        							$favourable_list[] = array(
        									'name' => $val['act_name'],
        									'type' => $val['act_type'] == '1' ? 'price_reduction' : 'price_discount',
        									'type_label' => $val['act_type'] == '1' ? __('满减') : __('满折'),
        							);
        						}
        						break;
        					case 3 : 
        						if (in_array($goods['goods_id'], $act_range_ext)) {
        							$favourable_list[] = array(
        									'name' => $val['act_name'],
        									'type' => $val['act_type'] == '1' ? 'price_reduction' : 'price_discount',
        									'type_label' => $val['act_type'] == '1' ? __('满减') : __('满折'),
        							);
        						}
        						break;
        					default:
        						break;
        				}
        			}
        			
        		}
        	}
        }
        
        if ($_SESSION['user_id']) {
            // 查询收藏夹状态
            $db_collect_goods = RC_Loader::load_app_model('collect_goods_model', 'goods');
            $count = $db_collect_goods->where(array('user_id' => $_SESSION[user_id] , 'goods_id' => $goods_id))->count();
            
            if ($count > 0) {
                $data['collected'] = 1;
            }
        }
        
        
        
        $data = API_DATA('GOODS', $data);
        $data['unformatted_shop_price'] = $goods['shop_price'];
        if ($rec_type == 'GROUPBUY_GOODS') {
        	/* 取得团购活动信息 */
        	$group_buy = group_buy_info($object_id);
        	$data['promote_price'] = $group_buy['cur_price'];
        	$data['formated_promote_price'] = $group_buy['formated_cur_price'];
        	$data['promote_start_date'] = $group_buy['formated_start_date'];
        	$data['promote_end_date'] = $group_buy['formated_end_date'];
        	$activity_type = 'GROUPBUY_GOODS';
        } else {
        	$mobilebuy_db = RC_Loader::load_app_model('goods_activity_model', 'goods');
        	$groupbuy = $mobilebuy_db->find(array(
        			'goods_id'	 => $data['id'],
        			'start_time' => array('elt' => RC_Time::gmtime()),
        			'end_time'	 => array('egt' => RC_Time::gmtime()),
        			'act_type'	 => GAT_GROUP_BUY,
        	));
        	$mobilebuy = $mobilebuy_db->find(array(
        			'goods_id'	 => $data['id'],
        			'start_time' => array('elt' => RC_Time::gmtime()),
        			'end_time'	 => array('egt' => RC_Time::gmtime()),
        			'act_type'	 => GAT_MOBILE_BUY,
        	));
        	/* 判断是否有促销价格*/
        	$price = ($data['unformatted_shop_price'] > $goods['promote_price_org'] && $goods['promote_price_org'] > 0) ? $goods['promote_price_org'] : $data['unformatted_shop_price'];
        	$activity_type = ($data['unformatted_shop_price'] > $goods['promote_price_org'] && $goods['promote_price_org'] > 0) ? 'PROMOTE_GOODS' : 'GENERAL_GOODS';
        	
        	
        	$mobilebuy_price = $groupbuy_price = 0;
        	if (!empty($mobilebuy)) {
       			$ext_info = unserialize($mobilebuy['ext_info']);
       			$mobilebuy_price = $ext_info['price'];
       			if ($mobilebuy_price < $price) {
       				$goods['promote_start_date'] = $mobilebuy['start_time'];
       				$goods['promote_end_date'] = $mobilebuy['end_time'];
       			}
       			$price = $mobilebuy_price > $price ? $price : $mobilebuy_price;
       			$activity_type = $mobilebuy_price > $price ? $activity_type : 'MOBILEBUY_GOODS';
			}
//         		if (!empty($groupbuy)) {
//         			$ext_info = unserialize($groupbuy['ext_info']);
//         			$price_ladder = $ext_info['price_ladder'];
//         			$groupbuy_price  = $price_ladder[0]['price'];
//         			if ($groupbuy_price < $price) {
//         				$start_time = $groupbuy['start_time'];
//         				$end_time = $groupbuy['end_time'];
//         			}
//         			$price = $groupbuy_price > $price ? $price : $groupbuy_price;
//         			$activity_type = $groupbuy_price > $price ? $activity_type : 'GROUPBUY_GOODS';
//         			$object_id = $groupbuy['act_id'];
//         		}
        	
        }
        /* 计算节约价格*/
        $saving_price = ($data['unformatted_shop_price'] - $price) > 0 ? $data['unformatted_shop_price'] - $price : 0;
        $data['activity_type']	= $activity_type;
        $data['saving_price']	= $saving_price;
        $data['formatted_saving_price'] = '已省'.$saving_price.'元';
        if ($price < $data['unformatted_shop_price'] && isset($price)) {
        	$data['promote_price'] = $price;
        	$data['formated_promote_price'] = price_format($price);
        	$data['promote_start_date'] = RC_Time::local_date('Y/m/d H:i:s O', $goods['promote_start_date']);
        	$data['promote_end_date']	= RC_Time::local_date('Y/m/d H:i:s O', $goods['promote_end_date']);
        }
        
        
        $data['rec_type'] = empty($rec_type) ? $activity_type : 'GROUPBUY_GOODS';
        $data['object_id'] = $object_id;
        
        if (ecjia::config('shop_touch_url', ecjia::CONFIG_EXISTS)) {
        	$data['goods_url'] = ecjia::config('shop_touch_url').'?goods&c=index&a=show&id='.$goods_id.'&hidenav=1&hidetab=1';
        } else {
        	$data['goods_url'] = null;
        }
        
        $data['favourable_list'] = $favourable_list;
        
        $location = _POST('location');
        $options = array(
       			'cat_id'	=> $data['cat_id'],
        		'intro'		=> 'hot',
       			'page'		=> 1,
       			'size'		=> 8,
       			'location'	=> $location,
       	);
        //商品详情页猜你喜欢  api2.4功能
    	$result = RC_Api::api('goods', 'goods_list', $options);
        
        $data['related_goods'] = array();
		if (!empty($result['list'])) {
			$mobilebuy_db = RC_Loader::load_app_model('goods_activity_model', 'goods');
			/* 手机专享*/
	        $result_mobilebuy = ecjia_app::validate_application('mobilebuy');
			$is_active = ecjia_app::is_active('ecjia.mobilebuy');
			foreach ($result['list'] as $val) {
				/* 判断是否有促销价格*/
				$price = ($val['unformatted_shop_price'] > $val['unformatted_promote_price'] && $val['unformatted_promote_price'] > 0) ? $val['unformatted_promote_price'] : $val['unformatted_shop_price'];
				$activity_type = ($val['unformatted_shop_price'] > $val['unformatted_promote_price'] && $val['unformatted_promote_price'] > 0) ? 'PROMOTE_GOODS' : 'GENERAL_GOODS';
				 /* 计算节约价格*/
				$saving_price = ($val['unformatted_shop_price'] > $val['unformatted_promote_price'] && $val['unformatted_promote_price'] > 0) ? $val['unformatted_shop_price'] - $val['unformatted_promote_price'] : (($val['unformatted_market_price'] > 0 && $val['unformatted_market_price'] > $val['unformatted_shop_price']) ? $val['unformatted_market_price'] - $val['unformatted_shop_price'] : 0);
        
				$mobilebuy_price = $object_id = 0;
				if (!is_ecjia_error($result_mobilebuy) && $is_active) {
					$mobilebuy = $mobilebuy_db->find(array(
						'goods_id'	 => $val['goods_id'],
						'start_time' => array('elt' => RC_Time::gmtime()),
						'end_time'	 => array('egt' => RC_Time::gmtime()),
						'act_type'	 => GAT_MOBILE_BUY,
					));
					if (!empty($mobilebuy)) {
						$ext_info = unserialize($mobilebuy['ext_info']);
						$mobilebuy_price = $ext_info['price'];
						if ($mobilebuy_price < $price) {
							$val['promote_price'] = price_format($mobilebuy_price);
				        	$object_id		= $mobilebuy['act_id'];
				        	$activity_type	= 'MOBILEBUY_GOODS';
			    			$saving_price = ($val['unformatted_shop_price'] - $mobilebuy_price) > 0 ? $val['unformatted_shop_price'] - $mobilebuy_price : 0;
			    		}
					}
				}
				
				$data['related_goods'][] = array(
						'goods_id'		=> $val['goods_id'],
						'name'			=> $val['name'],
						'market_price'	=> $val['market_price'],
						'shop_price'	=> $val['shop_price'],
						'promote_price'	=> $val['promote_price'],
						'img' => array(
								'thumb'	=> $val['goods_img'],
								'url'	=> $val['original_img'],
								'small'	=> $val['goods_thumb']
						),
						'activity_type' => $activity_type,
						'object_id'		=> $object_id,
						'saving_price'	=>	$saving_price,
						'formatted_saving_price' => $saving_price > 0 ? '已省'.$saving_price.'元' : '',
						'seller_id'		=> $val['seller_id'],
						'seller_name'	=> $val['seller_name'],
				);
			}
		}
//         } else {
//         	$data['related_goods'] = array();
//         }
        //多店铺的内容
        $data['seller_id'] = $goods['seller_id'];
        if ($goods['seller_id'] > 0) {
        	$seller_where = array();
        	$seller_where['ssi.status'] = 1;
//         	$seller_where['msi.merchants_audit'] = 1;
//         	$seller_where['msi.user_id'] = $data['seller_id'];
        	$seller_where['ssi.id'] = $goods['seller_id'];
//         	$msi_dbview = RC_Loader::load_app_model('merchants_shop_information_viewmodel', 'seller');
        	$ssi_dbview = RC_Model::model('seller/seller_shopinfo_viewmodel');
        	
        	$field ='ssi.*, ssi.id as seller_id, ssi.shop_name as seller_name, sc.cat_name, count(cs.seller_id) as follower, SUM(IF(cs.user_id = '.$_SESSION['user_id'].',1,0)) as is_follower';
			$info = $ssi_dbview->join(array('seller_category', 'collect_store'))
					        	->field($field)
					        	->where($seller_where)
					        	->find();
        	
        	if(substr($info['shop_logo'], 0, 1) == '.') {
        		$info['shop_logo'] = str_replace('../', '/', $info['shop_logo']);
        	}
        	$goods_db = RC_Loader::load_app_model('goods_model', 'goods');
        	$goods_count = $goods_db->where(array('user_id' => $data['seller_id'], 'is_on_sale' => 1, 'is_alone_sale' => 1, 'is_delete' => 0))->count();
        	
        	$cs_db = RC_Loader::load_app_model('collect_store_model', 'seller');
        	$follower_count = $cs_db->where(array('seller_id' => $data['seller_id']))->count();
        	
        	$db_goods_view = RC_Loader::load_app_model('comment_viewmodel', 'comment');
        	
        	$field = 'count(*) as count, SUM(IF(comment_rank>3,1,0)) as comment_rank, SUM(IF(comment_server>3,1,0)) as comment_server, SUM(IF(comment_delivery>3,1,0)) as comment_delivery';
        	$comment = $db_goods_view->join(array('goods'))->field($field)->where(array('g.seller_id' => $goods['seller_id'], 'parent_id' => 0, 'status' => 1))->find();
        	

        	$data['merchant_info'] = array(
        			'id'				=> $info['seller_id'],
        			'seller_name'		=> $info['seller_name'],
        			'seller_logo'		=> !empty($info['shop_logo']) ? RC_Upload::upload_url().'/'.$info['shop_logo'] : '',
        			'goods_count'		=> $goods_count,
        			'follower'			=> $follower_count,
        			'comment' 			=> array(
        					'comment_goods'		=> $comment['count'] > 0 && $comment['comment_rank'] > 0 ? round($comment['comment_rank']/$comment['count']*100).'%' : '100%',
        					'comment_server'	=> $comment['count'] > 0 && $comment['comment_server'] > 0  ? round($comment['comment_server']/$comment['count']*100).'%' : '100%',
        					'comment_delivery'	=> $comment['count'] > 0 && $comment['comment_delivery'] > 0  ? round($comment['comment_delivery']/$comment['count']*100).'%' : '100%',
        			)
        			
        	);
        }
        $data['is_warehouse'] = $goods['is_warehouse'];
        $warehouse_name = empty($warehouse_name) ? '总店' : $warehouse_name;
        $shop_name = empty($info['seller_name']) ? ecjia::config('shop_name') : $info['seller_name'];
        $data['server_desc'] = '由'.$shop_name.'从'.$warehouse_name.'发货并提供售后服务';
        
        EM_Api::outPut($data);
        
//      $db_goods->where(array('goods_id' => $goods_id))->update(array('click_count' => click_count + 1));
//      $_REQUEST['id'] = _POST('goods_id', 0);
//		$goods_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
    }
}

function EM_get_goods_gallery($goods_id) {
    $db_goods_gallery = RC_Loader::load_app_model('goods_gallery_model', 'goods');
    $row = $db_goods_gallery->field('img_id, img_url, thumb_url, img_desc, img_original')->where(array('goods_id' => $goods_id))->limit(ecjia::config('goods_gallery_number'))->select();
    /* 格式化相册图片路径 */
    RC_Loader::load_app_class('goods_image', 'goods');
    $img_list_sort = $img_list_id = array();
    foreach ($row as $key => $gallery_img) {
    	$desc_index = intval(strrpos($gallery_img['img_original'], '?')) + 1;
    	!empty($desc_index) && $row[$key]['desc'] = substr($gallery_img['img_original'], $desc_index);
    	$row[$key]['img_url'] = empty($gallery_img ['img_original']) ? RC_Uri::admin_url('statics/images/nopic.png') : goods_image::get_absolute_url($gallery_img ['img_original']);
    	$row[$key]['thumb_url'] = empty($gallery_img ['img_url']) ? RC_Uri::admin_url('statics/images/nopic.png') : goods_image::get_absolute_url($gallery_img ['img_url']);
    	$img_list_sort[$key] = $img_list[$key]['desc'];
    	$img_list_id[$key] = $gallery_img['img_id'];
    }
    //先使用sort排序，再使用id排序。
    array_multisort($img_list_sort, $img_list_id, $row);
    return $row;
}

/**
 * 获得指定商品的各会员等级对应的价格
 *
 * @access public
 * @param integer $goods_id            
 * @return array
 */
function get_user_rank_prices($goods_id, $shop_price) {
    $dbview = RC_Loader::load_app_model('user_rank_member_price_viewmodel', 'user');
    $dbview->view =array(
    		'member_price' 	=> array(
    				'type' 		=> Component_Model_View::TYPE_LEFT_JOIN,
    				'alias' 	=> 'mp',
    				'on' 		=> "mp.goods_id = '$goods_id' and mp.user_rank = r.rank_id "
    		),
    );
    
    $res = $dbview->join(array('member_price'))->field("rank_id, IFNULL(mp.user_price, r.discount * $shop_price / 100) AS price, r.rank_name, r.discount")->where("r.show_price = 1 OR r.rank_id = '$_SESSION[user_rank]'")->select();
    
    $arr = array();
    foreach ($res as $row) {
        $arr[$row['rank_id']] = array(
            'rank_name' => htmlspecialchars($row['rank_name']),
            'price' => price_format($row['price']),
        	'unformatted_price' => number_format( $row['price'], 2, '.', '')
        );
    }
    
    return $arr;
}


/**
 * 取得跟商品关联的礼包列表
 *
 * @param string $goods_id
 *            商品编号
 *            
 * @return 礼包列表
 */
function get_package_goods_list($goods_id) { 
    $dbview = RC_Loader::load_app_model('goods_activity_package_goods_viewmodel', 'goods');
    $db_view = RC_Loader::load_app_model('goods_attr_attribute_viewmodel', 'goods');
    $now = RC_Time::gmtime();
    $where = array(
		'ga.start_time' => array('elt' => $now) ,
		'ga.end_time' => array('egt' => $now) , 
    	'pg.goods_id' => $goods_id
    );
    $res = $dbview->join('package_goods')
    			  ->where($where)
    			  ->group('ga.act_id')
    			  ->order(array('ga.act_id' => 'asc'))
    			  ->select();
    
    foreach ($res as $tempkey => $value) {
        $subtotal = 0;
        $row = unserialize($value['ext_info']);
        unset($value['ext_info']);
        if ($row) {
            foreach ($row as $key => $val) {
                $res[$tempkey][$key] = $val;
            }
        }

        $goods_res = $dbview->join(array('goods','products','member_price'))
        					->where(array('pg.package_id' => $value['act_id']))
        					->order(array('pg.package_id' => 'asc', 'pg.goods_id' => 'asc'))
        					->select();
        
        foreach ($goods_res as $key => $val) {
            $goods_id_array[] = $val['goods_id'];
            $goods_res[$key]['goods_thumb'] = get_image_path($val['goods_id'], $val['goods_thumb'], true);
            $goods_res[$key]['market_price'] = price_format($val['market_price']);
            $goods_res[$key]['rank_price'] = price_format($val['rank_price']);
            $subtotal += $val['rank_price'] * $val['goods_number'];
        }
        
        /* 取商品属性 */
        $result_goods_attr = $db_view->where(array('a.attr_type' => 1))->in(array('goods_id' => $goods_id_array))->select();
        
        $_goods_attr = array();
        foreach ($result_goods_attr as $value) {
            $_goods_attr[$value['goods_attr_id']] = $value['attr_value'];
        }
        
        /* 处理货品 */
        $format = '[%s]';
        foreach ($goods_res as $key => $val) {
            if ($val['goods_attr'] != '') {
                $goods_attr_array = explode('|', $val['goods_attr']);
                $goods_attr = array();
                foreach ($goods_attr_array as $_attr) {
                    $goods_attr[] = $_goods_attr[$_attr];
                }
                $goods_res[$key]['goods_attr_str'] = sprintf($format, implode('，', $goods_attr));
            }
        }
        $res[$tempkey]['goods_list'] = $goods_res;
        $res[$tempkey]['subtotal'] = price_format($subtotal);
        $res[$tempkey]['saving'] = price_format(($subtotal - $res[$tempkey]['package_price']));
        $res[$tempkey]['package_price'] = price_format($res[$tempkey]['package_price']);
    }
    
    return $res;
}
// /**
//  * 获得购买过该商品的人还买过的商品
//  *
//  * @access public
//  * @param integer $goods_id
//  * @return array
//  */
// function get_also_bought($goods_id) {

//     $dbview = RC_Loader::load_app_model('order_goods_two_goods_viewmodel');
//     $res = $dbview->join(array('order_goods' , 'goods'))->where(array('a.goods_id' => $goods_id , 'b.goods_id' => array('neq' => $goods_id) , 'g.is_on_sale' => 0 , 'g.is_alone_sale' => 1 , 'g.is_delete' => 0))->group('b.goods_id')->order(array('num' => 'desc'))->limit(ecjia::config('bought_goods'))->select();

//     $key = 0;
//     $arr = array();
//     foreach ($res as $row) {
//         $arr[$key]['goods_id'] = $row['goods_id'];
//         $arr[$key]['goods_name'] = $row['goods_name'];
//         $arr[$key]['short_name'] = $GLOBALS['_CFG']['goods_name_length'] > 0 ? RC_String::sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
//         $arr[$key]['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
//         $arr[$key]['goods_img'] = get_image_path($row['goods_id'], $row['goods_img']);
//         $arr[$key]['shop_price'] = price_format($row['shop_price']);
//         $arr[$key]['url'] = build_uri('goods', array(
//             'gid' => $row['goods_id']
//         ), $row['goods_name']);

//         if ($row['promote_price'] > 0) {
//             $arr[$key]['promote_price'] = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
//             $arr[$key]['formated_promote_price'] = price_format($arr[$key]['promote_price']);
//         } else {
//             $arr[$key]['promote_price'] = 0;
//         }

//         $key ++;
//     }

//     return $arr;
// }

// /**
//  * 获得指定商品的销售排名
//  *
//  * @access public
//  * @param integer $goods_id
//  * @return integer
//  */
// function get_goods_rank($goods_id) {
//     /* 统计时间段 */
//     $period = intval(ecjia::config('top10_time'));
//     if ($period == 1)     // 一年
//     {
//         $ext = " AND o.add_time > '" . RC_Time::local_strtotime('-1 years') . "'";
//     } elseif ($period == 2)     // 半年
//     {
//         $ext = " AND o.add_time > '" . RC_Time::local_strtotime('-6 months') . "'";
//     } elseif ($period == 3)     // 三个月
//     {
//         $ext = " AND o.add_time > '" . RC_Time::local_strtotime('-3 months') . "'";
//     } elseif ($period == 4)     // 一个月
//     {
//         $ext = " AND o.add_time > '" . RC_Time::local_strtotime('-1 months') . "'";
//     } else {
//         $ext = '';
//     }

//     /* 查询该商品销量 */
//     $dbview = RC_Loader::load_app_model('order_info_order_goods_viewmodel');
//     $sales_count = $dbview->join('order_goods')->field('IFNULL(SUM(g.goods_number), 0) as num')->find("o.order_status = '" . OS_CONFIRMED . "' AND o.shipping_status in (".SS_SHIPPED.",".SS_RECEIVED.") AND o.pay_status in (".PS_PAYED.",".PS_PAYING.") AND g.goods_id = '$goods_id' " .$ext);

//     if ($sales_count['num'] > 0) {
//         /* 只有在商品销售量大于0时才去计算该商品的排行 */
//         $res = $dbview->join('order_goods')->field('DISTINCT SUM(goods_number) AS num')->where("o.order_status = '" . OS_CONFIRMED . "' AND o.shipping_status in (".SS_SHIPPED.",".SS_RECEIVED.") AND o.pay_status in (".PS_PAYED.",".PS_PAYING.") AND g.goods_id = '$goods_id' " .$ext)->group('g.goods_id')->having('num >'. $sales_count['num'])->select();

//         $rank = count($res);
//         if ($rank > 10) {
//             $rank = 0;
//         }
//     } else {
//         $rank = 0;
//     }

//     return $rank;
// }

// /**
//  * 获得商品选定的属性的附加总价格
//  *
//  * @param integer $goods_id
//  * @param array $attr
//  *
//  * @return void
//  */
// function get_attr_amount($goods_id, $attr) {
//     $db_goods_attr = RC_Loader::load_app_model('goods_attr_model','goods');
//     return $db_goods_attr->where(array('goods_id' => $goods_id))->in(array('goods_attr_id' => $attr))->sum('attr_price');
// }
// /**
//  * 获得指定商品的关联文章
//  *
//  * @access public
//  * @param integer $goods_id
//  * @return void
//  */
// function get_linked_articles($goods_id) {

//     $dbview = RC_Loader::load_app_model('goods_article_article_viewmodel');
//     $res = $dbview->join('article')->where(array('g.goods_id' => $goods_id , 'a.is_open' => 1))->order(array('a.add_time' => 'desc'))->select();

//     $arr = array();
//     foreach ($res as $row) {
//         $row['url'] = $row['open_type'] != 1 ? build_uri('article', array(
//             'aid' => $row['article_id']
//         ), $row['title']) : trim($row['file_url']);
//         $row['add_time'] = RC_Time::local_date(ecjia::config('date_format'), $row['add_time']);
//         $row['short_title'] = ecjia::config('article_title_length') > 0 ? RC_String::sub_str($row['title'], ecjia::config('article_title_length')) : $row['title'];

//         $arr[] = $row;
//     }

//     return $arr;
// }
// end