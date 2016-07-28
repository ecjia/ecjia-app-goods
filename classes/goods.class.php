<?php

class goods {
    
    /**
     * 取得推荐类型列表
     *
     * @return array 推荐类型列表
     */
    public static function intro_list() {
        $arr = array(
            'is_best'		=> RC_Lang::lang('is_best'),
            'is_new'		=> RC_Lang::lang('is_new'),
            'is_hot'		=> RC_Lang::lang('is_hot'),
            'is_promote'	=> RC_Lang::lang('is_promote'),
            'all_type'		=> RC_Lang::lang('all_type')
        );
        
        return $arr;
    }
    
    /**
     * 取得重量单位列表
     *
     * @return array 重量单位列表
     */
    public static function unit_list() {
        $arr = array(
            '1' =>		RC_Lang::lang('unit_kg'),
            '0.001' =>	RC_Lang::lang('unit_g')
        );
        
        return $arr;
    }
    
    
    /**
     * 获得商品列表
     *
     * @access public
     * @param
     *            s integer $isdelete
     * @param
     *            s integer $real_goods
     * @param
     *            s integer $conditions
     * @return array
     */
    public static function goods_list($is_delete, $real_goods = 1, $conditions = '', $seller_id) {
        $db = RC_Loader::load_app_model('goods_auto_viewmodel', 'goods');
        /* 过滤条件 */
        $param_str = '-' . $is_delete . '-' . $real_goods;
        $day = getdate();
        $today = RC_Time::local_mktime(23, 59, 59, $day ['mon'], $day ['mday'], $day ['year']);
    
        $filter['cat_id']		= empty($_GET['cat_id'])	 ? 0 : intval($_GET['cat_id']);
        $filter['intro_type']	= empty($_GET['intro_type']) ? '' : trim($_GET['intro_type']);
        $filter['is_promote']	= empty($_GET['is_promote']) ? 0 : intval($_GET['is_promote']);
        $filter['stock_warning'] = empty($_GET['stock_warning']) ? 0 : intval($_GET['stock_warning']);
        $filter['brand_id']		= empty($_GET['brand_id'])	 ? 0 : intval($_GET['brand_id']);
        $filter['keyword']		= empty($_GET['keyword'])	 ? '' : trim($_GET['keyword']);
        $filter['suppliers_id'] = isset ($_GET['suppliers_id']) ? (empty ($_GET['suppliers_id']) ? '' : trim($_GET['suppliers_id'])) : '';
        $filter['is_on_sale']	= !empty($_GET['is_on_sale']) ? ($_GET['is_on_sale'] == 1 ? 1 : 2) : 0;
    
        $filter['sort_by']		= empty ($_GET['sort_by'])	 ? 'g.sort_order' : trim($_GET['sort_by']);
        $filter['sort_order']	= empty ($_GET['sort_order']) ? 'ASC' : trim($_GET['sort_order']);
        $filter['extension_code'] = empty ($_GET['extension_code']) ? '' : trim($_GET['extension_code']);
        $filter['is_delete']	= $is_delete;
        $filter['real_goods']	= $real_goods;
    
        $where = array('is_delete' => $is_delete);
        
        if ($filter['cat_id'] > 0) {
        	$where[] = get_children($filter['cat_id']);
        }
    
        /* 推荐类型 */
        switch ($filter['intro_type']) {
        	case 'is_best' :
        		$where['is_best'] = 1;
        	    break;
        	case 'is_hot' :
        		$where['is_hot'] = 1;
        	    break;
        	case 'is_new' :
        		$where['is_new'] = 1;
        	    break;
        	case 'is_promote' :
        	    $where[] = "is_promote = 1 AND promote_price > 0 AND promote_start_date <= '$today' AND promote_end_date >= '$today'";
        	    break;
        	case 'all_type' :
        	    $where[] = "(is_best=1 OR is_hot=1 OR is_new=1 OR (is_promote = 1 AND promote_price > 0 AND promote_start_date <= '" . $today . "' AND promote_end_date >= '" . $today . "'))";
        	    break;
        }
    
        /* 库存警告 */
        if ($filter['stock_warning']) {
            $where[] = 'goods_number <= warn_number';
        }
    
        /* 品牌 */
        if ($filter['brand_id']) {
            $where['brand_id'] = $filter['brand_id'];
        }
    
        /* 扩展 */
        if ($filter['extension_code']) {
            $where['extension_code'] = $filter['extension_code'];
        }
    
        /* 关键字 */
        if (!empty ($filter['keyword'])) {
            $where[] = "(goods_sn LIKE '%" . mysql_like_quote($filter ['keyword']) . "%' OR goods_name LIKE '%" . mysql_like_quote($filter ['keyword']) . "%')";
        }
    
        if ($real_goods > -1) {
            $where['is_real'] = $real_goods;
        }
        
        /* 是否上架 */
        if ($filter['is_on_sale'] != 0) {
        	$is_on_sale = $filter ['is_on_sale'];
            $filter ['is_on_sale'] == 2 && $is_on_sale = 0;
            $where['is_on_sale'] = $is_on_sale;
        }
        
        /* 供货商 */
        if (!empty($filter['suppliers_id'])) {
            $where['suppliers_id'] = $filter['suppliers_id'];
        }
    
        if (!empty($conditions)) {
        	$where[] = $conditions;
        }
        if ($seller_id > 0) {
        	$where['seller_id'] = $seller_id;
        }
       
        /* 记录总数 */
        /* 加载分页类 */
        RC_Loader::load_sys_class('ecjia_page', false);
        $count = $db->join(null)->where($where)->count();
       
        
        //TODO  已上架数据
        $count_on_sale = $db->join(null)->where(array_merge($where, array('is_on_sale' => 1)))->count();
//         $count_where = str_replace("is_on_sale='0'", "is_on_sale='1'", $count_where);
//         $count_on_sale = $db->join(null)->where($count_where)->count();
        //TODO  未上架数据
        $count_not_sale = $db->join(null)->where(array_merge($where, array('is_on_sale' => 0)))->count();
//         $count_where = str_replace("is_on_sale='1'", "is_on_sale='0'", $count_where);
//         $count_not_sale = $db->join(null)->where($count_where)->count();
        $page = new ecjia_page ($count, 10, 5);
        $filter ['record_count'] = $count;
        $field = 'g.seller_id, goods_id, goods_name, goods_type, goods_sn, review_status, shop_price, goods_thumb, is_on_sale, is_best, is_new, is_hot, g.sort_order, goods_number, integral,(promote_price > 0 AND promote_start_date <= ' . $today . ' AND promote_end_date >= ' . $today . ')|is_promote, ssi.shop_name';
        $row = $db->field($field)->join('seller_shopinfo')->where($where)->order(array($filter['sort_by'] => $filter ['sort_order']))->limit($page->limit())->select();
        
        $filter ['keyword'] = stripslashes($filter ['keyword']);
        $filter ['count_on_sale']	= $count_on_sale;
        $filter ['count_not_sale']	= $count_not_sale;
        $filter ['count_goods_num']	= $count_not_sale + $count_on_sale;
        $filter ['count']			= $count;
    
        if (!empty($row)) {
        	foreach ($row as $k => $v) {
        		if (!file_exists(RC_Upload::upload_path() . $v['goods_thumb']) || empty($v['goods_thumb'])) {
        			$row[$k]['goods_thumb'] = RC_Uri::admin_url('statics/images/nopic.png');
        		} else {
        			$row[$k]['goods_thumb'] = RC_Upload::upload_url() . '/' . $v['goods_thumb'];
        		}
        		$row[$k]['shop_name'] = $v['seller_id'] == 0 ? '' : $v['shop_name'];
        	}
        }
        
        return array(
            'goods'		=> $row,
            'filter'	=> $filter,
            'page'		=> $page->show(5),
            'desc'		=> $page->page_desc()
        );
    }
}

// end