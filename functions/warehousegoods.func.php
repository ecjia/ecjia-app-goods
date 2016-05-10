<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 获得商品的属性和规格
 *
 * @access public
 * @param integer $goods_id
 * @return array
 */
function warehouse_get_goods_properties($goods_id) {
	$db_good_type = RC_Loader::load_app_model ( 'goods_type_viewmodel', 'goods' );
	$db_good_attr = RC_Loader::load_app_model ( 'goods_attr_viewmodel', 'goods' );
	$db_goods = RC_Loader::load_app_model('goods_model', 'goods');
	/* 对属性进行重新排序和分组 */

	$db_good_type->view = array (
		'goods' => array (
			'type' 	=> Component_Model_View::TYPE_LEFT_JOIN,
			'alias' => 'g',
			'field' => 'attr_group',
			'on' 	=> 'gt.cat_id = g.goods_type'
		)
	);

	$grp = $db_good_type->find ( array (
		'g.goods_id' => $goods_id
	) );
	$grp = $grp ['attr_group'];
	if (! empty ( $grp )) {
		$groups = explode ( "\n", strtr ( $grp, "\r", '' ) );
	}
	
	/* 获得商品的规格 */
	$db_good_attr->view = array (
		'attribute' => array (
			'type'     => Component_Model_View::TYPE_LEFT_JOIN,
			'alias'    => 'a',
			'field'    => 'a.attr_id, a.attr_name, a.attr_group, a.is_linked, a.attr_type, ga.goods_attr_id, ga.attr_value, ga.attr_price',
			'on'       => 'a.attr_id = ga.attr_id'
		)
	);

	$res = $db_good_attr->where(array('ga.goods_id' => $goods_id))->order(array('a.sort_order' => 'asc','ga.attr_price' => 'asc','ga.goods_attr_id' => 'asc'))->select();
	$arr ['pro'] = array (); // 属性
	$arr ['spe'] = array (); // 规格
	$arr ['lnk'] = array (); // 关联的属性

	if (! empty ( $res )) {
		foreach ( $res as $row ) {
			$row ['attr_value'] = str_replace ( "\n", '<br />', $row ['attr_value'] );
				
			if ($row ['attr_type'] == 0) {
				$group = (isset ( $groups [$row ['attr_group']] )) ? $groups [$row ['attr_group']] : RC_Lang::lang ( 'goods_attr' );

				$arr ['pro'] [$group] [$row ['attr_id']] ['name'] = $row ['attr_name'];
				$arr ['pro'] [$group] [$row ['attr_id']] ['value'] = $row ['attr_value'];
			} else {
				$arr ['spe'] [$row ['attr_id']] ['attr_type'] = $row ['attr_type'];
				$arr ['spe'] [$row ['attr_id']] ['name'] = $row ['attr_name'];
				$arr ['spe'] [$row ['attr_id']] ['values'] [] = array (
						'label' => $row ['attr_value'],
						'price' => $row ['attr_price'],
						'format_price' => price_format ( abs ( $row ['attr_price'] ), false ),
						'id' => $row ['goods_attr_id']
				);
			}
				
			if ($row ['is_linked'] == 1) {
				/* 如果该属性需要关联，先保存下来 */
				$arr ['lnk'] [$row ['attr_id']] ['name'] = $row ['attr_name'];
				$arr ['lnk'] [$row ['attr_id']] ['value'] = $row ['attr_value'];
			}
		}
	}
	return $arr;
}
// end