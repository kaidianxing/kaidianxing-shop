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
 * This is the model class for table "{{%wx_audit_category_images}}".
 *
 * @property int $id
 * @property int $wx_id 关联的分类审核表id
 * @property int $audit_category_id 关联的分类审核表id
 * @property string $path 路径
 * @property int $type 状态 10营业执照 20资质材料
 * @property string $create_time 创建时间
 */
class WxAuditCategoryImagesModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%wx_audit_category_images}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['audit_category_id', 'type', 'wx_id'], 'integer'],
            [['create_time'], 'safe'],
            [['path'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'wx_id' => '关联自定义交易表id',
            'audit_category_id' => '关联的分类审核表id',
            'path' => '路径',
            'type' => '状态 10营业执照 20资质材料',
            'create_time' => '创建时间',
        ];
    }
}
