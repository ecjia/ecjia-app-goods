<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 商品统计
 * @author will.chen
 *
 */
class goods_goods_stats_api extends Component_Event_Api {
	
    
	public function call(&$options) {	
		$db_goods = RC_Loader::load_app_model ('goods_model', 'goods');
	
		/* 获取在售的商品总数*/
		$stats['total'] = $db_goods->where(array('is_delete' => 0, 'is_alone_sale' => 1, 'is_real' => 1))->count();
	    
	    return $stats;
	}
	
	
}

// end