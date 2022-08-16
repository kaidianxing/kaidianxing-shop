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
 * 系统附件实体类
 * This is the model class for table "{{%core_attachment}}".
 *
 * @property int $id
 * @property int $group_id 分组id
 * @property int $account_id 账号ID(业务端、管理端: 用户ID，手机端会员ID)
 * @property int $type 附件类型
 * @property int $scene 上传场景 10: 业务端 20: 管理 30: 手机端
 * @property string $name 文件名称
 * @property string $ext 扩展名
 * @property string $path 相对附件目录路径
 * @property int $size 文件大小(kb)
 * @property string $md5 MD5值
 * @property int $year 上传年份
 * @property int $month 上传月份
 * @property string $extend 扩展字段(音视频信息等存入此字段)
 * @property string $created_at 上传时间
 */
class CoreAttachmentModel extends BaseActiveRecord
{
    /**
     * 托管存储
     */
    public const HOSTING = 10;

    /**
     * 映射
     * @var array
     */
    public static array $storageModelMap = [
        self::HOSTING => '托管存储',
    ];

    // TODO likexin 可以删除

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%core_attachment}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['group_id', 'account_id', 'type', 'scene', 'size', 'year', 'month'], 'integer'],
            [['extend'], 'required'],
            [['extend'], 'string'],
            [['created_at'], 'safe'],
            [['name'], 'string', 'max' => 128],
            [['ext'], 'string', 'max' => 10],
            [['path'], 'string', 'max' => 100],
            [['md5'], 'string', 'max' => 32],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'group_id' => '分组id',
            'account_id' => '账号ID(业务端、管理端: 用户ID，手机端会员ID)',
            'type' => '附件类型',
            'scene' => '上传场景 10: 业务端 20: 管理 30: 手机端',
            'name' => '文件名称',
            'ext' => '扩展名',
            'path' => '相对附件目录路径',
            'size' => '文件大小(kb)',
            'md5' => 'MD5值',
            'year' => '上传年份',
            'month' => '上传月份',
            'extend' => '扩展字段(音视频信息等存入此字段)',
            'created_at' => '上传时间',
        ];
    }
}
