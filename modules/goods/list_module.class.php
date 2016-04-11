<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 商品分类列表及关键词搜索
 * @author royalwang
 *
 */
class list_module implements ecjia_interface {

    public function run(ecjia_api & $api) {
    	EM_Api::authSession(false);
    	RC_Loader::load_app_func('main', 'api');
    	RC_Loader::load_app_func('category', 'goods');
		$filter = _POST('filter', array());
		
        $keyword = RC_String::unicode2string($filter['keywords']);
        $keyword = ! empty($keyword) ? htmlspecialchars(trim($keyword)) : '';
        $category = ! empty($filter['category_id']) ? intval($filter['category_id']) : 0;
        $brand = ! empty($filter['brand_id']) ? intval($filter['brand_id']) : 0;
        $filter['min_price'] = ! empty($filter['price_range']['price_min']) ? intval($filter['price_range']['price_min']) : 0;
        $filter['max_price'] = ! empty($filter['price_range']['price_max']) ? intval($filter['price_range']['price_max']) : 0;
        $intro = '';
        $order = 'DESC';
        $sort_by = $filter['sort_by'];
        $sort = 'goods_id';
        if ($sort_by == 'is_hot') {
            $sort = 'is_hot DESC,sort_order asc, goods_id';
            $order = 'DESC';
        } elseif ($sort_by == 'price_desc') {
            $sort = 'org_price DESC,sort_order asc, shop_price';
        } elseif ($sort_by == 'price_asc') {
        	$sort = 'org_price ASC,sort_order asc, shop_price';
            $order = 'ASC';
        } elseif ($sort_by == 'is_new') {
        	$sort = 'is_new DESC,sort_order asc, goods_id';
        	$order = 'DESC';
        }
		$filter['intro'] = $intro;
        $filter['order'] = $order;
        $filter['sort'] = $sort;
     	$filter['goods_type'] = ! empty($filter['goods_type']) ? intval($filter['goods_type']) : 0;
        $filter['sc_ds'] = ! empty($filter['sc_ds']) ? intval($filter['sc_ds']) : 0;//高级搜索中搜索简介
// 		$filter['outstock'] = ! empty($filter['outstock']) ? 1 : 0;//高级搜索中隐藏已脱销的商品
		$size = EM_Api::$pagination['count'];
		$page = EM_Api::$pagination['page'];
		
       	$action = '';
        /* 初始化搜索条件 */
        $keywords = '';
        $tag_where = '';

        if (! empty($keyword)) {
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
            foreach ($arr as $key => $val) {
                if ($key > 0 && $key < count($arr) && count($arr) > 1) {
                    $keywords .= $operator;
                }
                $val = mysql_like_quote(trim($val));
                $sc_dsad = $filter['sc_ds'] ? " OR goods_desc LIKE '%$val%'" : '';
                $keywords .= "(goods_name LIKE '%$val%' OR goods_sn LIKE '%$val%' OR keywords LIKE '%$val%' $sc_dsad)";
                
                $db_tag = RC_Loader::load_app_model('tag_model','goods');
            	$res = $db_tag->field('DISTINCT goods_id')->where("tag_words LIKE '%$val%'")->select();
                if (! empty($res)) {
                   	foreach ($res as $row) {
                 		$goods_ids[] = $row['goods_id'];
              		}
               	}
               	//插入keywords表数据 will.chen
               	$db_keywords = RC_Loader::load_app_model('keywords_model', 'goods');
               	$count = $db_keywords->where(array('date'=>RC_Time::local_date('Y-m-d'),'keyword'=>addslashes(str_replace('%', '', $val))))->count('*');
               	if ($count>0){
               		$db_keywords->where(array('date'=>RC_Time::local_date('Y-m-d'),'keyword'=>addslashes(str_replace('%', '', $val))))->update(array('count'=>$count+1));
               	} else {
               		$data = array(
               			'date' => RC_Time::local_date('Y-m-d'),
               			'searchengine' => 'ecjia',
               			'count'=> '1',
               			'keyword' => addslashes(str_replace('%', '', $val)));
               		$db_keywords->insert($data);
               	}
            }
            $keywords .= ')';
            
            $goods_ids = array_unique($goods_ids);
            $tag_where = implode(',', $goods_ids);
            if (! empty($tag_where)) {
            	RC_Loader::load_app_func('common', 'goods');
                $tag_where = 'OR g.goods_id ' . db_create_in($tag_where);
            }
        }
        
// 		$categories = ($category > 0) ? ' AND ' . get_children($category) : '';
//      $outstock = ! empty($filter['outstock']) ? " AND g.goods_number > 0 " : '';
        $min_price = $filter['min_price'] != 0 ? $filter['min_price'] : '';
        $max_price = $filter['max_price'] != 0 || $filter['min_price'] < 0 ? $filter['max_price'] : '';
        
        /* 排序、显示方式以及类型 */
// 		TODO:web端的布局及显示，api中不用吧
//         $default_display_type = ecjia::config('show_order_type')== '0' ? 'list' : (ecjia::config('show_order_type')== '1' ? 'grid' : 'text');
//         $default_sort_order_method = ecjia::config('sort_order_method') == '0' ? 'DESC' : 'ASC';
//         $default_sort_order_type = ecjia::config('sort_order_type') == '0' ? 'goods_id' : (ecjia::config('sort_order_type') == '1' ? 'shop_price' : 'last_update');      
//         $sort = isset($filter['sort']) ? trim($filter['sort']) : $default_sort_order_type;
//         $order = (isset($filter['order']) && in_array(trim(strtoupper($filter['order'])), array(
//             'ASC',
//             'DESC'
//         ))) ? trim($filter['order']) : $default_sort_order_method;
//         $display = (isset($filter['display']) && in_array(trim(strtolower($filter['display'])), array(
//             'list',
//             'grid',
//             'text'
//         ))) ? trim($filter['display']) : (isset($_SESSION['display_search']) ? $_SESSION['display_search'] : $default_display_type);
        
//         $_SESSION['display_search'] = $display;
        $display = '';
//         $page = ! empty($filter['page']) && intval($filter['page']) > 0 ? intval($filter['page']) : 1;
//         $size = ! empty($filter['page_size']) && intval($filter['page_size']) > 0 ? intval($filter['page_size']) : 10;
        
        $intromode = ''; // 方式，用于决定搜索结果页标题图片
        $attr_arg = array();
// 		TODO:现api中未用到
//         if (! empty($filter['intro'])) {
//             switch ($filter['intro']) {
//                 case 'best':
//                     $intro = ' AND g.is_best = 1';
//                     $intromode = 'best';
//                     $ur_here = RC_Lang::lang('best_goods');
//                     break;
//                 case 'new':
//                     $intro = ' AND g.is_new = 1';
//                     $intromode = 'new';
//                     $ur_here = RC_Lang::lang('new_goods');
//                     break;
//                 case 'hot':
//                     $intro = ' AND g.is_hot = 1';
//                     $intromode = 'hot';
//                     $ur_here = RC_Lang::lang('hot_goods');
//                     break;
//                 case 'promotion':
//                     $time = RC_Time::gmtime();
//                     $intro = " AND g.promote_price > 0 AND g.promote_start_date <= '$time' AND g.promote_end_date >= '$time'";
//                     $intromode = 'promotion';
//                     $ur_here = RC_Lang::lang('promotion_goods');
//                     break;
//                 default:
//                     $intro = '';
//             }
//         } else {
//             $intro = '';
//         }
        
//         if (empty($ur_here)) {
//             $ur_here = RC_Lang::lang('search_goods');
//         }
        
        /* ------------------------------------------------------ */
        // -- 从选购中心属性检索
        /* ------------------------------------------------------ */
        
// 		TODO:现api未用到        
//         $attr_in = '';
//         $attr_num = 0;
//         $attr_url = '';
//         if (! empty($filter['attr'])) {
//             $db_goods_attr = RC_Loader::load_app_model('goods_attr_model', 'goods');
//           	$sql = "SELECT goods_id, COUNT(*) AS num FROM ecs_goods_attr WHERE 0 ";
//             foreach ($filter['attr'] as $key => $val) {
//                 if (is_not_null($val) && is_numeric($key)) {
//                     $attr_num ++;
//                     $sql .= " OR (1 ";
//                     if (is_array($val)) {
//                         $sql .= " AND attr_id = '$key'";
//                         if (! empty($val['from'])) {
//                             $sql .= is_numeric($val['from']) ? " AND attr_value >= " . floatval($val['from']) : " AND attr_value >= '$val[from]'";
//                             $attr_arg["attr[$key][from]"] = $val['from'];
//                             $attr_url .= "&amp;attr[$key][from]=$val[from]";
//                         }
//                         if (! empty($val['to'])) {
//                             $sql .= is_numeric($val['to']) ? " AND attr_value <= " . floatval($val['to']) : " AND attr_value <= '$val[to]'";
//                             $attr_arg["attr[$key][to]"] = $val['to'];
//                             $attr_url .= "&amp;attr[$key][to]=$val[to]";
//                         }
//                     } else {
//                         /* 处理选购中心过来的链接 */
//                         $sql .= isset($filter['pickout']) ? " AND attr_id = '$key' AND attr_value = '" . $val . "' " : " AND attr_id = '$key' AND attr_value LIKE '%" . mysql_like_quote($val) . "%' ";
//                         $attr_url .= "&amp;attr[$key]=$val";
//                         $attr_arg["attr[$key]"] = $val;
//                     }
//                     $sql .= ')';
//                 }
//             }
//             /* 如果检索条件都是无效的，就不用检索 */
//             if ($attr_num > 0) {
//                 $sql .= " GROUP BY goods_id HAVING num = '$attr_num'";
//                 $row_tmp = $db_goods_attr->query($sql);
//             	foreach ($row_tmp as $v) {
//                 	$row[] = $v[0];
//                 }
//                 if (count($row)) {
//                     $attr_in = " AND " . db_create_in($row, 'g.goods_id');
//                 } else {
//                     $attr_in = " AND 0 ";
//                 }
//             }
//         } elseif (isset($filter['pickout'])) {
//             /* 从选购中心进入的链接 */
//         	$row = $db_goods_attr->field('DISTINCT(goods_id)')->select();
//            	foreach ($row as $v) {
//             	$col[] = $v[0];
//             }
//             // 如果商店没有设置商品属性,那么此检索条件是无效的
//             if (! empty($col)) {
//                 $attr_in = " AND " . db_create_in($col, 'g.goods_id');
//             }
//         }
        
        $ext = $keywords; // 商品查询条件扩展
        
        /* 获得符合条件的商品总数 */
		$children = get_children($category);
        
        RC_Loader::load_app_func('global', 'goods');
        $count = get_cagtegory_goods_count($children, $brand, $min_price, $max_price, $ext);
        
        $max_page = ($count > 0) ? ceil($count / $size) : 1;
        if ($page > $max_page) {
            $page = $max_page;
        }
        
        $arr = category_get_goods($children, $brand, $min_price, $max_price, $ext, $size, $page, $sort, $order);
		
        $data = array();
        if (!empty($arr)) {
        	$mobilebuy_db = RC_Loader::load_app_model('goods_activity_model', 'goods');
        	foreach ($arr as $val) {
        		$groupbuy = $mobilebuy_db->find(array(
        				'goods_id'	 => $val['goods_id'], 
        				'start_time' => array('elt' => RC_Time::gmtime()),
        				'end_time'	 => array('egt' => RC_Time::gmtime()),
        				'act_type'	 => GAT_GROUP_BUY,
        		));
        		$mobilebuy = $mobilebuy_db->find(array(
        				'goods_id'	 => $val['goods_id'], 
        				'start_time' => array('elt' => RC_Time::gmtime()),
        				'end_time'	 => array('egt' => RC_Time::gmtime()),
        				'act_type'	 => GAT_MOBILE_BUY,
        		));
        		/* 判断是否有促销价格*/
        		$price = ($val['unformatted_shop_price'] > $val['unformatted_promote_price'] && $val['unformatted_promote_price'] > 0) ? $val['unformatted_promote_price'] : $val['unformatted_shop_price'];
        		$activity_type = ($val['unformatted_shop_price'] > $val['unformatted_promote_price'] && $val['unformatted_promote_price'] > 0) ? 'PROMOTE_GOODS' : 'GENERAL_GOODS';
        		
        		$mobilebuy_price = $groupbuy_price = $object_id = 0;
        		
        			
        		if (!empty($mobilebuy)) {
        			$ext_info = unserialize($mobilebuy['ext_info']);
        			$mobilebuy_price = $ext_info['price'];
       				$price = $mobilebuy_price > $price ? $price : $mobilebuy_price;
       				$activity_type = $mobilebuy_price > $price ? $activity_type : 'MOBILEBUY_GOODS';
       				$object_id = $mobilebuy_price > $price ? $object_id : $mobilebuy['act_id'];
       			}
//         			if (!empty($groupbuy)) {
//         				$ext_info = unserialize($groupbuy['ext_info']);
//         				$price_ladder = $ext_info['price_ladder'];
//         				$groupbuy_price  = $price_ladder[0]['price'];
//         				$price = $groupbuy_price > $price ? $price : $groupbuy_price;
//         				$activity_type = $groupbuy_price > $price ? $activity_type : 'GROUPBUY_GOODS';
//         			}
        		/* 计算节约价格*/
        		$saving_price = ($val['unformatted_shop_price'] - $price) > 0 ? $val['unformatted_shop_price'] - $price : 0;
        		$data[] = array(
						'goods_id'		=> $val['goods_id'],
						'name'			=> $val['goods_name'],
						'market_price'	=> $val['market_price'],
						'shop_price'	=> $val['shop_price'],
        				'promote_price'	=> ($price < $val['unformatted_shop_price'] && $price > 0) ? price_format($price) : '',
        				'img' => array(
        						'thumb'	=> API_DATA('PHOTO', $val['goods_img']),
        						'url'	=> API_DATA('PHOTO', $val['original_img']),
        						'small'	=> API_DATA('PHOTO', $val['goods_thumb'])
        				),
        				'activity_type' => $activity_type,
        				'object_id'		=> $object_id,
        				'saving_price'	=> $saving_price,
        				'formatted_saving_price' => '已省'.$saving_price.'元',
        				'seller_id'		=> $val['seller_id'],
        				'seller_name'	=> $val['seller_name'],
        		);
        	}
        }
        //加载分页类
        RC_Loader::load_sys_class('ecjia_page', false);
        //实例化分页
        $page_row = new ecjia_page($count, $size, 6, '', $page);
        
//         $data = API_DATA("SIMPLEGOODS", $arr);

        $pager = array(
				"total" => $page_row->total_records,
				"count" => $page_row->total_records,
				"more" => $page_row->total_pages <= $page ? 0 : 1,
		);
        
        EM_Api::outPut($data, $pager);      

        //         $_REQUEST = array();
        //         $_REQUEST['keywords'] = $keyword;
        //         $_REQUEST['category'] = $category;
        //         $_REQUEST['brand'] = $brand_id;
        //         $_REQUEST['min_price'] = $price_range['price_min'];
        //         $_REQUEST['max_price'] = $price_range['price_max'];
        //         $_REQUEST['goods_type'] = 0;
        //         $_REQUEST['keywords'] = ! empty($_REQUEST['keywords']) ? htmlspecialchars(trim($_REQUEST['keywords'])) : '';
        //         $_REQUEST['category'] = ! empty($_REQUEST['category']) ? intval($_REQUEST['category']) : 0;
        //         $_REQUEST['brand'] = ! empty($_REQUEST['brand']) ? intval($_REQUEST['brand']) : 0;
        // 		$_REQUEST['min_price'] = ! empty($_REQUEST['min_price']) ? intval($_REQUEST['min_price']) : 0;
        //         $_REQUEST['max_price'] = ! empty($_REQUEST['max_price']) ? intval($_REQUEST['max_price']) : 0;
        //         $_REQUEST['goods_type'] = ! empty($_REQUEST['goods_type']) ? intval($_REQUEST['goods_type']) : 0;
        //         $_REQUEST['sc_ds'] = ! empty($filter['sc_ds']) ? intval($_REQUEST['sc_ds']) : 0;//高级搜索中搜索简介
        //         $_REQUEST['outstock'] = ! empty($_REQUEST['outstock']) ? 1 : 0;//高级搜索中隐藏已脱销的商品

        //         if (! empty($_REQUEST['keywords'])) {
        //             if (stristr($_REQUEST['keywords'], ' AND ') !== false) {
        //                 $arr = explode('AND', $_REQUEST['keywords']);
        //             } elseif (stristr($_REQUEST['keywords'], ' OR ') !== false) {
        //                 $arr = explode('OR', $_REQUEST['keywords']);
        //             } elseif (stristr($_REQUEST['keywords'], ' + ') !== false) {
        //                 $arr = explode('+', $_REQUEST['keywords']);
        //                 $arr = explode(' ', $_REQUEST['keywords']);
        
        
        //         $category = ! empty($_REQUEST['category']) ? intval($_REQUEST['category']) : 0;
        //         $brand = $_REQUEST['brand'];
        //         $outstock = ! empty($_REQUEST['outstock']) ? " AND g.goods_number > 0 " : '';
        //         $min_price = $_REQUEST['min_price'] != 0 ? $_REQUEST['min_price'] : '';
        //         $max_price = $_REQUEST['max_price'] != 0 || $_REQUEST['min_price'] < 0 ? $_REQUEST['max_price'] : '';        
        
        /* 分页 */
        //         $url_format = "search.php?category=$category&amp;keywords=" . urlencode(stripslashes($_REQUEST['keywords'])) . "&amp;brand=" . $brand . "&amp;action=" . $action . "&amp;goods_type=" . $_REQUEST['goods_type'] . "&amp;sc_ds=" . $_REQUEST['sc_ds'];
        //         if (! empty($intromode)) {
        //             $url_format .= "&amp;intro=" . $intromode;
        //         }
        //         if (isset($_REQUEST['pickout'])) {
        //             $url_format .= '&amp;pickout=1';
        //         }
        //         $url_format .= "&amp;min_price=" . $_REQUEST['min_price'] . "&amp;max_price=" . $_REQUEST['max_price'] . "&amp;sort=$sort";
        
        //         $url_format .= "$attr_url&amp;order=$order&amp;page=";
    }
}


// end