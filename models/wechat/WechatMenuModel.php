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

namespace shopstar\models\wechat;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\components\wechat\helpers\OfficialAccountFansHelper;
use shopstar\helpers\DateTimeHelper;

/**
 * This is the model class for table "{{%app_wechat_menu}}".
 *
 * @property int $id
 * @property string $name 菜单名称
 * @property string $menu_json 菜单数据
 * @property int $status 菜单状态
 * @property string $created_at 创建时间
 */
class WechatMenuModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%app_wechat_menu}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status'], 'integer'],
            [['menu_json'], 'required'],
            [['menu_json'], 'string'],
            [['created_at'], 'safe'],
            [['name'], 'string', 'max' => 168],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '菜单名称',
            'menu_json' => '菜单数据',
            'status' => '菜单状态',
            'created_at' => '创建时间',
        ];
    }

    /**
     * 获取单个
     * @param int $id
     * @param array $options
     * @return array|\yii\db\ActiveRecord|null
     * @author 青岛开店星信息技术有限公司.
     */
    public function getOne(int $id, array $options = [])
    {
        $options = array_merge([
            'andWhere' => [],
            'asArray' => true
        ], $options);

        return WechatMenuModel::find()->where([
            'id' => $id,
        ])->andWhere($options['andWhere'])->asArray($options['asArray'])->one();
    }
}