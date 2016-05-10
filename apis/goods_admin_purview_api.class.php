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
            array('action_name' => __('商品添加/编辑'), 'action_code' => 'goods_manage', 'relevance'   => ''),
            array('action_name' => __('商品回收/恢复'), 'action_code' => 'remove_back', 'relevance'   => ''),
        	array('action_name' => __('商品彻底删除'), 'action_code' => 'drop_goods', 'relevance'   => ''),
            array('action_name' => __('分类添加/编辑'), 'action_code' => 'cat_manage', 'relevance'   => ''),
            array('action_name' => __('分类转移/删除'), 'action_code' => 'cat_drop', 'relevance'   => 'cat_manage'),
            array('action_name' => __('店铺商品分类'), 'action_code' => 'category_store', 'relevance'   => ''),
            array('action_name' => __('商品属性管理'), 'action_code' => 'attr_manage', 'relevance'   => ''),
            array('action_name' => __('自营商品品牌管理'), 'action_code' => 'brand_manage', 'relevance'   => ''),
            array('action_name' => __('商家商品品牌管理'), 'action_code' => 'merchants_brand', 'relevance'   => ''),
            array('action_name' => __('商品类型'), 'action_code' => 'goods_type', 'relevance'   => ''),
            array('action_name' => __('商品自动上下架'), 'action_code' => 'goods_auto', 'relevance'   => ''),
            array('action_name' => __('虚拟卡管理'), 'action_code' => 'virualcard', 'relevance'   => ''),
            array('action_name' => __('图片批量处理'), 'action_code' => 'picture_batch', 'relevance'   => ''),
            array('action_name' => __('商品批量导出'), 'action_code' => 'goods_export', 'relevance'   => ''),
            array('action_name' => __('商品批量上传/修改'), 'action_code' => 'goods_batch', 'relevance'   => ''),
            array('action_name' => __('生成商品代码'), 'action_code' => 'gen_goods_script', 'relevance'   => ''),
        );
        
        return $purviews;
    }
}

// end