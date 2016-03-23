<?php

/**
*  ECJIA 管理中心商品相关函数
*/
defined('IN_ECJIA') or exit('No permission resources.');

// /**
// * 取得推荐类型列表
// *
// * @return array 推荐类型列表
// */
// function get_intro_list()
// {
// 	return array(
// 		'is_best'		=> RC_Lang::lang('is_best'),
// 		'is_new'		=> RC_Lang::lang('is_new'),
// 		'is_hot'		=> RC_Lang::lang('is_hot'),
// 		'is_promote'	=> RC_Lang::lang('is_promote'),
// 		'all_type'		=> RC_Lang::lang('all_type')
// 		);
// }

// /**
// * 取得重量单位列表
// *
// * @return array 重量单位列表
// */
// function get_unit_list()
// {
// 	return array(
// 		'1' =>		RC_Lang::lang('unit_kg'),
// 		'0.001' =>	RC_Lang::lang('unit_g')
// 		);
// }

/**
* 取得会员等级列表
*
* @return array 会员等级列表
*/
function get_user_rank_list()
{
	$db = RC_Loader::load_app_model('user_rank_model', 'user');
	return $db->order('min_points asc')->select();
}

/**
* 取得某商品的会员价格列表
*
* @param int $goods_id
*            商品编号
* @return array 会员价格列表 user_rank => user_price
*/
function get_member_price_list($goods_id)
{
	/* 取得会员价格 */
	$db = RC_Loader::load_app_model('member_price_model', 'goods');
	/* 取得会员价格 */
	$price_list = array();
	$data = $db->field('user_rank, user_price')->where(array('goods_id' => $goods_id))->select();
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
function handle_goods_attr($goods_id, $id_list, $is_spec_list, $value_price_list)
{
	$db = RC_Loader::load_app_model('goods_attr_model', 'goods');

	$goods_attr_id = array();

	/* 循环处理每个属性 */
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
		$result_id = $db->where(array('goods_id' => $goods_id, 'attr_id' => $id, 'attr_value' => $value))->get_field('goods_attr_id');
// 		$result_id = $result_id ['goods_attr_id'];
		
		if (!empty ($result_id)) {
			$data = array(
				'attr_value' => $value
				);
			$db->where(array('goods_id' => $goods_id, 'attr_id' => $id, 'goods_attr_id' => $result_id))->update($data);
			$goods_attr_id [$id] = $result_id;
		} else {
			$data = array(
				'goods_id' => $goods_id,
				'attr_id' => $id,
				'attr_value' => $value,
				'attr_price' => $price
				);
// 			$insert_id = $db->insert($data);
			$goods_attr_id [$id] = $db->insert($data);
		}
// 		if ($goods_attr_id [$id] == '') {
// 			$goods_attr_id [$id] = $insert_id;
// 		}
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
function handle_member_price($goods_id, $rank_list, $price_list)
{
	$db = RC_Loader::load_app_model('member_price_model', 'goods');
	/* 循环处理每个会员等级 */
	foreach ($rank_list as $key => $rank) {
		/* 会员等级对应的价格 */
		$price = $price_list [$key];

		// 插入或更新记录
		$count = $db->where(array('goods_id' => $goods_id, 'user_rank' => $rank))->count();

		if ($count) {
			/* 如果会员价格是小于0则删除原来价格，不是则更新为新的价格 */
			if ($price < 0) {
				$db->where(array('goods_id' => $goods_id, 'user_rank' => $rank))->delete();
			} else {
				$data = array(
					'user_price' => $price
					);
				$db->where(array('goods_id' => $goods_id, 'user_rank' => $rank))->update($data);
			}
		} else {
			if ($price == -1) {
				$sql = '';
			} else {
				$data = array(
					'goods_id' => $goods_id,
					'user_rank' => $rank,
					'user_price' => $price
					);
				$db->insert($data);
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
function handle_other_cat($goods_id, $add_list)
{
	/* 查询现有的扩展分类 */
	$db = RC_Loader::load_app_model('goods_cat_model', 'goods');

	$db->where(array('goods_id' => $goods_id))->delete();
	
	if (!empty ($add_list)) {
		$data = array();
		foreach ($add_list as $cat_id) {
			// 插入记录
			$data[] = array(
				'goods_id'  => $goods_id,
				'cat_id'    => $cat_id
				);
		}
		$db->batch_insert($data);
	}
	
	
	/* 查询现有的扩展分类 */
// 	$exist_list = $db->field('cat_id')->where(array('goods_id' => $goods_id))->select();
// 	if (empty($exist_list)){
// 	    $exist_list =array();
// 	} else {
// 	    foreach ($exist_list as $value) {
// 	        $arr[] = $value['cat_id'];
// 	    }
// 	}
// 	$exist_list = $arr;
// 	/* 删除不再有的分类 */
	
// 	$delete_list = array_diff($exist_list, $cat_list);
// 	array_unique($delete_list);
// 	_dump($delete_list);
// 	if ($delete_list) {
// 		$db->where(array('goods_id' => $goods_id))->in(array('cat_id' => $delete_list))->delete();
// 	}

// 	array_unique($delete_list);
	/* 添加新加的分类 */
// 	$add_list = array_diff($cat_list, $exist_list, array(0));
// 		$db->insert($data);
}

/**
* 保存某商品的关联商品
*
* @param int $goods_id
* @return void
*/
function handle_link_goods($goods_id)
{
	$db = RC_Loader::load_app_model('link_goods_model', 'goods');
	$data1 = array(
		'goods_id'		=> $goods_id
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
function handle_group_goods($goods_id)
{
	$db = RC_Loader::load_app_model('group_goods_model', 'goods');
	$data = array('parent_id' => $goods_id);
	$db->where(array('parent_id' => 0, 'admin_id' => $_SESSION [admin_id]))->update($data);
}

/**
* 保存某商品的关联文章
*
* @param int $goods_id
* @return void
*/
function handle_goods_article($goods_id)
{
	$db = RC_Loader::load_app_model('goods_article_model', 'goods');
	$data = array(
		'goods_id' => $goods_id
		);
	$db->where(array('goods_id' => 0, 'admin_id' => $_SESSION [admin_id]))->update($data);
}

// /**
// * 保存某商品的相册图片
// *
// * @param int $goods_id
// * @param array $image_files
// * @param array $image_descs
// * @return void
// */
// function handle_gallery_image($goods_id, $image_files, $image_descs, $image_urls)
// {
//     $db = RC_Loader::load_app_model('goods_gallery_model', 'goods');
// 	/* 是否处理缩略图 */
// 	$proc_thumb = (isset ($GLOBALS ['shop_id']) && $GLOBALS ['shop_id'] > 0) ? false : true;
// 	foreach ($image_descs as $key => $img_desc) {
// 		/* 是否成功上传 */
// 		$flag = false;
// 		if (isset ($image_files ['error'])) {
// 			if ($image_files ['error'] [$key] == 0) {
// 				$flag = true;
// 			}
// 		} else {
// 			if ($image_files ['tmp_name'] [$key] != 'none') {
// 				$flag = true;
// 			}
// 		}

// 		if ($flag) {
// 			if ($proc_thumb) {
// 			    //TODO
// // 				$thumb_url = $image->make_thumb($image_files ['tmp_name'] [$key], ecjia::config('thumb_width'), ecjia::config('thumb_height'));
// 				$thumb_url = is_string($thumb_url) ? $thumb_url : '';
// 			}

// 			$upload = array(
// 				'name'		=> $image_files ['name'] [$key],
// 				'type'		=> $image_files ['type'] [$key],
// 				'tmp_name'	=> $image_files ['tmp_name'] [$key],
// 				'size'		=> $image_files ['size'] [$key]
// 				);
// 			if (isset ($image_files ['error'])) {
// 				$upload ['error'] = $image_files ['error'] [$key];
// 			}

// 			$img_original = RC_Upload::uploader('image', array('save_path' => './data/afficheimg', 'auto_sub_dirs' => false));
// 			if ($img_original === false) {
// 			}
// 			$img_url = $img_original;

// 			if (!$proc_thumb) {
// 				$thumb_url = $img_original;
// 			}
// 			// 如果服务器支持GD 则添加水印
// 			if ($proc_thumb && RC_ENV::gd_version() > 0) {
// 				$pos = strpos(basename($img_original), '.');
// 				$newname = dirname($img_original) . '/' . RC_Upload::random_filename() . substr(basename($img_original), $pos);
// 				copy(RC_Upload::upload_path() . $img_original, RC_Upload::upload_path() . $newname);
// 				$img_url = $newname;
// 				$GLOBALS ['image']->add_watermark(RC_Upload::upload_path() . $img_url, '', ecjia::config('watermark'), ecjia::config('watermark_place'), ecjia::config('watermark_alpha'));
// 			}

// 			/* 重新格式化图片名称 */
// 			$img_original = reformat_image_name('gallery', $goods_id, $img_original, 'source');
// 			$img_url = reformat_image_name('gallery', $goods_id, $img_url, 'goods');
// 			$thumb_url = reformat_image_name('gallery_thumb', $goods_id, $thumb_url, 'thumb');
// 			$data = array(
// 				'goods_id'		=> $goods_id,
// 				'img_url'		=> $img_url,
// 				'img_desc'		=> $img_desc,
// 				'thumb_url'		=> $thumb_url,
// 				'img_original'	=> $img_original
// 				);
// 			$db->insert($data);
// 			/* 不保留商品原图的时候删除原图 */
// 			if ($proc_thumb && !ecjia::config('retain_original_img') && !empty ($img_original)) {
// 				$data = array(
// 					'img_original' => ''
// 					);
// 				$db->where('`goods_id` = {' . $goods_id . '}')->update($data);
// 				@unlink(RC_Upload::upload_path() . $img_original);
// 			}
// 		} elseif (!empty ($image_urls [$key]) && ($image_urls [$key] != RC_Lang::lang('img_file')) && ($image_urls [$key] != 'http://') && copy(trim($image_urls [$key]), ROOT_PATH . 'temp/' . basename($image_urls [$key]))) {
// 			$image_url = trim($image_urls [$key]);

// 			// 定义原图路径
// 			$down_img = RC_Upload::upload_path() . 'temp/' . basename($image_url);
// 			// 生成缩略图
// 			if ($proc_thumb) {
// 			    //TODO
// // 				$thumb_url = $image->make_thumb($down_img, ecjia::config('thumb_width'), ecjia::config('thumb_height'));
// 				$thumb_url = is_string($thumb_url) ? $thumb_url : '';
// 				$thumb_url = reformat_image_name('gallery_thumb', $goods_id, $thumb_url, 'thumb');
// 			}

// 			if (!$proc_thumb) {
// 				$thumb_url = htmlspecialchars($image_url);
// 			}

// 			/* 重新格式化图片名称 */
// 			$img_url = $img_original = htmlspecialchars($image_url);
// 			$data = array(
// 				'goods_id' => $goods_id,
// 				'img_url' => $img_url,
// 				'img_desc' => $img_desc,
// 				'thumb_url' => $thumb_url,
// 				'img_original' => $img_original
// 				);
// 			$db->insert($data);
// 			@unlink($down_img);
// 		}
// 	}
// }

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
function update_goods($goods_id, $field, $value)
{
	$db = RC_Loader::load_app_model('goods_model', 'goods');
	RC_Loader::load_app_func('common', 'goods');
	if ($goods_id) {
		$data = array(
			$field => $value,
			'last_update' => RC_Time::gmtime()
			);
		$db->in(array('goods_id' => $goods_id))->update($data);
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
function delete_goods($goods_id)
{
	RC_Loader::load_app_func('common', 'goods');
	$db_goods = RC_Loader::load_app_model('goods_model', 'goods');
	$db_products = RC_Loader::load_app_model('products_model', 'goods');
	$db_goods_gallery = RC_Loader::load_app_model('goods_gallery_model', 'goods');
	$db_collect_goods = RC_Loader::load_app_model('collect_goods_model', 'goods');
	$db_goods_article = RC_Loader::load_app_model('goods_article_model', 'goods');
	$db_goods_attr = RC_Loader::load_app_model('goods_attr_model', 'goods');
	$db_goods_cat = RC_Loader::load_app_model('goods_cat_model', 'goods');
	$db_member = RC_Loader::load_app_model('member_price_model', 'goods');
	$db_group = RC_Loader::load_app_model('group_goods_model', 'goods');
	$db_link_goods = RC_Loader::load_app_model('link_goods_model', 'goods');
	$db_tag = RC_Loader::load_app_model('tag_model', 'goods');
	$db_comment = RC_Loader::load_app_model('comment_model', 'comment');
	$db_virtual_card = RC_Loader::load_app_model('virtual_card_model', 'goods');
	if (empty($goods_id)) {
		return;
	}
	
	$data = $db_goods->field('goods_thumb, goods_img, original_img')->in(array('goods_id' => $goods_id))->select();
	if (!empty($data)) {
		$disk = RC_Filesystem::disk();
		foreach ($data as $goods) {
			if (!empty($goods['goods_thumb'])) {
// 				@unlink(RC_Upload::upload_path() . $goods['goods_thumb']);
				$disk->delete(RC_Upload::upload_path() . $goods['goods_thumb']);
			}
			if (!empty($goods['goods_img'])) {
// 				@unlink(RC_Upload::upload_path() . $goods['goods_img']);
				$disk->delete(RC_Upload::upload_path() . $goods['goods_img']);
			}
			if (!empty($goods['original_img'])) {
// 				@unlink(RC_Upload::upload_path() . $goods['original_img']);
				$disk->delete(RC_Upload::upload_path() . $goods['original_img']);
			}
		}
	}

	/* 删除商品 */
	$db_goods->in(array('goods_id' => $goods_id))->delete();

	/* 删除商品的货品记录 */
	$db_products->in(array('goods_id' => $goods_id))->delete();

	/* 删除商品相册的图片文件 */
	$data = $db_goods_gallery->field('img_url, thumb_url, img_original')->in(array('goods_id' => $goods_id))->select();

	if (!empty($data)) {
		$disk = RC_Filesystem::disk();
		foreach ($data as $row) {
			if (!empty($row ['img_url'])) {
// 				@unlink(RC_Upload::upload_path() . $row['img_url']);
				$disk->delete(RC_Upload::upload_path() . $row['img_url']);
			}
			if (!empty($row['thumb_url'])) {
// 				@unlink(RC_Upload::upload_path() . $row['thumb_url']);
				$disk->delete(RC_Upload::upload_path() . $row['thumb_url']);
			}
			if (!empty($row['img_original'])) {
// 				@unlink(RC_Upload::upload_path() . $row['img_original']);
				strrpos($row['img_original'], '?') && $row['img_original'] = substr($row['img_original'], 0, strrpos($row['img_original'], '?'));
				$disk->delete(RC_Upload::upload_path() . $row['img_original']);
			}
		}
	}
	/* 删除商品相册 */
	$db_goods_gallery->in(array('goods_id' => $goods_id))->delete();
	
	/* 删除相关表记录 */
	$db_collect_goods->in(array('goods_id' => $goods_id))->delete();
	$db_goods_article->in(array('goods_id' => $goods_id))->delete();
	$db_goods_attr->in(array('goods_id' => $goods_id))->delete();
	$db_goods_cat->in(array('goods_id' => $goods_id))->delete();
	$db_member->in(array('goods_id' => $goods_id))->delete();
	$db_group->in(array('parent_id' => $goods_id))->delete();
	$db_group->in(array('goods_id' => $goods_id))->delete();
	$db_link_goods->in(array('goods_id' => $goods_id))->delete();
	$db_link_goods->in(array('link_goods_id' => $goods_id))->delete();
	$db_tag->in(array('goods_id' => $goods_id))->delete();
	$db_comment->where(array('comment_type' => 0))->in(array('id_value' => $goods_id))->delete();

	/* 删除相应虚拟商品记录 */
	$query = $db_virtual_card->in(array('goods_id' => $goods_id))->delete();

	if (!$query && $db_goods->errno() != 1146) {
		die ($db_goods->error());
	}

}

/**
* 为某商品生成唯一的货号
*
* @param int $goods_id
*            商品编号
* @return string 唯一的货号
*/
function generate_goods_sn($goods_id)
{
	$db = RC_Loader::load_app_model('goods_model', 'goods');
	$goods_sn = ecjia::config('sn_prefix') . str_repeat('0', 6 - strlen($goods_id)) . $goods_id;
	$sn_list = $db->field('goods_sn')->where('goods_sn LIKE "' . mysql_like_quote($goods_sn) . '%" AND goods_id <> ' . $goods_id . '')->order('LENGTH(goods_sn) DESC')->select();

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
function check_goods_sn_exist($goods_sn, $goods_id = 0)
{
    $db = RC_Loader::load_app_model('goods_model', 'goods');
	$goods_sn = trim($goods_sn);
	$goods_id = intval($goods_id);
	if (strlen($goods_sn) == 0) {
		return true; // 重复
	}

	if (empty ($goods_id)) {
		$res = $db->field('goods_id')->find(array('goods_sn' => 'goods_sn'));
	} else {
		$res = $db->field('goods_id')->find(array('goods_sn' => $goods_sn, 'goods_id' => array('neq' => $goods_id)));
	}

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
function get_attr_list($cat_id, $goods_id = 0)
{
	$dbview = RC_Loader::load_app_model('attribute_goods_viewmodel', 'goods');
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
function get_goods_type_specifications()
{
 	$db = RC_Loader::load_app_model('attribute_model', 'goods');
	$row = $db->field('DISTINCT cat_id')->where(array('attr_type' => 1))->select();
	$return_arr = array();
	if (!empty($row)) {
		foreach ($row as $value) {
			$return_arr [$value ['cat_id']] = $value ['cat_id'];
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
function build_attr_html($cat_id, $goods_id = 0)
{
	$attr = get_attr_list($cat_id, $goods_id);
	$html = '';
	$spec = 0;

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
function get_linked_goods($goods_id)
{
	$dbview = RC_Loader::load_app_model('link_goods_viewmodel', 'goods');
	$dbview->view = array(
		'goods' => array(
			'type' => Component_Model_View::TYPE_LEFT_JOIN,
			'alias' => 'g',
			'field' => 'lg.link_goods_id AS goods_id, g.goods_name, lg.is_double',
			'on' => 'lg.link_goods_id = g.goods_id'
			)
		);

	if ($goods_id == 0) {
		$row = $dbview->where(array('lg.admin_id' => $_SESSION [admin_id]))->select();
	}
	$row = $dbview->where(array('lg.goods_id' => $goods_id))->select();
// 	if (!empty ($row)) {
// 		foreach ($row as $key => $val) {
// 			$linked_type = $val ['is_double'] == 0 ? RC_Lang::lang('single') : RC_Lang::lang('double');
// 			$row [$key] ['goods_name'] = $val ['goods_name'] . " -- [$linked_type]";
// 			unset($row[$key]['is_double']);
// 		}
// 	}

	return $row;
}

/**
* 获得指定商品的配件
*
* @access public
* @param integer $goods_id
* @return array
*/
function get_group_goods($goods_id)
{
	$dbview = RC_Loader::load_app_model('group_viewmodel', 'goods');
	$dbview->view = array(
		'goods' => array(
			'type' => Component_Model_View::TYPE_LEFT_JOIN,
			'alias' => 'g',
// 			'field' => "gg.goods_id, CONCAT(g.goods_name, ' -- [', gg.goods_price, ']') AS goods_name",
			'field' => "gg.goods_id, g.goods_name, gg.goods_price",
			'on' => 'gg.goods_id = g.goods_id'
			)
		);
	if ($goods_id == 0) {
		$row = $dbview->where(array('gg.parent_id' => $goods_id, 'gg.admin_id' => $_SESSION [admin_id]))->select();
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
function get_goods_articles($goods_id)
{
	$dbview = RC_Loader::load_app_model('goods_article_viewmodel', 'goods');
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

// /**
// * 获得商品列表
// *
// * @access public
// * @param
// *            s integer $isdelete
// * @param
// *            s integer $real_goods
// * @param
// *            s integer $conditions
// * @return array
// */
// function goods_list($is_delete, $real_goods = 1, $conditions = '')
// {
// 	$db = RC_Loader::load_app_model('goods_auto_viewmodel', 'goods');
// 	/* 过滤条件 */
// 	$param_str = '-' . $is_delete . '-' . $real_goods;
// 	$day = getdate();
// 	$today = RC_Time::local_mktime(23, 59, 59, $day ['mon'], $day ['mday'], $day ['year']);

// 	$filter ['cat_id'] = empty ($_REQUEST ['cat_id']) ? 0 : intval($_REQUEST ['cat_id']);
// 	$filter ['intro_type'] = empty ($_REQUEST ['intro_type']) ? '' : trim($_REQUEST ['intro_type']);
// 	$filter ['is_promote'] = empty ($_REQUEST ['is_promote']) ? 0 : intval($_REQUEST ['is_promote']);
// 	$filter ['stock_warning'] = empty ($_REQUEST ['stock_warning']) ? 0 : intval($_REQUEST ['stock_warning']);
// 	$filter ['brand_id'] = empty ($_REQUEST ['brand_id']) ? 0 : intval($_REQUEST ['brand_id']);
// 	$filter ['keyword'] = empty ($_REQUEST ['keyword']) ? '' : trim($_REQUEST ['keyword']);
// 	$filter ['suppliers_id'] = isset ($_REQUEST ['suppliers_id']) ? (empty ($_REQUEST ['suppliers_id']) ? '' : trim($_REQUEST ['suppliers_id'])) : '';
// 	$filter ['is_on_sale'] = !empty($_REQUEST ['is_on_sale']) ? ($_REQUEST ['is_on_sale'] == 1 ? 1 : 2) : 0;

// 	$filter ['sort_by'] = empty ($_REQUEST ['sort_by']) ? 'goods_id' : trim($_REQUEST ['sort_by']);
// 	$filter ['sort_order'] = empty ($_REQUEST ['sort_order']) ? 'DESC' : trim($_REQUEST ['sort_order']);
// 	$filter ['extension_code'] = empty ($_REQUEST ['extension_code']) ? '' : trim($_REQUEST ['extension_code']);
// 	$filter ['is_delete'] = $is_delete;
// 	$filter ['real_goods'] = $real_goods;

// 	$where = $filter ['cat_id'] > 0 ? " AND " . get_children($filter ['cat_id']) : '';

// 	/* 推荐类型 */
// 	switch ($filter ['intro_type']) {
// 		case 'is_best' :
// 		$where .= " AND is_best=1";
// 		break;
// 		case 'is_hot' :
// 		$where .= ' AND is_hot=1';
// 		break;
// 		case 'is_new' :
// 		$where .= ' AND is_new=1';
// 		break;
// 		case 'is_promote' :
// 		$where .= " AND is_promote = 1 AND promote_price > 0 AND promote_start_date <= '$today' AND promote_end_date >= '$today'";
// 		break;
// 		case 'all_type' :
// 		$where .= " AND (is_best=1 OR is_hot=1 OR is_new=1 OR (is_promote = 1 AND promote_price > 0 AND promote_start_date <= '" . $today . "' AND promote_end_date >= '" . $today . "'))";
// 	}

// 	/* 库存警告 */
// 	if ($filter ['stock_warning']) {
// 		$where .= ' AND goods_number <= warn_number ';
// 	}

// 	/* 品牌 */
// 	if ($filter ['brand_id']) {
// 		$where .= " AND brand_id='$filter[brand_id]'";
// 	}

// 	/* 扩展 */
// 	if ($filter ['extension_code']) {
// 		$where .= " AND extension_code='$filter[extension_code]'";
// 	}

// 	/* 关键字 */
// 	if (!empty ($filter ['keyword'])) {
// 		$where .= " AND (goods_sn LIKE '%" . mysql_like_quote($filter ['keyword']) . "%' OR goods_name LIKE '%" . mysql_like_quote($filter ['keyword']) . "%')";
// 	}

// 	if ($real_goods > -1) {
// 		$where .= " AND is_real='$real_goods'";
// 	}

// 	/* 是否上架 */
// 	if ($filter ['is_on_sale'] != 0) {
// 		$filter ['is_on_sale'] == 2 && $filter ['is_on_sale'] = 0;
// 		$where .= " AND (is_on_sale = '" . $filter ['is_on_sale'] . "')";
// 	}

// 	/* 供货商 */
// 	if (!empty ($filter ['suppliers_id'])) {
// 		$where .= " AND (suppliers_id = '" . $filter ['suppliers_id'] . "')";
// 	}

// 	$where .= $conditions;
// 	/* 记录总数 */
// 	/* 加载分页类 */
// 	RC_Loader::load_sys_class('ecjia_page', false);
// 	$count = $db->join(null)->where('is_delete = ' . $is_delete . '' . $where)->count();

// 	$count_where = array(
// 		'is_delete' => $is_delete,
// 		'is_real' => 1,
// 		'is_on_sale' => 1
// 		);

// 	if ($filter ['extension_code']) {
// 		$count_where['is_real'] = 0;
// 		$count_where['extension_code'] = $filter[extension_code];
// 	}

// 	//TODO  已上架数据
// 	$count_on_sale = $db->join(null)->where($count_where)->count();
// 	$count_where['is_on_sale'] = 0;
// 	//TODO  未上架数据
// 	$count_not_sale = $db->join(null)->where($count_where)->count();
// 	$page = new ecjia_page ($count, 10, 5);
// 	$filter ['record_count'] = $count;
// 	$sql = $db->field('goods_id, goods_name, goods_type, goods_sn, shop_price, goods_thumb, is_on_sale, is_best, is_new, is_hot, sort_order, goods_number, integral,(promote_price > 0 AND promote_start_date <= ' . $today . ' AND promote_end_date >= ' . $today . ')|is_promote')->where('is_delete = ' . $is_delete . '' . $where)->order($filter [sort_by] . ' ' . $filter [sort_order])->limit($page->limit())->select();
// 	$filter ['keyword'] = stripslashes($filter ['keyword']);
// 	$filter ['count_on_sale']	= $count_on_sale;
// 	$filter ['count_not_sale']	= $count_not_sale;
// 	$filter ['count_goods_num']	= $count_not_sale + $count_on_sale;
// 	$filter ['count']			= $count;
	

// 	foreach ($sql as $k => $v) {
// 		if (!file_exists(RC_Upload::upload_path() . $v['goods_thumb']) || empty($v['goods_thumb'])) {
// 			$sql[$k]['goods_thumb'] = RC_Uri::admin_url('statics/images/nopic.png');
// 		} else {
// 			$sql[$k]['goods_thumb'] = RC_Upload::upload_url() . '/' . $v['goods_thumb'];
// 		}
// 	}
	
// 	$row = $sql;
// 	return array(
// 		'goods'		=> $row,
// 		'filter'	=> $filter,
// 		'page'		=> $page->show(5),
// 		'desc'		=> $page->page_desc()
// 		);
// }

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
function check_goods_product_exist($goods_id, $conditions = '')
{
	$db = RC_Loader::load_app_model('products_model', 'goods');
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
function product_number_count($goods_id, $conditions = '')
{
	$db = RC_Loader::load_app_model('products_model', 'goods');
	if (empty($goods_id)) {
		return -1; // $goods_id不能为空
	}

	$nums = $db->where('goods_id = ' . $goods_id . $conditions . '')->sum('product_number');
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
function product_goods_attr_list($goods_id)
{
	$db = RC_Loader::load_app_model('goods_attr_model', 'goods');

	$results = $db->field('goods_attr_id, attr_value')->where(array('goods_id' => $goods_id))->select();
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
function get_goods_specifications_list($goods_id)
{
	$dbview = RC_Loader::load_app_model('goods_attr_attribute_viewmodel');
	if (empty ($goods_id)) {
		return array(); // $goods_id不能为空
	}

	$dbview->view = array(
		'attribute' => array(
			'type'	=> Component_Model_View::TYPE_LEFT_JOIN,
			'alias' => 'a',
			'field' => 'ga.goods_attr_id, ga.attr_value, ga.attr_id, a.attr_name',
			'on'	=> 'a.attr_id = ga.attr_id'
			)
		);
	return $dbview->where(array('goods_id' => $goods_id, 'a.attr_type' => 1))->order(array('ga.attr_id' => 'ASC'))->select();

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
function product_list($goods_id, $conditions = '')
{
	$db = RC_Loader::load_app_model('products_model', 'goods');
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
	$count = $db->where('goods_id = ' . $goods_id . $where . '')->count();
	$filter ['record_count'] = $count;

	$row = $db->field('product_id, goods_id, goods_attr|goods_attr_str, goods_attr, product_sn, product_number')->where('goods_id = ' . $goods_id . $where . '')->order($filter [sort_by] . ' ' . $filter [sort_order])->select();

	/* 处理规格属性 */
	$goods_attr = product_goods_attr_list($goods_id);
	if (!empty ($row)) {
		foreach ($row as $key => $value) {
			$_goods_attr_array = explode('|', $value ['goods_attr']);
			if (is_array($_goods_attr_array)) {
				$_temp = '';
				foreach ($_goods_attr_array as $_goods_attr_value) {
					$_temp [] = $goods_attr [$_goods_attr_value];
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
function get_product_info($product_id, $filed = '')
{
	$db = RC_Loader::load_app_model('products_model', 'goods');
	$return_array = array();
	if (empty ($product_id)) {
		return $return_array;
	}

	$filed = trim($filed);
	if (empty ($filed)) {
		$filed = '*';
	}
	$return_array = $db->field($filed)->find(array('product_id' => $product_id));
	return $return_array;
}

/**
* 检查单个商品是否存在规格
*
* @param int $goods_id
*            商品id
* @return bool true，存在；false，不存在
*/
function check_goods_specifications_exist($goods_id)
{
	$dbview = RC_Loader::load_app_model('attribute_goods_viewmodel', 'goods');
	$goods_id = intval($goods_id);
	$dbview->view = array(
		'goods' => array(
			'type'	=> Component_Model_View::TYPE_LEFT_JOIN,
			'alias'	=> 'g',
			'on' => 'a.cat_id = g.goods_type'
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
function check_goods_attr_exist($goods_attr, $goods_id, $product_id = 0)
{
	$db = RC_Loader::load_app_model('products_model', 'goods');
	$goods_id = intval($goods_id);
	if (strlen($goods_attr) == 0 || empty ($goods_id)) {
		return true; // 重复
	}
	if (empty ($product_id)) {
		$res = $db->field('product_id')->where(array('goods_attr' => $goods_attr,'goods_id' => $goods_id))->get_field('product_id');
	} else {
		$res = $db->where(array('goods_attr' => $goods_attr, 'goods_id' => $goods_id, 'product_id' => array('neq' => $product_id)))->get_field('product_id');
	}
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
function check_product_sn_exist($product_sn, $product_id = 0)
{
	$db_goods = RC_Loader::load_app_model('goods_model', 'goods');
	$db_product = RC_Loader::load_app_model('products_model', 'goods');
	$product_sn = trim($product_sn);
	$product_id = intval($product_id);
	if (strlen($product_sn) == 0) {
		return true; // 重复
	}
	
	$query = $db_goods->where(array('goods_sn' => $product_sn))->get_field('goods_id');
	if ($query) {
		return true; // 重复
	}
	if (empty ($product_id)) {
		$res = $db_product->where(array( 'product_sn' => $product_sn ))->get_field('product_id');
	} else {
		$res = $db_product->where(array( 'product_sn' => $product_sn, 'product_id' => array('neq'=> $product_id)))->get_field('product_id');
	}
	if (empty ($res)) {
		return false; // 不重复
	} else {
		return true; // 重复
	}
}

// /**
// * 格式化商品图片名称（按目录存储）
// */
// function reformat_image_name($type, $goods_id, $source_img, $position = '')
// {
// 	$rand_name = RC_Time::gmtime() . sprintf("%03d", mt_rand(1, 999));
// 	$img_ext = substr($source_img, strrpos($source_img, '.'));
// 	$dir = 'images';
// 	if (defined('IMAGE_DIR')) {
// 		$dir = IMAGE_DIR;
// 	}
// 	$sub_dir = date('Ym', RC_Time::gmtime());
// 	if (!make_dir(RC_Upload::upload_path() . $dir . '/' . $sub_dir)) {
// 		return false;
// 	}
// 	if (!make_dir(RC_Upload::upload_path() . $dir . '/' . $sub_dir . '/source_img')) {
// 		return false;
// 	}
// 	if (!make_dir(RC_Upload::upload_path() . $dir . '/' . $sub_dir . '/goods_img')) {
// 		return false;
// 	}
// 	if (!make_dir(RC_Upload::upload_path() . $dir . '/' . $sub_dir . '/thumb_img')) {
// 		return false;
// 	}
// 	switch ($type) {
// 		case 'goods' :
// 		$img_name = $goods_id . '_G_' . $rand_name;
// 		break;
// 		case 'goods_thumb' :
// 		$img_name = $goods_id . '_thumb_G_' . $rand_name;
// 		break;
// 		case 'gallery' :
// 		$img_name = $goods_id . '_P_' . $rand_name;
// 		break;
// 		case 'gallery_thumb' :
// 		$img_name = $goods_id . '_thumb_P_' . $rand_name;
// 		break;
// 	}
// 	if ($position == 'source') {
// 		if (move_image_file(RC_Upload::upload_path() . $source_img, RC_Upload::upload_path() . $dir . '/' . $sub_dir . '/source_img/' . $img_name . $img_ext)) {
// 			return $dir . '/' . $sub_dir . '/source_img/' . $img_name . $img_ext;
// 		}
// 	} elseif ($position == 'thumb') {
// 		if (move_image_file(RC_Upload::upload_path() . $source_img, RC_Upload::upload_path() . $dir . '/' . $sub_dir . '/thumb_img/' . $img_name . $img_ext)) {
// 			return $dir . '/' . $sub_dir . '/thumb_img/' . $img_name . $img_ext;
// 		}
// 	} else {
// 		if (move_image_file(RC_Upload::upload_path() . $source_img, RC_Upload::upload_path() . $dir . '/' . $sub_dir . '/goods_img/' . $img_name . $img_ext)) {
// 			return $dir . '/' . $sub_dir . '/goods_img/' . $img_name . $img_ext;
// 		}
// 	}
// 	return false;
// }

// function move_image_file($source, $dest)
// {
// 	if (@copy($source, $dest)) {
// 		@unlink($source);
// 		return true;
// 	}
// 	return false;
// }

// end