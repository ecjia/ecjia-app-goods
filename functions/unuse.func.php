<?php
defined('IN_ECJIA') or exit('No permission resources.');

// ======================================== 废弃方法整理 start ======================================== //

function get_user_category($options, $shopMain_category, $ru_id = 0, $admin_type = 0) {
	$db_merchants_category = RC_Model::model('seller/merchants_category_model');
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
	$db_category = RC_Model::model('goods/category_model');

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
	$db_category = RC_Model::model('goods/category_model');
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
	$db_category = RC_Model::model('goods/category_model');
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
	$db_goods_cat_viewmodel = RC_Model::model('goods/goods_cat_viewmodel');
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
		$db = RC_Model::model('seller/merchants_category_viewmodel');
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
	for ($i=0; $i<count($dt_list); $i++) {
		$dt_list[$i] = !empty($dt_list[$i]) ? trim($dt_list[$i]) : '';
		if (!empty($dt_id[$i])) {
			$catId = RC_DB::table('merchants_documenttitle')->where('dt_id', $dt_id[$i])->pluck('cat_id');
		} else {
			$catId = 0;
		}
		if (!empty($dt_list[$i])) {
			$parent = array(
					'cat_id' 	=> $cat_id,
					'dt_title' 	=> $dt_list[$i]
			);
			if ($catId > 0) {
				RC_DB::table('merchants_documenttitle')->where('dt_id', $dt_id[$i])->update($parent);
			} else {
				$id[] = RC_DB::table('merchants_documenttitle')->insertGetId($parent);
			}
				
		} else {
			if ($catId > 0) {
				//删除二级类目表数据
				RC_DB::table('merchants_documenttitle')->where('dt_id', $dt_id[$i])->delete();
			}
		}
	}
	$list = !empty($id) ? array_merge($dt_id, $id) : $dt_id;
	$dt_id_list = RC_DB::table('merchants_documenttitle')->where('cat_id', $cat_id)->lists('dt_id');

	$arr = array();
	if (!empty($dt_id_list)) {
		foreach ($dt_id_list as $v) {
			if (!in_array($v, $list)) {
				$arr[] = $v;
			}
		}
		if (!empty($arr)) {
			RC_DB::table('merchants_documenttitle')->whereIn('dt_id', $arr)->delete();
		}
	}
}

// ======================================== 废弃方法整理 end ======================================== //