<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 单个商品的祥情描述
 * @author royalwang
 *
 */
class desc_module implements ecjia_interface
{

    public function run(ecjia_api & $api)
    {
        /* 获得商品的信息 */
    	$goods_id = _POST('goods_id', 0);
    	
		RC_Loader::load_app_func('goods','goods');
        $goods = get_goods_info($goods_id);
        
        if ($goods === false) {
            /* 如果没有找到任何记录则跳回到首页 */
            EM_Api::outPut(13);
            exit();
        } elseif (empty($goods['goods_id'])) {
            /* 不存在该商品  */
            EM_Api::outPut(13);
            exit();
        } else {
        	$data = $goods;
	        $base = sprintf('<base href="%s/" />', dirname(SITE_URL));
	        $style = RC_App::apps_url('goods/statics/styles/goodsapi.css');
	        $html = '<!DOCTYPE html><html><head><title>' . $data['goods_name'] . '</title><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta name="viewport" content="initial-scale=1.0"><meta name="viewport" content="initial-scale = 1.0 , minimum-scale = 1.0 , maximum-scale = 1.0" /><link href="'.$style.'" rel="stylesheet">' . $base . '</head><body>' . $data['goods_desc'] . '</body></html>';
	        
	        EM_Api::outPut(array(
	            'data' => $html
	        ));
        }
        //         $_REQUEST['id'] = _POST('goods_id', 0);
        //         $goods_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
    }
}


// end