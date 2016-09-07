<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * ECJI后台商品菜单API
 */
class goods_admin_menu_api extends Component_Event_Api
{
	public function call(&$options)
	{
		$menus = ecjia_admin::make_admin_menu('02_cat_and_goods', __('商品管理'), '', 1);
		$submenus = array(
			ecjia_admin::make_admin_menu('01_goods_list', __('商品列表'), RC_Uri::url('goods/admin/init'), 1)->add_purview(array('goods_manage', 'remove_back')),
			ecjia_admin::make_admin_menu('02_goods_add', __('添加新商品'), RC_Uri::url('goods/admin/add'), 2)->add_purview('goods_manage'),
			ecjia_admin::make_admin_menu('03_category_list', __('平台商品分类'), RC_Uri::url('goods/admin_category/init'), 3)->add_purview(array('cat_manage', 'cat_drop')),
			ecjia_admin::make_admin_menu('06_goods_brand_list', __('自营品牌'), RC_Uri::url('goods/admin_brand/init'), 5)->add_purview('brand_manage'),
			ecjia_admin::make_admin_menu('08_goods_type', __('商品类型'), RC_Uri::url('goods/admin_goods_type/init'), 7)->add_purview('attr_manage'),
			ecjia_admin::make_admin_menu('11_goods_trash', __('商品回收站'), RC_Uri::url('goods/admin/trash'), 8)->add_purview(array('goods_manage', 'remove_back')),
			ecjia_admin::make_admin_menu('06_undispose_booking', __('缺货登记'), RC_Uri::url('goods/admin_goods_booking/init'), 9)->add_purview('booking'),
		);
		
        $menus->add_submenu($submenus);
        return $menus;
    }
}

// end