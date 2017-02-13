<?php
//
//    ______         ______           __         __         ______
//   /\  ___\       /\  ___\         /\_\       /\_\       /\  __ \
//   \/\  __\       \/\ \____        \/\_\      \/\_\      \/\ \_\ \
//    \/\_____\      \/\_____\     /\_\/\_\      \/\_\      \/\_\ \_\
//     \/_____/       \/_____/     \/__\/_/       \/_/       \/_/ /_/
//
//   上海商创网络科技有限公司
//
//  ---------------------------------------------------------------------------------
//
//   一、协议的许可和权利
//
//    1. 您可以在完全遵守本协议的基础上，将本软件应用于商业用途；
//    2. 您可以在协议规定的约束和限制范围内修改本产品源代码或界面风格以适应您的要求；
//    3. 您拥有使用本产品中的全部内容资料、商品信息及其他信息的所有权，并独立承担与其内容相关的
//       法律义务；
//    4. 获得商业授权之后，您可以将本软件应用于商业用途，自授权时刻起，在技术支持期限内拥有通过
//       指定的方式获得指定范围内的技术支持服务；
//
//   二、协议的约束和限制
//
//    1. 未获商业授权之前，禁止将本软件用于商业用途（包括但不限于企业法人经营的产品、经营性产品
//       以及以盈利为目的或实现盈利产品）；
//    2. 未获商业授权之前，禁止在本产品的整体或在任何部分基础上发展任何派生版本、修改版本或第三
//       方版本用于重新开发；
//    3. 如果您未能遵守本协议的条款，您的授权将被终止，所被许可的权利将被收回并承担相应法律责任；
//
//   三、有限担保和免责声明
//
//    1. 本软件及所附带的文件是作为不提供任何明确的或隐含的赔偿或担保的形式提供的；
//    2. 用户出于自愿而使用本软件，您必须了解使用本软件的风险，在尚未获得商业授权之前，我们不承
//       诺提供任何形式的技术支持、使用担保，也不承担任何因使用本软件而产生问题的相关责任；
//    3. 上海商创网络科技有限公司不对使用本产品构建的商城中的内容信息承担责任，但在不侵犯用户隐
//       私信息的前提下，保留以任何方式获取用户信息及商品信息的权利；
//
//   有关本产品最终用户授权协议、商业授权与技术服务的详细内容，均由上海商创网络科技有限公司独家
//   提供。上海商创网络科技有限公司拥有在不事先通知的情况下，修改授权协议的权力，修改后的协议对
//   改变之日起的新授权用户生效。电子文本形式的授权协议如同双方书面签署的协议一样，具有完全的和
//   等同的法律效力。您一旦开始修改、安装或使用本产品，即被视为完全理解并接受本协议的各项条款，
//   在享有上述条款授予的权力的同时，受到相关的约束和限制。协议许可范围以外的行为，将直接违反本
//   授权协议并构成侵权，我们有权随时终止授权，责令停止损害，并保留追究相关责任的权力。
//
//  ---------------------------------------------------------------------------------
//
defined('IN_ECJIA') or exit('No permission resources.');

class goods_hooks {
    
    public static function widget_admin_dashboard_goodsstat() {
    	
    	if (!ecjia_admin::$controller->admin_priv('goods_manage', ecjia::MSGTYPE_HTML, false)) {
    		return false;	
    	}
    	
        $title = RC_Lang::get('goods::goods.goods_count_info');
        
    	$goods = RC_Cache::app_cache_get('admin_dashboard_goods', 'goods');
	    if (!$goods) {
			$db_goods = RC_Loader::load_app_model('goods_model', 'goods');
			$time = RC_Time::gmtime();
			$fields = "SUM(IF(goods_id>0,1,0)) as total,SUM(IF(is_new=1,1,0)) as new,SUM(IF(is_best=1,1,0)) as best,SUM(IF(is_hot=1,1,0)) as hot,SUM(IF(goods_number <= warn_number,1,0)) as warn_goods,SUM(IF(promote_price>0 and promote_start_date<=".$time." and promote_end_date>=".$time.",1,0)) as promote_goods";
			$row = $db_goods ->field($fields)->where(array("is_delete"=>'0' , "is_real"=>'1'))->select();
			
			$goods['total'] 		= $row[0]['total'];
			$goods['new_goods']		= $row[0]['new'];
			$goods['new_goods_url'] = RC_Uri::url('goods/admin/init','intro_type=is_new');
			$goods['best_goods']	= $row[0]['best'];
			$goods['best_goods_url'] = RC_Uri::url('goods/admin/init','intro_type=is_best');
			$goods['hot_goods']		= $row[0]['hot'];
			$goods['hot_goods_url'] = RC_Uri::url('goods/admin/init','intro_type=is_hot');
			$goods['promote_goods'] = $row[0]['promote_goods'];
			$goods['promote_goods_url'] = RC_Uri::url('goods/admin/init','intro_type=is_promote');
			
			/* 缺货商品 */
			if (ecjia::config('use_storage')) {
				$goods['warn_goods'] = $row[0]['warn_goods'];
			} else {
				$goods['warn_goods'] = 0;
			}
			$goods['warn_goods_url'] = RC_Uri::url('goods/admin/init','stock_warning=1');
		
	    	RC_Cache::app_cache_set('admin_dashboard_goods', $goods, 'goods', 120);
	    }
		
		ecjia_admin::$controller->assign('title', $title);
		ecjia_admin::$controller->assign('goods', $goods);
		ecjia_admin::$controller->assign_lang();
		ecjia_admin::$controller->display(ecjia_app::get_app_template('library/widget_admin_dashboard_goodsstat.lbi', 'goods'));
    }
}

RC_Hook::add_action( 'admin_dashboard_left', array('goods_hooks', 'widget_admin_dashboard_goodsstat'), 20);

// end