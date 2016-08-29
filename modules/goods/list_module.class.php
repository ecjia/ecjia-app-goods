<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 商品分类列表及关键词搜索
 * @author royalwang
 *
 */
class list_module implements ecjia_interface {

    public function run(ecjia_api & $api) {
    	
    	//如果用户登录获取其session
    	EM_Api::authSession(false);
    	RC_Loader::load_app_func('main', 'api');
    	
    	$location = _POST('location');
    	
    	/* 筛选条件*/
		$filter = _POST('filter', array());
        $keyword = isset($filter['keywords']) ? RC_String::unicode2string($filter['keywords']): '';
        $keyword = ! empty($keyword) ? htmlspecialchars(trim($keyword)) : '';
        
        $category = ! empty($filter['category_id']) ? intval($filter['category_id']) : 0;
        $brand = ! empty($filter['brand_id']) ? intval($filter['brand_id']) : 0;
        /* 价格*/
        $filter['min_price'] = ! empty($filter['price_range']['price_min']) ? intval($filter['price_range']['price_min']) : 0;
        $filter['max_price'] = ! empty($filter['price_range']['price_max']) ? intval($filter['price_range']['price_max']) : 0;
        $min_price = $filter['min_price'] != 0 ? $filter['min_price'] : '';
        $max_price = $filter['max_price'] != 0 || $filter['min_price'] < 0 ? $filter['max_price'] : '';
        /* 属性筛选*/
        $filter_attr = empty($filter['filter_attr']) ? '' : explode('.', $filter['filter_attr']);
        
        /* 排序*/
        $sort_by = !empty($filter['sort_by']) ? $filter['sort_by'] : '';
        if ($sort_by == 'is_hot') {
        	$order_sort = array('is_hot' => 'DESC', 'sort_order' => 'ASC', 'goods_id' => 'DESC');
        } elseif ($sort_by == 'price_desc') {
        	$order_sort = array('org_price' => 'DESC', 'sort_order' => 'ASC', 'goods_id' => 'DESC');
        } elseif ($sort_by == 'price_asc') {
        	$order_sort = array('org_price' => 'ASC', 'sort_order' => 'ASC', 'goods_id' => 'DESC');
        } elseif ($sort_by == 'is_new') {
        	$order_sort = array('is_new' => 'DESC', 'sort_order' => 'ASC', 'goods_id' => 'DESC');
		} else {
        	$order_sort = array('goods_id' => 'DESC');
        }

		$size = EM_Api::$pagination['count'];
		$page = EM_Api::$pagination['page'];
		
       	$options = array(
       			'cat_id'	=> $category,
       			'brand'		=> $brand,
       			'keywords'	=> $keyword,
       			'min'		=> $min_price,
       			'max'		=> $max_price,
       			'sort'		=> $order_sort,
       			'page'		=> $page,
       			'size'		=> $size,
       			'filter_attr' => $filter_attr,
       			'location'	=> $location,
       	);
       	/*经纬度为空判断*/
       	if (!is_array($location) || empty($location['longitude']) || empty($location['latitude'])) {
       		$data = array();
       		$data['list'] = array();
       		$data['pager'] = array(
       				"total" => '0',
       				"count" => '0',
       				"more"	=> '0'
       		);
       		EM_Api::outPut($data['list'], $data['pager']);
       	}
       	
		$filter = empty($filter['filter_attr']) ? '' : $filter['filter_attr'];
       	$cache_id = sprintf('%X', crc32($category . '-' . $sort_by  .'-' . $page . '-' . $size . '-' . $_SESSION['user_rank']. '-' .
       			ecjia::config('lang') .'-'. $brand. '-'. $keyword. '-' . $max_price . '-' .$min_price . '-' . $filter ));
       	
       	$cache_key = 'api_goods_list_'.$category.'_'.$cache_id;
       	$data = RC_Cache::app_cache_get($cache_key, 'goods');
       	
       	if (empty($data)) {
			$result = RC_Api::api('goods', 'goods_list', $options);
			$data = array();
	        $data['pager'] = array(
					"total" => $result['page']->total_records,
					"count" => $result['page']->total_records,
					"more" => $result['page']->total_pages <= $page ? 0 : 1,
			);
	        $data['list'] = array();
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
					
					$data['list'][] = array(
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
			RC_Cache::app_cache_set($cache_key, $data, 'goods');
       	} else {
       		if (!empty($options['keywords'])) {
       			RC_Loader::load_app_class('goods_list', 'goods', false);
       			goods_list::get_keywords_where($options['keywords']);
       		}
       	}
		EM_Api::outPut($data['list'], $data['pager']);
        
    }
}


// end