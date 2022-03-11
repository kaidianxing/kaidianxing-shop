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

namespace shopstar\models\sysset;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\ValueHelper;

/**
 * This is the model class for table "{{%refund_address}}".
 *
 * @property int $id
 * @property string $title 标题
 * @property string $name 联系人姓名
 * @property string $mobile 联系人电话
 * @property string $tel 固定电话
 * @property string $province 省
 * @property string $city 市
 * @property string $area 区
 * @property string $address 详细地址
 * @property int $is_default 是否默认 0:否;1:是;
 * @property string $zip_code 邮编
 * @property string $created_at 创建时间
 * @property string $updated_at 修改时间
 */
class RefundAddressModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%refund_address}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_default'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['title', 'name', 'province', 'city', 'area'], 'string', 'max' => 30],
            [['mobile', 'tel', 'zip_code'], 'string', 'max' => 20],
            [['address'], 'string', 'max' => 255],
        ];
    }

    public function logAttributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '标题',
            'name' => '联系人',
            'mobile' => '手机',
            'tel' => '电话',
            'province' => '省',
            'city' => '市',
            'area' => '区',
            'address' => '详细地址',
            'is_default' => '是否默认',
            'zip_code' => '邮编',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '标题',
            'name' => '联系人姓名',
            'mobile' => '联系人电话',
            'tel' => '固定电话',
            'province' => '省',
            'city' => '市',
            'area' => '区',
            'address' => '详细地址',
            'is_default' => '是否默认 0:否;1:是;',
            'zip_code' => '邮编',
            'created_at' => '创建时间',
            'updated_at' => '修改时间',
        ];
    }

    /**
     * 保存
     * @param int $id
     * @return bool|array
     * @author 青岛开店星信息技术有限公司
     */
    public static function saveRefundAddress(int $id = 0)
    {
        $post = RequestHelper::post();

        // 验证
        if (!ValueHelper::isMobile($post['mobile'])) {
            return error('手机号格式不正确');
        }

        if (empty($post['province']) || empty($post['city']) || empty($post['area']) || empty($post['address'])) {
            return error('请填写地址');
        }

        // 保存
        if (empty($id)) {
            $refundAddress = new self();
        } else {
            $refundAddress = self::findOne(['id' => $id]);
            if (empty($refundAddress)) {
                return error('退货地址不存在');
            }
        }
        if ($post['is_default']) {
            self::updateAll(['is_default' => 0]);
        }

        $refundAddress->setAttributes($post);

        if ($refundAddress->save() === false) {
            return error($refundAddress->getErrorMessage());
        }
        return true;
    }

}