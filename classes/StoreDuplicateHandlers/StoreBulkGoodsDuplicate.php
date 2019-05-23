<?php

namespace Ecjia\App\Goods\StoreDuplicateHandlers;

use ecjia_error;
use RC_DB;
use Royalcms\Component\Database\QueryException;

/**
 * 复制店铺中的散装商品
 *
 * Class StoreBulkGoodsDuplicate
 * @package Ecjia\App\Goods\StoreDuplicateHandlers
 */
class StoreBulkGoodsDuplicate extends StoreSellingGoodsDuplicate
{
    /**
     * 代号标识
     * @var string
     */
    protected $code = 'store_bulk_goods_duplicate';

    protected $rank_order = 6;
    public function __construct($store_id, $source_store_id, $sort = 16)
    {
        parent::__construct($store_id, $source_store_id, '在售散装商品', $sort);
    }

    /**
     * 重写获取源店铺数据操作对象
     * @return mixed
     */
    public function getSourceStoreDataHandler()
    {
        return RC_DB::table('goods')->where('store_id', $this->source_store_id)->where('is_on_sale', 1)->where('is_delete', 0)->where('extension_code', 'bulk');
    }

    /**
     *  重写店铺复制操作的具体过程
     * @return bool|ecjia_error
     */
    protected function startDuplicateProcedure()
    {
        try {
            //初始化过程数据中该复制操作需要用到的依赖数据
            $this->initRelationDataFromProgressData();

            //将数据复制到 goods
            $this->duplicateGoods();

            //存储 goods 相关替换数据
            $this->setReplacementData($this->getCode(), ['goods' => $this->replacement_goods]);

        } catch (QueryException $e) {
            $this->enableException();
            $this->err_msg .= $e->getMessage();
        }

        if ($this->exception) {
            $this->disableException();
            ecjia_log_error($this->err_msg);
            return new ecjia_error('duplicate_data_error', $this->err_msg);
        }
        return true;
    }

}