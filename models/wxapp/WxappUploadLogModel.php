<?php
/**
 * 开店星新零售管理系统
 * @description 基于Yii2+Vue2.0+uniapp研发，H5+小程序+公众号全渠道覆盖，功能完善开箱即用，框架成熟易扩展二开
 * @author 青岛开店星信息技术有限公司
 * @link https://www.kaidianxing.com
 * @copyright Copyright (c) 2020-2022 Qingdao ShopStar Information Technology Co., Ltd.
 * @copyright 版权归青岛开店星信息技术有限公司所有
 * @warning Unauthorized deletion of copyright information is prohibited.
 * @warning 未经许可禁止私自删除版权信息
 */

namespace shopstar\models\wxapp;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\components\wechat\helpers\MiniProgramACodeHelper;
use shopstar\helpers\ShopUrlHelper;
use shopstar\models\shop\ShopSettings;
use yii\helpers\Json;

/**
 * 小程序上传日志
 * Class WxappUploadLogModel
 * @author 青岛开店星信息技术有限公司
 * @package apps\wxapp\models
 */
class WxappUploadLogModel extends BaseActiveRecord
{
    /**
     * 上传小程序缓存前缀
     */
    public static $wxappUploadTicketPrefix = 'wxapp_upload_ticket_';

    /**
     * 小程序码存储路径
     */
    public static $wxappWxacodePathPrefix = SHOP_STAR_PUBLIC_TMP_PATH . '/wxapp/';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wxapp_upload_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['server_upload_time', 'publish_time'], 'safe'],
            [['describe'], 'required'],
            [['with_goods', 'upload_id'], 'integer'],
            [['describe'], 'string', 'max' => 191],
            [['version', 'server_version'], 'string', 'max' => 20],
            [['with_plugins'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'version' => '版本号',
            'server_version' => '云端版本号',
            'server_upload_time' => '服务器上传时间',
            'publish_time' => '发布时间',
            'describe' => '小程序简介',
            'with_goods' => '是否携带好物圈 1是 0否',
            'upload_id' => '上传id',
            'with_plugins' => '缓存数据',
        ];
    }


    /**
     * 获取小程序码访问路径
     * @return string
     * @author likexin
     */
    private static function getWxappWxacodeUrlPrefix(): string
    {
        return 'addons/shop/public/tmp/wxapp';
    }


    /**
     * 获取小程序二维码
     * @param string $path 路由
     * @param array $params 参数
     * @return string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     * @author 青岛开店星信息技术有限公司
     */
    public static function getWxappQRcode(string $path = '', array $params = [])
    {
        //小程序码版本
        $info = ShopSettings::get('channel_setting.wxapp');

        if (!empty($path)) {
            $path = ShopUrlHelper::wxapp($path, $params);
        }

        $md5Str = 'wxaqrcode_' . '_' . (!empty($path) ? $path : '') . '_' . $info['appid'];

        $options = [
            'directory' => WxappUploadLogModel::$wxappWxacodePathPrefix,
            'fileName' => md5($md5Str),
        ];

        //小程序码版本
        $localPath = $options['directory'] . '/' . $options['fileName'];
        if (!file_exists($localPath)) {
            //处理小程序码
            MiniProgramACodeHelper::getQrCode($path, $options);
        }


        return ShopUrlHelper::build(str_replace('addons/kdx_shop/public', '', self::getWxappWxacodeUrlPrefix()) . '/' . md5($md5Str) . '.jpg', [], true);
    }

    /**
     * 获取小程序访问二维码
     * @param string $sceneValue
     * @param array $optional
     * @return string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     * @author 青岛开店星信息技术有限公司
     */
    public static function getWxappUnlimitedQRcode(string $sceneValue = 'index', array $optional = [])
    {
        //小程序码版本
        $info = ShopSettings::get('channel_setting.wxapp');

        $md5Str = 'wxaqrcode_' . '_' . (Json::encode($optional)) . '_' . $info['appid'];

        $options = [
            'directory' => WxappUploadLogModel::$wxappWxacodePathPrefix . '/unlimited',
            'fileName' => md5($md5Str),
        ];

        //小程序码版本
        $localPath = $options['directory'] . '/' . $options['fileName'];
        if (!file_exists($localPath)) {
            //处理小程序码
            MiniProgramACodeHelper::getUnlimited($sceneValue, $options);
        }

        return ShopUrlHelper::build(str_replace('addons/kdx_shop/public', '', self::getWxappWxacodeUrlPrefix()) . '/unlimited/' . md5($md5Str) . '.jpg', [], true);
    }
}
