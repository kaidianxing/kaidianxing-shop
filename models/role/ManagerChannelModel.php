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

namespace shopstar\models\role;

use shopstar\bases\model\BaseActiveRecord;

use shopstar\helpers\DateTimeHelper;

/**
 * This is the model class for table "{{%manager_channel}}".
 *
 * @property int $id
 * @property int $user_id 用户id
 * @property string $open_id openid
 * @property int $channel 渠道 0:商户端小程序
 * @property string $created_at 时间戳
 * @property string $avatar 头像
 * @property string $nickname 昵称
 */
class ManagerChannelModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%admin_channel}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'channel'], 'integer'],
            [['created_at'], 'safe'],
            [['open_id', 'nickname'], 'string', 'max' => 125],
            [['avatar'], 'string', 'max' => 168],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '用户id',
            'open_id' => 'openid',
            'channel' => '渠道 0:商户端小程序',
            'created_at' => '时间戳',
            'avatar' => '头像',
            'nickname' => '昵称',
        ];
    }

    /**
     * 检查关系
     * @param int $clientType
     * @param array $data
     * @return array
     * @author 青岛开店星信息技术有限公司.
     */
    public static function checkRelation(int $clientType, array $data)
    {
        if (empty($data['open_id'])) return error('缺少open_id');

        //关系条件
        $relationWhere = [
            'open_id' => $data['open_id'],
            'channel' => $clientType,
        ];

        //查找关系
        $relation = self::find()->where($relationWhere)->one();

        //如果没有关系，并且userid为空 则直接返回
        if (empty($relation) && empty($data['user_id'])) {
            return error("用户不存在");
        }

        //如果账号已经绑定则报错
        if (!empty($data['user_id']) && !empty($relation) && $relation->user_id != $data['user_id']) {
            return error('当前小程序已绑定');
        }

        //如果关系不存在但是存在userid 创建关系
        if (empty($relation)) {

            $relation = new self();
            $relation->setAttributes(array_merge($relationWhere, [
                'created_at' => DateTimeHelper::now(),
                'user_id' => $data['user_id'],
                'nickname' => $data['nickname'],
                'avatar' => $data['avatar']
            ]));

            //如果创建失败
            if (!$relation->save()) {
                return error($relation->getErrorMessage());
            }
        }

        //返回关系
        return $relation->toArray();
    }

    /**
     * 根据渠道获取openid
     * @param int $userId
     * @param int $channel
     * @return string
     * @author 青岛开店星信息技术有限公司.
     */
    public static function getOpenIdByChannel(int $userId, int $channel)
    {
        $channelInfo = self::find()->where(['user_id' => $userId, 'channel' => $channel])->select('open_id')->first();
        return $channelInfo['open_id'] ?? '';
    }


}