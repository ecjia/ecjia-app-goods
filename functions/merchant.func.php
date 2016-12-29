<?php
defined('IN_ECJIA') or exit('No permission resources.');

/**
 * 检查商家分类是否已经存在
 *
 * @param   string      $cat_name       分类名称
 * @param   integer     $parent_cat     上级分类
 * @param   integer     $exclude        排除的分类ID
 *
 * @return  boolean
 */
function merchant_cat_exists($cat_name, $parent_cat, $exclude = 0) {
	return (RC_DB::table('merchants_category')
		->where('parent_id', $parent_cat)
		->where('cat_name', $cat_name)
		->where('cat_id', '!=', $exclude)
		->where('store_id', $_SESSION['store_id'])
		->count() > 0) ? true : false;
}

/**
 * 获得商家指定分类下的子分类的数组
 *
 * @access public
 * @param int $cat_id
 *        	分类的ID
 * @param int $selected
 *        	当前选中分类的ID
 * @param boolean $re_type
 *        	返回的类型: 值为真时返回下拉列表,否则返回数组
 * @param int $level
 *        	限定返回的级数。为0时返回所有级数
 * @param int $is_show_all
 *        	如果为true显示所有分类，如果为false隐藏不可见分类。
 * @return mix
 */
function merchant_cat_list($cat_id = 0, $selected = 0, $re_type = true, $level = 0, $is_show_all = true) {
	// 加载方法
	RC_Loader::load_app_func('category', 'goods');
	static $res = NULL;
	if ($res === NULL) {
		$data = false;
		if ($data === false) {
			$res = RC_DB::table('merchants_category as c')
				->leftJoin('merchants_category as s', RC_DB::raw('c.cat_id'), '=', RC_DB::raw('s.parent_id'))
				->selectRaw('c.cat_id, c.cat_name, c.parent_id, c.is_show, c.sort_order, COUNT(s.cat_id) AS has_children')
				->groupBy(RC_DB::raw('c.cat_id'))
				->orderBy(RC_DB::raw('c.parent_id'), 'asc')->orderBy(RC_DB::raw('c.sort_order'), 'asc')
				->where(RC_DB::raw('c.store_id'), $_SESSION['store_id'])
				->get();
				
			$res2 = RC_DB::table('goods')
				->selectRaw('cat_id, COUNT(*) as goods_num')
				->where('is_delete', 0)
				->where('is_on_sale', 1)
				->groupBy('cat_id')
				->get();
				
			$res3 = RC_DB::table('goods_cat as gc')
				->leftJoin('goods as g', RC_DB::raw('g.goods_id'), '=', RC_DB::raw('gc.goods_id'))
				->where(RC_DB::raw('g.is_delete'), 0)->where(RC_DB::raw('g.is_on_sale'), 1)
				->groupBy(RC_DB::raw('gc.cat_id'))
				->get();
				
			$newres = array ();
			if (!empty($res2)) {
				foreach($res2 as $k => $v) {
					$newres [$v ['cat_id']] = $v ['goods_num'];
					if (!empty($res3)) {
						foreach ( $res3 as $ks => $vs ) {
							if ($v ['cat_id'] == $vs ['cat_id']) {
								$newres [$v ['cat_id']] = $v ['goods_num'] + $vs ['goods_num'];
							}
						}
					}
				}
			}
			if (! empty ( $res )) {
				foreach ( $res as $k => $v ) {
					$res [$k] ['goods_num'] = ! empty($newres [$v ['cat_id']]) ? $newres [$v['cat_id']] : 0;
				}
			}
				
		} else {
			$res = $data;
		}
	}
	if (empty ( $res ) == true) {
		return $re_type ? '' : array ();
	}

	$options = cat_options ( $cat_id, $res ); // 获得指定分类下的子分类的数组

	$children_level = 99999; // 大于这个分类的将被删除
	if ($is_show_all == false) {
		foreach ( $options as $key => $val ) {
			if ($val ['level'] > $children_level) {
				unset ( $options [$key] );
			} else {
				if ($val ['is_show'] == 0) {
					unset ( $options [$key] );
					if ($children_level > $val ['level']) {
						$children_level = $val ['level']; // 标记一下，这样子分类也能删除
					}
				} else {
					$children_level = 99999; // 恢复初始值
				}
			}
		}
	}

	/* 截取到指定的缩减级别 */
	if ($level > 0) {
		if ($cat_id == 0) {
			$end_level = $level;
		} else {
			$first_item = reset ( $options ); // 获取第一个元素
			$end_level = $first_item ['level'] + $level;
		}

		/* 保留level小于end_level的部分 */
		foreach ( $options as $key => $val ) {
			if ($val ['level'] >= $end_level) {
				unset ( $options [$key] );
			}
		}
	}

	if ($re_type == true) {
		$select = '';
		if (! empty ( $options )) {
			foreach ( $options as $var ) {
				$select .= '<option value="' . $var ['cat_id'] . '" ';
				$select .= ($selected == $var ['cat_id']) ? "selected='ture'" : '';
				$select .= '>';
				if ($var ['level'] > 0) {
					$select .= str_repeat ( '&nbsp;', $var ['level'] * 4 );
				}
				$select .= htmlspecialchars ( addslashes($var ['cat_name'] ), ENT_QUOTES ) . '</option>';
			}
		}
		return $select;
	} else {
		if (! empty($options )) {
			foreach ($options as $key => $value ) {
				$options [$key] ['url'] = build_uri ('category', array('cid' => $value ['cat_id']), $value ['cat_name']);
			}
		}
		return $options;
	}
}

/**
 * 获得商家指定分类下所有底层分类的ID
 *
 * @access public
 * @param integer $cat
 *        	指定的分类ID
 * @return string
 */
function merchant_get_children($cat = 0) {
	RC_Loader::load_app_func('common', 'goods');
	return 'g.merchant_cat_id ' . db_create_in (array_unique(array_merge(array($cat), array_keys(merchant_cat_list($cat, 0, false )))));
}