<?php
/**
 * Created by PhpStorm.
 * User: royalwang
 * Date: 2019-04-30
 * Time: 10:30
 */

namespace Ecjia\App\Goods\DataExport;

use Ecjia\App\Goods\Models\GoodsModel;
use Royalcms\Component\DataExport\Contracts\ExportsCustomizeData;
use Royalcms\Component\DataExport\CustomizeDataSelection;
use Royalcms\Component\DataExport\Exceptions\CouldNotAddToCustomizeDataSelection;
//use Royalcms\Component\Support\Collection;
//use Royalcms\Component\Support\Str;

class GoodsExport implements ExportsCustomizeData
{

    protected $model;

    public function __construct(GoodsModel $model)
    {
        $this->model = $model;

    }

    /**
     * 导出主商品信息
     * @param \Royalcms\Component\DataExport\CustomizeDataSelection $customizeDataSelection
     */
    protected function exportGoodsInfo(CustomizeDataSelection $customizeDataSelection)
    {
        try {

            //主表信息
            $customizeDataSelection->add($this->model->goods_sn.'/goods.json', $this->model->toArray());


            //主图信息
            $customizeDataSelection->addFile(\RC_Upload::upload_path($this->model->goods_thumb), $this->model->goods_thumb)
                ->addFile(\RC_Upload::upload_path($this->model->goods_img), $this->model->goods_img)
                ->addFile(\RC_Upload::upload_path($this->model->original_img), $this->model->original_img);

            //商品描述中的图片



            return true;
        }
        catch (CouldNotAddToCustomizeDataSelection $e) {
            return $e;
        }
    }

    /**
     * 导出商品相册信息
     * @param \Royalcms\Component\DataExport\CustomizeDataSelection $customizeDataSelection
     */
    protected function exportGoodsGallery(CustomizeDataSelection $customizeDataSelection)
    {
        if ($this->model->goods_gallery_collection->isNotEmpty()) {

            try {
                $customizeDataSelection->add($this->model->goods_sn.'/goods_gallery.json', $this->model->goods_gallery_collection->toArray());

                $this->model->goods_gallery_collection->map(function($model) use ($customizeDataSelection) {

                    try {
                        $customizeDataSelection->addFile(\RC_Upload::upload_path($model->img_url), $model->img_url)
                            ->addFile(\RC_Upload::upload_path($model->thumb_url), $model->thumb_url)
                            ->addFile(\RC_Upload::upload_path($model->img_original), $model->img_original);

                        return true;
                    }
                    catch (CouldNotAddToCustomizeDataSelection $e) {
                        return $e;
                    }

                });

                return true;
            }
            catch (CouldNotAddToCustomizeDataSelection $e) {
                return $e;
            }
        }
    }

    /**
     * 导出商品的活动信息
     * @param \Royalcms\Component\DataExport\CustomizeDataSelection $customizeDataSelection
     */
    protected function exportGoodsActivity(CustomizeDataSelection $customizeDataSelection)
    {

    }

    /**
     * 导出商品的规格信息
     * @param \Royalcms\Component\DataExport\CustomizeDataSelection $customizeDataSelection
     */
    protected function exportGoodsAttr(CustomizeDataSelection $customizeDataSelection)
    {

    }

    /**
     * 导出商品的货品信息
     * @param \Royalcms\Component\DataExport\CustomizeDataSelection $customizeDataSelection
     */
    protected function exportGoodsProduct(CustomizeDataSelection $customizeDataSelection)
    {

    }



    /**
     * @param \Royalcms\Component\DataExport\CustomizeDataSelection $customizeDataSelection
     * @return array
     */
    public function selectCustomizeData(CustomizeDataSelection $customizeDataSelection)
    {
        $result['export_goods_info'] = $this->exportGoodsInfo($customizeDataSelection);
        $result['export_goods_gallery'] = $this->exportGoodsGallery($customizeDataSelection);
        $result['export_goods_activity'] = $this->exportGoodsActivity($customizeDataSelection);
        $result['export_goods_attr'] = $this->exportGoodsAttr($customizeDataSelection);
        $result['export_goods_product'] = $this->exportGoodsProduct($customizeDataSelection);
//        dd($result);
        return $result;
    }

    /**
     * @return string
     */
    public function customizeDataExportName()
    {
//        $name = Str::slug($this->goods_sn);
        $name = 'goods_collection';
        return "export-data-{$name}.zip";
    }

    public function getKey()
    {
//        $name = Str::slug($this->goods_sn);
        $name = 'goods_collection';

        return $name;
    }

}