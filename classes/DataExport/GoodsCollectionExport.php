<?php
/**
 * Created by PhpStorm.
 * User: royalwang
 * Date: 2019-04-30
 * Time: 10:30
 */

namespace Ecjia\App\Goods\DataExport;

use Royalcms\Component\DataExport\Contracts\ExportsCustomizeData;
use Royalcms\Component\DataExport\CustomizeDataSelection;
use Royalcms\Component\DataExport\Exceptions\CouldNotAddToCustomizeDataSelection;
use Royalcms\Component\Support\Collection;
use Royalcms\Component\Support\Str;

class GoodsCollectionExport implements ExportsCustomizeData
{

    protected $collection;

    public function __construct(Collection $collection)
    {
        $this->collection = $collection;

    }

    /**
     * @param \Royalcms\Component\DataExport\CustomizeDataSelection $customizeDataSelection
     * @return void
     */
    public function selectCustomizeData(CustomizeDataSelection $customizeDataSelection)
    {

        $result = $this->collection->map(function($model) use ($customizeDataSelection) {
            try {
                $customizeDataSelection
                    ->add($model->goods_sn.'/goods.json', $model->toArray())
                    ->addFile(\RC_Upload::upload_path($model->goods_thumb), $model->goods_thumb)
                    ->addFile(\RC_Upload::upload_path($model->goods_img), $model->goods_img)
                    ->addFile(\RC_Upload::upload_path($model->original_img), $model->original_img);

                return true;
            }
            catch (CouldNotAddToCustomizeDataSelection $e) {
                return $e;
            }
        });


//        dd($result);
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