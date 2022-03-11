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

namespace shopstar\models\statistics;

use shopstar\bases\model\BaseActiveRecord;


/**
 * This is the model class for table "{{%statistics_page_view}}".
 *
 * @property int $id id
 * @property int $member_id 用户id
 * @property string $url 访问url
 * @property string $page 访问的页面
 * @property string $ip ip
 * @property int $client_type 来源  微信  小程序  h5等
 * @property string $created_at 时间
 * @property string $params 参数
 */
class StatisticsPageViewModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%statistics_page_view}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['member_id', 'client_type'], 'integer'],
            [['created_at'], 'safe'],
            [['params'], 'string'],
            [['url', 'page'], 'string', 'max' => 255],
            [['ip'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'member_id' => '用户id',
            'url' => '访问url',
            'page' => '访问的页面',
            'ip' => 'ip',
            'client_type' => '来源  微信  小程序  h5等',
            'created_at' => '时间',
            'params' => '参数',
        ];
    }
}