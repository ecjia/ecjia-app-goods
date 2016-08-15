<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 获取店铺商品商品分类
 * @author will.chen
 *
 */
class goods_seller_goods_category_api extends Component_Event_Api {
	
    /**
     * @param  $options['keyword'] 关键字
     *         $options['cat_id'] 分类id
     *         $options['brand_id'] 品牌id
     *
     * @return array
     */
	public function call(&$options) {
		//接口商家我的宝贝商品分类
		if (isset($options['seller_id']) && isset($options['type']) && $options['type'] == 'seller_goods_cat_list_api') {
			$row = $this->cat_list(0, 0, false, 0, true, $options['seller_id'], 'seller_goods_cat_list_api');
		}
		//总后台商品应用中店铺商品分类
		if (isset($options['seller_id']) && isset($options['type']) && $options['type'] == 'seller_goods_cat_list') {
			$row = $this->cat_list(0, 0, false, 0, true, $options['seller_id']);
		}
		//总后台店铺商品分类编辑页
		if (isset($options['parent_id']) && isset($options['seller_id']) && isset($options['type']) && ($options['type'] == 'edit')) {
			$row = $this->cat_list(0, $options['parent_id'], true, 0, true, $options['seller_id']);
		}
		//总后台店铺商品分类更新
		if (isset($options['cat_id']) && isset($options['seller_id']) && isset($options['type']) && $options['type'] == 'update') {
			$row = $this->cat_list($options['cat_id'], 0, false, 0, true, $options['seller_id']);
		}
		//商家商品分类
		if (isset($options['cat_id']) && isset($options['seller_id']) && isset($options['type']) && $options['type'] == 'seller_goods_cat') {
			$row = $this->cat_list($options['cat_id'], 0, false, 0, true, $options['seller_id']);
		}
	    return $row;
	}
	
	
	/**
	 * 获得指定分类下的子分类的数组
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
	function cat_list($cat_id = 0, $selected = 0, $re_type = true, $level = 0, $is_show_all = true, $seller_id, $type='') {
		// 加载方法
		RC_Loader::load_app_func('common', 'goods');
		$db_goods = RC_Loader::load_app_model('goods_model', 'goods');
		$db_category = RC_Loader::load_app_model('seller_goods_category_viewmodel', 'goods');
		$db_goods_cat = RC_Loader::load_app_model('goods_cat_viewmodel', 'goods');
		
		static $res = NULL;
	
		if ($res === NULL) {
			$data = false;
			if ($data === false) {
				//商户商品分类
				$where_cat = array();
				$where_cat['c.seller_id'] = $seller_id;
				if (!empty($type)) {
					$where_cat['c.is_show'] = 1;
				}
				
				$res = $db_category->join(array('seller_goods_category'))->where($where_cat)->group('c.cat_id')->order(array('c.parent_id' => 'asc', 'c.sort_order' => 'asc'))->select();
				$res2 = $db_goods->field ( 'cat_id, COUNT(*)|goods_num' )->where(array('is_delete' => 0, 'is_on_sale' => 1, 'seller_id' => $seller_id))->group ('cat_id asc')->select();
				$res3 = $db_goods_cat->join('goods')->where(array('g.is_delete' => 0, 'g.is_on_sale' => 1, 'g.seller_id' => $seller_id))->group ('gc.cat_id')->select();
			
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
	
		$options = $this->cat_options ( $cat_id, $res ); // 获得指定分类下的子分类的数组
	
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
	 * 过滤和排序所有分类，返回一个带有缩进级别的数组
	 *
	 * @access private
	 * @param int $cat_id
	 *        	上级分类ID
	 * @param array $arr
	 *        	含有所有分类的数组
	 * @param int $level
	 *        	级别
	 * @return void
	 */
	function cat_options($spec_cat_id, $arr) {
		static $cat_options = array ();
	
		if (isset ( $cat_options [$spec_cat_id] )) {
			return $cat_options [$spec_cat_id];
		}
	
		if (! isset ( $cat_options [0] )) {
			$level = $last_cat_id = 0;
			$options = $cat_id_array = $level_array = array ();
			$data = false;
			if ($data === false) {
				while ( ! empty ( $arr ) ) {
					foreach ( $arr as $key => $value ) {
						$cat_id = $value ['cat_id'];
						if ($level == 0 && $last_cat_id == 0) {
							if ($value ['parent_id'] > 0) {
								break;
							}
	
							$options [$cat_id] = $value;
							$options [$cat_id] ['level'] = $level;
							$options [$cat_id] ['id'] = $cat_id;
							$options [$cat_id] ['name'] = $value ['cat_name'];
							unset ( $arr [$key] );
							if (isset($value ['has_children'])) {
								if ($value ['has_children'] == 0) {
									continue;
								}
							}
							$last_cat_id = $cat_id;
							$cat_id_array = array (
									$cat_id
							);
							$level_array [$last_cat_id] = ++ $level;
							continue;
						}
							
						if ($value ['parent_id'] == $last_cat_id) {
							$options [$cat_id] = $value;
							$options [$cat_id] ['level'] = $level;
							$options [$cat_id] ['id'] = $cat_id;
							$options [$cat_id] ['name'] = $value ['cat_name'];
							unset ( $arr [$key] );
							if (isset($value ['has_children'])) {
								if ($value ['has_children'] > 0) {
									if (end ( $cat_id_array ) != $last_cat_id) {
										$cat_id_array [] = $last_cat_id;
									}
									$last_cat_id = $cat_id;
									$cat_id_array [] = $cat_id;
									$level_array [$last_cat_id] = ++ $level;
								}
							}
						} elseif ($value ['parent_id'] > $last_cat_id) {
							break;
						}
					}
	
					$count = count ( $cat_id_array );
					if ($count > 1) {
						$last_cat_id = array_pop ( $cat_id_array );
					} elseif ($count == 1) {
						if ($last_cat_id != end ( $cat_id_array )) {
							$last_cat_id = end ( $cat_id_array );
						} else {
							$level = 0;
							$last_cat_id = 0;
							$cat_id_array = array ();
							continue;
						}
					}
	
					if ($last_cat_id && isset ( $level_array [$last_cat_id] )) {
						$level = $level_array [$last_cat_id];
					} else {
						$level = 0;
					}
				}
			} else {
				$options = $data;
			}
			$cat_options [0] = $options;
		} else {
			$options = $cat_options [0];
		}
	
		if (! $spec_cat_id) {
			return $options;
		} else {
			if (empty ( $options [$spec_cat_id] )) {
				return array ();
			}
	
			$spec_cat_id_level = $options [$spec_cat_id] ['level'];
	
			foreach ( $options as $key => $value ) {
				if ($key != $spec_cat_id) {
					unset ( $options [$key] );
				} else {
					break;
				}
			}
	
			$spec_cat_id_array = array ();
			foreach ( $options as $key => $value ) {
				if (($spec_cat_id_level == $value ['level'] && $value ['cat_id'] != $spec_cat_id) || ($spec_cat_id_level > $value ['level'])) {
					break;
				} else {
					$spec_cat_id_array [$key] = $value;
				}
			}
			$cat_options [$spec_cat_id] = $spec_cat_id_array;
	
			return $spec_cat_id_array;
		}
	}
	
	
}

// end