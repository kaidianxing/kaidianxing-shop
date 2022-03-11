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

namespace shopstar\models\member;

use shopstar\bases\model\BaseActiveRecord;

use shopstar\helpers\DateTimeHelper;

/**
 * This is the model class for table "{{%member_red_package_log}}".
 *
 * @property int $id
 * @property int $member_id 会员id
 * @property string $money 金额
 * @property string $expire_time 过期时间
 * @property string $created_at 创建时间
 * @property string $extend 扩展字段
 * @property int $scene 场景
 * @property int $scene_id 场景值
 * @property int $status 状态 0待领取 1已领取
 * @property string $updated_at 修改时间(领取时间)
 */
class MemberRedPackageModel extends BaseActiveRecord
{
    /**
     * 场景 - 拼团返利
     */
    public const SCENE_GROUPS_REBATE = 10;

    /**
     * 场景 - 购物奖励
     */
    public const SCENE_SHOPPING_REWARD = 11;

    /**
     * 场景 - 消费奖励
     */
    public const SCENE_CONSUME_REWARD = 12;

    /**
     * 场景 - 好评奖励
     */
    public const SCENE_GOODS_COMMENT = 13;

    /**
     * 场景 - 分销直推奖
     */
    public const SCENE_PERFORMANCE_AWARD = 14;

    /**
     * @var string[]
     * @author 青岛开店星信息技术有限公司.
     */
    public static $sceneMap = [
        self::SCENE_GROUPS_REBATE => '拼团返利',
        self::SCENE_SHOPPING_REWARD => '购物奖励',
        self::SCENE_CONSUME_REWARD => '消费奖励',
        self::SCENE_GOODS_COMMENT => '好评奖励',
        self::SCENE_PERFORMANCE_AWARD => '分销商达标奖',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%member_red_package_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['member_id', 'scene', 'scene_id', 'status'], 'integer'],
            [['money'], 'number'],
            [['expire_time', 'created_at', 'updated_at'], 'safe'],
            [['extend'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member_id' => '会员id',
            'money' => '金额',
            'expire_time' => '过期时间',
            'created_at' => '创建时间',
            'extend' => '扩展字段',
            'scene' => '场景',
            'scene_id' => '场景值',
            'status' => '状态 0待领取 1已领取',
            'updated_at' => '修改时间(领取时间)',
        ];
    }

    /**
     * 创建日志
     * @param array $data
     * @return array|int
     * @author 青岛开店星信息技术有限公司.
     */
    public static function createLog(array $data)
    {
        //判断
        if (empty($data['scene'])) return error('scene场景不能为空');

        //判断
        if (empty($data['money'])) return error('money金额不能为空');

        //判断
        if (empty($data['money'])) return error('money真实金额不能为空');

        $data['created_at'] = DateTimeHelper::now();

        try {
            $model = new self();
            $model->setAttributes($data);
            if (!$model->save()) {
                throw new \Exception($model->getErrorMessage());
            }
        } catch (\Exception $exception) {
            return error($exception->getMessage());
        }

        return $model->id;
    }
}