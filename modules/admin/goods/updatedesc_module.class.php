<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 编辑商品详情
 *
 */
class updatedesc_module implements ecjia_interface
{
	private $db_goods;
	
    public function run(ecjia_api & $api)
    { 
    	$ecjia = RC_Loader::load_app_class('api_admin', 'api');
    	$ecjia->authadminSession();
    	$result = $ecjia->admin_priv('goods_manage');
    	if (is_ecjia_error($result)) {
    		EM_Api::outPut($result);
    	}
		//请求参数：
       	$goods_id				= _POST('goods_id', 0);
       	$goods_desc				= _POST('goods_desc', '');
    	if (empty($goods_id) || empty($goods_desc)) {
    		EM_Api::outPut(101);
    	}
    	
    	$this->db_goods = RC_Loader::load_app_model('goods_model','goods');
    	$goods_info = $this->db_goods->where(array('goods_id' => $goods_id))->find();
    	if (empty($goods_info)) {
    	    return new ecjia_error('goods_not_exists', '商品不存在');
    	}
    	/*当前登录账号是平台还是商家*/
    	$is_ru_id = $_SESSION['ru_id'];

    	if ($is_ru_id > 0) {
    		/*获取商家积分等级限制处理*/
    		$ru_id = $this->db_goods->where(array('goods_id' => $goods_id))->get_field('user_id');
    		if ($is_ru_id != $ru_id) {
    			return new ecjia_error('no_purview', '您没权限修改此商品信息');
    		}
    	}
    	
    	$data = array(
    			'goods_desc'			=> $goods_desc,
    			'last_update'			=> RC_Time::gmtime()
    	);
    	/*如果设有审核商家商品，商家账号修改商品信息后，商品下架，状态改为待审核*/
    	$review_goods = RC_Model::model('seller/merchants_shop_information_model')->where(array('user_id' => $is_ru_id))->get_field('review_goods');
    	if ($is_ru_id > 0 && $review_goods == 1) {
    		$data['is_on_sale'] = 0;
    		$data['review_status'] = 1;
    	}
    	
    	$rs = $this->db_goods->where(array('goods_id' => $goods_id))->update($data);
    	if ($rs) {
    		$goods_name = $this->db_goods->where(array('goods_id' => $goods_id))->get_field('goods_name');
    		ecjia_admin::admin_log($goods_name, 'edit', 'goods');
    		return array();
    	}
    	
    }
}