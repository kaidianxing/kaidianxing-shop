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

namespace shopstar\models\goods;

use shopstar\bases\model\BaseActiveRecord;

use shopstar\helpers\ArrayHelper;
use shopstar\models\member\group\MemberGroupMapModel;
use shopstar\models\member\MemberModel;
use Yii;
use yii\base\InvalidArgumentException;

/**
 * This is the model class for table "{{%goods_perm_map}}".
 *
 * @property int $goods_id 商品id
 * @property int $perm_type 权限类型(0浏览 1购买)
 * @property int $member_type 会员类型(0全部会员 1会员等级 2会员标签 3会员群体)
 * @property int $type_id 会员类型对应id
 */
class GoodsPermMapModel extends BaseActiveRecord
{
    /**
     * 全部会员
     */
    const MEMBER_TYPE_ALL = 0;

    /**
     * 根据会员等级
     */
    const MEMBER_TYPE_LEVEL = 1;

    /**
     * 根据会员标签
     */
    const MEMBER_TYPE_TAG = 2;


    /**
     * 浏览权限
     */
    const PERM_VIEW = 0;

    /**
     * 购买权限
     */
    const PERM_BUY = 1;

    /**
     * 价格浏览权限
     */
    const PERM_PRICE = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_perm_map}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_id', 'perm_type', 'member_type', 'type_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'goods_id' => '商品id',
            'perm_type' => '权限类型(0浏览 1购买)',
            'member_type' => '会员类型(0全部会员 1会员等级 2会员标签 3会员群体)',
            'type_id' => '会员类型对应id',
        ];
    }

    /**
     * 根据商品id设置商品拥有的权限
     * @param string $goodsIds 商品ID
     * @param array $typeIdArr 等级类型数组
     * @param int $permType 权限类型
     * @param int $memberType 会员类型
     * @return bool
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function setGoodsPermById($goodsIds = '', $typeIdArr = [], $permType = self::PERM_VIEW, $memberType = self::MEMBER_TYPE_LEVEL): bool
    {
        if (empty($goodsIds) || empty($typeIdArr)) {
            throw new InvalidArgumentException("商品权限设置, 参数错误");
        }

        if (!is_array($goodsIds)) {
            $goodsIds = ArrayHelper::explode(',', $goodsIds);
        }
        if (!is_array($typeIdArr)) {
            $typeIdArr = ArrayHelper::explode(',', $typeIdArr);
        }
        $mapValue = [];
        $field = ['goods_id', 'perm_type', 'member_type', 'type_id'];
        foreach ((array)$goodsIds as $goodsId) {
            // 重新组织商品、权限映射关系
            foreach ((array)$typeIdArr as $typeId) {
                $mapValue[] = [$goodsId, $permType, $memberType, $typeId];
            }
        }
        if (!empty($mapValue)) {
            //替换更新店铺商品权限映射表
            Yii::$app->db->createCommand()->batchInsert(self::tableName(), $field, $mapValue)->execute();
        }
        return true;
    }

    /**
     * 删除权限
     * @param array $condition
     * @param int $permType
     * @param int $memberType
     * @return int
     * @author 青岛开店星信息技术有限公司
     */
    public static function delGoodsPerm($condition = [], $permType = self::PERM_VIEW, $memberType = self::MEMBER_TYPE_LEVEL): int
    {
        $delCondition = ['perm_type' => $permType, 'member_type' => $memberType];
        $delCondition = array_merge($condition, $delCondition);
        return self::deleteAll($delCondition);
    }

    /**
     * 检测商品权限
     * @param int $goodsId
     * @param int $memberId
     * @param int $permType
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkGoodsPerm(int $goodsId, int $memberId, int $permType = self::PERM_VIEW): bool
    {

        $permData = GoodsPermMapModel::find()->where(['goods_id' => $goodsId, 'perm_type' => $permType])->asArray()->all();
        //如果没有设置商品权限，则直接跳过
        if (empty($permData)) {
            return true;
        }

        $member = MemberModel::find()->where(['id' => $memberId])->asArray()->one();
        //没有会员无法查看
        if (empty($member)) {
            return false;
        }

        //会员标签
        $memberGroupId = MemberGroupMapModel::getGroupIdByMemberId($memberId);

        //循环获取商品权限是否可用
        foreach ($permData as $permDataIndex => $permDataItem) {
            if ($permDataItem['member_type'] == 1) {
                // 1会员等级
                if ($permDataItem['type_id'] == $member['level_id']) {
                    return true;
                }
            } elseif ($permDataItem['member_type'] == 2) {
                //2会员标签
                if (in_array($permDataItem['type_id'], $memberGroupId)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * 获取有权限的商品Id
     * @param array $goodsId
     * @param int $memberId
     * @param int $permType
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function getHasPermGoodsId(array $goodsId, int $memberId, $permType = self::PERM_VIEW)
    {
        $permData = GoodsPermMapModel::find()->where(['goods_id' => $goodsId, 'perm_type' => $permType])->asArray()->all();
        //如果没有设置商品权限，则直接跳过
        if (empty($permData)) {
            return true;
        }

        $member = MemberModel::find()->where(['id' => $memberId])->asArray()->one();
        //没有会员无法查看
        if (empty($member)) {
            return false;
        }

        //会员标签
        $memberGroupId = MemberGroupMapModel::getGroupIdByMemberId($memberId);
        //循环获取商品权限是否可用
        foreach ($permData as $permDataIndex => $permDataItem) {
            if ($permDataItem['member_type'] == 1) {
                // 1会员等级
                if ($permDataItem['type_id'] != $member['level_id']) {
                    unset($permData[$permDataIndex]);
                }
            } elseif ($permDataItem['member_type'] == 2) {
                //2会员标签
                if (!in_array($permDataItem['type_id'], $memberGroupId ?: [])) {
                    unset($permData[$permDataIndex]);
                }
            }
        }

        return array_column($permData, 'goods_id') ?: [];
    }

    /**
     * 获取无权限的商品Id
     * @param array $goodsId
     * @param int $memberId
     * @param int $permType
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function getNotHasPermGoodsId(array $goodsId, int $memberId, $permType = self::PERM_VIEW)
    {
        $permData = GoodsPermMapModel::find()->where(['goods_id' => $goodsId, 'perm_type' => $permType])->asArray()->all();
        //如果没有设置商品权限，则直接跳过
        if (empty($permData)) {
            return [];
        }
        $goodsId = array_intersect($goodsId, array_column($permData, 'goods_id'));
        $member = MemberModel::find()->where(['id' => $memberId])->asArray()->one();

        //会员标签
        $memberGroupId = MemberGroupMapModel::getGroupIdByMemberId($memberId);
        //循环获取商品权限是否可用
        foreach ($permData as $permDataIndex => $permDataItem) {
            if ($permDataItem['member_type'] == 1) {
                // 1会员等级
                if ($permDataItem['type_id'] == $member['level_id']) {
                    $goodsId = ArrayHelper::deleteByValue($goodsId, $permDataItem['goods_id']);
                }
            } elseif ($permDataItem['member_type'] == 2) {
                //2会员标签
                if (in_array($permDataItem['type_id'], $memberGroupId ?: [])) {
                    $goodsId = ArrayHelper::deleteByValue($goodsId, $permDataItem['goods_id']);
                }
            }
        }

        return $goodsId ?: [];
    }

    /**
     * 获取商品的权限
     * @param int $goodsId
     * @param bool $format 是否返回格式化
     * @return array|\yii\db\ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    public static function getGoodsPerm(int $goodsId, bool $format = false)
    {
        $perm = self::find()->where([
            'goods_id' => $goodsId,
        ])->asArray()->all();


        $data = [];
        if ($format) {
            $data['browse']['member_level'] = [];
            $data['browse']['member_tag'] = [];
            $data['buy']['member_level'] = [];
            $data['buy']['member_tag'] = [];
            foreach ($perm as $item) {
                //浏览权限
                if ($item['perm_type'] == self::PERM_VIEW) {
                    if ($item['member_type'] == self::MEMBER_TYPE_LEVEL) {
                        $data['browse']['member_level'][] = $item['type_id'];
                    } else {
                        $data['browse']['member_tag'][] = $item['type_id'];
                    }
                }

                //购买权限
                if ($item['perm_type'] == self::PERM_BUY) {
                    if ($item['member_type'] == self::MEMBER_TYPE_LEVEL) {
                        $data['buy']['member_level'][] = $item['type_id'];
                    } else {
                        $data['buy']['member_tag'][] = $item['type_id'];
                    }
                }
            }
        }


        return $format ? $data : $perm;
    }

}
