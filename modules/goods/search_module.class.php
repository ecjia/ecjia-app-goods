<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 商品店铺搜索
 * @author will.chen
 *
 */
class search_module implements ecjia_interface {

    public function run(ecjia_api & $api) {
    	$msi_dbview = RC_Loader::load_app_model('merchants_shop_information_viewmodel', 'seller');
    	$keywords = _POST('keywords');
    	$location = _POST('location');
    	
    	$size = EM_Api::$pagination['count'];
    	$page = EM_Api::$pagination['page'];
    	$options = array(
    			'location'		=> $location,
    			'keywords'		=> $keywords,
    			'size'			=> $size,
    			'page'			=> $page,
    	);
    	
    	$result = RC_Api::api('seller', 'seller_list', $options);
    	if (!empty($result['seller_list'])) {
    		$db_goods_view = RC_Loader::load_app_model('comment_viewmodel', 'comment');
    		$max_goods = 0;
    		$mobilebuy_db = RC_Loader::load_app_model('goods_activity_model', 'goods');
    		$db_favourable = RC_Model::model('favourable/favourable_activity_model');
    			
    		/* 手机专享*/
    		$result_mobilebuy = ecjia_app::validate_application('mobilebuy');
    		$is_active = ecjia_app::is_active('ecjia.mobilebuy');
    		$seller_list = array();
    		foreach ($result['seller_list'] as $row) {
    			$field = 'count(*) as count, SUM(comment_rank) as comment_rank';
    			$comment = $db_goods_view->join(null)->field($field)->where(array('ru_id' => $row['id'], 'parent_id' => 0, 'status' => 1))->find();
    		
    			$favourable_result = $db_favourable->where(array('user_id' => $row['id'], 'start_time' => array('elt' => RC_Time::gmtime()), 'end_time' => array('egt' => RC_Time::gmtime()), 'act_type' => array('neq' => 0)))->select();
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
    		
    		
    			$goods_options = array('page' => 1, 'size' => 3, 'user_id' => $row['id']);
    			if (!empty($goods_category)) {
    				$goods_options['cat_id'] = $goods_category;
    			}
    			$goods_result = RC_Api::api('goods', 'goods_list', $goods_options);
    			$goods_list = array();
    			if (!empty($goods_result['list'])) {
    				foreach ($goods_result['list'] as $val) {
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
    		
    					$goods_list[] = array(
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
    					);
    				}
    			}
    			if ($goods_result['page']->total_records >= $max_goods) {
    				array_unshift($seller_list, array(
    				'id'				=> $row['id'],
    				'seller_name'		=> $row['seller_name'],
    				'seller_category'	=> $row['seller_category'],
    				'seller_logo'		=> $row['seller_logo'],
    				'seller_goods'		=> $goods_list,
    				'follower'			=> $row['follower'],
    				'is_follower'		=> $row['is_follower'],
    				'goods_count'		=> $goods_result['page']->total_records,
    				'comment'			=> $comment['count'] > 0 ? round($comment['comment_rank']/($comment['count']*5)*100).'%' : '100%',
    				'favourable_list'	=> $favourable_list,
    				));
    			} else {
    				$seller_list[] = array(
    						'id'				=> $row['id'],
    						'seller_name'		=> $row['seller_name'],
    						'seller_category'	=> $row['seller_category'],
    						'seller_logo'		=> $row['seller_logo'],
    						'seller_goods'		=> $goods_list,
    						'follower'			=> $row['follower'],
    						'is_follower'		=> $row['is_follower'],
    						'goods_count'		=> $goods_result['page']->total_records,
    						'comment'			=> $comment['count'] > 0 ? round($comment['comment_rank']/($comment['count']*5)*100).'%' : '100%',
    						'favourable_list'	=> $favourable_list,
    				);
    			}
    		}
    		
    		$data = array();
    		$data['type'] = 'seller';
    		$data['result'] = $seller_list;
    		$page = array(
				'total'	=> $result['page']->total_records,
				'count'	=> $result['page']->total_records,
				'more'	=> $result['page']->total_pages <= $page ? 0 : 1,
			);
    		
    		EM_Api::outPut($data, $pager);
    	} else {
    		$options = array(
    				'keywords'	=> $keywords,
    				'sort'		=> array('sort_order' => 'ASC', 'goods_id' => 'DESC'),
    				'page'		=> $page,
    				'size'		=> $size,
    				'location'	=> $location,
    		);
    		$result = RC_Api::api('goods', 'goods_list', $options);
    		$goods_list = array();
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
    					
    				$goods_list[] = array(
    						'id'		=> $val['goods_id'],
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
    		
    		$data = array();
    		$data['type'] = 'goods';
    		$data['result'] = $goods_list;
    		$data['pager'] = array(
    				"total" => $result['page']->total_records,
    				"count" => $result['page']->total_records,
    				"more"	=> $result['page']->total_pages <= $page ? 0 : 1,
    		);
    		
    		EM_Api::outPut($data, $pager);
    	}
    	
    	
    	
    	
    	
    }
}


// end