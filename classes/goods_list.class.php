<?php

/**
 * 商品列表相关处理类
 * @author will.chen
 *
 */
class goods_list {
    
	private static $keywords_where;
	/* 初始化搜索条件 */
	public static function get_keywords_where($keyword) {
	
		$keywords = '';
		$tag_where = '';
		if (!empty($keyword)) {
			$arr = array();
			if (stristr($keyword, ' AND ') !== false) {
				/* 检查关键字中是否有AND，如果存在就是并 */
				$arr = explode('AND', $keyword);
				$operator = " AND ";
			} elseif (stristr($keyword, ' OR ') !== false) {
				/* 检查关键字中是否有OR，如果存在就是或 */
				$arr = explode('OR', $keyword);
				$operator = " OR ";
			} elseif (stristr($keyword, ' + ') !== false) {
				/* 检查关键字中是否有加号，如果存在就是或 */
				$arr = explode('+', $keyword);
				$operator = " OR ";
			} else {
				/* 检查关键字中是否有空格，如果存在就是并 */
				$arr = explode(' ', $keyword);
				$operator = " AND ";
			}
	
			$keywords = '(';
			$goods_ids = array();
			if (!empty($arr)) {
				$db_tag = RC_Loader::load_app_model('tag_model', 'goods');
				$db_keywords = RC_Loader::load_app_model('keywords_model', 'goods');
				foreach ($arr as $key => $val) {
					if ($key > 0 && $key < count($arr) && count($arr) > 1) {
						$keywords .= $operator;
					}
					$val = mysql_like_quote(trim($val));
					$keywords .= "(goods_name LIKE '%$val%' OR goods_sn LIKE '%$val%' OR keywords LIKE '%$val%')";
						
						
					$res = $db_tag->field('DISTINCT goods_id')->where("tag_words LIKE '%$val%'")->select();
					if (! empty($res)) {
						foreach ($res as $row) {
							$goods_ids[] = $row['goods_id'];
						}
					}
					//插入keywords表数据 will.chen
					$count = $db_keywords->where(array('date' => RC_Time::local_date('Y-m-d'), 'keyword'=>addslashes(str_replace('%', '', $val))))->get_field('count');
					if (!empty($count) && $count > 0) {
						$db_keywords->where(array('date'=>RC_Time::local_date('Y-m-d'),'keyword'=>addslashes(str_replace('%', '', $val))))->update(array('count' => $count + 1));
					} else {
						$data = array(
								'date' => RC_Time::local_date('Y-m-d'),
								'searchengine' => 'ecjia',
								'count'=> '1',
								'keyword' => addslashes(str_replace('%', '', $val)));
						$db_keywords->insert($data);
					}
				}
			}
			$keywords .= ')';
	
			$goods_ids = array_unique($goods_ids);
			/* 标签查询*/
			$tag_where = $goods_ids;
		}
		self::$keywords_where['keywords']	= $keywords;
		self::$keywords_where['tag_where']	= $tag_where;
		return true;
	}
	
	
	/**
	 * 取得商品列表
	 * @param   object  $filters    过滤条件
	 */
	public static function get_goods_list($filter) {
		RC_Loader::load_app_class('goods_category', 'goods', false);
		$dbview = RC_Loader::load_app_model('goods_member_viewmodel', 'goods');
		$children = '';
		isset($filter['cat_id']) and $children = goods_category::get_children($filter['cat_id']);
		$where = array(
			'is_on_sale'	=> 1,
			'is_alone_sale' => 1,
			'is_delete'		=> 0,
		);
		if (!empty($children)) {
			$where[] = "(". $children ." OR ".goods_category::get_extension_goods($children).")";
		}
		if (isset($filter['brand']) && $filter['brand'] > 0) {
			$where['brand_id'] = $filter['brand'];
		}
		if (isset($filter['min']) && $filter['min'] > 0) {
			$where[] = "shop_price >= ".$filter['min'];
		}
		if (isset($filter['max']) && $filter['max'] > 0) {
			$where[] = "shop_price <= ".$filter['max'];
		}
	
		if (!empty(self::$keywords_where['keywords'])) {
			$where[] = self::$keywords_where['keywords'];
		}
	
		if (!empty(self::$keywords_where['tag_where'])) {
			$where[] = 'or';
			$where['g.goods_id'] = self::$keywords_where['tag_where'];
		}
		
		if (!empty($filter['intro'])) {
			switch ($filter['intro']) {
				case 'best':
					$where['g.is_best'] = 1;
					break;
				case 'new':
					$where['g.is_new'] = 1;
					break;
				case 'hot':
					$where['g.is_hot'] = 1;
					break;
				case 'promotion':
					$time    = RC_Time::gmtime();
					$where['g.promote_price']		= array('gt' => 0);
					$where['g.promote_start_date']	= array('elt' => $time);
					$where['g.promote_end_date']	= array('egt' => $time);
					break;
				default:
						
			}
		}
	
		/* 扩展商品查询条件 */
		if (!empty($filter['filter_attr'])) {
			$cat = goods_category::get_cat_info($filter['cat_id']);
			$cat_filter_attr = explode(',', $cat['filter_attr']);       //提取出此分类的筛选属性
			// 			$ext_sql = "SELECT DISTINCT(b.goods_id) FROM " . $ecs->table('goods_attr') . " AS a, " . $ecs->table('goods_attr') . " AS b " .  "WHERE ";
			$ext_group_goods = array();
				
			$db_goods_attr_view = RC_Loader::load_app_model('goods_attr_viewmodel', 'goods');
			$db_goods_attr_view->view = array(
				'goods_attr' => array(
					'type'		=> Component_Model_View::TYPE_LEFT_JOIN,
					'alias'		=> 'b',
					'on'		=> 'b.attr_value = ga.attr_value'
				)
			);
			/* 查出符合所有筛选属性条件的商品id */
			foreach ($filter['filter_attr'] AS $k => $v) {
				$goods_ids = array();
				if (is_numeric($v) && $v !=0 && isset($cat_filter_attr[$k])) {
					// 					$sql = $ext_sql . "b.attr_value = a.attr_value AND b.attr_id = " . $cat_filter_attr[$k] ." AND a.goods_attr_id = " . $v;
					// 					$ext_group_goods = $db->getColCached($sql);
					$ext_group_goods = $db_goods_attr_view->field(array('DISTINCT(b.goods_id) as goods_id'))->join(array('goods_attr'))->where(array('b.attr_id' => $cat_filter_attr[$k], 'ga.goods_attr_id' => $v))->select();
	
					if (!empty($ext_group_goods)) {
						foreach ($ext_group_goods as $val) {
							$goods_ids[] = $val['goods_id'];
						}
					}
					$where[] = $this->db_create_in($goods_ids, 'g.goods_id');
				}
			}
	
		}
	
		/* 返回商品总数 */
		$count = $dbview->join(null)->where($where)->count();
	
		//实例化分页
		$page_row = new ecjia_page($count, $filter['size'], 6, '', $filter['page']);
	
		$data = $dbview->join('member_price')->where($where)->order($filter['sort'])->limit($page_row->limit())->select();
	
		$arr = array();
		if (!empty($data)) {
			RC_Loader::load_app_func('goods', 'goods');
			foreach ($data as $key => $row) {
				if ($row['promote_price'] > 0) {
					$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
				} else {
					$promote_price = 0;
				}
				// 				/* 处理商品水印图片 */
				// 				$watermark_img = '';
				// 				if ($promote_price != 0) {
				// 					$watermark_img = "watermark_promote_small";
				// 				} elseif ($row['is_new'] != 0) {
				// 					$watermark_img = "watermark_new_small";
				// 				} elseif ($row['is_best'] != 0) {
				// 					$watermark_img = "watermark_best_small";
				// 				} elseif ($row['is_hot'] != 0) {
				// 					$watermark_img = 'watermark_hot_small';
				// 				}
	
				// 				if ($watermark_img != '') {
				// 					$arr[$row['goods_id']]['watermark_img'] = $watermark_img;
				// 				}
	
	
				if ($filter['display'] == 'grid') {
					$arr[$key]['goods_name'] = ecjia::config('goods_name_length') > 0 ? RC_String::sub_str($row['goods_name'], ecjia::config('goods_name_length')) : $row['goods_name'];
				} else {
					$arr[$key]['goods_name'] = $row['goods_name'];
				}
	
				$arr[$key]['goods_id']		= $row['goods_id'];
				$arr[$key]['name']			= $row['goods_name'];
				$arr[$key]['goods_brief'] 	= $row['goods_brief'];
				/* 增加商品样式*/
				$arr[$key]['goods_style_name'] = add_style($row['goods_name'], $row['goods_name_style']);
				$arr[$key]['market_price']	= $row['market_price'] > 0 ? price_format($row['market_price']) : 0;
				$arr[$key]['shop_price']	= $row['shop_price'] > 0 ? price_format($row['shop_price']) : RC_Lang::get('goods::goods.free');
				$arr[$key]['type']			= $row['goods_type'];
				$arr[$key]['promote_price']	= ($promote_price > 0) ? price_format($promote_price) : '';
	
				$arr[$key]['goods_thumb']	= !empty($row['goods_thumb']) ? RC_Upload::upload_url($row['goods_thumb']) : RC_Uri::admin_url('statics/images/nopic.png');
				$arr[$key]['original_img']	= !empty($row['original_img']) ? RC_Upload::upload_url($row['original_img']) : RC_Uri::admin_url('statics/images/nopic.png');
				$arr[$key]['goods_img']		= !empty($row['goods_img']) ? RC_Upload::upload_url($row['goods_img']) : RC_Uri::admin_url('statics/images/nopic.png');
				$arr[$key]['url'] 			= build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
					
				/* 增加返回原始未格式价格  will.chen*/
				$arr[$key]['unformatted_shop_price']	= $row['shop_price'];
				$arr[$key]['unformatted_promote_price'] = $promote_price;
				$arr[$key]['unformatted_market_price'] = $row['market_price'];
			}
		}
		return array('list' => $arr, 'page' => $page_row);
	}
	
	
	/**
	 * 创建像这样的查询: "IN('a','b')";
	 *
	 * @access public
	 * @param mix $item_list
	 *        	列表数组或字符串
	 * @param string $field_name
	 *        	字段名称
	 *
	 * @return void
	 */
	private function db_create_in($item_list, $field_name = '') {
		if (empty ( $item_list )) {
			return $field_name . " IN ('') ";
		} else {
			if (! is_array ( $item_list )) {
				$item_list = explode ( ',', $item_list );
			}
			$item_list = array_unique ( $item_list );
			$item_list_tmp = '';
			foreach ( $item_list as $item ) {
				if ($item !== '') {
					$item_list_tmp .= $item_list_tmp ? ",'$item'" : "'$item'";
				}
			}
			if (empty ( $item_list_tmp )) {
				return $field_name . " IN ('') ";
			} else {
				return $field_name . ' IN (' . $item_list_tmp . ') ';
			}
		}
	}
}

// end