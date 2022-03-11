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
 * This is the model class for table "{{%core_rule}}".
 *
 * @property int $id auto increment id
 * @property string $type 类型
 * @property int $rule_id 海报ID
 * @property string $keyword 关键字
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class CoreRuleModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%core_rule}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [[ 'rule_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['keyword', 'type'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'auto increment id',
            'type'        => '类型',
            'rule_id'     => '类型对象ID',
            'keyword'     => '关键字',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    /**
     * 新增或更新rule
     * @param int $ruleId
     * @param string $keyword
     * @param string $type
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function createOrUpdateRule(int $ruleId, string $keyword, string $type)
    {
        $rule = self::find()->where(['rule_id' => $ruleId])->one();

        if (!$rule->id) {
            $rule = new self();
        }

        $attr = [
            'type'      => $type,
            'rule_id'   => $ruleId,
            'keyword'   => $keyword
        ];

        $rule->setAttributes($attr);

        $rule->save();

        return true;
    }
}