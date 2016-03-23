<?php
defined('IN_ECJIA') or exit('No permission resources.');

	/**
	 * 查询仓库列表
	 */
	function get_warehouse_goods_list($goods_id = 0)
	{
		$db_warehouse_goods = RC_Loader::load_app_model('warehouser_goods_viewmodel','goods');
		$rs = $db_warehouse_goods->field('wg.w_id, wg.region_id, wg.region_number, wg.warehouse_price, wg.warehouse_promote_price, rw.region_name')->where(array('goods_id' =>$goods_id))->select();
// 		_dump($db_warehouse_goods->last_sql());
// 		_dump($rs,1);
		return $rs;
	}

	/**
	 * 查询地区仓库列表
	 */
	function get_warehouse_area_goods_list($goods_id = 0)
	{
		$db_area_goods = RC_Loader::load_app_model('warehouse_area_goods_viewmodel', 'goods');
		$db_region = RC_Loader::load_app_model('region_warehouose_model', 'goods');
		
		$data = $db_region->where(array('parent_id' => 0))->select();
		foreach ($data as $key => $val){
			$arr[$val['region_id']] = $val['region_name'];
		}
		$field = 'wa.a_id, wa.region_id, wa.region_number, wa.region_price, wa.region_promote_price, rw.region_name, rw.parent_id';
		$rs = $db_area_goods->field($field)->where(array('wa.goods_id' => $goods_id))->select();
		foreach ($rs as $key => $val){
		    $rs[$key][ware_name] = $arr[$val['parent_id']];
		    $rs[$key][warehouse] = $val['parent_id'];
		}
		return $rs;
	}

// end