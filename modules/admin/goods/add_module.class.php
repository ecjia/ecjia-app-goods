<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 添加单个商品基本信息
 * @author chenzhejun@ecmoban.com
 *
 */
class add_module implements ecjia_interface
{
 	
    public function run(ecjia_api & $api)
    {  	
    	$ecjia = RC_Loader::load_app_class('api_admin', 'api');
    	$ecjia->authadminSession();
    	$result = $ecjia->admin_priv('goods_manage');
    	if (is_ecjia_error($result)) {
    		EM_Api::outPut($result);
    	}
    	
    	$goods_name		= _POST('goods_name');
    	if (empty($goods_name)) {
    	    return new ecjia_error('goods_name_empty', '请输入商品名称');
    	}
    	$category_id	= _POST('category_id', 0);
    	$merchant_category_id = _POST('merchant_category', 0);
    	$goods_price	= _POST('goods_price', 0.00);
    	$stock			= _POST('stock', 0);
    	
    	if (isset($_SESSION['ru_id']) && $_SESSION['ru_id']) {
    		$review_status = RC_Model::model('seller/merchants_shop_information_model')->where(array('user_id' => $_SESSION['ru_id']))->get_field('review_goods');
    		if ($review_status == 0) {
    			$review_status = 5;
    		} else {
    			$review_status = 0;
    		}
    	} else {
    		$review_status = 5;
    	}
    	RC_Loader::load_app_func('system_goods', 'goods');
    	/* 如果没有输入商品货号则自动生成一个商品货号 */
    	
    	$max_id = RC_Model::model('goods/goods_model')->field('MAX(goods_id) + 1|max')->find();
    	if (empty($max_id['max'])) {
    		$goods_sn_bool = true;
    		$goods_sn = '';
    	} else {
    		$goods_sn = generate_goods_sn($max_id['max']);
    	}
    	
    	/*新增商品信息入库*/
    	$goods_id = RC_Model::model('goods/goods_model')->insert(array(
					    	'goods_name'         => $goods_name,
					    	'user_id'            => isset($_SESSION['ru_id']) ? $_SESSION['ru_id'] : 0,
					    	'goods_sn'           => $goods_sn,
					    	'cat_id'             => $category_id,
    						'user_cat'			 => $merchant_category_id,
					    	'shop_price'         => $goods_price,
					    	'market_price'       => $goods_price * 1.1,
					    	'goods_number'       => $stock,
					    	'integral'           => 0,
					    	'give_integral'      => 0,
    						'rank_integral'      => 0,
					    	'is_real'            => 1,
					    	'is_on_sale'         => 0,
					    	'is_alone_sale'      => 1,
					    	'is_shipping'        => 0,
					    	'add_time'           => RC_Time::gmtime(),
					    	'last_update'        => RC_Time::gmtime(),
    	));
    	
    	if ($goods_sn_bool) {
    		$goods_sn = generate_goods_sn($goods_id);
    		RC_Model::model('goods/goods_model')->where(array('goods_id' => $goods_id))->update(array('goods_sn' => $goods_sn));
    	}
    	
    	
    	RC_Loader::load_app_class('goods_image', 'goods', false);
    	
    	/* 处理商品图片 */
    	$goods_img		= ''; // 初始化商品图片
    	$goods_thumb	= ''; // 初始化商品缩略图
    	$img_original	= ''; // 初始化原始图片
    	
    	$upload = RC_Upload::uploader('image', array('save_path' => 'images', 'auto_sub_dirs' => true));
    	
    	/* 是否处理商品图 */
    	$proc_goods_img = true;
    	if (isset($_FILES['goods_image']) && !$upload->check_upload_file($_FILES['goods_image'])) {
    		$proc_goods_img = false;
    	}
    	/* 是否处理缩略图 */
    	$proc_thumb_img = true;
    	
    	if ($proc_goods_img) {
    		if (isset($_FILES['goods_image'])) {
    			$image_info = $upload->upload($_FILES['goods_image']);	
    		}	 
    	}
    	
    	/* 更新上传后的商品图片 */
    	if ($proc_goods_img) {
    		if (isset($image_info)) {
    			$goods_image = new goods_image($image_info);
    			if ($proc_thumb_img) {
    				$goods_image->set_auto_thumb(false);
    			}
    			$result = $goods_image->update_goods($goods_id);
    		}
    	}
    	
    	/* 更新上传后的缩略图片 */
    	if ($proc_thumb_img) {
    		if (isset($image_info)) {
    			$thumb_image = new goods_image($image_info);
    			$result = $thumb_image->update_thumb($goods_id);
    		}
    	}
    	
    	/* 记录日志 */
    	ecjia_admin::admin_log($goods_name, 'add', 'goods');
		
    	$today = RC_Time::gmtime();
    	$field = '*, (promote_price > 0 AND promote_start_date <= ' . $today . ' AND promote_end_date >= ' . $today . ') AS is_promote';
    	$row = RC_Model::model('goods/goods_model')->field($field)->find(array('goods_id' => $goods_id));
    	
    	RC_Loader::load_app_func('category', 'goods');
    	$brand_db = RC_Loader::load_app_model('brand_model', 'goods');
    	$category_db = RC_Loader::load_app_model('category_model', 'goods');
    		
    	$brand_name = $row['brand_id'] > 0 ? $brand_db->where(array('brand_id' => $row['brand_id']))->get_field('brand_name') : '';
    	$category_name = $category_db->where(array('cat_id' => $row['cat_id']))->get_field('cat_name');
    	$merchant_category = RC_Model::model('goods/merchants_category_model')->where(array('cat_id' => $row['user_cat']))->get_field('cat_name');
    		
    	if (ecjia::config('shop_touch_url', ecjia::CONFIG_EXISTS)) {
    		$goods_desc_url = ecjia::config('shop_touch_url').'index.php?m=goods&c=index&a=init&id='.$id.'&hidenav=1&hidetab=1';
    	} else {
    		$goods_desc_url = null;
    	}
    	
    	$goods_detail = array(
    			'goods_id'	=> $row['goods_id'],
    			'name'		=> $row['goods_name'],
    			'goods_sn'	=> $row['goods_sn'],
    			'brand_name' 	=> $brand_name,
        	    'category_id'	=> $row['cat_id'],
        	    'category_name' => $category_name,
        	    'category' => get_parent_cats($row['cat_id']),
        	    'merchant_category_id'		=> empty($row['user_cat']) ? 0 : $row['user_cat'],
        	    'merchant_category_name'	=> empty($merchant_category) ? '' : $merchant_category,
        	    'merchant_category' => get_parent_cats($row['user_cat'], 1, $_SESSION['ru_id']),
    			'market_price'	=> price_format($row['market_price'] , false),
    			'shop_price'	=> price_format($row['shop_price'] , false),
    			'is_promote'	=> $row['is_promote'],
    			'promote_price'	=> price_format($row['promote_price'], false),
    			'promote_start_date'	=> !empty($row['promote_start_date']) ? RC_Time::local_date('Y-m-d H:i:s', $row['promote_start_date']) : '',
    			'promote_end_date'		=> !empty($row['promote_end_date']) ? RC_Time::local_date('Y-m-d H:i:s', $row['promote_end_date']) : '',
    			'clicks'		=> intval($row['click_count']),
    			'stock'			=> (ecjia::config('use_storage') == 1) ? $row['goods_number'] : '',
    			'goods_weight'	=> $row['goods_weight']  = (intval($row['goods_weight']) > 0) ?
    			$row['goods_weight'] . __('千克') :
    			($row['goods_weight'] * 1000) . __('克'),
    			'is_promote'	=> $row['is_promote'],
    			'is_best'		=> $row['is_best'],
    			'is_new'		=> $row['is_new'],
    			'is_hot'		=> $row['is_hot'],
    			'is_shipping'	=> $row['is_shipping'],
    			'is_on_sale'	=> $row['is_on_sale'],
    			'is_alone_sale' => $row['is_alone_sale'],
    			'last_updatetime' => RC_Time::local_date(ecjia::config('time_format'), $row['last_update']),
    			'goods_desc' 	=> '',
    				
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
    			'sales_volume'				=> $row['sales_volume'],
    	);
    	
    	RC_Loader::load_app_func('common', 'goods');
    		
    	$goods_detail['user_rank'] = array();
    		
    	$discount_price = get_member_price_list($goods_id);
    	$user_rank = get_user_rank_list();
    	if(!empty($user_rank)){
    		foreach ($user_rank as $key => $value){
    			$goods_detail['user_rank'][] = array(
    					'rank_id'	 => $value['rank_id'],
    					'rank_name'	 => $value['rank_name'],
    					'discount'	 => $value['discount'],
    					'price'		 => !empty($discount_price[$value['rank_id']]) ? $discount_price[$value['rank_id']] : '-1',
    			);
    		}
    	}
    	$goods_detail['volume_number'] = array();
    	$volume_number = '';
    	$volume_number = get_volume_price_list($goods_id);
    	
    	if(!empty($volume_number)) {
    		foreach ($volume_number as $key=>$value) {
    			$goods_detail['volume_number'][] =array(
    					'number'	=> $value['number'],
    					'price'		=> $value['price']
    			);
    		}
    	}
    	
    	$goods_detail['pictures'] = array();
    	
    	
    	return $goods_detail;
    	
    }
    	 
    
}