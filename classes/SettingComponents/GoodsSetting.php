<?php
//
//    ______         ______           __         __         ______
//   /\  ___\       /\  ___\         /\_\       /\_\       /\  __ \
//   \/\  __\       \/\ \____        \/\_\      \/\_\      \/\ \_\ \
//    \/\_____\      \/\_____\     /\_\/\_\      \/\_\      \/\_\ \_\
//     \/_____/       \/_____/     \/__\/_/       \/_/       \/_/ /_/
//
//   上海商创网络科技有限公司
//
//  ---------------------------------------------------------------------------------
//
//   一、协议的许可和权利
//
//    1. 您可以在完全遵守本协议的基础上，将本软件应用于商业用途；
//    2. 您可以在协议规定的约束和限制范围内修改本产品源代码或界面风格以适应您的要求；
//    3. 您拥有使用本产品中的全部内容资料、商品信息及其他信息的所有权，并独立承担与其内容相关的
//       法律义务；
//    4. 获得商业授权之后，您可以将本软件应用于商业用途，自授权时刻起，在技术支持期限内拥有通过
//       指定的方式获得指定范围内的技术支持服务；
//
//   二、协议的约束和限制
//
//    1. 未获商业授权之前，禁止将本软件用于商业用途（包括但不限于企业法人经营的产品、经营性产品
//       以及以盈利为目的或实现盈利产品）；
//    2. 未获商业授权之前，禁止在本产品的整体或在任何部分基础上发展任何派生版本、修改版本或第三
//       方版本用于重新开发；
//    3. 如果您未能遵守本协议的条款，您的授权将被终止，所被许可的权利将被收回并承担相应法律责任；
//
//   三、有限担保和免责声明
//
//    1. 本软件及所附带的文件是作为不提供任何明确的或隐含的赔偿或担保的形式提供的；
//    2. 用户出于自愿而使用本软件，您必须了解使用本软件的风险，在尚未获得商业授权之前，我们不承
//       诺提供任何形式的技术支持、使用担保，也不承担任何因使用本软件而产生问题的相关责任；
//    3. 上海商创网络科技有限公司不对使用本产品构建的商城中的内容信息承担责任，但在不侵犯用户隐
//       私信息的前提下，保留以任何方式获取用户信息及商品信息的权利；
//
//   有关本产品最终用户授权协议、商业授权与技术服务的详细内容，均由上海商创网络科技有限公司独家
//   提供。上海商创网络科技有限公司拥有在不事先通知的情况下，修改授权协议的权力，修改后的协议对
//   改变之日起的新授权用户生效。电子文本形式的授权协议如同双方书面签署的协议一样，具有完全的和
//   等同的法律效力。您一旦开始修改、安装或使用本产品，即被视为完全理解并接受本协议的各项条款，
//   在享有上述条款授予的权力的同时，受到相关的约束和限制。协议许可范围以外的行为，将直接违反本
//   授权协议并构成侵权，我们有权随时终止授权，责令停止损害，并保留追究相关责任的权力。
//
//  ---------------------------------------------------------------------------------
//
/**
 * Created by PhpStorm.
 * User: royalwang
 * Date: 2018/7/23
 * Time: 11:56 AM
 */

namespace Ecjia\App\Goods\SettingComponents;


use Ecjia\App\Setting\ComponentAbstract;

class GoodsSetting extends ComponentAbstract
{

    /**
     * 代号标识
     * @var string
     */
    protected $code = 'goods';

    /**
     * 名称
     * @var string
     */
    protected $name = '商品显示设置';

    /**
     * 描述
     * @var string
     */
    protected $description = '';


    public function handle()
    {
        $data = [
            ['code' => 'review_goods', 'value' => '1', 'options' => ['type' => 'select', 'store_range' => '0,1']],
            ['code' => 'sn_prefix', 'value' => 'ECS', 'options' => ['type' => 'text']],
            ['code' => 'use_storage', 'value' => '1', 'options' => ['type' => 'select', 'store_range' => '1,0']],
            ['code' => 'default_storage', 'value' => '1000', 'options' => ['type' => 'text']],
            ['code' => 'no_picture', 'value' => '', 'options' => ['type' => 'file', 'store_dir' => 'data/assets/']],
            ['code' => 'auto_generate_gallery', 'value' => '1', 'options' => ['type' => 'select', 'store_range' => '1,0']],
            ['code' => 'retain_original_img', 'value' => '1', 'options' => ['type' => 'select', 'store_range' => '1,0']],
            ['code' => 'image_width', 'value' => '1200', 'options' => ['type' => 'text']],
            ['code' => 'image_height', 'value' => '1200', 'options' => ['type' => 'text']],
            ['code' => 'thumb_width', 'value' => '240', 'options' => ['type' => 'text']],
            ['code' => 'thumb_height', 'value' => '240', 'options' => ['type' => 'text']],
            ['code' => 'goods_name_length', 'value' => '100', 'options' => ['type' => 'text']],


        ];

        return $data;
    }


    public function getConfigs()
    {
        $config = [
            [
                'cfg_code' => 'review_goods',
                'cfg_name' => __('审核商家商品', 'goods'),
                'cfg_desc' => __('设置是否需要审核商家添加的商品，如果开启则所有商家添加的商品都需要审核之后才能显示', 'goods'),
                'cfg_range' => array(
                    '0' => __('否', 'goods'),
                    '1' => __('是', 'goods'),
                ),
            ],

            [
                'cfg_code' => 'sn_prefix',
                'cfg_name' => __('商品货号前缀', 'goods'),
                'cfg_desc' => '',
                'cfg_range' => '',
            ],

            [
                'cfg_code' => 'use_storage',
                'cfg_name' => __('是否启用库存管理', 'goods'),
                'cfg_desc' => '',
                'cfg_range' => array(
                    '0' => __('否', 'goods'),
                    '1' => __('是', 'goods'),
                ),
            ],

            [
                'cfg_code' => 'default_storage',
                'cfg_name' => __('默认库存', 'goods'),
                'cfg_desc' => '',
                'cfg_range' => '',
            ],

            [
                'cfg_code' => 'no_picture',
                'cfg_name' => __('商品的默认图片', 'goods'),
                'cfg_desc' => '',
                'cfg_range' => '',
            ],

            [
                'cfg_code' => 'auto_generate_gallery',
                'cfg_name' => __('上传商品是否自动生成相册图', 'goods'),
                'cfg_desc' => '',
                'cfg_range' => array(
                    '0' => __('否', 'goods'),
                    '1' => __('是', 'goods'),
                ),
            ],

            [
                'cfg_code' => 'retain_original_img',
                'cfg_name' => __('上传商品时是否保留原图', 'goods'),
                'cfg_desc' => '',
                'cfg_range' => array(
                    '0' => __('否', 'goods'),
                    '1' => __('是', 'goods'),
                ),
            ],

            [
                'cfg_code' => 'image_width',
                'cfg_name' => __('商品图片宽度', 'goods'),
                'cfg_desc' => '',
                'cfg_range' => '',
            ],

            [
                'cfg_code' => 'image_height',
                'cfg_name' => __('商品图片高度', 'goods'),
                'cfg_desc' => __('如果您的服务器支持GD，在您上传商品图片的时候将自动将图片缩小到指定的尺寸。', 'goods'),
                'cfg_range' => '',
            ],

            [
                'cfg_code' => 'thumb_width',
                'cfg_name' => __('缩略图宽度', 'goods'),
                'cfg_desc' => '',
                'cfg_range' => '',
            ],

            [
                'cfg_code' => 'thumb_height',
                'cfg_name' => __('缩略图高度', 'goods'),
                'cfg_desc' => '',
                'cfg_range' => '',
            ],

            [
                'cfg_code' => 'goods_name_length',
                'cfg_name' => __('商品名称的长度', 'goods'),
                'cfg_desc' => '',
                'cfg_range' => '',
            ],



        ];

        return $config;
    }



}