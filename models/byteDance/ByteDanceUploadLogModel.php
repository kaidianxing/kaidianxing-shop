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

namespace shopstar\models\byteDance;


use shopstar\bases\model\BaseActiveRecord;
use shopstar\components\byteDance\helpers\ByteDanceQrcodeHelper;
use shopstar\helpers\ShopUrlHelper;
use shopstar\models\shop\ShopSettings;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%byte_dance_upload_log}}".
 *
 * @property int $id
 * @property string $version 版本号
 * @property string $server_version 服务端版本号
 * @property string $server_upload_time 服务器上传时间
 * @property string $publish_time 发布时间
 * @property string $describe 小程序简介
 * @property int $upload_id 上传id
 */
class ByteDanceUploadLogModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%byte_dance_upload_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'upload_id'], 'integer'],
            [['server_upload_time', 'publish_time'], 'safe'],
            [['version', 'server_version'], 'string', 'max' => 20],
            [['describe'], 'string', 'max' => 191],
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
            'server_version' => '服务端版本号',
            'server_upload_time' => '服务器上传时间',
            'publish_time' => '发布时间',
            'describe' => '小程序简介',
            'upload_id' => '上传id',
        ];
    }

    /**
     * 获取二维码
     * @param string $appName
     * @param string $url
     * @param array $params
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    public static function getByteDanceQrcode(string $appName = 'toutiao', string $url = 'index', array $params = [])
    {
        $appid = ShopSettings::get('channel_setting.byte_dance')['appid'];
        $path = SHOP_STAR_PUBLIC_TMP_PATH . '/byte_dance_qrcode/' . md5('_' . $appName . '_' . $appid . '_' . $url . '_' . Json::encode($params)) . '.jpg';
        if (!file_exists($path)) {
            ByteDanceQrcodeHelper::getCode(
                $url,
                array_merge(['appname' => $appName], $params),
                ['directory' => SHOP_STAR_PUBLIC_TMP_PATH . '/byte_dance_qrcode/', 'file_name' => md5('_' . $appName . '_' . $appid . '_' . $url . '_' . Json::encode($params)) . '.jpg']
            );
        }

        return ShopUrlHelper::build('tmp/byte_dance_qrcode/' . md5('_' . $appName . '_' . $appid . '_' . $url . '_' . Json::encode($params)) . '.jpg', [], true);
    }
}