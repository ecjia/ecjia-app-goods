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
/**
 * 商品货品详情
 * @author zrl
 *
 */
class admin_merchant_goods_product_detail_module extends api_admin implements api_interface {
    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {

		$this->authadminSession();
		if ($_SESSION['staff_id'] <= 0) {
			return new ecjia_error(100, 'Invalid session');
		}
		$result = $this->admin_priv('goods_manage');
		if (is_ecjia_error($result)) {
		    return $result;
		}
    	
    	$goods_id		= $this->requestData('goods_id');
    	$product_id		= intval($this->requestData('product_id', 0)); //商品货品id
    	 
    	if (empty($goods_id) || empty($product_id)) {
    	    return new ecjia_error('invalid_parameter', sprintf(__('请求接口%s参数无效', 'goods'), __CLASS__));
    	}
    	
    	//商品信息
    	$GoodsBasicInfo = new Ecjia\App\Goods\Goods\GoodsBasicInfo($goods_id, $_SESSION['store_id']);
    	$goods = $GoodsBasicInfo->goodsInfo();
    	if (empty($goods)) {
    		return new ecjia_error('goods_not_exist_info', __('商品信息不存在', 'goods'));
    	}
    	
    	$ProductBasicInfo = new Ecjia\App\Goods\Goods\ProductBasicInfo($product_id, $goods_id);
    	$product = $ProductBasicInfo->productInfo();
    	
    	if (empty($product)) {
    		return new ecjia_error('product_not_exist_info', __('未检测到此货品', 'goods'));
    	}
    	$label_product_attr = '';
    	if ($product->goods_attr) {
    		$goods_attr = explode('|', $product->goods_attr);
    		if ($goods->goods_attr_collection) {
    			$product_attr_value = $goods->goods_attr_collection->whereIn('goods_attr_id', $goods_attr)->sortBy('goods_attr_id')->lists('attr_value');
    			$product_attr_value = $product_attr_value->implode('/');
    			$label_product_attr = $product_attr_value;
    		}
    	}
    	
    	$p_gallery = $ProductBasicInfo->getProductGallery();
    	
    	$product_gallery = $this->format_product_gallery($p_gallery);
    	
    	$product_detail = [
    		'goods_id'				=> intval($product->goods_id),
    		'product_id'			=> intval($product->product_id),
    		'product_name'			=> empty($product->product_name) ? '' : trim($product->product_name),
    		'product_sn'			=> $product->product_sn,
    		'product_shop_price'	=> $product->product_shop_price,
    		'product_number'		=> intval($product->product_number),
    		'product_attr'			=> trim($product->goods_attr),
    		'product_bar_code'		=> empty($product->product_bar_code) ? '' : $product->product_bar_code,
    		'label_product_attr'	=> $label_product_attr,
    		'img'					=> array(
    										'thumb'	=> empty($product->product_thumb) ? '' : RC_Upload::upload_url($product->product_thumb),
    										'url'	=> empty($product->product_original_img) ? '' : RC_Upload::upload_url($product->product_original_img),
    										'small'	=> empty($product->product_img) ? '' : RC_Upload::upload_url($product->product_img),
    									),
    		'product_gallery'		=> $product_gallery,
    	];
    		
    	return $product_detail;
    }
    
    /**
     * 货品相册
     */
    private function format_product_gallery($p_gallery)
    {
    	$pictures_array = [];
    	/* 格式化相册图片路径 */
    	if (!empty($p_gallery)) {
    		foreach ($p_gallery as $key => $gallery_img) {
    			
    			$desc_index = intval(strrpos($gallery_img['img_original'], '?')) + 1;
    			!empty($desc_index) && $p_gallery[$key]['desc'] = substr($gallery_img['img_original'], $desc_index);
    			
    			$p_gallery[$key]['small']	= !empty($gallery_img['img_url']) ? RC_Upload::upload_url($gallery_img['img_url']) : '';
    			$p_gallery[$key]['url']		= !empty($gallery_img['img_original']) ? RC_Upload::upload_url($gallery_img['img_original']) : '';
    			$p_gallery[$key]['thumb']	= !empty($gallery_img['thumb_url']) ? RC_Upload::upload_url($gallery_img['thumb_url']) : '';
    	
    			$img_list_sort[$key] 	= $p_gallery[$key]['desc'];
    			$img_list_id[$key] 		= $gallery_img['img_id'];
    		}
    		//先使用sort排序，再使用id排序。
    		if ($p_gallery) {
    			array_multisort($img_list_sort, $img_list_id, $p_gallery);
    		}
    	}
    	
    	$pictures = $p_gallery;
    	if (!empty($p_gallery)) {
    		foreach ($pictures as $val) {
    			$pictures_array[] = array(
    					'img_id'	=> $val['img_id'],
    					'thumb'		=> $val['thumb'],
    					'url'		=> $val['url'],
    					'small'		=> $val['small'],
    			);
    		}
    	}
    	return $pictures_array;
    }
}
