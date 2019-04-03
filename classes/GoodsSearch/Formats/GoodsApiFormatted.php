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
        return [

            //store info
            'store_id' => $this->model->store_id,

            //goods info
            'goods_id' => $this->model->goods_id,
            'product_id' => $this->filterProductId($this->model->product_id),

            'goods_sn' => $this->filterGoodsSn($this->model->goods_sn),

            'goods_barcode' => $this->filterGoodsBarcode($this->model->goods_barcode),
            'goods_name' => $this->filterGoodsName($this->model->goods_name),


            //picture info






        ];
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
}