<?php
/**
 * Created by PhpStorm.
 * User: royalwang
 * Date: 2018/12/12
 * Time: 14:04
 */

namespace Ecjia\App\Goods\StoreDuplicateHandlers;

use Ecjia\App\Store\StoreDuplicate\StoreDuplicateAbstract;
use ecjia_error;
use RC_DB;
use RC_Api;
use ecjia_admin;
use RC_Time;

/**
 * 复制店铺中的在售商品
 *
 * Class StoreSellingGoodsDuplicate
 * @package Ecjia\App\Goods\StoreDuplicateHandlers
 */
class StoreSellingGoodsDuplicate extends StoreDuplicateAbstract
{
    /**
     * 代号标识
     * @var string
     */
    protected $code = 'store_selling_goods_duplicate';

    /**
     * 排序RC_Hook::apply_filters(
     * @var int
     */
    protected $sort = 14;

    protected $dependents = [
        'store_goods_merchant_category_duplicate',
    ];

    public function __construct($store_id, $source_store_id)
    {
        $this->name = __('在售普通商品', 'goods');
        parent::__construct($store_id, $source_store_id);
    }

    /**
     * 获取源店铺数据操作对象
     */
    public function getSourceStoreDataHandler()
    {
        return RC_DB::table('goods')->where('store_id', $this->source_store_id)->where('is_on_sale', 1)->where('is_delete', '!=', 1);
        //->select('goods_id', 'store_id', 'merchant_cat_id', 'bonus_type_id', 'goods_type', 'specification_id', 'parameter_id');
    }

    /**
     * 数据描述及输出显示内容
     */
    public function handlePrintData()
    {
        $count = $this->handleCount();
        $text = sprintf(__('店铺内总共有<span class="ecjiafc-red ecjiaf-fs3">%s</span>件在售商品', 'goods'), $count);

        return <<<HTML
<span class="controls-info">{$text}</span>
HTML;
    }

    /**
     * 统计数据条数并获取
     *
     * @return mixed
     */
    public function handleCount()
    {
        //如果已经统计过，直接返回统计过的条数
        if ($this->count) {
            return $this->count;
        }

        // 统计数据条数
        $this->count = $this->getSourceStoreDataHandler()->count();
        return $this->count;
    }

    /**
     * 执行复制操作
     *
     * @return mixed
     */
    public function handleDuplicate()
    {
        //检测当前对象是否已复制完成
        if ($this->isCheckFinished()) {
            return true;
        }

        //如果当前对象复制前仍存在依赖，则需要先复制依赖对象才能继续复制
        if (!empty($this->dependents)) { //如果设有依赖对象
            //检测依赖
            $items = $this->dependentCheck();
            if (!empty($items)) {
                return new ecjia_error('handle_duplicate_error', __('复制依赖检测失败！', 'store'), $items);
            }
        }

        //执行具体任务
        $result = $this->startDuplicateProcedure();
        if (is_ecjia_error($result)) {
            return $result;
        }

        //标记处理完成
        $this->markDuplicateFinished();

        //记录日志
        $this->handleAdminLog();

        return true;
    }

    /**
     * 店铺复制操作的具体过程
     *
     * 由于该任务比较复杂，考虑到部分表处理失败会导致数据完整性受影响，是否需要事务支持？
     * 如果开启事务的话，将对部分数据进行稍长时间的锁定，怎样合理安排隔离级别？
     *
     * 目前运行代码中尚未涉及事务相关功能，执行过程都属于乐观性操作，日后还需讨论更为严谨的方案
     *
     */
    protected function startDuplicateProcedure()
    {
        try {
//            RC_DB::beginTransaction();
//            RC_DB::rollBack();
//            RC_DB::commit();
//            RC_DB::transaction(function () {
//                RC_DB::table('users')->update(['votes' => 1]);
//
//                RC_DB::table('posts')->delete();
//            });

            //从过程数据中提取需要用到的替换数据
            $progress_data = (new \Ecjia\App\Store\StoreDuplicate\ProgressDataStorage($this->store_id))->getDuplicateProgressData();

            //商家商品分类的替换数据
            $merchant_category_replacement = $progress_data->getReplacementDataByCode('store_goods_merchant_category_duplicate');

            //店铺红包的替换数据
            $store_bonus_replacement = $progress_data->getReplacementDataByCode('store_bonus_duplicate');

            //商品规格中商品类型的替换数据
            $goods_specification_replacement = $progress_data->getReplacementDataByCode('store_goods_specification_duplicate.goods_type');

            //商品参数中商品类型的替换数据
            $goods_parameter_duplicate_replacement = $progress_data->getReplacementDataByCode('store_goods_parameter_duplicate.goods_type');

            //合并商品参数、规格中的 goods_type 替换数据
            $goods_type_replacement = $goods_specification_replacement + $goods_parameter_duplicate_replacement;

            //用于存储替换数据的关联关系
            $replacement_data = [];

            // goods 表中的替换
            $replacement_goods = [];

            $this->getSourceStoreDataHandler()->chunk(50, function ($items) use (
                &$replacement_goods,
                $merchant_category_replacement,
                $store_bonus_replacement,
                $goods_specification_replacement,
                $goods_parameter_duplicate_replacement,
                $goods_type_replacement
            ) {
                $time = RC_Time::gmtime();
                //构造可用于复制的数据
                //$this->buildDuplicateData($items);

                foreach ($items as $item) {
                    $goods_id = $item['goods_id'];
                    unset($item['goods_id']);

                    //将源店铺ID设为新店铺的ID
                    $item['store_id'] = $this->store_id;

                    //设置新店铺 merchat_cat_level1_id
                    $item['merchat_cat_level1_id'] = array_get($merchant_category_replacement, $item['merchat_cat_level1_id'], $item['merchat_cat_level1_id']);

                    //设置新店铺 merchat_cat_level2_id
                    $item['merchat_cat_level2_id'] = array_get($merchant_category_replacement, $item['merchat_cat_level2_id'], $item['merchat_cat_level2_id']);

                    //设置新店铺 merchant_cat_id
                    $item['merchant_cat_id'] = array_get($merchant_category_replacement, $item['merchant_cat_id'], $item['merchant_cat_id']);

                    //设置新店铺 bonus_type_id
                    $item['bonus_type_id'] = array_get($store_bonus_replacement, $item['bonus_type_id'], $item['bonus_type_id']);

                    //设置新店铺 goods_type
                    $item['goods_type'] = array_get($goods_type_replacement, $item['goods_type'], $item['goods_type']);

                    //设置新店铺 specification_id
                    $item['specification_id'] = array_get($goods_specification_replacement, $item['specification_id'], $item['specification_id']);

                    //设置新店铺 parameter_id
                    $item['parameter_id'] = array_get($goods_parameter_duplicate_replacement, $item['parameter_id'], $item['parameter_id']);

                    //click_count，商品点击数是否设为0
                    $item['click_count'] = 0;

                    //goods_number 商品库存数量设为1000
                    $item['click_count'] = 1000;

                    //add_time  商品添加时间设为当前时间
                    $item['add_time'] = $time;

                    //last_update  最近一次更新商品配置的时间设为当前时间
                    $item['last_update'] = $time;

                    //comments_number 评论设置为0
                    $item['comments_number'] = 0;

                    //sales_volume 销量设置为0
                    $item['sales_volume'] = 0;

                    //goods_sn，商品唯一货号需要重新生成（暂未实现）
                    //$item['goods_sn'] = generate_goods_sn($goods_id);


                    //@todo 图片字段的处理  goods_desc goods_thumb goods_img original_img


                    //插入数据到新店铺
                    //$new_goods_id = $goods_id + 1;
                    $new_goods_id = RC_DB::table('goods')->insertGetId($item);
                    $replacement_goods[$goods_id] = $new_goods_id;

                }
            });
            //存储 goods 相关替换数据
            $replacement_data['goods'] = $replacement_goods;

            //取出源店铺 goods_id
            $old_goods_id = array_keys($replacement_goods);
            //dump($replacement_goods);
            if (!empty($old_goods_id)) {

                //获取商品规格、参数中的 goods_type 替换数据
                $replacement_attribute = $progress_data->getReplacementDataByCode('store_goods_specification_duplicate.attribute') + $progress_data->getReplacementDataByCode('store_goods_parameter_duplicate.attribute');

                // goods_attr 表中的替换
                $replacement_goods_attr = [];

                //将数据同步到 goods_attr
                RC_DB::table('goods_attr')->whereIn('goods_id', $old_goods_id)->chunk(50, function ($items) use ($replacement_goods, $replacement_attribute, &$replacement_goods_attr) {
                    foreach ($items as $item) {
                        $goods_attr_id = $item['goods_attr_id'];
                        unset($item['goods_attr_id']);

                        //通过 goods 替换数据设置新店铺的 goods_id
                        $item['goods_id'] = array_get($replacement_goods, $item['goods_id'], $item['goods_id']);

                        //通过 attribute 替换数据设置新店铺 attr_id
                        $item['attr_id'] = array_get($replacement_attribute, $item['attr_id'], $item['attr_id']);

                        //@todo 图片字段的处理 attr_img_file attr_img_site attr_gallery_flie


                        //将数据插入到新店铺
                        $new_goods_attr_id = RC_DB::table('goods_attr')->insertGetId($item);
                        //$new_goods_attr_id = $goods_attr_id + 1;

                        //存储替换记录
                        $replacement_goods_attr[$goods_attr_id] = $new_goods_attr_id;
                    }
                });
                // 存储 goods_attr 相关替换数据
                $replacement_data['goods_attr'] = $replacement_goods_attr;

                //将数据同步到 goods_cat
                RC_DB::table('goods_cat')->whereIn('goods_id', $old_goods_id)->chunk(50, function ($items) use ($merchant_category_replacement, $replacement_goods) {
                    foreach ($items as &$item) {

                        //通过 goods 替换数据设置新店铺的 goods_id
                        $item['goods_id'] = array_get($replacement_goods, $item['goods_id'], $item['goods_id']);

                        //通过 merchant_category 替换数据设置新店铺的 cat_id
                        $item['cat_id'] = array_get($merchant_category_replacement, $item['cat_id'], $item['cat_id']);
                    }

                    //将数据插入到新店铺
                    RC_DB::table('goods_cat')->insert($items);
                });

                // products 表中的替换
                $replacement_products = [];
                RC_DB::table('products')->whereIn('goods_id', $old_goods_id)->chunk(50, function ($items) use (&$replacement_products, $replacement_goods, $replacement_goods_attr) {
                    foreach ($items as $item) {
                        $product_id = $item['product_id'];
                        unset($item['product_id']);

                        //通过 goods 替换数据设置新店铺的 goods_id
                        $item['goods_id'] = array_get($replacement_goods, $item['goods_id'], $item['goods_id']);

                        //新建当前item用于存储新店铺商品属性的数组
                        $new_goods_attr = [];
                        foreach (explode('|', $item['goods_attr']) as $goods_attr_id) {
                            //将新的goods_attr_id插入到新商品属性数组，在此之前检查一下该id是否存在可替换的值
                            isset($replacement_goods_attr[$goods_attr_id]) && $new_goods_attr[] = $replacement_goods_attr[$goods_attr_id];
                        }

                        //若新商品属性数组不为空，用其设置新店铺的 goods_attr
                        if (!empty($new_goods_attr)) {
                            $item['goods_attr'] = implode('|', $new_goods_attr);
                        }

                        //设置唯一产品货号 product_sn，通过相关代码和数据库内容，暂未发现有何规律或规则，也无货号唯一约束，该值暂时无法确认，先生成其他唯一码用于测试
                        $item['product_sn'] = bin2hex(random_bytes(16));


                        //@todo 图片字段的处理 product_thumb product_img product_original_img product_desc


                        //将数据插入到新店铺
                        //$new_product_id = $product_id + 1;
                        $new_product_id = RC_DB::table('products')->insertGetId($item);

                        //建立替换数据的关联关系
                        $replacement_products[$product_id] = $new_product_id;
                    }
                    //dd($replacement_products,$items);
                });
                //存储 products 相关替换数据
                $replacement_data['products'] = $replacement_products;

                //goods_gallery 表中的替换
                $replacement_goods_gallery = [];

                //将数据同步到 goods_gallery
                RC_DB::table('goods_gallery')->whereIn('goods_id', $old_goods_id)->chunk(50, function ($items) use ($replacement_goods, &$replacement_goods_gallery, $replacement_products) {
                    foreach ($items as $item) {
                        $img_id = $item['img_id'];
                        unset($item['img_id']);

                        //通过 goods 替换数据设置新店铺的 goods_id
                        $item['goods_id'] = array_get($replacement_goods, $item['goods_id'], $item['goods_id']);

                        ////通过 products 替换数据设置新店铺的 product_id
                        $item['product_id'] = array_get($replacement_products, $item['product_id'], $item['product_id']);

                        //@todo 图片字段的处理 img_url img_desc thumb_url img_original


                        //将数据插入到新店铺
                        //$new_img_id = $img_id + 1;
                        $new_img_id = RC_DB::table('goods_gallery')->insertGetId($item);

                        //存储替换记录
                        $replacement_goods_gallery[$img_id] = $new_img_id;
                    }
                    //dd($replacement_goods_gallery, $items);
                });
                //存储 goods_gallery 相关替换数据
                $replacement_data['goods_gallery'] = $replacement_goods_gallery;

                // member_price 表中的替换
                $replacement_member_price = [];

                //将数据同步到 member_price
                RC_DB::table('member_price')->whereIn('goods_id', $old_goods_id)->chunk(50, function ($items) use ($replacement_goods, &$replacement_member_price) {
                    foreach ($items as $item) {
                        $price_id = $item['price_id'];
                        unset($item['price_id']);

                        //通过 goods 替换数据设置新店铺的 goods_id
                        $item['goods_id'] = array_get($replacement_goods, $item['goods_id'], $item['goods_id']);

                        //将数据插入到新店铺
                        //$new_price_id = $price_id + 1;
                        $new_price_id = RC_DB::table('member_price')->insertGetId($item);

                        //存储替换记录
                        $replacement_member_price[$price_id] = $new_price_id;
                    }
                    //dd($replacement_member_price, $items);
                });
                //存储 member_price 相关替换数据
                $replacement_data['member_price'] = $replacement_member_price;

                //将数据同步到 volume_price
                RC_DB::table('volume_price')->whereIn('goods_id', $old_goods_id)->chunk(50, function ($items) use ($replacement_goods) {
                    foreach ($items as &$item) {
                        //price_type取值是什么暂时不清楚

                        //通过 goods 替换数据设置新店铺的 goods_id
                        $item['goods_id'] = array_get($replacement_goods, $item['goods_id'], $item['goods_id']);
                    }

                    //将数据插入到新店铺
                    RC_DB::table('volume_price')->insert($items);
                    //dd($items);
                });

                //将数据同步到 link_goods
                RC_DB::table('link_goods')->whereIn('goods_id', $old_goods_id)->chunk(50, function ($items) use ($replacement_goods) {
                    foreach ($items as &$item) {
                        //通过 goods 替换数据设置新店铺的 link_goods_id
                        $item['link_goods_id'] = array_get($replacement_goods, $item['link_goods_id'], $item['link_goods_id']);

                        //通过 goods 替换数据设置新店铺的 goods_id
                        $item['goods_id'] = array_get($replacement_goods, $item['goods_id'], $item['goods_id']);
                    }

                    //将数据插入到新店铺
                    RC_DB::table('link_goods')->insert($items);
                    //dd($items);
                });
            }

            $this->setReplacementData($this->getCode(), $replacement_data);

            return true;
        } catch (\Royalcms\Component\Repository\Exceptions\RepositoryException $e) {
            return new ecjia_error('duplicate_data_error', $e->getMessage());
        }


    }

    /**
     * 返回操作日志编写
     *
     * @return mixed
     */
    public function handleAdminLog()
    {
        \Ecjia\App\Store\Helper::assign_adminlog_content();

        $store_info = RC_Api::api('store', 'store_info', array('store_id' => $this->store_id));

        $merchants_name = !empty($store_info) ? sprintf(__('店铺名是%s', 'goods'), $store_info['merchants_name']) : sprintf(__('店铺ID是%s', 'goods'), $this->store_id);

        ecjia_admin::admin_log($merchants_name, 'clean', 'store_goods');
    }

}