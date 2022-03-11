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

namespace shopstar\models\core;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\models\form\FormLogModel;

/**
 * This is the model class for table "{{%core_address}}".
 *
 * @property int $id 编号
 * @property int $code_id 地址id
 * @property int $parent_id 父级ID
 * @property string $name 名称
 * @property int $level 级别
 * @property string $location 经纬度
 * @property string $letter
 * @property string|null $sort 前缀大写
 */
class CoreAddressModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%core_address}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code_id', 'parent_id', 'level'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['location', 'letter'], 'string', 'max' => 128],
            [['sort'], 'string', 'max' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '编号',
            'code_id' => '地址id',
            'parent_id' => '父级ID',
            'name' => '名称',
            'level' => '级别',
            'location' => '经纬度',
            'letter' => 'Letter',
            'sort' => '前缀大写',
        ];
    }

    public static function getAll()
    {
        $child = self::find()
            ->where(['parent_id' => 0, 'level' => 1])
            ->with(['child' => function ($query) {
                $query->orderBy(['sort' => 'desc']);
                $query->with(['child' => function ($child) {
                    $child->orderBy(['sort' => 'desc']);
                }]);
            }])
            ->orderBy(['sort' => 'desc'])
            ->asArray()
            ->all();
        $hot = self::getHotArea();
        $list = (self::prapareAdddress($child));
        return compact('list', 'hot');
    }

    public function getChild()
    {
        return $this->hasMany(self::class, ['parent_id' => 'code_id']);
    }

    public static function prapareAdddress($list)
    {
        $address = [];
        foreach ($list as $item) {
            if (isset($item['child']) && count($item['child']) > 0) {
                $item['list'] = self::prapareAdddress($item['child']);
            }
            unset($item['child']);
            $address[$item['sort']][] = $item;
        }
        return $address;
//        dd($address);
    }


    private static function getHotArea()
    {
        //北京 上海 广州 深圳 杭州 南京 苏州 天津 武汉 长沙 重庆 成都
        $cityIds = [110100, 310100, 440100, 440300, 330100, 320100, 320500, 120100, 420100, 430100, 500100, 510100];

        $cityData = self::find()->select(['code_id', 'parent_id', 'name', 'letter', 'sort'])->where(['code_id' => $cityIds])
            ->union(self::find()->select(['code_id', 'parent_id', 'name', 'letter', 'sort'])->where(['parent_id' => $cityIds]))
            ->union(self::find()->select(['country.code_id', 'country.parent_id', 'country.name', 'country.letter', 'country.sort'])->alias('country')->leftJoin(self::tableName() . ' city', 'city.parent_id = country.code_id')->where(['city.code_id' => $cityIds]))->indexBy('code_id')->orderBy('letter')->asArray()->all();
        $subject = [];
        //占位
        foreach ($cityIds as $id) {
            if (empty($cityData[$id])) {
                continue;
            }
            $subject[$id] = $cityData[$id];
            unset($cityData[$id]);
            $subject[$id]['parent'] = $cityData[$subject[$id]['parent_id']];
        }

        foreach ($cityData as $datum) {
            if (empty($subject[$datum['parent_id']])) {
                continue;
            }

            $letter = $datum['sort'];
            $subject[$datum['parent_id']]['children'][$letter][] = $datum;
        }

        foreach ($subject as &$item) {
            ksort($item['children']);
        }

        return array_values($subject);
    }

    public static function getResult()
    {
        $child = self::find()
            ->where(['parent_id' => 0, 'level' => 1])
            ->with(['child' => function ($query) {
                $query->orderBy(['sort' => 'desc']);
                $query->with(['child' => function ($child) {
                    $child->orderBy(['sort' => 'desc']);
                }]);
            }])
            ->orderBy(['sort' => 'desc'])
            ->asArray()
            ->all();
        return $child;
    }


    /**
     * 查找下级区域的code及名称
     * @param int $cityCode
     * @return array|\yii\db\ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    public static function getChildCode(int $cityCode)
    {
        $child = self::find()
            ->select(['code_id as adcode', 'name'])
            ->where(['parent_id' => $cityCode])
            ->orderBy(['sort' => 'desc'])
            ->asArray()
            ->all();

        return $child;
    }


    public function getFormData()
    {
        return $this->hasMany(FormLogModel::class, ['order_id' => 'id']);
    }

}
