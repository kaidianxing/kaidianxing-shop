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

namespace shopstar\models\wxTransactionComponent;

use shopstar\bases\model\BaseActiveRecord;

/**
 * This is the model class for table "{{%wx_audit_category}}".
 *
 * @property int $id
 * @property string $audit_id 审核单号
 * @property int $category_id 分类id
 * @property int $status 状态 0审核中 1审核成功 9审核失败
 * @property string $create_time 创建时间
 */
class WxAuditCategoryModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%wx_audit_category}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['category_id', 'status'], 'integer'],
            [['create_time'], 'safe'],
            [['audit_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'audit_id' => '审核单号',
            'category_id' => '分类id',
            'status' => '状态 0审核中 1审核成功 9审核失败',
            'create_time' => '创建时间',
        ];
    }
}
