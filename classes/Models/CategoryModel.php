<?php
/**
 * Created by PhpStorm.
 * User: royalwang
 * Date: 2018/12/30
 * Time: 15:34
 */

namespace Ecjia\App\Goods\Models;

use Royalcms\Component\Database\Eloquent\Model;

class CategoryModel extends Model
{

    protected $table = 'category';

 	protected $primaryKey = 'cat_id';

    /**
     * 可以被批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'cat_name',
        'category_img',
        'keywords',
        'cat_desc',
        'parent_id',
        'sort_order',
        'template_file',
        'measure_unit',
        'show_in_nav',
        'style',
        'is_show',
        'grade',
        'filter_attr',
    ];


    /**
     * 获取商品店铺信息
     */
    public function parentCategory()
    {
        return $this->belongsTo('Ecjia\App\Goods\Models\CategoryModel', 'parent_id', 'cat_id');
    }

    /**
     * 商品会员等级价信息
     */
    public function childCategories()
    {
        return $this->hasMany('Ecjia\App\Goods\Models\CategoryModel', 'cat_id', 'parent_id');
    }

}