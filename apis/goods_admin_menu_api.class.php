<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * ECJI后台商品菜单API
 */
class goods_admin_menu_api extends Component_Event_Api
{
	public function call(&$options)
	{
		$menus = ecjia_admin::make_admin_menu('03_cat_and_goods',RC_Lang::get('goods::goods.goods_manage'), '', 4);
		$submenus = array(
			ecjia_admin::make_admin_menu('01_goods_list', RC_Lang::get('goods::goods.goods_list'), RC_Uri::url('goods/admin/init'), 1)->add_purview(array('goods_manage', 'remove_back')),
			ecjia_admin::make_admin_menu('02_category_list', RC_Lang::get('goods::goods.category'), RC_Uri::url('goods/admin_category/init'), 2)->add_purview(array('cat_manage', 'cat_drop')),
			ecjia_admin::make_admin_menu('03_goods_brand_list', RC_Lang::get('goods::goods.brand'), RC_Uri::url('goods/admin_brand/init'), 3)->add_purview('brand_manage'),
			ecjia_admin::make_admin_menu('04_goods_type', RC_Lang::get('goods::goods.goods_type'), RC_Uri::url('goods/admin_goods_type/init'), 4)->add_purview('attr_manage'),
			ecjia_admin::make_admin_menu('05_goods_trash', RC_Lang::get('goods::goods.goods_recycle'), RC_Uri::url('goods/admin/trash'), 5)->add_purview(array('goods_manage', 'remove_back')),
		);
		
        $menus->add_submenu($submenus);
        return $menus;
    }
}

// end