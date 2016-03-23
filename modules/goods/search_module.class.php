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
    	
    	$where = array();
    	$where[] = "(CONCAT(shoprz_brandName,shopNameSuffix) like '%".$keywords."%' OR shop_class_keyWords like '%".$keywords."%')";
    	$where['ssi.status'] = 1;
    	$where['msi.merchants_audit'] = 1;
    	$count = $msi_dbview->join(array('seller_shopinfo'))->where($where)->count();
    	/* 获取留言的数量 */
    	$size = EM_Api::$pagination['count'];
    	$page = EM_Api::$pagination['page'];
    	
    	if ($count > 0) {
    		//加载分页类
    		RC_Loader::load_sys_class('ecjia_page', false);
    		//实例化分页
    		$page_row = new ecjia_page($count, $size, 6, '', $page);
    		 
    		$user_id = EM_Api::$session['uid'];
    		$user_id = empty($user_id) ? 0 : $user_id;
    		$field ='msi.user_id, CONCAT(shoprz_brandName,shopNameSuffix) as seller_name, c.cat_name, ssi.shop_logo, count(cs.ru_id) as follower, SUM(IF(cs.user_id = '.$user_id.',1,0)) as is_follower';
    		$result = $msi_dbview->join(array('category', 'seller_shopinfo', 'collect_store'))
					    		->field($field)
					    		->where($where)
					    		->limit($page_row->limit())
					    		->group('msi.shop_id')
					    		->select();
    		
    		$list = array();
    		if ( !empty ($result)) {
    			$goods_db = RC_Loader::load_app_model('goods_model', 'goods');
    			$warehouse_goodsdb = RC_Loader::load_app_model('warehouse_goods_model', 'seller');
    			$warehouse_areagoodsdb = RC_Loader::load_app_model('warehouse_area_goods_model', 'seller');
    			foreach ($result as $val) {
    				$where = array(
    						'user_id' => $val['user_id'],
    						'is_on_sale' => 1,
    						'is_alone_sale' => 1,
    						'is_delete' => 0,
    				);
    				if (ecjia::config('review_goods')) {
    					$where['review_status'] = array('gt' => 2);
    				}
    				$goods_count  = $goods_db->where($where)->count();
    				$goods_result = $goods_db->where($where)->limit(10)->order(array('sort_order' => 'asc', 'goods_id' => 'desc'))->select();
    				$goods_list = array();
    				if (!empty ($goods_result)) {
    					RC_Loader::load_app_func('goods', 'goods');
    					$mobilebuy_db = RC_Loader::load_app_model('goods_activity_model', 'goods');
    					foreach ($goods_result as $v) {
    						//仓库价格
    						if ($v['model_price'] == 1) {
    							$price = $warehouse_goodsdb->where(array('goods_id' => $v['goods_id']))->get_field('warehouse_price');
    							$v['shop_price'] = empty($price) ? $v['shop_price'] : $price;
    							$v['promote_price'] = $warehouse_goodsdb->where(array('goods_id' => $v['goods_id']))->get_field('warehouse_promote_price');
    						}
    						//区域价格
    						if ($v['model_price'] == 2) {
    							$price = $warehouse_areagoodsdb->where(array('goods_id' => $v['goods_id']))->get_field('region_price');
    							$v['shop_price'] = empty($price) ? $v['shop_price'] : $price;
    							$v['promote_price'] = $warehouse_areagoodsdb->where(array('goods_id' => $v['goods_id']))->get_field('region_promote_price');
    						}
    						/* 修正促销价格 */
    						if ($v ['promote_price'] > 0) {
    							$promote_price = bargain_price($v['promote_price'], $v['promote_start_date'], $v['promote_end_date']);
    						} else {
    							$promote_price = 0;
    						}
    						
    						$groupbuy = $mobilebuy_db->find(array(
    								'goods_id'	 => $v['goods_id'],
    								'start_time' => array('elt' => RC_Time::gmtime()),
    								'end_time'	 => array('egt' => RC_Time::gmtime()),
    								'act_type'	 => GAT_GROUP_BUY,
    						));
    						$mobilebuy = $mobilebuy_db->find(array(
    								'goods_id'	 => $v['goods_id'],
    								'start_time' => array('elt' => RC_Time::gmtime()),
    								'end_time'	 => array('egt' => RC_Time::gmtime()),
    								'act_type'	 => GAT_MOBILE_BUY,
    						));
    						/* 判断是否有促销价格*/
    						$price = ($v['shop_price'] > $promote_price && $promote_price > 0) ? $promote_price : $v['shop_price'];
    						$activity_type = ($v['shop_price'] > $promote_price && $promote_price > 0) ? 'PROMOTE_GOODS' : 'GENERAL_GOODS';
    						
    						$mobilebuy_price = $groupbuy_price = $object_id = 0;
    						if (!empty($mobilebuy)) {
    							$ext_info = unserialize($mobilebuy['ext_info']);
    							$mobilebuy_price = $ext_info['price'];
    							$price = $mobilebuy_price > $price ? $price : $mobilebuy_price;
    							$activity_type = $mobilebuy_price > $price ? $activity_type : 'MOBILEBUY_GOODS';
    							$object_id = $mobilebuy_price > $price ? $object_id : $mobilebuy['act_id'];
    						}
// 							if (!empty($groupbuy)) {
// 								$ext_info = unserialize($groupbuy['ext_info']);
// 								$price_ladder = $ext_info['price_ladder'];
// 								$groupbuy_price  = $price_ladder[0]['price'];
// 								$price = $groupbuy_price > $price ? $price : $groupbuy_price;
// 								$activity_type = $groupbuy_price > $price ? $activity_type : 'GROUPBUY_GOODS';
// 								$object_id = $mobilebuy_price > $price ? $object_id : $groupbuy['act_id'];
// 							}
    						/* 计算节约价格*/
    						$saving_price = ($v['shop_price'] - $price) > 0 ? $v['shop_price'] - $price : 0;
    						
    						$goods_list[] = array(
    								'id'			=> $v['goods_id'],
    								'name'			=> $v['goods_name'],
    								'market_price'	=> price_format($v['market_price']),
    								'shop_price'	=> price_format($v['shop_price']),
    								'promote_price' => ($price < $v['shop_price'] && $price > 0) ? price_format($price) : '',
    								'img' => array(
    										'thumb'	=> API_DATA('PHOTO', $v['goods_img']),
    										'url'	=> API_DATA('PHOTO', $v['original_img']),
    										'small'	=> API_DATA('PHOTO', $v['goods_thumb'])
    								),
    								'activity_type' => $activity_type,
    								'object_id'		=> $object_id,
    								'saving_price'	=> $saving_price,
    								'formatted_saving_price' => '已省'.$saving_price.'元'
    						);
    					}
    				}
    				if(substr($val['shop_logo'], 0, 1) == '.') {
    					$val['shop_logo'] = str_replace('../', '/', $val['shop_logo']);
    				}
    				$list[] = array(
    						'id'				=> $val['user_id'],
    						'seller_name'		=> $val['seller_name'],
    						'seller_category'	=> $val['cat_name'],
    						'seller_logo'		=> empty($val['shop_logo']) ? '' : RC_Upload::upload_url().'/'.$val['shop_logo'],
    						'seller_goods'		=> $goods_list,
    						'follower'			=> $val['follower'],
    						'is_follower'		=> $val['is_follower'],
    						'goods_count'		=> $goods_count,
    				);
    			}
    		}
    		$data = array();
    		$data['type'] = 'seller';
    		$data['result'] = $list;
    		$pager = array(
    				"total" => $page_row->total_records,
    				"count" => $page_row->total_records,
    				"more"	=> $page_row->total_pages <= $page ? 0 : 1,
    		);
    		
    		EM_Api::outPut($data, $pager);
    	} else {
    		$category = 0;
    		$sort_type = $order_by = array('sort_order' => 'asc', 'goods_id' => 'desc');
    		
    		RC_Loader::load_app_func('category', 'goods');
    		RC_Loader::load_app_func('goods', 'goods');
    		
    		$children = get_children($category);
    		$where = array();
    		$where = array(
    				'g.is_on_sale' => 1,
    				'g.is_alone_sale' => 1,
    				'g.is_delete' => 0,
    				"(".$children ." OR ".get_extension_goods($children).")",
    		);
    		if (ecjia::config('review_goods')) {
    			$where['g.review_status'] = array('gt' => 2);
    		}
    		if (! empty($keywords)) {
    			/* 记录搜索关键字  */
    			$db_keywords = RC_Loader::load_app_model('keywords_model', 'goods');
    			$keyword_where = array(
    					'date'		=>RC_Time::local_date('Y-m-d'),
    					'keyword'	=> $keywords
    			);
    			$count = $db_keywords->where($keyword_where)->count('*');
    			if ($count > 0) {
    				$db_keywords->where($keyword_where)->update(array('count' => $count + 1));
    			} else {
    				$data = array(
    						'date'			=> RC_Time::local_date('Y-m-d'),
    						'searchengine'	=> 'ecjia',
    						'count'			=> '1',
    						'keyword'		=> $keywords
    				);
    				$db_keywords->insert($data);
    			}
    				
    			$where[]= "(goods_name LIKE '%$keywords%' OR goods_sn LIKE '%$keywords%' OR keywords LIKE '%$keywords%')";
    		}
    		$goods_dbview = RC_Loader::load_app_model('goods_viewmodel', 'seller');
    		$count = $goods_dbview->join(null)->where($where)->count();
    		//如果为0直接返回
    		if ($count == 0) {
    			$pager = array(
    					"total" => 0,
    					"count" => 0,
    					"more" => 0,
    			);
    			EM_Api::outPut(array(), $pager);
    		}
    		
    		//加载分页类
    		RC_Loader::load_sys_class('ecjia_page', false);
    		//实例化分页
    		$page_row = new ecjia_page($count, $size, 6, '', $page);
    		
    		$field = "g.goods_id, g.goods_thumb, g.goods_img,g.original_img,g.goods_name,g.goods_brief, g.user_id, g.promote_start_date, g.promote_end_date, g.market_price, ";
    		$field .= "IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * '$_SESSION[discount]') AS shop_price, ";
    		$field .= "IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)) as promote_price";
    		
    		$goods_result = $goods_dbview->join(array('member_price', 'warehouse_goods', 'warehouse_area_goods'))
							    		->field($field)
							    		->where($where)
							    		->order($order_by)
							    		->limit($page_row->limit())
							    		->group('g.goods_id')
							    		->select();
    		$goods_list = array();
    		if (!empty($goods_result)) {
    			$mobilebuy_db = RC_Loader::load_app_model('goods_activity_model', 'goods');
    			foreach ($goods_result as $row) {
    				if ($row['promote_price'] > 0) {
    					$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
    				} else {
    					$promote_price = 0;
    				}
//     				$groupbuy = $mobilebuy_db->find(array(
//     						'goods_id'	 => $row['goods_id'],
//     						'start_time' => array('elt' => RC_Time::gmtime()),
//     						'end_time'	 => array('egt' => RC_Time::gmtime()),
//     						'act_type'	 => GAT_GROUP_BUY,
//     				));
    				$mobilebuy = $mobilebuy_db->find(array(
    						'goods_id'	 => $row['goods_id'],
    						'start_time' => array('elt' => RC_Time::gmtime()),
    						'end_time'	 => array('egt' => RC_Time::gmtime()),
    						'act_type'	 => GAT_MOBILE_BUY,
    				));
    				/* 判断是否有促销价格*/
    				$price = ($row['shop_price'] > $promote_price && $promote_price > 0) ? $promote_price : $row['shop_price'];
    				$activity_type = ($row['shop_price'] > $promote_price && $promote_price > 0) ? 'PROMOTE_GOODS' : 'GENERAL_GOODS';
    				
    				$mobilebuy_price = $groupbuy_price = $object_id = 0;
    				if (!empty($mobilebuy)) {
    					$ext_info = unserialize($mobilebuy['ext_info']);
    					$mobilebuy_price = $ext_info['price'];
    					$price = $mobilebuy_price > $price ? $price : $mobilebuy_price;
    					$activity_type = $mobilebuy_price > $price ? $activity_type : 'MOBILEBUY_GOODS';
    					$object_id = $mobilebuy_price > $price ? $object_id : $mobilebuy['act_id'];
    				}
//     					if (!empty($groupbuy)) {
//     						$ext_info = unserialize($groupbuy['ext_info']);
//     						$price_ladder = $ext_info['price_ladder'];
//     						$groupbuy_price  = $price_ladder[0]['price'];
//     						$price = $groupbuy_price > $price ? $price : $groupbuy_price;
//     						$activity_type = $groupbuy_price > $price ? $activity_type : 'GROUPBUY_GOODS';
//     						$object_id = $mobilebuy_price > $price ? $object_id : $groupbuy['act_id'];
//     					}
    				/* 计算节约价格*/
    				$saving_price = ($row['shop_price'] - $price) > 0 ? $row['shop_price'] - $price : 0;
    				
    				$goods_list[] = array(
    						'id'			=> $row['goods_id'],
    						'name'			=> $row['goods_name'],
    						'brief'			=> $row['goods_brief'],
    						'market_price'	=> price_format($row['market_price']),
    						'shop_price'	=> price_format($row['shop_price']),
    						'promote_price'	=> ($price < $row['shop_price'] && $price > 0) ? price_format($price) : '',
    						'img' => array(
    								'thumb' => get_image_path($row['goods_id'], $row['goods_img']),
    								'url'	=> get_image_path($row['goods_id'], $row['original_img'], true),
    								'small'	=> get_image_path($row['goods_id'], $row['goods_thumb'], true),
    						),
    						'activity_type' => $activity_type,
    						'object_id'		=> $object_id,
    						'saving_price'	=> $saving_price,
    						'formatted_saving_price' => '已省'.$saving_price.'元'
    				);
    		
    			}
    		}
    		
    		$data = array();
    		$data['type'] = 'goods';
    		$data['result'] = $goods_list;
    		$pager = array(
    				"total" => $page_row->total_records,
    				"count" => $page_row->total_records,
    				"more" => $page_row->total_pages <= $page ? 0 : 1,
    		);
    		
    		EM_Api::outPut($data, $pager);
    	}
    	
    	
    	
    	
    	
    }
}


// end