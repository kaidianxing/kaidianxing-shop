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

namespace shopstar\models\core\attachment;

use shopstar\bases\model\BaseActiveRecord;

/**
 * 系统附件分组实体类
 * This is the model class for table "{{%core_attachment_group}}".
 *
 * @property int $id
 * @property int $type 附件类型
 * @property int $account_id 账号ID(业务端、管理端: 用户ID，手机端会员ID)
 * @property string $name 分组名称
 * @property int $scene 上传场景 10: 业务端 20: 管理 30: 手机端
 * @property int $total 附件总数
 * @property string $created_at 创建时间
 */
class CoreAttachmentGroupModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%core_attachment_group}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['type', 'account_id', 'scene', 'total'], 'integer'],
            [['created_at'], 'safe'],
            [['name'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => '附件类型',
            'account_id' => '账号ID(业务端、管理端: 用户ID，手机端会员ID)',
            'name' => '分组名称',
            'scene' => '上传场景 10: 业务端 20: 管理 30: 手机端',
            'total' => '附件总数',
            'created_at' => '创建时间',
        ];
    }

}