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

namespace shopstar\models\commission;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\constants\commission\CommissionGoodsConstant;
use shopstar\models\activity\ShopMarketingModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\goods\GoodsOptionModel;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%commission_goods}}".
 *
 * @property int $id id
 * @property int $goods_id 商品id
 * @property int $has_option 是否有规格
 * @property int $type 0系统默认,1按商品  2按规格
 * @property string $commission_rule 分销规则
 */
class CommissionGoodsModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%commission_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_id', 'has_option', 'type'], 'integer'],
            [['goods_id'], 'required'],
            [['commission_rule'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'goods_id' => '商品id',
            'has_option' => '是否有规格',
            'type' => '0系统默认,1按商品  2按规格',
            'commission_rule' => '分销规则',
        ];
    }

    /**
     * 保存商品佣金
     * @param array $goodsCommission
     * @param int $goodsId
     * @param array $option
     * @return bool|void
     * @author 青岛开店星信息技术有限公司
     */
    public static function saveGoodsCommission(array $goodsCommission, int $goodsId, array $option = [])
    {
        // 取记录
        $model = self::findOne(['goods_id' => $goodsId]);
        if (empty($model)) {
            $model = new self();
            $model->goods_id = $goodsId;
        }
        $model->type = $goodsCommission['type'];
        // 系统默认
        if ($goodsCommission['type'] == CommissionGoodsConstant::TYPE_SYSTEM) {
            $model->save();
        } else if ($goodsCommission['type'] == CommissionGoodsConstant::TYPE_GOODS) {
            // 按商品
            $res = self::checkData($goodsCommission['info']);
            if (is_error($res)) {
                return $res;
            }
            $model->type = $goodsCommission['type'];
            $model->commission_rule = Json::encode($goodsCommission['info']);
            $model->save();
        } else if ($goodsCommission['type'] == CommissionGoodsConstant::TYPE_GOODS_OPTION) {
            // 按规格 TODO 青岛开店星信息技术有限公司 一期不做
        }
        return true;
    }

    /**
     * 检查数据
     * @param array $data
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkData(array $data)
    {
        // 获取所有分销等级
        $levels = CommissionLevelModel::findAll([]);
        foreach ($data as $item) {
            foreach ($levels as $level) {
                if (isset($item['level_' . $level['id']])) {
                    // 按比例类型
                    if ($item['level_' . $level['id']]['type'] == CommissionGoodsConstant::COMMISSION_TYPE_SCALE) {
                        if (bccomp($item['level_' . $level['id']]['num'], 0, 2) < 0
                            || bccomp($item['level_' . $level['id']]['num'], 100, 2) > 0) {
                            return error('佣金设置错误');
                        }
                    } else {
                        // 按佣金类型
                        if (bccomp($item['level_' . $level['id']]['num'], 0, 2) < 0
                            || bccomp($item['level_' . $level['id']]['num'], 9999999.99, 2) > 0) {
                            return error('佣金设置错误');
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * 获取商品佣金设置
     * @param int $goodsId
     * @return array|\yii\db\ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    public static function getCommission(int $goodsId)
    {
        $detail = self::find()->where(['goods_id' => $goodsId])->first();
        $detail['commission_rule'] = Json::decode($detail['commission_rule']);

        return $detail;
    }

    /**
     * 获取预计佣金
     * 该等级下最大佣金
     * @param int $goodsId
     * @param int $hasOption
     * @param int $levelId
     * @param int $clientType
     * @return int|mixed|null
     * @author 青岛开店星信息技术有限公司
     */
    public static function getMaxCommission(int $goodsId, int $goodsType, int $hasOption, int $levelId, int $clientType = 0)
    {
        // 预计佣金
        $money = 0;
        $allCommission = self::getGoodsAllCommission($goodsId, $clientType);

        if (is_error($allCommission)) {
            return $money;
        }

        // 获取层级设置
        $showLevelType = CommissionSettings::get('set.show_commission_level_type');

        //多规格或预约商品
        if ($hasOption) {
            // 取所有规格里最大的
            $data = [];
            $optionCommission = $allCommission['level_' . $levelId];
            if (is_array($optionCommission)) {
                foreach ($optionCommission as $commissionNum => $item) {

                    //如果是数组，代表多规格，或者是多规格预约
                    if (is_array($item)) {
                        $data[] = $item['commission_' . $showLevelType];
                    } else {
                        //如果是单规格预约就不是数组，直接取key
                        $data[] = $optionCommission['commission_' . $showLevelType];
                    }
                }
                if (!empty($data)) {
                    $money = max($data);
                }
            }

        } else {
            // 单规格
            $money = $allCommission['level_' . $levelId]['commission_' . $showLevelType];
        }
        return $money;
    }

    /**
     * 获取商品所有的佣金
     * @param int $goodsId 商品id
     * @param int $clientType
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getGoodsAllCommission(int $goodsId, int $clientType = 0)
    {
        // 商品信息
        $goods = GoodsModel::findOne(['id' => $goodsId]);
        if (empty($goods)) {
            return error('商品不存在');
        }
        // 分销商品信息
        $commissionGoods = CommissionGoodsModel::findOne(['goods_id' => $goodsId]);
        if (empty($commissionGoods)) {
            return error('分销商品不存在');
        }
        // 获取分销设置
        $levelSet = CommissionSettings::get('set.commission_level');
        if (empty($levelSet)) {
            return error('未开启分销');
        }
        // 获取系统设置等级
        $commissionLevel = CommissionLevelModel::find()->where(['status' => 1])->get();
        if (empty($commissionLevel)) {
            return error('无分销等级');
        }
        // 商品价格
        $goodsPrice = $goods->price;
        // 如果有多规格
        $goodsOption = [];
        if ($goods->has_option) {
            $goodsOption = GoodsOptionModel::find()->select('id, price')->where(['goods_id' => $goodsId])->indexBy('id')->asArray()->get();
        }

        // 活动是否参与分销
        $isCommission = ShopMarketingModel::checkGoodsCommission($goodsId, $clientType);
        if (!$isCommission) {
            return error('活动不支持');
        }

        switch ($commissionGoods['type']) {
            case CommissionGoodsConstant::TYPE_SYSTEM: // 系统默认
                $data = self::getCommissionBySystem($commissionLevel, $goodsPrice, $goodsOption);
                break;
            case CommissionGoodsConstant::TYPE_GOODS: // 按商品
                $data = self::getCommissionByGoods($commissionLevel, $goodsPrice, Json::decode($commissionGoods['commission_rule']), $goodsOption);
                break;
            case CommissionGoodsConstant::TYPE_GOODS_OPTION: // TODO 青岛开店星信息技术有限公司 按规格 暂不支持
                $data = self::getCommissionByOption();
                break;
        }

        return $data;
    }

    /**
     * 获取佣金信息
     * 按系统等级设置
     * @param array $commissionLevel
     * @param float $goodsPrice
     * @param array $goodsOption
     * @param array $activityRules
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    private static function getCommissionBySystem(array $commissionLevel, float $goodsPrice, array $goodsOption = [], $activityRules = [])
    {
        $data = [];
        // 遍历查询
        foreach ($commissionLevel as $item) {
            $rule['commission_1'] = ['num' => $item['commission_1'], 'type' => CommissionGoodsConstant::COMMISSION_TYPE_SCALE];
            $rule['commission_2'] = ['num' => $item['commission_2'], 'type' => CommissionGoodsConstant::COMMISSION_TYPE_SCALE];

            // 如果有多规格
            if (!empty($goodsOption)) {
                foreach ($goodsOption as $option) {
                    $data['level_' . $item['id']]['option_' . $option['id']] = self::calculateCommission($option['price'], $rule);
                }
            } else {
                // 无多规格
                $data['level_' . $item['id']] = self::calculateCommission($goodsPrice, $rule);
            }
        }
        return $data;
    }

    /**
     * 获取佣金信息
     * 按商品设置
     * @param array $commissionLevel
     * @param float $goodsPrice
     * @param array $commissionRules
     * @param array $goodsOption
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    private static function getCommissionByGoods(array $commissionLevel, float $goodsPrice, array $commissionRules, array $goodsOption = [])
    {
        $data = [];
        foreach ($commissionLevel as $item) {
            // 保存和这的数据结构不一致 重新取值
            $rule['commission_1'] = $commissionRules['commission_1']['level_' . $item['id']];
            $rule['commission_2'] = $commissionRules['commission_2']['level_' . $item['id']];

            // 如果有多规格
            if (!empty($goodsOption)) {
                foreach ($goodsOption as $option) {
                    $data['level_' . $item['id']]['option_' . $option['id']] = self::calculateCommission($option['price'], $rule);
                }
            } else {
                $data['level_' . $item['id']] = self::calculateCommission($goodsPrice, $rule);
            }
        }
        return $data;
    }

    /**
     * 获取佣金信息
     * 按商品规格
     * TODO 青岛开店星信息技术有限公司 暂不支持
     * @author 青岛开店星信息技术有限公司
     */
    public static function getCommissionByOption()
    {
        return [];
    }

    /**
     * 获取计算佣金的商品价格
     * 预售活动等的价格不用商品价格
     * @author 青岛开店星信息技术有限公司
     */
    private static function getCalculatePrice()
    {

    }

    /**
     * 计算佣金
     * @param float $price
     * @param array $rule
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    private static function calculateCommission(float $price, array $rule)
    {
        $data = [];
        // 一级佣金
        if (!empty($rule['commission_1'])) {
            // 按比例
            if ($rule['commission_1']['type'] == CommissionGoodsConstant::COMMISSION_TYPE_SCALE) {
                $data['commission_1'] = bcmul($price, bcdiv($rule['commission_1']['num'], 100, 4), 2);
            } else if ($rule['commission_1']['type'] == CommissionGoodsConstant::COMMISSION_TYPE_MONEY) {
                // 固定金额
                $data['commission_1'] = $rule['commission_1']['num'];
            }
        }
        // 二级佣金
        if (!empty($rule['commission_2'])) {
            if ($rule['commission_2']['type'] == CommissionGoodsConstant::COMMISSION_TYPE_SCALE) {
                $data['commission_2'] = bcmul($price, bcdiv($rule['commission_2']['num'], 100, 4), 2);
            } else if ($rule['commission_2']['type'] == CommissionGoodsConstant::COMMISSION_TYPE_MONEY) {
                // 固定金额
                $data['commission_2'] = $rule['commission_2']['num'];
            }
        }

        return $data;
    }

}