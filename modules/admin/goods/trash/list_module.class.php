<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 商品回收站列表
 * @author will
 *
 */
class list_module implements ecjia_interface {
	
	public function run(ecjia_api & $api) {
		
		$ecjia = RC_Loader::load_app_class('api_admin', 'api');
		$ecjia->authadminSession();
		$result = $ecjia->admin_priv('goods_manage');
		if (is_ecjia_error($result)) {
			EM_Api::outPut($result);
		}
		
		$keywords	= _POST('keywords');
		
		$size = EM_Api::$pagination['count'];
		$page = EM_Api::$pagination['page'];
		
		$where = array(
			'is_delete' => 1,
		);
		if ($_SESSION['ru_id'] > 0) {
			$where = array_merge($where, array('user_id' => $_SESSION['ru_id']));
		}
		
		if ( !empty($keywords)) {
			$where[] = "( goods_name like '%".$keywords."%' or goods_sn like '%".$keywords."%' )"; 
		}
		
		$db = RC_Loader::load_app_model('goods_model', 'goods');
		
		/* 获取记录条数 */
		$record_count = $db->where($where)->count();
			
		//加载分页类
		RC_Loader::load_sys_class('ecjia_page', false);
		//实例化分页
		$page_row = new ecjia_page($record_count, $size, 6, '', $page);
		$today = RC_Time::gmtime();
		$field = "*, (promote_price > 0 AND promote_start_date <= ' . $today . ' AND promote_end_date >= ' . $today . ')|is_promote";
		$data = $db->field($field)->where($where)->order(array('goods_id' => 'desc'))->limit($page_row->limit())->select();
		
		$goods_list = array();
		if (!empty($data)) {
			RC_Loader::load_app_func('goods', 'goods');
			RC_Loader::load_sys_func('global');
			foreach ($data as $key => $val) {
				$goods_list[] = array(
						'goods_id'	=> intval($val['goods_id']),
						'name'		=> $val['goods_name'],
						'goods_sn'	=> $val['goods_sn'],
						'market_price'	=> price_format($val['market_price'] , false),
						'shop_price'	=> price_format($val['shop_price'] , false),
						'is_promote'	=> $val['is_promote'],
						'promote_price'	=> price_format($val['promote_price'] , false),
						'promote_start_date'	=> intval($val['promote_start_date']),
						'promote_end_date'		=> intval($val['promote_end_date']),
						'formatted_promote_start_date'	=> !empty($val['promote_start_date']) ? RC_Time::local_date('Y-m-d H:i:s', $val['promote_start_date']) : '',
						'formatted_promote_end_date'	=> !empty($val['promote_end_date']) ? RC_Time::local_date('Y-m-d H:i:s', $val['promote_end_date']) : '',
						'clicks'		=> intval($val['click_count']),
						'stock'			=> (ecjia::config('use_storage') == 1) ? $val['goods_number'] : '',
						'goods_weight'	=> $val['goods_weight']  = (intval($val['goods_weight']) > 0) ?
											$val['goods_weight'] . __('千克') :
											($val['goods_weight'] * 1000) . __('克'),
						'is_best'		=> $val['is_best'],
						'is_new'		=> $val['is_new'],
						'is_hot'		=> $val['is_hot'],
						'is_shipping'	=> $val['is_shipping'],
						'last_updatetime' => RC_Time::local_date(ecjia::config('time_format'), $val['last_update']),
						'sales_volume'	=> $val['sales_volume'],
						'img' => array(
								'thumb'	=> !empty($val['goods_img']) ? RC_Upload::upload_url($val['goods_img']) : '',
								'url'	=> !empty($val['original_img']) ? RC_Upload::upload_url($val['original_img']) : '',
								'small'	=> !empty($val['goods_thumb']) ? RC_Upload::upload_url($val['goods_thumb']) : '',
						)
					
				);
			}
		}
		
		$pager = array(
				"total" => $page_row->total_records,
				"count" => $page_row->total_records,
				"more" => $page_row->total_pages <= $page ? 0 : 1,
		);
		
		EM_Api::outPut($goods_list , $pager);
	}
	
	
}


// end