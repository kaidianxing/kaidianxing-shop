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

namespace shopstar\models\member;

use shopstar\components\amap\AmapClient;

use shopstar\helpers\RequestHelper;
use shopstar\helpers\ValueHelper;
use shopstar\models\core\CoreAddressModel;

/**
 * This is the model class for table "{{%member_address}}".
 *
 * @property int $id
 * @property int $member_id 用户id
 * @property string $name 用户姓名
 * @property string $mobile 手机号
 * @property string $province 省
 * @property string $city 市
 * @property string $area 区
 * @property string $address 详细地址
 * @property int $is_default 是否默认
 * @property string $zip_code 唯一标识
 * @property int $is_delete 是否删除
 * @property string $lng 经度
 * @property string $lat 维度
 * @property string $address_code 地区编码
 */
class MemberAddressModel extends \shopstar\bases\model\BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%member_address}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['member_id'], 'required'],
            [['member_id', 'is_default', 'is_delete'], 'integer'],
            [['name', 'mobile', 'lng', 'lat'], 'string', 'max' => 20],
            [['province', 'city', 'area'], 'string', 'max' => 30],
            [['address'], 'string', 'max' => 255],
            [['address_code'], 'string', 'max' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member_id' => '用户id',
            'name' => '用户姓名',
            'mobile' => '手机号',
            'province' => '省',
            'city' => '市',
            'area' => '区',
            'address' => '详细地址',
            'is_default' => '是否默认',
            'zip_code' => '邮编',
            'is_delete' => '是否删除',
            'lng' => '经度',
            'lat' => '维度',
            'address_code' => '地区编码',
        ];
    }

    /**
     * 保存地址
     * @param int $memberId
     * @param int $id
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function saveAddress(int $memberId, int $id = 0): array
    {
        $post = RequestHelper::post();

        $name = $post['name'];
        $mobile = $post['mobile'];
        $province = $post['province'];
        $city = $post['city'];
        $area = $post['area'];
        $address = $post['address'];
        $isDefault = (int)$post['is_default'];
        $addressCode = $post['address_code'];
        // 适配国外地址 暂定code为999999
        if ((empty($province) || empty($city) || empty($area) || empty($address)) && $addressCode != '990101') {
            return error('地址填写错误');
        }
        // 验证
        if (!ValueHelper::isMobile($mobile) && !ValueHelper::isTelephone($mobile, false)) {
            return error('请输入正确的手机号');
        }

        // 判断传入的额accessCode是否在地址库
        if (!empty($addressCode) && $addressCode != '990101') {
            $addressExist = CoreAddressModel::find()
                ->where([
                    'code_id' => $addressCode,
                ])
                ->exists();
            if (!$addressExist) {
                return error('当前地址库不完整 ' . $addressCode);
            }
        }

        // 微信导入地址自动补充addressCode
        if (!empty($area) && empty($addressCode)) {
            $coreAddress = CoreAddressModel::findOne(['name' => $area]);
            $addressCode = $coreAddress->code_id;
        }
        // 获取坐标
        $addressDetail = $province . $city . $area . $address;
        $location = AmapClient::getLocationPoint($addressDetail);
        if (is_error($location)) {
            $location = [
                'lng' => '',
                'lat' => '',
            ];
        }
        if (empty($id)) {
            $memberAddress = new self();
            $memberAddress->member_id = $memberId;
        } else {
            $memberAddress = self::find()
                ->where(['id' => $id, 'member_id' => $memberId])
                ->one();
            if ($memberAddress === null) {
                return error('地址不存在');
            }
        }
        // 如果是默认  把默认改为非默认
        if ($isDefault) {
            self::updateAll(['is_default' => 0], ['member_id' => $memberId, 'is_default' => 1]);
        }
        $memberAddress->name = $name;
        $memberAddress->mobile = $mobile;
        $memberAddress->province = $province;
        $memberAddress->city = $city;
        $memberAddress->area = $area;
        $memberAddress->address = $address;
        $memberAddress->is_default = $isDefault;
        $memberAddress->address_code = empty($addressCode) ? '' : strval($addressCode);
        $memberAddress->lng = $location['lng'] ?? '';
        $memberAddress->lat = $location['lat'] ?? '';

        if ($memberAddress->save() === false) {
            return error('地址保存失败');
        }

        return [
            'id' => $memberAddress->id,
        ];
    }

    /**
     * 删除地址
     * @param int $id
     * @param int $memberId
     * @return int|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function deleteAddress(int $id, int $memberId)
    {
        $address = self::findOne(['id' => $id, 'member_id' => $memberId]);
        if ($address->is_delete == 1) {
            return error('地址已删除');
        }

        $address->is_delete = 1;

        // 如果是默认地址
        if ($address->is_default == 1) {
            $address->is_default = 0;
            // 把最近添加的地址设为默认
            $lastAddress = self::find()
                ->where(['member_id' => $memberId, 'is_delete' => 0])
                ->orderBy(['id' => SORT_DESC])
                ->one();
            if (!empty($lastAddress)) {
                $lastAddress->is_default = 1;
                if ($lastAddress->save() === false) {
                    return error('地址删除失败');
                }
            }
        }

        if ($address->save() === false) {
            return error('地址删除失败');
        }

        return true;
    }

}
