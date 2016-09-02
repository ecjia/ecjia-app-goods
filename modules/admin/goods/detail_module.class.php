<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 商品详情
 * @author luchongchong
 *
 */
class detail_module extends api_admin implements api_interface {
    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {
    		
		$this->authadminSession();
		$ecjia = RC_Loader::load_app_class('api_admin', 'api');

		$result = $ecjia->admin_priv('goods_manage');
		if (is_ecjia_error($result)) {
			EM_Api::outPut($result);
		}
		
		$id = _POST('goods_id');
		if (empty($id)) {
			EM_Api::outPut(101);
		}
		
// 		$db_goods = RC_Loader::load_app_model('goods_model', 'goods');
		$db_goods = RC_Model::model('goods/goods_model');
		$where = array('goods_id' => $id, 'is_delete' => 0);
// 		if ($_SESSION['ru_id'] > 0) {
// 			$where = array_merge($where, array('user_id' => $_SESSION['ru_id']));
// 		}
		if ($_SESSION['seller_id'] > 0) {
			$where = array_merge($where, array('seller_id' => $_SESSION['seller_id']));
		}
		$row = $db_goods->find($where);
		if (empty($row)) {
			EM_Api::outPut(13);
		} else {
			$brand_db		= RC_Model::model('goods/brand_model');
			$category_db	= RC_Model::model('goods/category_model');
// 			$brand_db = RC_Loader::load_app_model('brand_model', 'goods');
// 			$category_db = RC_Loader::load_app_model('category_model', 'goods');
			
			$brand_name = $row['brand_id'] > 0 ? $brand_db->where(array('brand_id' => $row['brand_id']))->get_field('brand_name') : '';
			$category_name = $category_db->where(array('cat_id' => $row['cat_id']))->get_field('cat_name');
			
			if (ecjia::config('shop_touch_url', ecjia::CONFIG_EXISTS)) {
				$goods_desc_url = ecjia::config('shop_touch_url').'index.php?m=goods&c=index&a=init&id='.$id.'&hidenav=1&hidetab=1';
			} else {
				$goods_desc_url = null;
			}
			if ($row['promote_price'] > 0) {
				$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
			} else {
				$promote_price = 0;
			}
			$goods_detail = array(
					'goods_id'	=> $row['goods_id'],
					'name'		=> $row['goods_name'],
					'goods_sn'	=> $row['goods_sn'],
					'brand_name' 	=> $brand_name,
					'category_name' => $category_name,
					'market_price'	=> price_format($row['market_price'] , false),
					'shop_price'	=> price_format($row['shop_price'] , false),
					'promote_price'	=> $promote_price > 0 ? price_format($promote_price , false) : $promote_price,
					'promote_start_date'	=> RC_Time::local_date('Y-m-d H:i:s', $row['promote_start_date']),
					'promote_end_date'		=> RC_Time::local_date('Y-m-d H:i:s', $row['promote_end_date']),
					'clicks'		=> intval($row['click_count']),
					'stock'			=> (ecjia::config('use_storage') == 1) ? $row['goods_number'] : '',
					'goods_weight'	=> $row['goods_weight']  = (intval($row['goods_weight']) > 0) ?
										$row['goods_weight'] . __('千克') :
										($row['goods_weight'] * 1000) . __('克'),
					'is_promote'	=> $row['is_promote'] == 1 ? true : false, 
					'is_best'		=> $row['is_best'] == 1 ? true : false,
					'is_new'		=> $row['is_new'] == 1 ? true : false,
					'is_hot'		=> $row['is_hot'] == 1 ? true : false,
					'is_shipping'	=> $row['is_shipping'] == 1 ? true : false,
					'is_on_sale'	=> $row['is_on_sale'] == 1 ? true : false,
					'is_alone_sale' => $row['is_alone_sale'] == 1 ? true : false,
					'last_updatetime' => RC_Time::local_date(ecjia::config('time_format'), $row['last_update']),
					'goods_desc' 	=> $goods_desc_url,
					'img' => array(
							'thumb'	=> !empty($row['goods_img']) ? RC_Upload::upload_url($row['goods_img']) : '',
							'url'	=> !empty($row['original_img']) ? RC_Upload::upload_url($row['original_img']) : '',
							'small'	=> !empty($row['goods_thumb']) ? RC_Upload::upload_url($row['goods_thumb']) : '',
					),
					'unformatted_shop_price'	=> $row['shop_price'],
					'unformatted_market_price'	=> $row['market_price'],
					'unformatted_promote_price'	=> $row['promote_price'],
					'give_integral'				=> $row['give_integral'],
					'rank_integral'				=> $row['rank_integral'],
					'integral'					=> $row['integral'],
			);

			RC_Loader::load_app_func('system_goods', 'goods');
			RC_Loader::load_app_func('common', 'goods');
			
			$goods_detail['user_rank'] = array();
			
			$discount_price = get_member_price_list($id);
			$user_rank = get_user_rank_list();
		    if(!empty($user_rank)){
		    	foreach ($user_rank as $key=>$value){
				    		$goods_detail['user_rank'][]=array(
				    				'rank_id'	 => $value['rank_id'],
				    				'rank_name'	 => $value['rank_name'],
				    				'discount'	 => $value['discount'],
				    			    'price'		 => !empty($discount_price[$value['rank_id']])?$discount_price[$value['rank_id']]:'-1',
				    		);
		    	}
		    }
		    $goods_detail['volume_number'] = array();
		    $volume_number = '';
		    $volume_number = get_volume_price_list($id);

		    if(!empty($volume_number)) {
		    	foreach ($volume_number as $key=>$value) {
		    		$goods_detail['volume_number'][] =array(
		    			   'number'	=> $value['number'],
		    			   'price'	=> $value['price']	
		    		);
		    	}
		    }
			
			EM_Api::outPut($goods_detail);
		}
		
	}
	
}
/**
 * 判断某个商品是否正在特价促销期
 *
 * @access public
 * @param float $price
 *        	促销价格
 * @param string $start
 *        	促销开始日期
 * @param string $end
 *        	促销结束日期
 * @return float 如果还在促销期则返回促销价，否则返回0
 */
function bargain_price($price, $start, $end) {
	if ($price == 0) {
		return 0;
	} else {
		$time = RC_Time::gmtime ();
		if ($time >= $start && $time <= $end) {
			return $price;
		} else {
			return 0;
		}
	}
}

// end