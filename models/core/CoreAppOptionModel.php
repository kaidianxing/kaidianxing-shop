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

namespace shopstar\models\core;

use shopstar\bases\model\BaseActiveRecord;

/**
 * 系统应用规格实体类
 * This is the model class for table "{{%core_app_option}}".
 *
 * @property int $id
 * @property int $app_id 应用ID
 * @property string $name 名称
 * @property int $duration 时长
 * @property string $price 价格
 * @property string $is_corner_mark 是否开启角标
 * @property string $corner_mark_info 角标信息
 * @property string $original_price 原价
 */
class CoreAppOptionModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%core_app_option}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['app_id', 'duration', 'is_corner_mark'], 'integer'],
            [['price'], 'required'],
            [['price', 'original_price'], 'number'],
            [['name', 'corner_mark_info'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'app_id' => '应用ID',
            'name' => '名称',
            'duration' => '时长',
            'price' => '价格',
            'is_corner_mark' => '是否开启角标',
            'corner_mark_info' => '角标信息',
            'original_price' => '原价',
        ];
    }

    /**
     * 获取应用
     * @param int $appId
     * @return array|\yii\db\ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    public static function getPlan(int $appId)
    {
        return self::find()->where(['app_id' => $appId])->asArray()->all();
    }

    /**
     * 保存应用规格
     * @param int $appId
     * @param $data
     * @param string $unit
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function saveAppOption(int $appId, array $data, string $unit = '条')
    {
        $tr = \Yii::$app->db->beginTransaction();
        try {
            $optionId = [];
            foreach ((array)$data as $row) {
                if (!isset($row['duration'])) {
                    throw new \Exception('参数错误 缺少duration');
                } elseif (!isset($row['price'])) {
                    throw new \Exception('参数错误 缺少price');
                }

                if (!empty($row['id'])) {
                    $model = CoreAppOptionModel::findOne(['id' => $row['id']]);
                    if (empty($model)) {
                        throw new \Exception('缺少应用规格');
                    }
                } else {
                    $model = new CoreAppOptionModel();
                }

                $model->setAttributes([
                    'duration' => (int)$row['duration'],
                    'price' => round2($row['price']),
                    'app_id' => $appId,
                    'name' => (int)$row['duration'] . $unit
                ]);

                if (!$model->save()) {
                    throw new \Exception('保存失败');
                }

                $optionId[] = $model->id;
            }

            //删除其他的规格
            self::deleteAll([
                'and',
                ['app_id' => $appId],
                ['not in', 'id', $optionId]
            ]);

            $tr->commit();
        } catch (\Throwable $throwable) {
            $tr->rollBack();
            return error($throwable->getMessage());
        }

        return true;
    }

}
