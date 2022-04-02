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

namespace shopstar\models\form;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\constants\form\FormConstant;
use shopstar\constants\form\FormLogConstant;

/**
 * This is the model class for table "{{%form_log}}".
 *
 * @property int $id auto increment id
 * @property int $form_id 表单ID
 * @property int $member_id 会员ID
 * @property int $order_id 订单ID
 * @property string $content 提交内容
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property int $is_deleted 是否删除
 * @property int $goods_id 商品id
 * @property int $md5 表单md5
 * @property int $source 来源 1: 下单提交 2: 价格面议
 */
class FormLogModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%form_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['form_id', 'member_id', 'order_id', 'is_deleted', 'goods_id', 'source'], 'integer'],
            [['content'], 'required'],
            [['content', 'md5'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'auto increment id',
            'form_id' => '表单ID',
            'member_id' => '会员ID',
            'content' => '提交内容',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'goods_id' => '商品id',
            'is_deleted' => '是否删除',
            'md5' => '表单m5',
            'source' => '来源', //1: 下单提交 2: 议价商品
        ];
    }

    /**
     * 提交
     * @param $memberId
     * @param $params
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function submit($memberId, $params)
    {
        try {

            $where = [
                'form_id' => $params['form_id'],
                'member_id' => $memberId,
                'order_id' => $params['order_id']
            ];
            // 有商品id, 增加商品id的查询条件
            if (isset($params['goods_id']) && !empty($params['goods_id'])) {
                $where['goods_id'] = $params['goods_id'];
            }

            $formLog = self::findOne($where) ?: new self();

            $formLog->setAttributes([
                'form_id' => $params['form_id'],
                'member_id' => $memberId,
                'order_id' => $params['order_id'] ?? 0,
                'content' => $params['content'],
                'goods_id' => $params['goods_id'] ?? 0,
                'md5' => $params['md5'],
                'source' => $params['source'] ?? FormLogConstant::FORM_SOURCE_ORDER,
            ]);

            //会员跟分销商只操作一次
            if (($params['type'] == 2 || $params['type'] == 3) && !isset($formLog['id'])) {
                FormModel::updateAllCounters([
                    'count' => 1
                ], [
                    'id' => $params['form_id']
                ]);
            }

            $formLog->save();
            //其余的可以多次
            if ($params['type'] == 1 || $params['type'] == 4) {
                FormModel::updateAllCounters([
                    'count' => 1
                ], [
                    'id' => $params['form_id']
                ]);
            }

        } catch (\Throwable $e) {

            return error($e->getMessage(), $e->getCode());
        }

        return true;
    }

    /**
     * 获取用户提交记录
     * @param $type
     * @param $memberId
     * @param int $orderId
     * @param null $formId
     * @return array|null
     * @author 青岛开店星信息技术有限公司
     */
    public static function get($type, $memberId, $orderId = 0, $formId = null): ?array
    {
        $array = [
            'log.member_id' => $memberId,
            'log.order_id' => $orderId,
            'form.type' => $type,
            'form.is_deleted' => 0,
        ];
        if (!empty($formId)) {
            $array['log.form_id'] = $formId;
        } else {
            $array['form.status'] = FormConstant::FORM_ACTIVE;
        }
        $formLog = self::find()
            ->alias('log')
            ->leftJoin(FormModel::tableName() . ' form', 'form.id = log.form_id')
            ->where($array)
            ->select('log.id, log.content')
            ->first();

        return $formLog ?? [];
    }
}