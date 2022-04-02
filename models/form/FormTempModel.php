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
use shopstar\exceptions\form\FormException;

/**
 * 系统表单模型类
 * Class FormTempModel
 * @package shopstar\models\form
 */
class FormTempModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%form_temp}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['member_id', 'form_id', 'goods_id', 'cart_id', 'source'], 'integer'],
            [['content'], 'required'],
            [['content', 'md5'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Id',
            'content' => '提交数据',
            'member_id' => '会员ID',
            'form_id' => '表单ID',
            'md5' => '表单唯一值',
            'source' => '来源',//(目前只用于商品表单)来源 1: 下单商品 2: 价格面议
        ];
    }

    /**
     * 订单创建处理表单
     * @param int $orderId 订单ID
     * @param int $memberId 会员ID
     * @param array $orderGoods 订单商品详情
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function orderCreate(int $orderId, int $memberId, array $orderGoods, array $cartIds = [])
    {
        // 取出订单商品的ID
        $goodsStatus = [];
        foreach ($orderGoods as $k => $v) {
            if (!empty($v['form_id']) && $v['form_status'] == 1) {
                $goodsStatus[] = $v;
            }
        }

        //取出新的有用的表单
        $goodsIds = array_column($goodsStatus, 'goods_id');

        // 查询临时数据是否有商品存入
        $formData = self::find()
            ->where([
                'goods_id' => $goodsIds,
                'member_id' => $memberId,
                'cart_id' => !empty($cartIds) ? $cartIds : 0,
            ])
            ->get();

        //查询表单开启的数据
        $formIds = FormModel::find()
            ->where([
                'id' => array_column($formData, 'form_id'),
                'status' => 1,
                'is_deleted' => 0,
            ])
            ->select('id')
            ->column();

        if (empty($formIds)) {
            return;
        }

        // 遍历所有的formData，判断如果不在formIds中说明，删除或者未启用
        foreach ($formData as $index => $row) {
            if (!in_array($row['form_id'], $formIds)) {
                unset($formData[$index]);
                continue;
            }
        }
        // 重新更新下索引值
        $formData = array_values($formData);

        // 如果有数据 拼接格式
        if (!empty($formData)) {

            $trans = \Yii::$app->db->beginTransaction();

            try {
                //订单编号赋予
                foreach ($formData as &$v) {
                    $v['order_id'] = $orderId;
                    unset($v['id']);
                    unset($v['cart_id']);

                    //操作记录+1
                    FormModel::updateAllCounters([
                        'count' => 1
                    ], [
                        'id' => $v['form_id']
                    ]);
                }
                unset($v);

                // 批量插入
                FormLogModel::batchInsert(array_keys($formData[0]), $formData);

                //插入后删除
                self::deleteAll(['member_id' => $memberId, 'goods_id' => $goodsIds, 'cart_id' => array_column($formData, 'cart_id') ? array_column($formData, 'cart_id') : 0]);


                $trans->commit();

            } catch (\Throwable $e) {

                $trans->rollBack();

                throw new FormException(FormException::FORM_TEMP_TO_LOG_EMPTY);

            }
        }
        return true;

    }

    /**
     * 插入临时表
     * @param int $memberId
     * @param array $params
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function submitTempData(int $memberId, array $params)
    {


        $existsData = self::findOne(['member_id' => $memberId, 'goods_id' => $params['goods_id'], 'cart_id' => $params['cart_id']]);

        try {

            $formLog = new self();

            if (empty($existsData) || $existsData['cart_id'] != $params['cart_id']) {

                $formLog->setAttributes([
                    'form_id' => $params['form_id'],
                    'member_id' => $memberId,
                    'content' => $params['content'],
                    'goods_id' => $params['goods_id'] ?? 0,
                    'cart_id' => $params['cart_id'],
                    'md5' => $params['md5'] ?? '',
                    'source' => $params['source'] ?? 0,
                ]);

                $formLog->save();

            } else {

                $existsData->setAttributes([
                    'id' => $existsData['id'],
                    'form_id' => $params['form_id'],
                    'member_id' => $memberId,
                    'content' => $params['content'],
                    'goods_id' => $params['goods_id'] ?? 0,
                    'md5' => $params['md5'],
                    'source' => $params['source'] ?? 0,
                ]);

                $existsData->save();
            }

        } catch (\Throwable $e) {

            return error($e->getMessage(), $e->getCode());
        }

        return true;
    }

}