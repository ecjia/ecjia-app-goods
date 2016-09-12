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
        		
        		
            array('action_name' => __('分类添加/编辑'), 'action_code' => 'cat_manage', 'relevance'   => ''),
            array('action_name' => __('分类转移/删除'), 'action_code' => 'cat_drop', 'relevance'   => 'cat_manage'),
            array('action_name' => __('店铺商品分类'), 'action_code' => 'category_store', 'relevance'   => ''),
            array('action_name' => __('商品属性管理'), 'action_code' => 'attr_manage', 'relevance'   => ''),
            array('action_name' => __('商品类型'), 'action_code' => 'goods_type', 'relevance'   => ''),
        );
        
        return $purviews;
    }
}

// end