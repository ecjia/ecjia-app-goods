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
use Royalcms\Component\Support\Str;

class GoodsCollectionExport implements ExportsCustomizeData
{

    /**
     * @param \Royalcms\Component\DataExport\CustomizeDataSelection $customizeDataSelection
     * @return void
     */
    public function selectCustomizeData(CustomizeDataSelection $customizeDataSelection)
    {
        try {
            $customizeDataSelection
                ->add('user.json', $this->toArray())
                ->addFile(\RC_Upload::upload_path($this->goods_thumb))
                ->addFile(\RC_Upload::upload_path($this->goods_img))
                ->addFile(\RC_Upload::upload_path($this->original_img));
        }
        catch (CouldNotAddToCustomizeDataSelection $e) {
            dd($e);
        }

    }

    /**
     * @return string
     */
    public function customizeDataExportName()
    {
        $name = Str::slug($this->goods_sn);

        return "export-data-{$name}.zip";
    }

    public function getKey()
    {
        $name = Str::slug($this->goods_sn);

        return $name;
    }

}