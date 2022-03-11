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
use shopstar\components\wechat\helpers\MiniProgramBroadcastRoomHelper;
use shopstar\constants\broadcast\BroadcastLogConstant;
use shopstar\constants\broadcast\BroadcastRoomIsDeletedConstant;
use shopstar\constants\broadcast\BroadcastRoomStatusConstant;
use shopstar\models\log\LogModel;

/**
 * This is the model class for table "{{%app_broadcast_room}}".
 *
 * @property int $id
 * @property int $broadcast_room_id 直播间id
 * @property string $title 直播间标题
 * @property string $anchor_name 主播昵称
 * @property string $start_time 开始时间
 * @property string $end_time 结束时间
 * @property int $status 状态   101：直播中   102：未开始   103：已结束  104：禁播   105：暂停  106：异常   107：已过期
 * @property string $cover_img 背景图
 * @property string $cover_img_media_id 背景图 media id
 * @property string $anchor_wechat 主播微信号
 * @property string $share_img 分享图
 * @property string $share_img_media_id 分享图media id
 * @property int $broadcast_type 直播类型 0手机直播 1推流
 * @property int $screen_type 横屏、竖屏 【1：横屏，0：竖屏】（横屏：视频宽高比为16:9、4:3、1.85:1 ；竖屏：视频宽高比为9:16、2:3）
 * @property int $close_like 是否关闭点赞 1是0否
 * @property int $close_goods 是否关闭货架 1是0否
 * @property int $close_comment 是否关闭评论 1是0否
 * @property int $created_at 创建时间
 * @property int $is_deleted 是否删除1是0否
 * @property int $is_hide 是否隐藏
 */
class BroadcastRoomModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%app_broadcast_room}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['broadcast_room_id', 'status', 'broadcast_type', 'screen_type', 'close_like', 'close_goods', 'close_comment', 'is_deleted', 'is_hide'], 'integer'],
            [['start_time', 'end_time', 'created_at'], 'safe'],
            [['title', 'anchor_name', 'anchor_wechat', 'cover_img_media_id', 'share_img_media_id'], 'string', 'max' => 120],
            [['cover_img', 'share_img'], 'string', 'max' => 191],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'broadcast_room_id' => '直播间id',
            'title' => '直播间标题',
            'anchor_name' => '主播昵称',
            'start_time' => '开始时间',
            'end_time' => '结束时间',
            'status' => '状态
101：直播中
102：未开始
103：已结束
104：禁播
105：暂停
106：异常
107：已过期',
            'cover_img' => '背景图',
            'cover_img_media_id' => '背景图 media id',
            'anchor_wechat' => '主播微信号',
            'share_img' => '分享图',
            'share_img_media_id' => '分享图media id',
            'broadcast_type' => '直播类型 0手机直播 1推流',
            'screen_type' => '横屏、竖屏 【1：横屏，0：竖屏】（横屏：视频宽高比为16:9、4:3、1.85:1 ；竖屏：视频宽高比为9:16、2:3）',
            'close_like' => '是否关闭点赞 1是0否',
            'close_goods' => '是否关闭货架 1是0否',
            'close_comment' => '是否关闭评论 1是0否',
            'created_at' => '创建时间',
            'is_deleted' => '是否删除1是0否',
            'is_hide' => '是否隐藏1是0否',
        ];
    }

    /**
     * 同步状态
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function syncStatus()
    {

        //获取店铺下的所有的直播间
        $start = 0;
        $limit = 100;

        $roomList = [];
        while (true) {

            $roomListResult = MiniProgramBroadcastRoomHelper::getLiveInfo([
                'start' => $start,
                'limit' => $limit
            ]);

            if (is_error($roomListResult)) {
                break;
            }

            $roomList = array_merge($roomList, $roomListResult['room_info']);

            //如果小于limit就说明没有下一页了
            if (count($roomListResult['room_info']) <= $limit) {
                break;
            }

            $start += $limit;
        }

        foreach ((array)$roomList as $item) {
            $model = self::findOne(['broadcast_room_id' => $item['roomid']]);
            if (empty($model)) {
                continue;
            }

            $model->status = $item['live_status'];
            $model->save();
        }

        return true;
    }

    /**
     * 同步直播间数据
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function syncRoom(int $userId)
    {
        //获取店铺下的所有的直播间
        $start = 0;
        $limit = 100;

        $roomList = [];
        while (true) {

            $roomListResult = MiniProgramBroadcastRoomHelper::getLiveInfo([
                'start' => $start,
                'limit' => $limit
            ]);

            if (is_error($roomListResult)) {
                break;
            }

            $roomList = array_merge($roomList, $roomListResult['room_info']);

            //如果小于limit就说明没有下一页了
            if (count($roomListResult['room_info']) <= $limit) {
                break;
            }

            $start += $limit;
        }

        $roomId = [];
        foreach ((array)$roomList as $item) {
            $model = self::findOne(['broadcast_room_id' => $item['roomid'], 'is_deleted' => BroadcastRoomIsDeletedConstant::BROADCAST_ROOM_IS_DELETED_NO]);
            if (empty($model)) {
                $model = new self();
            }

            $model->setAttributes([
                'broadcast_room_id' => $item['roomid'],
                //标题
                'title' => $item['name'],
                //主播昵称
                'anchor_name' => $item['anchor_name'],
                //开始时间
                'start_time' => date('Y-m-d H:i:s', $item['start_time']),
                //结束时间
                'end_time' => date('Y-m-d H:i:s', $item['end_time']),
                //状态
                'status' => $item['live_status'],
                //背景图
                'cover_img' => $item['cover_img'],
                //分享图
                'share_img' => $item['share_img'],
            ]);

            $model->save();

            $roomId[] = $model->id;

            LogModel::write(
                $userId,
                BroadcastLogConstant::BROADCAST_SYNC_ROOM,
                BroadcastLogConstant::getText(BroadcastLogConstant::BROADCAST_SYNC_ROOM),
                $model->id,
                [
                    'log_data' => $model->attributes,
                    'log_primary' => [
                        '直播间id' => $model->id,
                        '直播间名称' => $model->title,
                        '主播昵称' => $model->anchor_name,
                        '开播时间' => $model->start_time,
                        '结束时间' => $model->end_time,
                        '商品数量' => count($item['goods']) . '个',
                    ]
                ]
            );
        }

        //把不存在的直播间修改为已删除
        if (!empty($roomId)) {
            self::updateAll(['is_deleted' => BroadcastRoomIsDeletedConstant::BROADCAST_ROOM_IS_DELETED_YES], ['not in', 'id', $roomId]);
        }

        return true;
    }

    /**
     * 获取商品直播中直播间
     * @param int $goodsId
     * @return array|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function getGoodsRoom(int $goodsId)
    {
        $roomId = BroadcastRoomGoodsMapModel::find()->where(['goods_id' => $goodsId])->select('room_id')->column();
        if (empty($roomId)) {
            return error('商品未添加到直播间');
        }

        $room = BroadcastRoomModel::find()->where([
            'id' => $roomId,
            'status' => BroadcastRoomStatusConstant::BROADCAST_ROOM_STATUS_UNDERWAY,
        ])->orderBy([
            'start_time' => SORT_DESC
        ])->asArray()->one();

        if (empty($room)) {
            return error('直播间不存在');
        }

//        if ($room['status'] != BroadcastRoomStatusConstant::BROADCAST_ROOM_STATUS_UNDERWAY) {
//            return error('直播状态异常');
//        }

        return $room;
    }
}
