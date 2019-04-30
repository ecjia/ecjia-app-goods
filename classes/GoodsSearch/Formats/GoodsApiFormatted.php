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

    /**
     * @var GoodsModel
     */
    protected $model;
    
    protected $user_rank_discount;
    
    protected $user_rank;
    
    public function __construct(GoodsModel $model, $user_rank_discount = 1, $user_rank = 0)
    {
        $this->model = $model;
        $this->user_rank_discount = $user_rank_discount;
        $this->user_rank = $user_rank;
    }

    public function toArray()
    {
        //店铺logo
    	$store_logo = $this->storeLogo();
    	
    	//市场价
    	$market_price = $this->model->market_price;
    	
    	//会员等级价
    	$user_price = $this->userPrice();
    	
    	$shop_price = $user_price > 0 ? $user_price : $this->model->shop_price*$this->user_rank_discount;
    	
    	//商品促销价格
    	$promote_price = $this->filterPromotePrice($this->model->promote_price, $this->model->is_promote, $this->model->promote_limited);
    	
    	//市场价最终价
    	$final_shop_price = $promote_price > 0 ? min($shop_price, $promote_price) : $shop_price;
    	 
        if ($this->model->product_id > 0) {
        	$total_attr_price = 0;
        	$product_goods_attr = explode('|', $this->model->product_goods_attr);
        	//货品没自定义价格时计算货品属性价格的和
        	if ($this->model->product_shop_price < 0) {
        		$total_attr_price = $this->totalAttrPrice($product_goods_attr);
        	}
        	
        	//货品会员等级价
        	$product_shop_price = $this->model->product_shop_price*$this->user_rank_discount;
        	//货品促销价格
        	$product_promote_price = $this->filterPromotePrice($this->model->product_promote_price, $this->model->is_product_promote, $this->model->product_promote_limited);
        	
        	$market_price += $total_attr_price;

        	//货品有设置自定义价格
        	if ($this->model->product_shop_price > 0) {
        		$shop_price = $product_shop_price;
        	} else {
        		$shop_price += $total_attr_price;
        	}

        	$promote_price = $product_promote_price;
        	//市场价最终价
        	$final_shop_price = $promote_price > 0 ? min($shop_price, $promote_price) : $shop_price;
        }

    	$activity_type = ($promote_price > 0) ? 'PROMOTE_GOODS' : 'GENERAL_GOODS';
    	/* 计算节约价格*/
    	$saving_price = ($this->model->shop_price > $promote_price && $promote_price > 0) ? $this->model->shop_price - $promote_price : (($this->model->market_price > 0 && $this->model->market_price > $this->model->shop_price) ? $this->model->market_price - $this->model->shop_price : 0);
    	
    	//商品规格属性
    	list($properties, $specification) = $this->goodsProperties();
    	
        return [
            //store info
            'store_id' 					=> $this->model->store_id,
            'store_name'				=> $this->model->store_franchisee_model->merchants_name,
            'store_logo'				=> !empty($store_logo) ? \RC_Upload::upload_url($store_logo) : '',
            'manage_mode' 				=> $this->model->store_franchisee_model->manage_mode,
            'seller_id'					=> $this->model->store_id,
            'seller_name'				=> $this->model->store_franchisee_model->merchants_name,
            'seller_logo'				=> !empty($store_logo) ? \RC_Upload::upload_url($store_logo) : '',
            //goods info
            'goods_id' 					=> $this->model->goods_id,
            'goods_name' 				=> $this->filterGoodsName($this->model->goods_name),
            'id'						=> $this->model->goods_id,
            'name'	   					=> $this->filterGoodsName($this->model->goods_name),
            'goods_sn' 					=> $this->filterGoodsSn($this->model->goods_sn),
            'market_price'				=> ecjia_price_format($market_price, false),
            'unformatted_market_price'  => sprintf("%.2f", $market_price),
            'shop_price'				=> ecjia_price_format($final_shop_price, false),
            'unformatted_shop_price'    => sprintf("%.2f", $final_shop_price),
            'promote_price' 			=> $promote_price > 0 ? ecjia_price_format($promote_price, false) : '',
            'promote_start_date'        => \RC_Time::local_date('Y/m/d H:i:s O', $this->model->promote_start_date),
            'promote_end_date'          => \RC_Time::local_date('Y/m/d H:i:s O', $this->model->promote_end_date),
            'unformatted_promote_price' => $promote_price > 0 ? sprintf("%.2f", $promote_price) : 0,
            'product_id' 				=> $this->filterProductId($this->model->product_id),
            'product_goods_attr'		=> $this->filterProductGoodsAttr($this->model->product_goods_attr),
            'goods_barcode' 			=> $this->filterGoodsBarcode($this->model->goods_barcode),
            'activity_type' 			=> $activity_type,
            'object_id'     			=> 0,
            'saving_price'  			=> sprintf("%.2f", $saving_price),
            'formatted_saving_price'    => $saving_price > 0 ? sprintf(__('已省%s元', 'goods'), $saving_price) : '',
			'properties'				=> $properties,
			'specification'				=> $this->model->product_id > 0 ? [] : $specification,
            //picture info
	        'img' 						=> $this->filterGoodsImg($this->model->product_id),
			
        ];
    }

    /**
     * 商品主图信息处理
     * @param int $product_id
     * @return array
     */
    protected function filterGoodsImg($product_id)
    {
    	$img = [
    		'thumb' => $this->model->goods_thumb ? \RC_Upload::upload_url($this->model->goods_thumb) : '',
    		'url'     => $this->model->original_img ? \RC_Upload::upload_url($this->model->original_img) : '',
    		'small'   => $this->model->goods_img ? \RC_Upload::upload_url($this->model->goods_img) : '',
    	];
    	if ($product_id > 0) {
    		if (!empty($this->model->product_thumb)) {
    			$img['thumb'] = \RC_Upload::upload_url($this->model->product_thumb);
    		}
    		if (!empty($this->model->product_original_img)) {
    			$img['url'] = \RC_Upload::upload_url($this->model->product_original_img);
    		}
    		if (!empty($this->model->product_img)) {
    			$img['small'] = \RC_Upload::upload_url($this->model->product_img);
    		}
    	}
    	
    	return $img;
    }
    
	/**
	 * 促销价处理
	 * @param unknown $promote_price
	 * @return Ambigous <number, float>
	 */
    protected function filterPromotePrice($promote_price, $is_promote = 0, $promote_limited = 0)
    {
    	if ($promote_price > 0 && $is_promote == 1 && $promote_limited > 0) {
    		$promote_price = \Ecjia\App\Goods\BargainPrice::bargain_price($promote_price, $this->model->promote_start_date, $this->model->promote_end_date, $promote_limited);
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
        return $this->model->product_name ?: $goods_name;
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
    
    /**
     * 获取货品属性价的和
     * @param array $product_goods_attr
     * @return float
     */
    protected function totalAttrPrice($product_goods_attr)
    {
    	if ($this->model->goods_attr) {
    		$total_attr_price = $this->model->goods_attr->map(function ($item, $key) use ($product_goods_attr) {
    			if (in_array($item->goods_attr_id, $product_goods_attr)) {
    				return $attr_price += $item->attr_price;
    			}
    		})->sum();
    	}
    	return $total_attr_price;
    }
    
    /**
     * 店铺logo
     * @return string
     */
    protected function storeLogo()
    {
    	if ($this->model->store_franchisee_model->merchants_config_collection) {
    		$shop_logo_group = $this->model->store_franchisee_model->merchants_config_collection->where('code', 'shop_logo')->first();
    		$store_logo = $shop_logo_group ? $shop_logo_group->value : '';
    	} else {
    		$store_logo = '';
    	}
    	
    	return $store_logo;
    }
    
    /**
     * 商品会员价
     * @return number
     */
    protected function userPrice()
    {
    	if ($this->model->member_price_collection) {
    		$member_price = $this->model->member_price_collection->where('goods_id', $this->model->goods_id)->where('user_rank', $this->user_rank)->first();
    		$user_price = $member_price ? $member_price->user_price : 0;
    	} else {
    		$user_price = 0;
    	}
    	return $user_price;
    }
    
    protected function goodsProperties()
    {
    	$properties = ['pro' => [], 'spe' => [], 'lnk' => []];
    	if ($this->model->goods_type_model) {
    		$grp = $this->model->goods_type_model->attr_group;
    		if (! empty ( $grp )) {
    			$groups = explode ( "\n", strtr ( $grp, "\r", '' ) );
    		}
    		if ($this->model->goods_attr_collection) {
    			$res = $this->model->goods_attr_collection->map(function ($item) {
    				if ($item->attribute_collection) {
    					$attribute = $item->attribute_collection->map(function ($v, $k) use($item) {
    						  return  [
    									'goods_attr_id' => $item->goods_attr_id,
    									'attr_value'	=> $item->attr_value,
    									'attr_price'	=> $item->attr_price,
    									'attr_id'		=> $v->attr_id,
    									'attr_name'		=> $v->attr_name,
    									'attr_group'	=> $v->attr_group,
    									'is_linked'		=> $v->is_linked,
    									'attr_type'		=> $v->attr_type
    								];
    					});
    				}
    				return $attribute;
    			})->map(function ($val, $key) {
    				return $val['0'];
    			});
    			
    			$properties = $this->formatGoodsProperties($groups, $res);
    		}
    	}
    	
    	$pro = empty($properties['pro']) ? [] : $this->formatProperties($properties['pro']);
    	$spe = empty($properties['spe']) ? [] : $this->formatSpecification($properties['spe']);
    	
    	return [$pro, $spe];
    }
    
    
    protected function formatGoodsProperties($groups, $res)
    {
    	$arr ['pro'] = array (); // 属性
    	$arr ['spe'] = array (); // 规格
    	$arr ['lnk'] = array (); // 关联的属性
    	
    	foreach ( $res as $row ) {
    		$row ['attr_value'] = str_replace ( "\n", '<br />', $row ['attr_value'] );
    	
    		if ($row ['attr_type'] == 0) {
    			$group = (isset ( $groups [$row ['attr_group']] )) ? $groups [$row ['attr_group']] : __('商品属性', 'goods');
    	
    			$arr ['pro'] [$group] [$row ['attr_id']] ['name'] = $row ['attr_name'];
    			$arr ['pro'] [$group] [$row ['attr_id']] ['value'] = $row ['attr_value'];
    		} else {
    			$attr_price = $row['attr_price'];
    	
    			$arr ['spe'] [$row ['attr_id']] ['attr_type'] = $row ['attr_type'];
    			$arr ['spe'] [$row ['attr_id']] ['name'] = $row ['attr_name'];
    			$arr ['spe'] [$row ['attr_id']] ['value'] [] = array (
    					'label' => $row ['attr_value'],
    					'price' => $row ['attr_price'],
    					'format_price' => price_format ( abs ( $row ['attr_price'] ), false ),
    					'id' => $row ['goods_attr_id']
    			);
    		}
    	
    		if ($row ['is_linked'] == 1) {
    			/* 如果该属性需要关联，先保存下来 */
    			$arr ['lnk'] [$row ['attr_id']] ['name'] = $row ['attr_name'];
    			$arr ['lnk'] [$row ['attr_id']] ['value'] = $row ['attr_value'];
    		}
    	}
    	return $arr;
    }
    
}