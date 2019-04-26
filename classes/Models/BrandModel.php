<?php
/**
 * Created by PhpStorm.
 * User: royalwang
 * Date: 2018/12/30
 * Time: 15:34
 */

namespace Ecjia\App\Goods\Models;

use Royalcms\Component\Database\Eloquent\Model;

class BrandModel extends Model
{

    protected $table = 'brand';

    protected $primaryKey = 'brand_id';

    /**
     * 可以被批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'brand_name',
        'brand_logo',
        'brand_desc',
        'site_url',
        'sort_order',
        'is_show',
        'is_delete',
    ];


//    /**
//     * 获取商品店铺信息
//     */
//    public function parentCategory()
//    {
//        return $this->belongsTo('Ecjia\App\Goods\Models\CategoryModel', 'parent_id', 'cat_id');
//    }
//
//    /**
//     * 商品会员等级价信息
//     */
//    public function childCategories()
//    {
//        return $this->hasMany('Ecjia\App\Goods\Models\CategoryModel', 'cat_id', 'parent_id');
//    }

}