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

namespace shopstar\models\finance;

use shopstar\bases\model\BaseActiveRecord;

use shopstar\helpers\DateTimeHelper;

/**
 * This is the model class for table "{{%finance_remit}}".
 *
 * @property int $id
 * @property int $member_id 会员id
 * @property string $trans_no 交易单号
 * @property int $scene 场景
 * @property int $scene_id 场景id
 * @property string $created_at 创建时间
 * @property string $money 金额
 * @property int $remit_type 汇款类型
 * @property string $trans_id 商户单号
 * @property string $real_money 实际金额
 * @property int $status 订单状态 0生成 10成功 11手动打款 20失败
 * @property string $remark 备注
 * @property string $updated_at 更新时间
 */
class FinanceRemitModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%finance_remit}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['member_id', 'scene', 'scene_id', 'remit_type', 'status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['money', 'real_money'], 'number'],
            [['trans_no', 'trans_id'], 'string', 'max' => 33],
            [['remark'], 'string', 'max' => 255],
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
            'trans_no' => '交易单号',
            'scene' => '场景',
            'scene_id' => '场景id',
            'created_at' => '创建时间',
            'money' => '金额',
            'remit_type' => '汇款类型',
            'trans_id' => '商户单号',
            'real_money' => '实际金额',
            'status' => '订单状态 0生成 10成功 11手动打款 20失败',
            'remark' => '备注',
            'updated_at' => '更新时间',
        ];
    }

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
     * 创建日志
     * @param array $data
     * @return array|bool
     * @author 青岛开店星信息技术有限公司.
     */
    public static function createLog(array $data)
    {
        //判断
        if (empty($data['trans_no'])) return error('trans_no订单号不能为空');

        //判断
        if (empty($data['scene'])) return error('scene场景不能为空');

        //判断
        if (empty($data['money'])) return error('money金额不能为空');

        //判断
//        if (empty($data['remit_type'])) return error('remit_type打款方式不能为空');

        //判断
        if (empty($data['real_money'])) return error('real_money真实金额不能为空');

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

    /**
     * 修改状态
     * @param $data
     * @param $condition
     * @return int
     * @author 青岛开店星信息技术有限公司.
     */
    public static function updateLog($data, $condition)
    {

        //TODO 青岛开店星信息技术有限公司 需要判断是否重复判断，并且判断是否重复发送
        $result = self::updateAll($data, $condition);

        return $result;
    }
}