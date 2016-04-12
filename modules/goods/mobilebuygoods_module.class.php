<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 手机专享商品列表
 * @author will.chen
 *
 */
class mobilebuygoods_module implements ecjia_interface {

    public function run(ecjia_api & $api) {
    	$mobilebuywhere['act_type']		= GAT_MOBILE_BUY;
    	$mobilebuywhere['start_time']	= array('elt' => RC_Time::gmtime());
    	$mobilebuywhere['end_time']		= array('egt' => RC_Time::gmtime());
    	$mobilebuywhere[] = 'g.goods_id is not null';
    	$mobilebuywhere['g.is_delete'] = '0';
    	$mobilebuywhere['g.is_on_sale'] = 1;
    	$mobilebuywhere['g.is_alone_sale'] = 1;
    	if (ecjia::config('review_goods')) {
    		$mobilebuywhere['g.review_status'] = array('gt' => 2);
    	}
    	
    	$location = _POST('location');
    	if (is_array($location) && isset($location['latitude']) && isset($location['longitude'])) {
    		$request = array('location' => $location);
    		$geohash = RC_Loader::load_app_class('geohash', 'shipping');
    		$where_geohash = $geohash->encode($location['latitude'] , $location['longitude']);
    		$where_geohash = substr($where_geohash, 0, 5);
    		 
    		$seller_shopinfo_db = RC_Loader::load_app_model('seller_shopinfo_model', 'seller');
    		$ru_id = $seller_shopinfo_db->where(array('geohash' => array('like' => "%$where_geohash%")))->get_field('ru_id', true);
    		 
    		$mobilebuywhere['g.user_id'] = $ru_id;
    	}
    	
    	$db_goods_activity = RC_Loader::load_app_model('goods_activity_viewmodel', 'groupbuy');
    	
    	$count = $db_goods_activity->join(array('goods'))->where($mobilebuywhere)->count();
    	
		/* 查询总数为0时直接返回  */
		if ($count == 0) {
			$pager = array(
					'total' => 0,
					'count' => 0,
					'more'	=> 0,
			);
			EM_Api::outPut(array(), $pager);
		}
		
		/* 获取数量 */
    	$size = EM_Api::$pagination['count'];
    	$page = EM_Api::$pagination['page'];
    	
    	//加载分页类
    	RC_Loader::load_sys_class('ecjia_page', false);
    	//实例化分页
    	$page_row = new ecjia_page($count, $size, 6, '', $page);
    	
    	$res = $db_goods_activity->field('ga.act_id, ga.goods_id, ga.goods_name, ga.start_time, ga.end_time, ext_info, shop_price, market_price, goods_brief, goods_thumb, goods_img, original_img')->join(array('goods'))->where($mobilebuywhere)->order(array('act_id' => 'DESC'))->limit($page_row->limit())->select();
    	
    	$list = array();
    	if (!empty($res)) {
    		foreach ($res as $val) {
    			$ext_info = unserialize($val['ext_info']);
    			$price  = $ext_info['price'];;    		// 初始化
    			/* 计算节约价格*/
    			$saving_price = ($val['shop_price'] - $price) > 0 ? $val['shop_price'] - $price : 0;
    			$list[] = array(
    					'id'	=> $val['goods_id'],
    					'name'	=> $val['goods_name'],
    					'market_price'	=> price_format($val['market_price']),
    					'shop_price'	=> price_format($val['shop_price']),
    					'promote_price'	=> price_format($price),
    					'promote_start_date'	=> RC_Time::local_date('Y/m/d H:i:s', $val['start_time']),
    					'promote_end_date'		=> RC_Time::local_date('Y/m/d H:i:s', $val['end_time']),
    					'brief' => $val['goods_brief'],
    					'img'	=> array(
    							'small'	=> RC_Upload::upload_url(). '/' .$val['goods_thumb'],
    							'thumb'	=> RC_Upload::upload_url(). '/' .$val['goods_img'],
    							'url'	=> RC_Upload::upload_url(). '/' .$val['original_img']
    					),
    					'activity_type' => 'MOBILEBUY_GOODS',
    					'saving_price'	=> $saving_price,
    					'formatted_saving_price' => '已省'.$saving_price.'元',
    					'object_id'	=> $val['act_id'],
    			);
    		}
    	}
    	
    	
    	$pager = array(
    			"total" => $page_row->total_records,
    			"count" => $page_row->total_records,
    			"more"	=> $page_row->total_pages <= $page ? 0 : 1,
    	);
    	
    	EM_Api::outPut($list, $pager);
    	
    }
}


// end