<?php
/**
 * Created by PhpStorm.
 * User: royalwang
 * Date: 2019-02-19
 * Time: 18:16
 */

namespace Ecjia\App\Goods\Models;

use Royalcms\Component\Database\Eloquent\Model;

class AttributeModel extends Model
{

    protected $table = 'attribute';

    protected $primaryKey = 'attr_id';

    /**
     * 可以被批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = false;


    /**
     * 一对一
     * 获取属性分类信息
     */
    public function goods_type_model()
    {
        return $this->belongsTo('Ecjia\App\Goods\Models\GoodsTypeModel', 'cat_id', 'cat_id');
    }
    
}