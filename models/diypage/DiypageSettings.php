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

namespace shopstar\models\diypage;


use shopstar\bases\model\BaseSettings;


/**
 * 应用-店铺装修-设置实体类
 * This is the model class for table "{{%diypage_settings}}".
 *
 * @property string $key 设置名
 * @property string $value 设置值
 */
class DiypageSettings extends BaseSettings
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%diypage_settings}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['key'], 'required'],
            [['value'], 'string'],
            [['key'], 'string', 'max' => 50],
            [['key'], 'unique', 'targetAttribute' => ['key']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'key' => '设置名',
            'value' => '设置值',
        ];
    }

    /**
     * 读取设置
     * @param string $key 设置Key
     * @param string $defaultValue 默认值
     * @return array|mixed|string
     * @author likexin
     */
    public static function get($key = '', $defaultValue = '')
    {
        // 设置缓存前缀
        return parent::get($key, $defaultValue, []);
    }

    /**
     * 保存设置
     * @param string $key 设置Key
     * @param mixed $value 设置值
     * @return void
     * @throws \yii\db\Exception
     * @author likexin
     */
    public static function set($key = '', $value = '')
    {
        // 设置缓存前缀
        return parent::set($key, $value, []);
    }

    /**
     * 默认设置
     * @return array
     * @author likexin
     */
    public static function defaultSettings()
    {
        return [
            /**
             * @var array 浮动按钮
             */
            'float_button' => [

            ],

            /**
             * @var array 返回顶部
             */
            'go_top' => [

            ],

            /**
             * @var array 关注条
             */
            'follow_bar' => [

            ],

            /**
             * @var array 下单提醒
             */
            'order_notice' => [

            ],
        ];
    }

}