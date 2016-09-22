<?php

/**
*  ECJIA 管理中心商品相关函数
*/
defined('IN_ECJIA') or exit('No permission resources.');

/**
* 取得会员等级列表
*
* @return array 会员等级列表
*/
function get_user_rank_list() {
// 	$db = RC_Model::model('user/user_rank_model');
// 	return $db->order('min_points asc')->select();
	
	return RC_DB::table('user_rank')->orderBy('min_points', 'asc')->get();
}

/**
* 取得某商品的会员价格列表
*
* @param int $goods_id
*            商品编号
* @return array 会员价格列表 user_rank => user_price
*/
function get_member_price_list($goods_id) {
// 	$db = RC_Model::model('goods/member_price_model');
	/* 取得会员价格 */
// 	$data = $db->field('user_rank, user_price')->where(array('goods_id' => $goods_id))->select();
	$data = RC_DB::table('member_price')->select('user_rank', 'user_price')->where('goods_id', $goods_id)->get();
	
	$price_list = array();
	if (!empty($data)) {
		foreach ($data as $row) {
			$price_list[$row ['user_rank']] = $row ['user_price'];
		}
	}
	return $price_list;
}

/**
* 插入或更新商品属性
*
* @param int $goods_id
*            商品编号
* @param array $id_list
*            属性编号数组
* @param array $is_spec_list
*            是否规格数组 'true' | 'false'
* @param array $value_price_list
*            属性值数组
* @return array 返回受到影响的goods_attr_id数组
*/
function handle_goods_attr($goods_id, $id_list, $is_spec_list, $value_price_list) {
// 	$db = RC_Model::model('goods/goods_attr_model');

	$goods_attr_id = array();
	/* 循环处理每个属性 */
	if (!empty($id_list)) {
		foreach ($id_list as $key => $id) {
			$is_spec = $is_spec_list [$key];
			if ($is_spec == 'false') {
				$value = $value_price_list [$key];
				$price = '';
			} else {
				$value_list = array();
				$price_list = array();
				if ($value_price_list [$key]) {
					$vp_list = explode(chr(13), $value_price_list [$key]);
					foreach ($vp_list as $v_p) {
						$arr = explode(chr(9), $v_p);
						$value_list [] = $arr [0];
						$price_list [] = $arr [1];
					}
				}
				$value = join(chr(13), $value_list);
				$price = join(chr(13), $price_list);
			}
		
			// 插入或更新记录
// 			$result_id = $db->where(array('goods_id' => $goods_id, 'attr_id' => $id, 'attr_value' => $value))->get_field('goods_attr_id');
			$result_id = RC_DB::table('goods_attr')->where('goods_id', $goods_id)->where('attr_id', $id)->where('attr_value', $value)->pluck('goods_attr_id');
			
			if (!empty ($result_id)) {
				$data = array(
					'attr_value' => $value
				);
// 				$db->where(array('goods_id' => $goods_id, 'attr_id' => $id, 'goods_attr_id' => $result_id))->update($data);
				RC_DB::table('goods_attr')->where('goods_id', $goods_id)->where('attr_id', $id)->where('goods_attr_id', $result_id)->update($data);
				
				$goods_attr_id [$id] = $result_id;
			} else {
				$data = array(
					'goods_id' 		=> $goods_id,
					'attr_id' 		=> $id,
					'attr_value' 	=> $value,
					'attr_price' 	=> $price
				);
// 				$goods_attr_id [$id] = $db->insert($data);
				$goods_attr_id [$id] = RC_DB::table('goods_attr')->insertGetId($data);
			}
		}
	}
	return $goods_attr_id;
}

/**
* 保存某商品的会员价格
*
* @param int $goods_id
*            商品编号
* @param array $rank_list
*            等级列表
* @param array $price_list
*            价格列表
* @return void
*/
function handle_member_price($goods_id, $rank_list, $price_list) {
// 	$db = RC_Model::model('goods/member_price_model');
	
	/* 循环处理每个会员等级 */
	if (!empty($rank_list)) {
		foreach ($rank_list as $key => $rank) {
			/* 会员等级对应的价格 */
			$price = $price_list [$key];
			// 插入或更新记录
// 			$count = $db->where(array('goods_id' => $goods_id, 'user_rank' => $rank))->count();
			$count = RC_DB::table('member_price')->where('goods_id', $goods_id)->where('user_rank', $rank)->count();
		
			if ($count) {
				/* 如果会员价格是小于0则删除原来价格，不是则更新为新的价格 */
				if ($price < 0) {
// 					$db->where(array('goods_id' => $goods_id, 'user_rank' => $rank))->delete();
					RC_DB::table('member_price')->where('goods_id', $goods_id)->where('user_rank', $rank)->delete();
				} else {
					$data = array(
						'user_price' => $price
					);
// 					$db->where(array('goods_id' => $goods_id, 'user_rank' => $rank))->update($data);
					RC_DB::table('member_price')->where('goods_id', $goods_id)->where('user_rank', $rank)->update($data);
				}
			} else {
				if ($price == -1) {
					$sql = '';
				} else {
					$data = array(
						'goods_id' 		=> $goods_id,
						'user_rank' 	=> $rank,
						'user_price' 	=> $price
					);
// 					$db->insert($data);
					RC_DB::table('member_price')->insert($data);
				}
			}
		}
	}
}

/**
* 保存某商品的扩展分类
*
* @param int $goods_id
*            商品编号
* @param array $cat_list
*            分类编号数组
* @return void
*/
function handle_other_cat($goods_id, $add_list) {
	/* 查询现有的扩展分类 */
// 	$db = RC_Model::model('goods/goods_cat_model');
// 	$db->where(array('goods_id' => $goods_id))->delete();
	
	RC_DB::table('goods_cat')->where('goods_id', $goods_id)->delete();
	if (!empty ($add_list)) {
		$data = array();
		foreach ($add_list as $cat_id) {
			$data[] = array(
				'goods_id'  => $goods_id,
				'cat_id'    => $cat_id
			);
		}
// 		$db->batch_insert($data);
		RC_DB::table('goods_cat')->insert($data);
	}
}

/**
* 保存某商品的关联商品
*
* @param int $goods_id
* @return void
*/
function handle_link_goods($goods_id) {
	$db = RC_Model::model('goods/link_goods_model');
	$data1 = array(
		'goods_id' => $goods_id
	);
	$data2 = array(
		'link_goods_id' => $goods_id
	);
	$db->where(array('goods_id' => 0, 'admin_id' => $_SESSION [admin_id]))->update($data1);
	$db->where(array('link_goods_id' => 0, 'admin_id' => $_SESSION [admin_id]))->update($data2);
}

/**
* 保存某商品的配件
*
* @param int $goods_id
* @return void
*/
function handle_group_goods($goods_id) {
	$db = RC_Model::model('goods/group_goods_model');
	$data = array('parent_id' => $goods_id);
	$db->where(array('parent_id' => 0, 'admin_id' => $_SESSION [admin_id]))->update($data);
}

/**
* 保存某商品的关联文章
*
* @param int $goods_id
* @return void
*/
function handle_goods_article($goods_id) {
	$db = RC_Model::model('goods/goods_article_model');
	$data = array(
		'goods_id' => $goods_id
	);
	$db->where(array('goods_id' => 0, 'admin_id' => $_SESSION [admin_id]))->update($data);
}

/**
* 修改商品某字段值
*
* @param string $goods_id
*            商品编号，可以为多个，用 ',' 隔开
* @param string $field
*            字段名
* @param string $value
*            字段值
* @return bool
*/
function update_goods($goods_id, $field, $value) {
// 	$db = RC_Model::model('goods/goods_model');
// 	RC_Loader::load_app_func('common', 'goods');
	if ($goods_id) {
		$data = array(
			$field 			=> $value,
			'last_update' 	=> RC_Time::gmtime()
		);
// 		$db->in(array('goods_id' => $goods_id))->update($data);
		RC_DB::table('goods')->whereIn('goods_id', $goods_id)->update($data);
		
	} else {
		return false;
	}
}

/**
* 从回收站删除多个商品
*
* @param mix $goods_id
*            商品id列表：可以逗号格开，也可以是数组
* @return void
*/
function delete_goods($goods_id) {
	RC_Loader::load_app_func('common', 'goods');
	
	$db_goods 			= RC_Model::model('goods/goods_model');
	$db_products 		= RC_Model::model('goods/products_model');
	$db_goods_gallery 	= RC_Model::model('goods/goods_gallery_model');
	$db_collect_goods 	= RC_Model::model('goods/collect_goods_model');
	$db_goods_article 	= RC_Model::model('goods/goods_article_model');
	$db_goods_attr 		= RC_Model::model('goods/goods_attr_model');
	$db_goods_cat 		= RC_Model::model('goods/goods_cat_model');
	$db_member 			= RC_Model::model('goods/member_price_model');
	$db_group 			= RC_Model::model('goods/group_goods_model');
	$db_link_goods 		= RC_Model::model('goods/link_goods_model');
// 	$db_tag 			= RC_Model::model('goods/tag_model');
	$db_comment 		= RC_Model::model('comment/comment_model');
// 	$db_virtual_card 	= RC_Model::model('goods/virtual_card_model');
	
	if (empty($goods_id)) {
		return;
	}
	
// 	$data = $db_goods->field('goods_thumb, goods_img, original_img')->in(array('goods_id' => $goods_id))->select();
	$data = RC_DB::table('goods')->select('goods_thumb', 'goods_img', 'original_img')->whereIn('goods_id', $goods_id)->get();
	
	if (!empty($data)) {
		$disk = RC_Filesystem::disk();
		foreach ($data as $goods) {
			if (!empty($goods['goods_thumb'])) {
				$disk->delete(RC_Upload::upload_path() . $goods['goods_thumb']);
			}
			if (!empty($goods['goods_img'])) {
				$disk->delete(RC_Upload::upload_path() . $goods['goods_img']);
			}
			if (!empty($goods['original_img'])) {
				$disk->delete(RC_Upload::upload_path() . $goods['original_img']);
			}
		}
	}

	/* 删除商品 */
// 	$db_goods->in(array('goods_id' => $goods_id))->delete();
	RC_DB::table('goods')->whereIn('goods_id', $goods_id)->delete();

	/* 删除商品的货品记录 */
// 	$db_products->in(array('goods_id' => $goods_id))->delete();
	RC_DB::table('products')->whereIn('goods_id', $goods_id)->delete();

	/* 删除商品相册的图片文件 */
	$data = $db_goods_gallery->field('img_url, thumb_url, img_original')->in(array('goods_id' => $goods_id))->select();
	RC_DB::table('goods_gallery')->select('img_url', 'thumb_url', 'img_original')->whereIn('goods_id', $goods_id)->get();

	if (!empty($data)) {
		$disk = RC_Filesystem::disk();
		foreach ($data as $row) {
			if (!empty($row ['img_url'])) {
				$disk->delete(RC_Upload::upload_path() . $row['img_url']);
			}
			if (!empty($row['thumb_url'])) {
				$disk->delete(RC_Upload::upload_path() . $row['thumb_url']);
			}
			if (!empty($row['img_original'])) {
				strrpos($row['img_original'], '?') && $row['img_original'] = substr($row['img_original'], 0, strrpos($row['img_original'], '?'));
				$disk->delete(RC_Upload::upload_path() . $row['img_original']);
			}
		}
	}
	/* 删除商品相册 */
// 	$db_goods_gallery->in(array('goods_id' => $goods_id))->delete();
	RC_DB::table('goods_gallery')->whereIn('goods_id', $goods_id)->delete();
	
	/* 删除相关表记录 */
// 	$db_collect_goods->in(array('goods_id' => $goods_id))->delete();
// 	$db_goods_article->in(array('goods_id' => $goods_id))->delete();
// 	$db_goods_attr->in(array('goods_id' => $goods_id))->delete();
// 	$db_goods_cat->in(array('goods_id' => $goods_id))->delete();
// 	$db_member->in(array('goods_id' => $goods_id))->delete();
// 	$db_group->in(array('parent_id' => $goods_id))->delete();
// 	$db_group->in(array('goods_id' => $goods_id))->delete();
// 	$db_link_goods->in(array('goods_id' => $goods_id))->delete();
// 	$db_link_goods->in(array('link_goods_id' => $goods_id))->delete();
// 	$db_tag->in(array('goods_id' => $goods_id))->delete();
// 	$db_comment->where(array('comment_type' => 0))->in(array('id_value' => $goods_id))->delete();
	
	RC_DB::table('collect_goods')->whereIn('goods_id', $goods_id)->delete();
	RC_DB::table('goods_article')->whereIn('goods_id', $goods_id)->delete();
	RC_DB::table('goods_attr')->whereIn('goods_id', $goods_id)->delete();
	RC_DB::table('goods_cat')->whereIn('goods_id', $goods_id)->delete();
	RC_DB::table('member_price')->whereIn('goods_id', $goods_id)->delete();
	RC_DB::table('group_goods')->whereIn('parent_id', $goods_id)->orWhereIn('goods_id', $goods_id)->delete();
	RC_DB::table('link_goods')->whereIn('goods_id', $goods_id)->orWhereIn('link_goods_id', $goods_id)->delete();
// 	RC_DB::table('tag')->whereIn('goods_id', $goods_id)->delete();
	RC_DB::table('comment')->where('comment_type', 0)->whereIn('id_value', $goods_id)->delete();
	
	/* 删除相应虚拟商品记录 */
// 	$query = $db_virtual_card->in(array('goods_id' => $goods_id))->delete();

// 	if (!$query && $db_goods->errno() != 1146) {
// 		die ($db_goods->error());
// 	}
}

/**
* 为某商品生成唯一的货号
*
* @param int $goods_id
*            商品编号
* @return string 唯一的货号
*/
function generate_goods_sn($goods_id) {
// 	$db = RC_Model::model('goods/goods_model');
	$goods_sn = ecjia::config('sn_prefix') . str_repeat('0', 6 - strlen($goods_id)) . $goods_id;
// 	$sn_list = $db->field('goods_sn')->where('goods_sn LIKE "%' . mysql_like_quote($goods_sn) . '%" AND goods_id <> ' . $goods_id . '')->order('LENGTH(goods_sn) DESC')->select();
	
	$sn_list = RC_DB::table('goods')->where('goods_sn', 'like', '%' . mysql_like_quote($goods_sn) . '%')
		->where('goods_id', '!=', $goods_id)->orderBy(RC_DB::raw('LENGTH(goods_sn)'), 'desc')
		->get();

	/* 判断数组为空就创建数组类型否则类型为null 报错 */
	$sn_list = empty($sn_list) ? array() : $sn_list;
	if (in_array($goods_sn, $sn_list)) {
		$max = pow(10, strlen($sn_list[0]) - strlen($goods_sn) + 1) - 1;
		$new_sn = $goods_sn . mt_rand(0, $max);
		while (in_array($new_sn, $sn_list)) {
			$new_sn = $goods_sn . mt_rand(0, $max);
		}
		$goods_sn = $new_sn;
	}
	return $goods_sn;
}

/**
* 商品货号是否重复
*
* @param string $goods_sn
*            商品货号；请在传入本参数前对本参数进行SQl脚本过滤
* @param int $goods_id
*            商品id；默认值为：0，没有商品id
* @return bool true，重复；false，不重复
*/
function check_goods_sn_exist($goods_sn, $goods_id = 0) {
//     $db = RC_Model::model('goods/goods_model');
    
	$goods_sn = trim($goods_sn);
	$goods_id = intval($goods_id);
	
	if (strlen($goods_sn) == 0) {
		return true; // 重复
	}

	$db_goods = RC_DB::table('goods');
	
	$db_goods->where('goods_sn', $goods_sn);
	if (!empty ($goods_id)) {
// 		$res = $db->field('goods_id')->find(array('goods_sn' => $goods_sn));
// 	} else {
// 		$res = $db->field('goods_id')->find(array('goods_sn' => $goods_sn, 'goods_id' => array('neq' => $goods_id)));
		$db_goods->where('goods_id', '!=', $goods_id);
	}
	$res = $db_goods->first();

	if (empty ($res)) {
		return false; // 不重复
	} else {
		return true; // 重复
	}
}

/**
* 取得通用属性和某分类的属性，以及某商品的属性值
*
* @param int $cat_id
*            分类编号
* @param int $goods_id
*            商品编号
* @return array 规格与属性列表
*/
function get_attr_list($cat_id, $goods_id = 0) {
	$dbview = RC_Model::model('goods/attribute_goods_viewmodel');
	if (empty ($cat_id)) {
		return array();
	}

	$dbview->view = array(
		'goods_attr' => array(
			'type' => Component_Model_View::TYPE_LEFT_JOIN,
			'alias' => 'v',
			'field' => 'a.attr_id, a.attr_name, a.attr_input_type, a.attr_type, a.attr_values, v.attr_value, v.attr_price',
			'on' => "v.attr_id = a.attr_id AND v.goods_id = '$goods_id'"
		)
	);
	$row = $dbview->where('a.cat_id = "' . intval($cat_id) . '" OR a.cat_id = 0')->order(array('a.sort_order' => 'asc', 'a.attr_type' => 'asc', 'a.attr_id' => 'asc', 'v.attr_price' => 'asc', 'v.goods_attr_id' => 'asc'))->select();
	return $row;
}

/**
* 获取商品类型中包含规格的类型列表
*
* @access public
* @return array
*/
function get_goods_type_specifications() {
// 	$db = RC_Model::model('goods/attribute_model');
// 	$row = $db->field('DISTINCT cat_id')->where(array('attr_type' => 1))->select();
	
	$row = RC_DB::table('attribute')->selectRaw('DISTINCT cat_id')->where('attr_type', 1)->get();
	$return_arr = array();
	if (!empty($row)) {
		foreach ($row as $value) {
			$return_arr[$value['cat_id']] = $value['cat_id'];
		}
	}
	return $return_arr;
}

/**
* 根据属性数组创建属性的表单
*
* @access public
* @param int $cat_id
*            分类编号
* @param int $goods_id
*            商品编号
* @return string
*/
function build_attr_html($cat_id, $goods_id = 0) {
	$attr = get_attr_list($cat_id, $goods_id);
	$html = '';
	$spec = 0;
	
	if (!empty($attr)) {
		foreach ($attr as $key => $val) {
			$html .= "<div class='control-group formSep'><label class='control-label'>";
			$html .= "$val[attr_name]</label><div class='controls'><input type='hidden' name='attr_id_list[]' value='$val[attr_id]' />";
			if ($val ['attr_input_type'] == 0) {
				$html .= '<input name="attr_value_list[]" type="text" value="' . htmlspecialchars($val ['attr_value']) . '" size="40" /> ';
			} elseif ($val ['attr_input_type'] == 2) {
				$html .= '<textarea name="attr_value_list[]" rows="3" cols="40">' . htmlspecialchars($val ['attr_value']) . '</textarea>';
			} else {
				$html .= '<select name="attr_value_list[]" autocomplete="off">';
				$html .= '<option value="">' . RC_Lang::lang('select_please') . '</option>';
				$attr_values = explode("\n", $val ['attr_values']);
				foreach ($attr_values as $opt) {
					$opt = trim(htmlspecialchars($opt));
		
					$html .= ($val ['attr_value'] != $opt) ? '<option value="' . $opt . '">' . $opt . '</option>' : '<option value="' . $opt . '" selected="selected">' . $opt . '</option>';
				}
				$html .= '</select> ';
			}
			$html .= ($val ['attr_type'] == 1 || $val ['attr_type'] == 2) ? '<span class="m_l5 m_r5">' . RC_Lang::lang('spec_price') . '</span>' . ' <input type="text" name="attr_price_list[]" value="' . $val ['attr_price'] . '" size="5" maxlength="10" />' : ' <input type="hidden" name="attr_price_list[]" value="0" />';
			if ($val ['attr_type'] == 1 || $val ['attr_type'] == 2) {
				$html .= ($spec != $val ['attr_id']) ? "<a class='m_l5' href='javascript:;' data-toggle='clone-obj' data-parent='.control-group'><i class='fontello-icon-plus'></i></a>" : "<a class='m_l5' href='javascript:;' data-trigger='toggleSpec'><i class='fontello-icon-minus'></i></a>";
				$spec = $val ['attr_id'];
			}
			$html .= '</div></div>';
		}
	}
	$html .= '';
	return $html;
}

/**
* 获得指定商品相关的商品
*
* @access public
* @param integer $goods_id
* @return array
*/
function get_linked_goods($goods_id) {
	$dbview = RC_Model::model('goods/link_goods_viewmodel');
	$dbview->view = array(
		'goods' => array(
			'type' 	=> Component_Model_View::TYPE_LEFT_JOIN,
			'alias' => 'g',
			'field' => 'lg.link_goods_id AS goods_id, g.goods_name, lg.is_double',
			'on' 	=> 'lg.link_goods_id = g.goods_id'
		)
	);
	if ($goods_id == 0) {
		$row = $dbview->where(array('lg.admin_id' => $_SESSION['admin_id']))->select();
	}
	$row = $dbview->where(array('lg.goods_id' => $goods_id))->select();
	return $row;
}

/**
* 获得指定商品的配件
*
* @access public
* @param integer $goods_id
* @return array
*/
function get_group_goods($goods_id) {
	$dbview = RC_Model::model('goods/group_viewmodel');
	$dbview->view = array(
		'goods' => array(
			'type' 	=> Component_Model_View::TYPE_LEFT_JOIN,
			'alias' => 'g',
			'field' => "gg.goods_id, g.goods_name, gg.goods_price",
			'on' 	=> 'gg.goods_id = g.goods_id'
		)
	);
	if ($goods_id == 0) {
		$row = $dbview->where(array('gg.parent_id' => $goods_id, 'gg.admin_id' => $_SESSION ['admin_id']))->select();
	}
	$row = $dbview->where(array('gg.parent_id' => $goods_id))->select();
	return $row;
}

/**
* 获得商品的关联文章
*
* @access public
* @param integer $goods_id
* @return array
*/
function get_goods_articles($goods_id) {
	$dbview = RC_Model::model('goods/goods_article_viewmodel');
	$dbview->view = array(
		'article' => array(
			'type'  => Component_Model_View::TYPE_LEFT_JOIN,
			'alias' => 'a',
			'field' => 'ga.article_id, a.title',
			'on'    => 'ga.article_id = a.article_id'
		)
	);

	if ($goods_id == 0) {
		$row = $dbview->where(array('ga.goods_id' => $goods_id, 'ga.admin_id' => $_SESSION ['admin_id']))->select();
	}
	return $dbview->where(array('ga.goods_id' => $goods_id))->select();
}

/**
* 检测商品是否有货品
*
* @access public
* @param
*            s integer $goods_id 商品id
* @param
*            s string $conditions sql条件，AND语句开头
* @return string number -1，错误；1，存在；0，不存在
*/
function check_goods_product_exist($goods_id, $conditions = '') {
	$db = RC_Model::model('goods/products_model');
	if (empty ($goods_id)) {
		return -1; 
	}
	$result = $db->field('goods_id')->find('goods_id = ' . $goods_id . $conditions . '');
	if (empty($result)) {
		return 0;
	}
	return 1;
}

/**
* 获得商品的货品总库存
*
* @access public
* @param
*            s integer $goods_id 商品id
* @param
*            s string $conditions sql条件，AND语句开头
* @return string number
*/
function product_number_count($goods_id, $conditions = '') {
// 	$db = RC_Model::model('goods/products_model');
	if (empty($goods_id)) {
		return -1; // $goods_id不能为空
	}
// 	$nums = $db->where('goods_id = ' . $goods_id . $conditions . '')->sum('product_number');
	$nums = RC_DB::table('products')->whereRaw('goods_id = ' . $goods_id . $conditions . '')->sum('product_number');
	$nums = empty ($nums) ? 0 : $nums;
	return $nums;
}

/**
* 获得商品的规格属性值列表
*
* @access public
* @param
*            s integer $goods_id
* @return array
*/
function product_goods_attr_list($goods_id) {
// 	$db = RC_Model::model('goods/goods_attr_model');
// 	$results = $db->field('goods_attr_id, attr_value')->where(array('goods_id' => $goods_id))->select();
	$results = RC_DB::table('goods_attr')->select('goods_attr_id', 'attr_value')->where('goods_id', $goods_id)->get();
	
	$return_arr = array();
	if (!empty ($results)) {
		foreach ($results as $value) {
			$return_arr [$value ['goods_attr_id']] = $value ['attr_value'];
		}
	}
	return $return_arr;
}

/**
* 获得商品已添加的规格列表
*
* @access public
* @param
*            s integer $goods_id
* @return array
*/
function get_goods_specifications_list($goods_id) {
// 	$dbview = RC_Model::model('goods/goods_attr_attribute_viewmodel');

	if (empty($goods_id)) {
		return array(); // $goods_id不能为空
	}

// 	$dbview->view = array(
// 		'attribute' => array(
// 			'type'	=> Component_Model_View::TYPE_LEFT_JOIN,
// 			'alias' => 'a',
// 			'field' => 'ga.goods_attr_id, ga.attr_value, ga.attr_id, a.attr_name',
// 			'on'	=> 'a.attr_id = ga.attr_id'
// 		)
// 	);
// 	return $dbview->where(array('goods_id' => $goods_id, 'a.attr_type' => 1))->order(array('ga.attr_id' => 'ASC'))->select();
	
	return RC_DB::table('goods_attr as ga')
		->leftJoin('attribute as a', RC_DB::raw('a.attr_id'), '=', RC_DB::raw('ga.attr_id'))
		->where('goods_id', $goods_id)
		->where(RC_DB::raw('a.attr_type'), 1)
		->selectRaw('ga.goods_attr_id, ga.attr_value, ga.attr_id, a.attr_name')
		->orderBy(RC_DB::raw('ga.attr_id'), 'asc')
		->get();
}

/**
* 获得商品的货品列表
*
* @access public
* @param
*            s integer $goods_id
* @param
*            s string $conditions
* @return array
*/
function product_list($goods_id, $conditions = '') {
	$db = RC_Model::model('goods/products_model');
	/* 过滤条件 */
	$param_str = '-' . $goods_id;

	$day 	= getdate();
	$today 	= RC_Time::local_mktime(23, 59, 59, $day ['mon'], $day ['mday'], $day ['year']);
	$filter ['goods_id'] 		= $goods_id;
	$filter ['keyword'] 		= empty ($_REQUEST ['keyword']) ? '' : trim($_REQUEST ['keyword']);
	$filter ['stock_warning'] 	= empty ($_REQUEST ['stock_warning']) ? 0 : intval($_REQUEST ['stock_warning']);
	$filter ['sort_by'] 		= empty ($_REQUEST ['sort_by']) ? 'product_id' : trim($_REQUEST ['sort_by']);
	$filter ['sort_order'] 		= empty ($_REQUEST ['sort_order']) ? 'DESC' : trim($_REQUEST ['sort_order']);
	$filter ['extension_code'] 	= empty ($_REQUEST ['extension_code']) ? '' : trim($_REQUEST ['extension_code']);
	$filter ['page_count'] 		= isset ($filter ['page_count']) ? $filter ['page_count'] : 1;

	$where = '';
	/* 库存警告 */
	if ($filter ['stock_warning']) {
		$where .= ' AND goods_number <= warn_number ';
	}

	/* 关键字 */
	if (!empty ($filter ['keyword'])) {
		$where .= " AND (product_sn LIKE '%" . $filter ['keyword'] . "%')";
	}

	$where .= $conditions;

	/* 记录总数 */    
// 	$count = $db->where('goods_id = ' . $goods_id . $where . '')->count();
	$count = RC_DB::table('products')->whereRaw('goods_id = ' . $goods_id . $where)->count();
	$filter ['record_count'] = $count;

// 	$row = $db->field('product_id, goods_id, goods_attr|goods_attr_str, goods_attr, product_sn, product_number')->where('goods_id = ' . $goods_id . $where . '')->order($filter ['sort_by'] . ' ' . $filter ['sort_order'])->select();
	$row = RC_DB::table('products')
		->selectRaw('product_id, goods_id, goods_attr as goods_attr_str, goods_attr, product_sn, product_number')
		->whereRaw('goods_id = ' . $goods_id . $where)
		->orderBy($filter ['sort_by'], $filter['sort_order'])
		->get();
	
	/* 处理规格属性 */
	$goods_attr = product_goods_attr_list($goods_id);
	if (!empty ($row)) {
		foreach ($row as $key => $value) {
			$_goods_attr_array = explode('|', $value ['goods_attr']);
			if (is_array($_goods_attr_array)) {
				$_temp = '';
				foreach ($_goods_attr_array as $_goods_attr_value) {
					$_temp[] = $goods_attr [$_goods_attr_value];
				}
				$row [$key] ['goods_attr'] = $_temp;
			}
		}
	}
	return array(
		'product'		=> $row,
		'filter'		=> $filter,
		'page_count'	=> $filter ['page_count'],
		'record_count'	=> $filter ['record_count']
	);
}

/**
* 取货品信息
*
* @access public
* @param int $product_id
*            货品id
* @param int $filed
*            字段
* @return array
*/
function get_product_info($product_id, $filed = '') {
// 	$db = RC_Model::model('goods/products_model');
	
	$return_array = array();
	if (empty ($product_id)) {
		return $return_array;
	}
	$filed = trim($filed);
	if (empty ($filed)) {
		$filed = '*';
	}
	
// 	$return_array = $db->field($filed)->find(array('product_id' => $product_id));
// 	return $return_array;
	
	return RC_DB::table('products')->selectRaw($field)->where('product_id', $product_id)->first();
}

/**
* 检查单个商品是否存在规格
*
* @param int $goods_id
*            商品id
* @return bool true，存在；false，不存在
*/
function check_goods_specifications_exist($goods_id) {
	$dbview = RC_Model::model('goods/attribute_goods_viewmodel');
	$goods_id = intval($goods_id);
	$dbview->view = array(
		'goods' => array(
			'type'	=> Component_Model_View::TYPE_LEFT_JOIN,
			'alias'	=> 'g',
			'on' 	=> 'a.cat_id = g.goods_type'
		)
	);
	$count = $dbview->where(array('g.goods_id' => $goods_id))->count('a.attr_id');
	if ($count > 0) {
		return true; // 存在
	} else {
		return false; // 不存在
	}
}

/**
* 商品的货品规格是否存在
*
* @param string $goods_attr
*            商品的货品规格
* @param string $goods_id
*            商品id
* @param int $product_id
*            商品的货品id；默认值为：0，没有货品id
* @return bool true，重复；false，不重复
*/
function check_goods_attr_exist($goods_attr, $goods_id, $product_id = 0) {
// 	$db = RC_Model::model('goods/products_model');
	
	$db_products = RC_DB::table('products');
	$goods_id = intval($goods_id);
	if (strlen($goods_attr) == 0 || empty ($goods_id)) {
		return true; // 重复
	}
	
	$db_products->where('goods_attr', $goods_attr)->where('goods_id', $goods_id);
	if (!empty ($product_id)) {
// 		$res = $db->where(array('goods_attr' => $goods_attr, 'goods_id' => $goods_id, 'product_id' => array('neq' => $product_id)))->get_field('product_id');
		$db_products->where('product_id', '!=', $product_id);
	}
	$res = $db_products->pluck('product_id');
	
	if (empty ($res)) {
		return false; // 不重复
	} else {
		return true; // 重复
	}
}

/**
 * 商品的货品货号是否重复
 *
 * @param string $product_sn
 *            商品的货品货号；请在传入本参数前对本参数进行SQl脚本过滤
 * @param int $product_id
 *            商品的货品id；默认值为：0，没有货品id
 * @return bool true，重复；false，不重复
 */
function check_product_sn_exist($product_sn, $product_id = 0) {
	$product_sn = trim($product_sn);
	$product_id = intval($product_id);
	
	if (strlen($product_sn) == 0) {
		return true; // 重复
	}
	
	$query = RC_DB::table('goods')->where('goods_sn', $product_sn)->pluck('goods_id');
	
	if ($query) {
		return true; // 重复
	}
	
	$db_product = RC_DB::table('products')->where('product_sn', $product_sn);
	
	if (!empty($product_id)) {
		$db_product->where('product_id', '!=', $product_id);
	}
	$res = $db_product->pluck('product_id');
	
	if (empty ($res)) {
		return false; // 不重复
	} else {
		return true; // 重复
	}
}
	
//end