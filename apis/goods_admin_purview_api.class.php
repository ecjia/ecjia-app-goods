<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 后台权限API
 * @author royalwang
 *
 */
class goods_admin_purview_api extends Component_Event_Api {
    
    public function call(&$options) {
        $purviews = array(
            array('action_name' => __('商品管理'), 'action_code' => 'goods_manage', 'relevance'   => ''),
        	array('action_name' => __('更新商品'), 'action_code' => 'goods_update', 'relevance'   => ''),
            array('action_name' => __('商品回收/恢复'), 'action_code' => 'remove_back', 'relevance'   => ''),
        	array('action_name' => __('商品彻底删除'), 'action_code' => 'goods_delete', 'relevance'   => ''),
        		
            array('action_name' => __('分类管理'), 'action_code' => 'cat_manage', 'relevance'   => ''),
        	array('action_name' => __('更新分类'), 'action_code' => 'cat_update', 'relevance'   => ''),
            array('action_name' => __('分类转移/删除'), 'action_code' => 'cat_drop', 'relevance'   => ''),
        		
            array('action_name' => __('商品属性管理'), 'action_code' => 'attr_manage', 'relevance'   => ''),
        	array('action_name' => __('商品属性更新'), 'action_code' => 'attr_update', 'relevance'   => ''),
        	array('action_name' => __('商品属性删除'), 'action_code' => 'attr_delete', 'relevance'   => ''),
        		
        	array('action_name' => __('商品品牌管理'), 'action_code' => 'brand_manage', 'relevance'   => ''),
        	array('action_name' => __('商品品牌更新'), 'action_code' => 'brand_update', 'relevance'   => ''),
        	array('action_name' => __('商品品牌删除'), 'action_code' => 'brand_delete', 'relevance'   => ''),
        		
            array('action_name' => __('商品类型管理'), 'action_code' => 'goods_type', 'relevance'   => ''),
        	array('action_name' => __('商品类型更新'), 'action_code' => 'goods_type_update', 'relevance'   => ''),
        	array('action_name' => __('商品类型删除'), 'action_code' => 'goods_type_delete', 'relevance'   => ''),
        );
        return $purviews;
    }
}

// end