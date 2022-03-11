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

use shopstar\constants\form\FormTypeConstant;
use shopstar\bases\model\BaseActiveRecord;

/**
 * This is the model class for table "{{%app_form}}".
 *
 * @property int $id auto increment id
 * @property string $name 名称
 * @property string $content 内容
 * @property int $type 类型 0自定义 1订单统一下单表单 2分销商申请资料表单 3会员资料表单
 * @property int $status 状态：0禁用1启用
 * @property int $is_deleted 是否删除：0否1是
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class FormModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%app_form}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'status', 'is_deleted', 'count'], 'integer'],
            [['content'], 'required'],
            [['content'], 'string'],
            [['created_at', 'updated_at', 'last_update_time'], 'safe'],
            [['name'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'auto increment id',
            'name' => '名称',
            'content' => '内容',
            'type' => '类型 0自定义 1订单统一下单表单 2分销商申请资料表单 3会员资料表单',
            'status' => '状态：0禁用1启用',
            'is_deleted' => '是否删除：0否1是',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    /**
     * 添加表单
     * @param $params
     * @return array|FormModel
     * @author 青岛开店星信息技术有限公司
     */
    public static function addResult($params)
    {
        $transaction = \Yii::$app->db->beginTransaction();

        try {

            // 添加表单
            $form = new self();

            $attr = [
                'name' => $params['name'],
                'content' => $params['content'],
                'type' => $params['type'] ?? 0,
                'status' => $params['status'] ?? 0
            ];

            $form->setAttributes($attr);

            $form->save();

            // 更新状态
            $params['status'] && self::updateStatus($form->id, $params['type']);

            $transaction->commit();
        } catch (\Throwable $e) {

            $transaction->rollBack();

            return error($e->getMessage(), $e->getCode());
        }

        return $form;
    }

    /**
     * 修改表单状态 禁用|启用
     * @param $formId
     * @param $status
     * @return array|null|static
     * @author 青岛开店星信息技术有限公司
     */
    public static function changeStatus($formId, $status)
    {
        // 校验是否存在
        $form = FormModel::findOne(['id' => $formId]);
        if (empty($form)) {
            return error('表单不存在');
        }


        $trans = \Yii::$app->db->beginTransaction();

        try {

            if ($form['type'] != 4) {
                // 更新状态
                self::updateAll(['status' => $status], ['id' => $formId]);

                // 只允许一个启用
                $status && self::updateStatus($formId, $form->type);
            } else {

                //商品表单可以随便启用
                $form->setAttributes([
                    'id' => $form['id'],
                    'status' => $status
                ]);

                $form->save();
            }

            $trans->commit();

        } catch (\Throwable $e) {

            $trans->rollBack();

            return error($e->getMessage(), $e->getCode());
        }

        return $form;
    }

    /**
     * 删除
     * @param $formId
     * @return array|null|static
     * @author 青岛开店星信息技术有限公司
     */
    public static function deleteResult($formId)
    {
        // 校验是否存在
        $form = FormModel::findOne(['id' => $formId]);
        if (empty($form)) {
            return error('表单不存在');
        }

        try {

            self::updateAll(['is_deleted' => 1], ['id' => $formId]);

        } catch (\Throwable $e) {

            return error($e->getMessage(), $e->getCode());
        }

        return $form;
    }

    /**
     * 获取用户表单信息或提交记录
     * @param $type
     * @param $memberId
     * @param bool $isFormContent 是否返回模板信息
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function get($type, $memberId, $isFormContent = false, $orderId = 0)
    {
        if ($isFormContent || $type == FormTypeConstant::FORM_TYPE_MEMBER || $type == FormTypeConstant::FORM_TYPE_COMMISSION) {

            $result = self::find()
                ->where([
                    'form.type' => $type,
                    'form.status' => 1,
                    'form.is_deleted' => 0,
                ])
                ->alias('form')
                ->leftJoin(FormLogModel::tableName() . ' log', 'log.form_id = form.id and member_id = ' . $memberId . ' and order_id = ' . $orderId)
                ->select('form.type,form.id, form.content as form_content, log.content as log_content,form.updated_at,log.created_at as log_create,log.updated_at as log_update')
                ->first();
        } else {

            $result = self::find()
                ->where([
                    'form.type' => $type,
                    'form.status' => 1,
                    'form.is_deleted' => 0,
                    'log.order_id' => $orderId,
                ])
                ->alias('form')
                ->leftJoin(FormLogModel::tableName() . ' log', 'log.form_id = form.id and member_id = ' . $memberId)
                ->select('form.type,form.id, form.content as form_content, log.content as log_content,form.updated_at,log.created_at as log_create,log.updated_at as log_update')
                ->first();
        }


        // 用户填写资料使用用户的
        $final = null;
        if (!empty($result)) {

            //如果是订单表单，下单后更改不了
            if ($result['type'] == 1) {
                $final = [
                    'id' => $result['id'],
                    'content' => $result['log_content'] && !$isFormContent ? $result['log_content'] : $result['form_content']
                ];
            } else {
                //如果是会员跟分销商信息，判断表单修改时间跟提交表单的时间
                $final = [
                    'id' => $result['id'],
                    'content' => $result['log_content'] && !$isFormContent && (($result['log_create'] > $result['updated_at']) || $result['log_update'] > $result['updated_at']) ? $result['log_content'] : $result['form_content']
                ];
            }

        }
        return $final;
    }

    /**
     * 更新相同页面其他页面的启用状态
     * @param int $id
     * @param int $type
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    private static function updateStatus(int $id, int $type)
    {
        self::updateAll(['status' => 0], [
            'and',
            [
                'type' => $type,
                'status' => 1,
            ],
            ['<>', 'id', $id],
        ]);

        return true;
    }

    /**
     * 验证名称重复
     * @param int $type
     * @param string $name
     * @param int $id
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkName(int $type, string $name, int $id = 0)
    {
        $exist = self::findOne(['name' => $name, 'type' => $type, 'is_deleted' => 0]);


        if (!$exist) {
            return true;
        }

        if (!empty($exist) && $exist->id == $id) {
            return true;
        }

        return false;
    }

    /**
     * 修改统计条数
     * @param int $formId
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function updateData(int $formId)
    {
        $formData = self::findOne(['id' => $formId]);

        if (!$formData) {
            return error('表单数据未找到');
        }

        $countList = FormLogModel::find()
            ->where([
                'form_id' => $formId,
            ])
            ->count();

        if ($countList) {

            $formData->setAttributes([
                'count' => $countList
            ]);

            if (!$formData->save()) {
                return error('数据更新失败');
            }
        }
        return true;
    }


    /**
     * 查询表单状态
     * @param int $formId
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function getStatus(int $formId)
    {
        $formStatus = self::find()
            ->where([
                'id' => $formId,
                'status' => 1
            ])
            ->first();

        if ($formStatus) {
            return true;
        } else {
            return false;
        }

    }

}