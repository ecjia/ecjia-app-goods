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
 * js语言包设置
 */

defined('IN_ECJIA') or exit('No permission resources.');

return array(

    [
      'cfg_code' => 'basic',
      'cfg_name' => __('基本设置', 'goods'),
      'cfg_desc' => '',
      'cfg_range' => '',
    ],

    [
        'cfg_code' => 'display',
        'cfg_name' => __('显示设置', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => '',
    ],

    [
        'cfg_code' => 'shop_info',
        'cfg_name' => __('网店信息', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => '',
    ],

    [
        'cfg_code' => 'shopping_flow',
        'cfg_name' => __('购物流程', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => '',
    ],

    [
        'cfg_code' => 'smtp',
        'cfg_name' => __('邮件服务器设置', 'goods'),
        'cfg_desc' => __('设置邮件服务器基本参数', 'goods'),
        'cfg_range' => '',
    ],

    [
        'cfg_code' => 'goods',
        'cfg_name' => __('商品显示设置', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => '',
    ],











    [
        'cfg_code' => 'integral_name',
        'cfg_name' => __('消费积分名称', 'goods'),
        'cfg_desc' => __('您可以将消费积分重新命名。例如：烧币<br>消费积分功能本名叫“积分”，未填写直接显示本名，只影响到前台用户端的显示，不会对后台功能名称作影响', 'goods'),
        'cfg_range' => '',
    ],

    [
        'cfg_code' => 'integral_scale',
        'cfg_name' => __('积分换算比例', 'goods'),
        'cfg_desc' => __('每100积分可抵多少元现金', 'goods'),
        'cfg_range' => '',
    ],

    [
        'cfg_code' => 'integral_percent',
        'cfg_name' => __('积分支付比例', 'goods'),
        'cfg_desc' => __('每100元商品最多可以使用多少元积分', 'goods'),
        'cfg_range' => '',
    ],

    [
        'cfg_code' => 'enable_order_check',
        'cfg_name' => __('是否开启新订单提醒', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => array(
            '0' => __('否', 'goods'),
            '1' => __('是', 'goods'),
        ),
    ],





    [
        'cfg_code' => 'order_number',
        'cfg_name' => __('订单显示数量', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => '',
    ],



    [
        'cfg_code' => 'shop_website',
        'cfg_name' => __('商店网址', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => '',
    ],









    [
        'cfg_code' => 'user_notice',
        'cfg_name' => __('用户中心公告', 'goods'),
        'cfg_desc' => __('该信息将在用户中心欢迎页面显示', 'goods'),
        'cfg_range' => '',
    ],



    [
        'cfg_code' => 'shop_reg_closed',
        'cfg_name' => __('是否关闭注册', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => array(
            '0' => __('否', 'goods'),
            '1' => __('是', 'goods'),
        ),
    ],

    [
        'cfg_code' => 'send_mail_on',
        'cfg_name' => __('是否开启自动发送邮件', 'goods'),
        'cfg_desc' => __('启用该选项登录后台时，会自动发送邮件队列中尚未发送的邮件', 'goods'),
        'cfg_range' => array(
            '0' => __('关闭', 'goods'),
            '1' => __('开启', 'goods'),
        ),
    ],

    [
        'cfg_code' => 'member_email_validate',
        'cfg_name' => __('是否开启会员邮件验证', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => array(
            '0' => __('关闭', 'goods'),
            '1' => __('开启', 'goods'),
        ),
    ],

    [
        'cfg_code' => 'send_verify_email',
        'cfg_name' => __('用户注册时自动发送验证邮件', 'goods'),
        'cfg_desc' => __('“是否开启会员邮件验证”设为开启时才可使用此功能', 'goods'),
        'cfg_range' => array(
            '0' => __('关闭', 'goods'),
            '1' => __('开启', 'goods'),
        ),
    ],

    [
        'cfg_code' => 'message_board',
        'cfg_name' => __('是否启用留言板功能', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => array(
            '0' => __('关闭', 'goods'),
            '1' => __('开启', 'goods'),
        ),
    ],

    [
        'cfg_code' => 'message_check',
        'cfg_name' => __('用户留言是否需要审核', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => array(
            '0' => __('不需要', 'goods'),
            '1' => __('需要', 'goods'),
        ),
    ],

    [
        'cfg_code' => 'store_identity_certification',
        'cfg_name' => __('商家强制认证', 'goods'),
        'cfg_desc' => __('设置是否需要认证商家资质，如果开启则认证通过后的商家才能开店和显示', 'goods'),
        'cfg_range' => array(
            '0' => __('否', 'goods'),
            '1' => __('是', 'goods'),
        ),
    ],

    [
        'cfg_code' => 'order_ship_note',
        'cfg_name' => __('设置订单为“已发货”时', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => array(
            '0' => __('无需填写备注', 'goods'),
            '1' => __('必须填写备注', 'goods'),
        ),
    ],

    [
        'cfg_code' => 'when_dec_storage',
        'cfg_name' => __('什么时候减少库存', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => array(
            '0' => __('下定单时', 'goods'),
            '1' => __('发货时', 'goods'),
        ),
    ],


    [
        'cfg_code' => 'order_cancel_note',
        'cfg_name' => __('取消订单时', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => array(
            '0' => __('无需填写备注', 'goods'),
            '1' => __('必须填写备注', 'goods'),
        ),
    ],



    [
        'cfg_code' => 'page_style',
        'cfg_name' => __('分页样式', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => array(
            '0' => __('默认经典', 'goods'),
            '1' => __('流行页码', 'goods'),
        ),
    ],

    [
        'cfg_code' => 'sort_order_type',
        'cfg_name' => __('商品分类页默认排序类型', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => array(
            '0' => __('按上架时间', 'goods'),
            '1' => __('按商品价格', 'goods'),
            '2' => __('按最后更新时间', 'goods'),
        ),
    ],

    [
        'cfg_code' => 'sort_order_method',
        'cfg_name' => __('商品分类页默认排序方式', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => array(
            '0' => __('降序排列', 'goods'),
            '1' => __('升序排列', 'goods'),
        ),
    ],

    [
        'cfg_code' => 'show_order_type',
        'cfg_name' => __('商品分类页默认显示方式', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => array(
            '0' => __('列表显示', 'goods'),
            '1' => __('表格显示', 'goods'),
            '2' => __('文本显示', 'goods'),
        ),
    ],

    [
        'cfg_code' => 'register_points',
        'cfg_name' => __('会员注册赠送积分', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => '',
    ],





    [
        'cfg_code' => 'show_brand',
        'cfg_name' => __('是否显示品牌', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => array(
            '0' => __('不显示', 'goods'),
            '1' => __('显示', 'goods'),
        ),
    ],

    [
        'cfg_code' => 'goodsattr_style',
        'cfg_name' => __('商品属性显示样式', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => array(
            '0' => __('下拉列表', 'goods'),
            '1' => __('单选按钮', 'goods'),
        ),
    ],

    [
        'cfg_code' => 'test_mail_address',
        'cfg_name' => __('邮件地址', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => '',
    ],

    [
        'cfg_code' => 'send',
        'cfg_name' => __('发送测试邮件', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => '',
    ],


    [
        'cfg_code' => 'email_content',
        'cfg_name' => __('您好！这是一封检测邮件服务器设置的测试邮件。收到此邮件，意味着您的邮件服务器设置正确！您可以进行其它邮件发送的操作了！', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => '',
    ],

    [
        'cfg_code' => 'sms',
        'cfg_name' => __('短信通知', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => '',
    ],

    [
        'cfg_code' => 'sms_shop_mobile',
        'cfg_name' => __('商家的手机号码', 'goods'),
        'cfg_desc' => __('请先注册手机短信服务再填写手机号码', 'goods'),
        'cfg_range' => '',
    ],

    [
        'cfg_code' => 'sms_order_placed',
        'cfg_name' => __('客户下订单时是否给商家发短信', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => array(
            '0' => __('不发短信', 'goods'),
            '1' => __('发短信', 'goods'),
        ),
    ],

    [
        'cfg_code' => 'sms_order_payed',
        'cfg_name' => __('客户付款时是否给商家发短信', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => array(
            '0' => __('不发短信', 'goods'),
            '1' => __('发短信', 'goods'),
        ),
    ],

    [
        'cfg_code' => 'sms_order_shipped',
        'cfg_name' => __('商家发货时是否给客户发短信', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => array(
            '0' => __('不发短信', 'goods'),
            '1' => __('发短信', 'goods'),
        ),
    ],

    [
        'cfg_code' => 'attr_related_number',
        'cfg_name' => __('属性关联的商品数量', 'goods'),
        'cfg_desc' => __('在商品详情页面显示多少个属性关联的商品。', 'goods'),
        'cfg_range' => '',
    ],

    [
        'cfg_code' => 'top10_time',
        'cfg_name' => __('排行统计的时间', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => array(
            '0' => __('所有', 'goods'),
            '1' => __('一年', 'goods'),
            '2' => __('半年', 'goods'),
            '3' => __('三个月', 'goods'),
            '4' => __('一个月', 'goods'),
        ),
    ],





    [
        'cfg_code' => 'search_keywords',
        'cfg_name' => __('首页搜索的关键字', 'goods'),
        'cfg_desc' => __('首页显示的搜索关键字,请用半角逗号(,)分隔多个关键字', 'goods'),
        'cfg_range' => '',
    ],

    [
        'cfg_code' => 'bgcolor',
        'cfg_name' => __('缩略图背景色', 'goods'),
        'cfg_desc' => __('颜色请以#FFFFFF格式填写', 'goods'),
        'cfg_range' => '',
    ],









    //与底部语言合并

    [
        'cfg_code' => 'mail_service',
        'cfg_name' => __('邮件服务', 'goods'),
        'cfg_desc' => __('如果您选择了采用服务器内置的 Mail 服务，您不需要填写下面的内容。', 'goods'),
        'cfg_range' => array(
            '0' => __('采用服务器内置的 Mail 服务', 'goods'),
            '1' => __('采用其他的 SMTP 服务', 'goods'),
        ),    ],

    [
        'cfg_code' => 'smtp_host',
        'cfg_name' => __('发送邮件服务器地址(SMTP)', 'goods'),
        'cfg_desc' => __('邮件服务器主机地址。如果本机可以发送邮件则设置为localhost', 'goods'),
        'cfg_range' => '',
    ],

    [
        'cfg_code' => 'smtp_port',
        'cfg_name' => __('服务器端口', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => '',
    ],

    [
        'cfg_code' => 'smtp_user',
        'cfg_name' => __('邮件发送帐号', 'goods'),
        'cfg_desc' => __('发送邮件所需的认证帐号，如果没有就为空着', 'goods'),
        'cfg_range' => '',
    ],

    [
        'cfg_code' => 'smtp_pass',
        'cfg_name' => __('帐号密码', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => '',
    ],

    [
        'cfg_code' => 'smtp_mail',
        'cfg_name' => __('邮件回复地址', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => '',
    ],

    [
        'cfg_code' => 'mail_charset',
        'cfg_name' => __('邮件编码', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => array(
            'UTF8' => __('国际化编码（utf8）', 'goods'),
            'GB2312' => __('简体中文', 'goods'),
            'BIG5' => __('繁体中文', 'goods'),
        ),
    ],

    [
        'cfg_code' => 'smtp_ssl',
        'cfg_name' => __('邮件服务器是否要求加密连接(SSL)', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => array(
            '0' => __('否', 'goods'),
            '1' => __('是', 'goods'),
        ),
    ],

    [
        'cfg_code' => 'wap',
        'cfg_name' => __('H5应用设置', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => '',
    ],

    [
        'cfg_code' => 'wap_config',
        'cfg_name' => __('是否使用H5应用功能', 'goods'),
        'cfg_desc' => __('此功能不仅可以在APP中内嵌使用，还可以在微信公众号中作为微信商城使用。', 'goods'),
        'cfg_range' => array(
            '0' => __('关闭', 'goods'),
            '1' => __('开启', 'goods'),
        ),
    ],

    [
        'cfg_code' => 'map_qq_referer',
        'cfg_name' => __('腾讯地图应用名称', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => '',
    ],

    [
        'cfg_code' => 'map_qq_key',
        'cfg_name' => __('腾讯地图KEY', 'goods'),
        'cfg_desc' => __('使用QQ账号，进行<a target="_blank" href="http://lbs.qq.com/key.html">开发密钥申请</a>，填写应用名及应用描述即可申请。一个账号可以申请多个key。', 'goods'),
        'cfg_range' => '',
    ],

    [
        'cfg_code' => 'wap_logo',
        'cfg_name' => __('H5 LOGO上传', 'goods'),
        'cfg_desc' => __('适用于收藏夹图标，为了更好地兼容各种手机类型，LOGO 最好为png图片', 'goods'),
        'cfg_range' => '',
    ],

    [
        'cfg_code' => 'mobile_touch_qrcode',
        'cfg_name' => __('H5 访问二维码', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => '',
    ],

    [
        'cfg_code' => 'mobile_touch_url',
        'cfg_name' => __('H5 商城URL', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => '',
    ],

    [
        'cfg_code' => 'wap_app_download_show',
        'cfg_name' => __('是否推广APP下载', 'goods'),
        'cfg_desc' => __('在H5首页底部推广您的APP，增加下载量。', 'goods'),
        'cfg_range' => array(
            '0' => '关闭',
            '1' => '开启',
        ),
    ],

    [
        'cfg_code' => 'wap_app_download_img',
        'cfg_name' => __('推广APP下载图片', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => '',
    ],

    [
        'cfg_code' => 'mobile_iphone_download',
        'cfg_name' => __('iPhone下载地址', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => '',
    ],

    [
        'cfg_code' => 'mobile_android_download',
        'cfg_name' => __('Android下载地址', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => '',
    ],

    [
        'cfg_code' => 'shop_app_icon',
        'cfg_name' => __('APP图标', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => '',
    ],

    [
        'cfg_code' => 'mobile_app_description',
        'cfg_name' => __('移动应用简介', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => '',
    ],

    [
        'cfg_code' => 'comment',
        'cfg_name' => __('评论设置', 'goods'),
        'cfg_desc' => '',
        'cfg_range' => '',
    ],

);
//end
