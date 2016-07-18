<?php
defined('IN_ECJIA') or exit('No permission resources.');

/**
 * 检查分类是否已经存在
 *
 * @param   string      $cat_name       分类名称
 * @param   integer     $parent_cat     上级分类
 * @param   integer     $exclude        排除的分类ID
 *
 * @return  boolean
 */
function cat_exists($cat_name, $parent_cat, $exclude = 0) {
	$db = RC_Loader::load_app_model ( 'category_model', 'goods' );
	return ($db->where(array('parent_id' => $parent_cat, 'cat_name' => $cat_name, 'cat_id' => array('neq' => $exclude)))->count() > 0) ? true : false;
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
function cat_list($cat_id = 0, $selected = 0, $re_type = true, $level = 0, $is_show_all = true) {
	// 加载方法
	RC_Loader::load_app_func('common', 'goods');
	$db_goods = RC_Loader::load_app_model('goods_model', 'goods');
	$db_category = RC_Loader::load_app_model('sys_category_viewmodel', 'goods');
	$db_goods_cat = RC_Loader::load_app_model('goods_cat_viewmodel', 'goods');
	static $res = NULL;	
	if ($res === NULL) {
		$data = false;
		if ($data === false) {
			$res = $db_category->join('category')->group('c.cat_id')->order(array('c.parent_id' => 'asc', 'c.sort_order' => 'asc'))->select();
			$res2 = $db_goods->field ( 'cat_id, COUNT(*)|goods_num' )->where(array('is_delete' => 0, 'is_on_sale' => 1))->group ('cat_id asc')->select();
			$res3 = $db_goods_cat->join('goods')->where(array('g.is_delete' => 0, 'g.is_on_sale' => 1))->group ('gc.cat_id')->select();
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

// ======================================== ru start ========================================

function get_user_category($options, $shopMain_category, $ru_id = 0, $admin_type = 0) {
	$db_merchants_category = RC_Loader::load_app_model('merchants_category_model', 'seller');
	if ($ru_id > 0) {
		$shopMain_category = get_category_child_tree($shopMain_category);
		$arr = array();
		if (!empty($shopMain_category)) {
			$category = explode(',', $shopMain_category);
			foreach ($options as $key=>$row) {
				if ($row['level'] < 3) {
					for ($i=0; $i<count($category); $i++) {
						if ($key == $category[$i]) {
							$arr[$key] = $row;
						}
					}
				} else {
					$uc_id = $db_merchants_category->where(array('cat_id' => $row['cat_id'], 'user_id' => $ru_id))->get_field('uc_id');
						
					if ($admin_type == 0) {
						if ($uc_id > 0) {
							$arr[$key] = $row;
						}
					}
				}
			}
		}
		return $arr;
	} else {
		return $options;
	}
}

function get_category_child_tree($shopMain_category){
	$db_category = RC_Loader::load_app_model('category_model', 'goods');

	$category = explode('-',$shopMain_category);

	for ($i=0; $i<count($category); $i++) {
		$category[$i] = explode(':', $category[$i]);

		$twoChild = explode(',', $category[$i][1]);
		for ($j=0; $j<count($twoChild); $j++) {
			$threeChild = $db_category->field('cat_id, cat_name')->where(array('parent_id'=>$twoChild[$j]))->select();
			$category[$i]['three_' . $twoChild[$j]] = get_category_three_child($threeChild);
			$category[$i]['three'] .= $category[$i][0] .','. $category[$i][1] .','. $category[$i]['three_' . $twoChild[$j]]['threeChild'] . ',';
		}
		$category[$i]['three'] = substr($category[$i]['three'], 0, -1);
	}
	$category = get_link_cat_id($category);
	$category = $category['all_cat'];
	return $category;
}

function get_category_three_child($threeChild){

	for ($i=0; $i<count($threeChild); $i++) {
		if (!empty($threeChild[$i]['cat_id'])) {
			$threeChild['threeChild'] .= $threeChild[$i]['cat_id'] . ",";
		}
	}
	$threeChild['threeChild'] = substr($threeChild['threeChild'], 0, -1);
	return $threeChild;
}

function get_link_cat_id($category) {
	for ($i=0; $i<count($category); $i++) {
		if (!empty($category[$i]['three'])) {
			$category['all_cat'] .= $category[$i]['three'] . ',';
		}
	}
	$category['all_cat'] = substr($category['all_cat'], 0, -1);
	return $category;
}

function get_class_nav($cat_id) {
	$db_category = RC_Loader::load_app_model('category_model', 'goods');
	$res = $db_category->field('cat_id,cat_name,parent_id')->where(array('cat_id'=>$cat_id))->select();
	$arr = array();
	$arr['catId'] = '';
	if (!empty($res)) {
		foreach ($res as $key => $row) {
			$arr[$key]['cat_id'] 	= $row['cat_id'];
			$arr[$key]['cat_name'] 	= $row['cat_name'];
			$arr[$key]['parent_id'] = $row['parent_id'];
		
			$arr['catId'] .= $row['cat_id'] . ",";
			$arr[$key]['child'] = get_parent_child($row['cat_id']);
		
			if (empty($arr[$key]['child']['catId'])) {
				$arr['catId'] = $arr['catId'];
			}else{
				$arr['catId'] .= $arr[$key]['child']['catId'];
			}
		}
	}
	return $arr;
}

function get_parent_child($parent_id = 0){
	$db_category = RC_Loader::load_app_model('category_model', 'goods');
	$res = $db_category->field('cat_id, cat_name, parent_id')->where(array('parent_id' => $parent_id))->select();
	$arr = array();
	$arr['catId'] = '';
	
	if (!empty($res)) {
		foreach($res as $key => $row){
			$arr[$key]['cat_id'] 	= $row['cat_id'];
			$arr[$key]['cat_name'] 	= $row['cat_name'];
			$arr[$key]['parent_id'] = $row['parent_id'];
	
			$arr['catId'] .= $row['cat_id'] . ",";
			$arr[$key]['child'] = get_parent_child($row['cat_id']);
	
			$arr['catId'] .= $arr[$key]['child']['catId'];
		}
	}
	return $arr;
}

/**
 * 查询扩展分类商品id
 *
 *@param int cat_id
 *
 *@return int extentd_count
 * by guan
 */
function get_goodsCat_num($cat_id, $goods_ids=array(), $ruCat = array()) {
	$db_goods_cat_viewmodel = RC_Loader::load_app_model('goods_cat_viewmodel', 'goods');
	$cat_goods = $db_goods_cat_viewmodel->join('goods')->where(array_merge(array('g.is_delete'=>0, 'gc.cat_id' => $cat_id), $ruCat))->select();

	if (!empty($cat_goods)) {
		foreach($cat_goods as $key => $val) {
			if (!empty($val['goods_id'])) {
				if(in_array($val['goods_id'], $goods_ids)) {
					unset($cat_goods[$key]);
				}
			}
		}	
	}
	return count($cat_goods);
}

function get_fine_store_category($options, $web_type, $array_type = 0, $ru_id){
	$cat_array = array();
	if ($web_type == 'admin' || $web_type == 'goodsInfo') {
		$db = RC_Loader::load_app_model('merchants_category_viewmodel', 'seller');
		$store_cat = $db->join(null)->field('cat_id, user_id')->select();

		if (!empty($store_cat)) {
			foreach($store_cat as $row){
				$cat_array[$row['cat_id']]['cat_id'] = $row['cat_id'];
				$cat_array[$row['cat_id']]['user_id'] = $row['user_id'];
			}	
		}
	}
	if ($web_type == 'admin') {
		if ($cat_array) {
			if ($array_type == 0) {
				$options = array_diff_key($options, $cat_array);
			} else {
				$options = array_intersect_key($options, $cat_array);
			}
		}
		return $options;
	} elseif ($web_type == 'goodsInfo' && $ru_id == 0){
		$options = array_diff_key($options, $cat_array);
		return $options;
	} else {
		return $options;
	}
}

/**
 * 获得指定分类下的子分类的数组,商家店铺分类
 *
 * @access  public
 * @param   int     $cat_id     分类的ID
 * @param   int     $selected   当前选中分类的ID
 * @param   boolean $re_type    返回的类型: 值为真时返回下拉列表,否则返回数组
 * @param   int     $level      限定返回的级数。为0时返回所有级数
 * @param   int     $is_show_all 如果为true显示所有分类，如果为false隐藏不可见分类。
 * @return  mix
 */
function goods_admin_store_cat_list($cat_info) {
	$arr = array();
	if ($cat_info) {
		foreach ($cat_info as $key => $row) {
			if ($row['level'] > 2) {
				$arr[$key] = $row;
			}
		}

		$arr = get_admin_goods_cat_list_child($arr);
		foreach ($arr as $key=>$row) {
			$arr[$key] = $row;
			if ($row['child_array']) {
				$arr[$key]['child_array'] = array_values($row['child_array']);
			}
		}
	}
	return $arr;
}

function get_admin_goods_cat_list_child($arr){
	$arr = array_values($arr);

	$newArr = array();
	for ($i=0; $i<count($arr); $i++) {
		if ($arr[$i]['level'] == 3) {
			$newArr[$i] = $arr[$i];
			$newArr[$i]['level'] = 0;
		}
	}
	$newArr = array_values($newArr);

	for ($i=0; $i<count($newArr); $i++) {
		for ($j=0; $j<count($arr); $j++) {
			if ($arr[$j]['level'] == 4) {
				if ($newArr[$i]['cat_id'] == $arr[$j]['parent_id']) {
					$newArr[$i]['child_array'][$j] = $arr[$j];
					$newArr[$i]['child_array'][$j]['level'] = 1;
				}
			}
		}
	}
	return $newArr;
}

//添加类目证件标题
function get_documentTitle_insert_update($dt_list, $cat_id, $dt_id = array()) {
	$db_merchants_documenttitle = RC_Loader::load_app_model('merchants_documenttitle_model', 'goods');
	for ($i=0; $i<count($dt_list); $i++) {
		$dt_list[$i] = !empty($dt_list[$i]) ? trim($dt_list[$i]) : '';
		if (!empty($dt_id[$i])) {
			$catId = $db_merchants_documenttitle->where(array('dt_id' => $dt_id[$i]))->get_field('cat_id');
		} else {
			$catId = 0;
		}
		if (!empty($dt_list[$i])) {
			$parent = array(
				'cat_id' 	=> $cat_id,
				'dt_title' 	=> $dt_list[$i]
			);
			if ($catId > 0) {
				$db_merchants_documenttitle->where(array('dt_id' => $dt_id[$i]))->update($parent);
			} else {
				$id[] = $db_merchants_documenttitle->insert($parent);
			}
			
		} else {
			if ($catId > 0) {
				//删除二级类目表数据
// 				$db_merchants_documenttitle->where(array('dt_id' => $dt_id[$i], 'user_id' => $_SESSION['ru_id']))->delete();
				$db_merchants_documenttitle->where(array('dt_id' => $dt_id[$i]))->delete();
			}
		}
	}
	$list = !empty($id) ? array_merge($dt_id, $id) : $dt_id;
	$dt_id_list = $db_merchants_documenttitle->where(array('cat_id' => $cat_id))->get_field('dt_id', true);
	$arr = array();
	if (!empty($dt_id_list)) {
		foreach ($dt_id_list as $v) {
			if (!in_array($v, $list)) {
				$arr[] = $v;
			}
		}
		if (!empty($arr)) {
			$db_merchants_documenttitle->in(array('dt_id' => $arr))->delete();
		}
	}
}
// ======================================== ru end ========================================


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
function merchant_cat_list() {
	// 加载方法
	RC_Loader::load_app_func('common', 'goods');
	$db_goods = RC_Loader::load_app_model('goods_model', 'goods');
	$db_category = RC_Loader::load_app_model('sys_category_viewmodel', 'goods');
	$db_goods_cat = RC_Loader::load_app_model('goods_cat_viewmodel', 'goods');
	$db_category->view = array(
		'merchants_category' => array(
			'type'  =>	Component_Model_View::TYPE_LEFT_JOIN,
			'alias' =>	'mc',
			'on'   	=>	'mc.cat_id = c.cat_id'
		)
	);
	$res = $db_category->join('merchants_category')->where(array('mc.seller_id'=>array('neq'=> 0)))->group('c.cat_id')->order(array('c.parent_id' => 'asc', 'c.sort_order' => 'asc'))->select();
	$res2 = $db_goods->field ( 'cat_id, COUNT(*)|goods_num' )->where(array('is_delete' => 0,'is_on_sale' => 1,'seller_id' => (isset($_SESSION['seller_id']) ? $_SESSION['seller_id'] : 0)))->group ('cat_id asc')->select();
	$res3 = $db_goods_cat->join('goods')->where(array('g.is_delete' => 0,'g.is_on_sale' => 1, 'seller_id' => (isset($_SESSION['seller_id']) ? $_SESSION['seller_id'] : 0)))->group ('gc.cat_id')->select();
		
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
	
	return $res;
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
						
						if ($value ['has_children'] == 0) {
							continue;
						}
						$last_cat_id = $cat_id;
						$cat_id_array = array($cat_id);
						$level_array [$last_cat_id] = ++ $level;
						continue;
					}
					
					if ($value ['parent_id'] == $last_cat_id) {
						$options [$cat_id] = $value;
						$options [$cat_id] ['level'] = $level;
						$options [$cat_id] ['id'] = $cat_id;
						$options [$cat_id] ['name'] = $value ['cat_name'];
						unset ( $arr [$key] );
						
						if ($value ['has_children'] > 0) {
							if (end ( $cat_id_array ) != $last_cat_id) {
								$cat_id_array [] = $last_cat_id;
							}
							$last_cat_id = $cat_id;
							$cat_id_array [] = $cat_id;
							$level_array [$last_cat_id] = ++ $level;
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

/**
 * 获得指定分类下所有底层分类的ID
 *
 * @access public
 * @param integer $cat
 *        	指定的分类ID
 * @return string
 */
function get_children($cat = 0) {
	RC_Loader::load_app_func('common', 'goods');
	return 'g.cat_id ' . db_create_in (array_unique(array_merge(array($cat), array_keys(cat_list($cat, 0, false )))));
}

// end