<?php
/**
 * Created by PhpStorm.
 * User: royalwang
 * Date: 2019-02-19
 * Time: 18:16
 */

namespace Ecjia\App\Goods\Models;

use Royalcms\Component\Database\Eloquent\Model;

class GoodsModel extends Model
{

    protected $table = 'goods';

    protected $primaryKey = 'goods_id';

    /**
     * 可以被批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'store_id',
        'cat_id',
        'cat_level1_id',
        'cat_level2_id',
        'merchant_cat_id',
        'merchat_cat_level1_id',
        'merchat_cat_level2_id',
        'goods_sn',
        'goods_barcode',
        'goods_name',
        'goods_name_style',
        'click_count',
        'brand_id',
        'provider_name',
        'goods_number',
        'weight_stock',
        'goods_weight',
        'weight_unit',
        'default_shipping',
        'market_price',
        'shop_price',
        'cost_price',
        'generate_date',
        'expiry_date',
        'limit_days',
        'promote_price',
        'promote_start_date',
        'promote_end_date',
        'warn_number',
        'keywords',
        'goods_brief',
        'goods_desc',
        'goods_thumb',
        'goods_img',
        'original_img',
        'is_real',
        'extension_code',
        'is_on_sale',
        'is_alone_sale',
        'is_shipping',
        'integral',
        'add_time',
        'sort_order',
        'store_sort_order',
        'is_delete',
        'is_best',
        'is_new',
        'is_hot',
        'is_promote',
        'bonus_type_id',
        'last_update',
        'goods_type',
        'seller_note',
        'give_integral',
        'rank_integral',
        'suppliers_id',
        'is_check',
        'store_hot',
        'store_new',
        'store_best',
        'group_number',
        'is_xiangou',
        'xiangou_start_date',
        'xiangou_end_date',
        'xiangou_num',
        'review_status',
        'review_content',
        'goods_shipai',
        'comments_number',
        'sales_volume',
        'model_price',
        'model_inventory',
        'model_attr',
        'largest_amount',
        'pinyin_keyword',
        'goods_product_tag',
        'goodslib_id',
        'goodslib_update_time'
    ];

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * 获取商品店铺信息
     */
    public function store()
    {
        return $this->belongsTo('Ecjia\App\Merchant\Models\StoreFranchiseeModel', 'store_id', 'store_id');
    }
    
    /**
     * 商品会员等级价信息
     */
    public function member_price()
    {
    	return $this->hasMany('Ecjia\App\User\Models\MemberPriceModel', 'goods_id', 'goods_id');
    }
    
    /**
     * 商品货品信息
     */
    public function products()
    {
    	return $this->hasMany('Ecjia\App\Cart\Models\ProductsModel', 'goods_id', 'goods_id');
    }
    
    /**
     * 商品商家分类信息
     */
    public function merchants_category()
    {
    	return $this->belongsTo('Ecjia\App\Goods\Models\MerchantCategoryModel', 'merchant_cat_id', 'cat_id');
    }

}