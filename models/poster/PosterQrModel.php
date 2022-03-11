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

namespace shopstar\models\poster;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\helpers\DateTimeHelper;
use shopstar\models\member\MemberWechatModel;

/**
 * This is the model class for table "{{%app_poster_qr}}".
 *
 * @property int $id auto increment id
 * @property string $openid openid
 * @property int $poster_id 海报ID
 * @property int $type 海报类型
 * @property string $ticket 二维码票据
 * @property string $url 二维码连接
 * @property string $image 二维码图片
 * @property string $scene 场景参数
 * @property string $created_at 创建时间
 */
class PosterQrModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%app_poster_qr}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['poster_id', 'type'], 'integer'],
            [['created_at'], 'safe'],
            [['openid'], 'string', 'max' => 64],
            [['ticket', 'url', 'image', 'scene'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'auto increment id',
            'openid' => 'openid',
            'poster_id' => '海报ID',
            'type' => '海报类型',
            'ticket' => '二维码票据',
            'url' => '二维码连接',
            'image' => '二维码图片',
            'scene' => '场景参数',
            'created_at' => '创建时间',
        ];
    }

    /**
     * 添加海报二维码记录
     * @param $posterId
     * @param $openId
     * @param $posterType
     * @param $ticket
     * @param $url
     * @param $image
     * @param string $scene
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function addQr($posterId, $openId, $posterType, $ticket, $url, $image, $scene = '')
    {
        $qr = new PosterQrModel();

        $qrAttr = [
            'poster_id' => $posterId,
            'openid' => $openId,
            'type' => $posterType,
            'ticket' => $ticket,
            'url' => $url,
            'image' => $image,
            'scene' => $scene,
            'created_at' => DateTimeHelper::now()
        ];

        $qr->setAttributes($qrAttr);

        $qr->save();

        return $qr->toArray();

    }
}