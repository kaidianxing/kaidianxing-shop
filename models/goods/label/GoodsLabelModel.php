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

namespace shopstar\models\goods\label;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\exceptions\goods\GoodsException;
use shopstar\helpers\DateTimeHelper;

/**
 * This is the model class for table "{{%goods_label}}".
 *
 * @property int $id
 * @property string $name 商品标签名称
 * @property int $group_id 分组id
 * @property string $desc 商品标签概述
 * @property string $created_at 创建时间
 * @property string $content 标签内容
 */
class GoodsLabelModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_label}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['group_id'], 'integer'],
            [['created_at'], 'safe'],
            [['name'], 'string', 'max' => 60],
            [['desc'], 'string', 'max' => 191],
            [['content'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '商品标签名称',
            'group_id' => '分组id',
            'desc' => '商品标签概述',
            'created_at' => '创建时间',
            'content' => '标签内容',
        ];
    }

    /**
     * @param $data
     * @return int
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public static function saveLabel($data)
    {

        if (empty($data['id'])) {
            $model = new self();
            $model->created_at = DateTimeHelper::now();
        } else {
            $model = self::findOne($data['id']);
            if (empty($model)) {

                throw new GoodsException(GoodsException::LABEL_SAVE_ERROR, $model->getErrorMessage());
            }
        }

        $model->setAttributes($data);

        if (!$model->save()) {
            throw new GoodsException(GoodsException::LABEL_SAVE_ERROR, $model->getErrorMessage());
        }
        return $model->id;
    }

}
