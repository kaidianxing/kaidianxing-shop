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

namespace shopstar\models\broadcast;


use shopstar\bases\model\BaseActiveRecord;
use shopstar\components\wechat\helpers\MiniProgramBroadcastGoodsHelper;

/**
 * This is the model class for table "{{%app_broadcast_goods}}".
 *
 * @property int $id
 * @property int $broadcast_goods_id 直播间商品库id
 * @property int $goods_id 商品id
 * @property int $audit_id 审核id
 * @property string $cover_img_url 首图
 * @property string $cover_img_media_id 首图的media_id
 * @property int $status 审核状态 0：未审核。1：审核中，2：审核通过，3：审核驳回
 * @property int $created_at 创建时间
 */
class BroadcastGoodsModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%app_broadcast_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_id'], 'required'],
            [['broadcast_goods_id', 'goods_id', 'audit_id', 'status'], 'integer'],
            [['cover_img_url'], 'string', 'max' => 191],
            [['created_at'], 'safe'],
            [['cover_img_media_id'], 'string', 'max' => 120],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'broadcast_goods_id' => '直播间商品库id',
            'goods_id' => '商品id',
            'audit_id' => '审核id',
            'cover_img_url' => '首图',
            'cover_img_media_id' => '首图的media_id',
            'status' => '审核状态 0：未审核。1：审核中，2：审核通过，3：审核驳回',
            'created_at' => '创建时间',
        ];
    }

    /**
     * 同步状态
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function syncStatus()
    {
        //获取所有商品id
        $goodsId = BroadcastGoodsModel::find()->select([
            'broadcast_goods_id'
        ])->asArray()->column();

        //获取直播商品id
        $goodsList = MiniProgramBroadcastGoodsHelper::getGoodsWarehouse([
            'goods_ids' => $goodsId,
        ]);

        foreach ((array)$goodsList['goods'] as $item) {
            $model = self::findOne(['broadcast_goods_id' => $item['goods_id']]);
            if (empty($model)) {
                continue;
            }

            $model->status = $item['audit_status'];
            $model->save();
        }

        return true;
    }

    /**
     * 删除直播间商品
     * @param $goodsId
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function deleteGoodsAndGoodsMapByGoodsId($goodsId)
    {
        $goods = BroadcastGoodsModel::find()->where([
            'goods_id' => $goodsId,
        ])->asArray()->all();

        foreach ((array)$goods as $item) {
            try {
                //删除小程序商品库
                MiniProgramBroadcastGoodsHelper::delete(['goodsId' => $item['broadcast_goods_id']]);
            } catch (\Exception $exception) {

            }
            //删除商品库
            BroadcastGoodsModel::deleteAll(['goods_id' => $goodsId]);
            //删除商品映射
            BroadcastRoomGoodsMapModel::deleteAll(['goods_id' => $goodsId]);
        }

        return true;
    }
}
