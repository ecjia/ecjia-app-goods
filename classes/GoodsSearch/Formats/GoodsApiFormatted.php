<?php
/**
 * Created by PhpStorm.
 * User: royalwang
 * Date: 2019-04-03
 * Time: 19:00
 */

namespace Ecjia\App\Goods\GoodsSearch\Formats;


use Ecjia\App\Goods\Models\GoodsModel;

class GoodsApiFormatted
{

    protected $model;
    
    public function __construct(GoodsModel $model)
    {
        $this->model = $model;
    }

    public function toArray()
    {
    	$store_logo = \Ecjia\App\Store\StoreFranchisee::StoreLogo($this->model->store_id);
    	//$shop_price = $this->model->member_price->user_price ? $this->model->member_price->user_price : $this->model->shop_price*$this->user_rank_discount;
    	
    	$promote_price = $this->filterPromotePrice($this->model->product_id ? $this->model->product_promote_price : $this->model->promote_price);
    	
    	$activity_type = ($this->model->shop_price > $promote_price && $promote_price > 0) ? 'PROMOTE_GOODS' : 'GENERAL_GOODS';
    	/* 计算节约价格*/
    	$saving_price = ($this->model->shop_price > $promote_price && $promote_price > 0) ? $this->model->shop_price - $promote_price : (($this->model->market_price > 0 && $this->model->market_price > $this->model->shop_price) ? $this->model->market_price - $this->model->shop_price : 0);
    	 
    	$properties = \Ecjia\App\Goods\GoodsFunction::get_goods_properties($this->model->goods_id);
    	$pro = empty($properties['pro']) ? [] : $this->formatProperties($properties['pro']);
    	$spe = empty($properties['specification']) ? [] : $this->formatSpecification($properties['spe']);
    	
        return [
            //store info
            'store_id' 					=> $this->model->store_id,
            'store_name'				=> $this->model->store->merchants_name,
            'store_logo'				=> $store_logo,
            'manage_mode' 				=> $this->model->store->manage_mode,
            'seller_id'					=> $this->model->store_id,
            'seller_name'				=> $this->model->store->merchants_name,
            'seller_logo'				=> $store_logo,
            //goods info
            'goods_id' 					=> $this->model->goods_id,
            'goods_name' 				=> $this->filterGoodsName($this->model->goods_name),
            'id'						=> $this->model->goods_id,
            'name'	   					=> $this->filterGoodsName($this->model->goods_name),
            'goods_sn' 					=> $this->filterGoodsSn($this->model->goods_sn),
            'market_price'				=> ecjia_price_format($this->model->market_price, false),
            'unformatted_market_price'  => $this->model->market_price,
            'shop_price'				=> ecjia_price_format($this->model->shop_price, false),
            'unformatted_shop_price'    => $this->model->shop_price,
            'promote_price' 			=> $promote_price > 0 ? ecjia_price_format($promote_price, false) : '',
            'promote_start_date'        => \RC_Time::local_date('Y/m/d H:i:s O', $this->model->promote_start_date),
            'promote_end_date'          => \RC_Time::local_date('Y/m/d H:i:s O', $this->model->promote_end_date),
            'unformatted_promote_price' => $promote_price,
            'product_id' 				=> $this->filterProductId($this->model->product_id),
            'product_goods_attr'		=> $this->filterProductGoodsAttr($this->model->product_goods_attr),
            'goods_barcode' 			=> $this->filterGoodsBarcode($this->model->goods_barcode),
            'activity_type' 			=> $activity_type,
            'object_id'     			=> 0,
            'saving_price'  			=> sprintf("%.2f", $saving_price),
            'formatted_saving_price'    => $saving_price > 0 ? sprintf(__('已省%s元', 'goods'), $saving_price) : '',
			'properties'				=> $pro,
			'specification'				=> array_values($properties['spe']),
			
            //picture info
	        'img' => array(
	        		'thumb'   => $this->model->goods_img ? \RC_Upload::upload_url($this->model->goods_img) : '',
	        		'url'     => $this->model->original_img ? \RC_Upload::upload_url($this->model->original_img) : '',
	        		'small'   => $this->model->goods_thumb ? \RC_Upload::upload_url($this->model->goods_thumb) : '',
	        ),
			
        ];
    }

	/**
	 * 促销价处理
	 * @param unknown $promote_price
	 * @return Ambigous <number, float>
	 */
    protected function filterPromotePrice($promote_price)
    {
    	if ($promote_price > 0) {
    		$promote_price = \Ecjia\App\Goods\BargainPrice::bargain_price($promote_price, $this->model->promote_start_date, $this->model->promote_end_date);
    	} else {
    		$promote_price = 0;
    	}
    	
    	return $promote_price;
    }
    
    protected function filterGoodsBarcode($goods_barcode)
    {
        return $goods_barcode;
    }

    protected function filterGoodsSn($goods_sn)
    {
        return $this->model->product_sn ?: $goods_sn;
    }

    /**
     * 过滤product_name
     * @param $goods_name
     * @return mixed
     */
    protected function filterGoodsName($goods_name)
    {
        return $goods_name;
    }

    /**
     * 过滤product_id
     * @param $product_id
     * @return int
     */
    protected function filterProductId($product_id)
    {
        return $product_id ?: 0;
    }
    
    /**
     * 过滤货品属性id
     * @param $product_goods_attr
     * @return string
     */
    protected function filterProductGoodsAttr($product_goods_attr)
    {
    	return $product_goods_attr ?: '';
    }
    
    /**
     * 处理商品属性
     * @param $pro
     * @return array
     */
    protected function formatProperties($pro)
    {
    	$outData = [];
    	if (!empty($pro)) {
    		foreach ($pro as $key => $value) {
    			// 处理分组
    			foreach ($value as $k => $v) {
    				$v['value']                 = strip_tags($v['value']);
    				$outData[]				    = $v;
    			}
    		}
    	}
    	return $outData;
    }
    
    /**
     * 处理商品规格
     * @param $pro
     * @return array
     */
    protected function formatSpecification($spe)
    {
    	$outData = [];
    	if (!empty($spe)) {
    		foreach ($spe as $key => $value) {
    			if (!empty($value['values'])) {
    				$value['value'] = $value['values'];
    				unset($value['values']);
    			}
    			$outData[] = $value;
    		}
    	}
    	return $outData;
    }
}