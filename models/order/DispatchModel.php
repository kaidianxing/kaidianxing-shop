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

namespace shopstar\models\order;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\shop\ShopSettings;
use yii\helpers\Json;


/**
 * This is the model class for table "{{%dispatch}}".
 *
 * @property int $id
 * @property string $dispatch_name 配送方式名称
 * @property int $sort 排序
 * @property int $calculate_type 计费方式 0:按重量;1:按件;
 * @property string $start_num 首件数量默认区域
 * @property string $start_num_price 首件运费默认区域
 * @property string $add_num 续件运费默认区域
 * @property string $add_num_price 续件数量默认区域
 * @property string $start_weight 首重重量默认区域
 * @property string $start_weight_price 首重运费默认区域
 * @property string $add_weight 续重重量默认区域
 * @property string $add_weight_price 续重运费默认区域
 * @property string $free_dispatch 满额包邮 0:不包邮
 * @property string $dispatch_area 配送区域设置
 * @property int $state 是否启用 0:否;1:是;
 * @property int $is_default 是否默认 0:否;1:是;
 * @property string $express 物流公司
 * @property int $dispatch_area_type 配送限制区域类型 0:不配送区域;1:只配送区域;
 * @property string $dispatch_limit_area 配送限制区域
 * @property string $dispatch_limit_area_code 配送限制区域代码
 * @property string $created_at 创建时间
 * @property string $updated_at 修改时间
 */
class DispatchModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%dispatch}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sort', 'calculate_type', 'state', 'is_default', 'dispatch_area_type'], 'integer'],
            [['start_num', 'start_num_price', 'add_num', 'add_num_price', 'start_weight', 'start_weight_price', 'add_weight', 'add_weight_price', 'free_dispatch'], 'number'],
            [['dispatch_area', 'dispatch_limit_area', 'dispatch_limit_area_code'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['dispatch_name'], 'string', 'max' => 50],
            [['express'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'dispatch_name' => '配送方式名称',
            'sort' => '排序',
            'calculate_type' => '计费方式 0:按重量;1:按件;',
            'start_num' => '首件数量默认区域',
            'start_num_price' => '首件运费默认区域',
            'add_num' => '续件运费默认区域',
            'add_num_price' => '续件数量默认区域',
            'start_weight' => '首重重量默认区域',
            'start_weight_price' => '首重运费默认区域',
            'add_weight' => '续重重量默认区域',
            'add_weight_price' => '续重运费默认区域',
            'free_dispatch' => '满额包邮 0:不包邮',
            'dispatch_area' => '配送区域设置',
            'state' => '是否启用 0:否;1:是;',
            'is_default' => '是否默认 0:否;1:是;',
            'express' => '物流公司',
            'dispatch_area_type' => '配送限制区域类型 0:不配送区域;1:只配送区域;',
            'dispatch_limit_area' => '配送限制区域',
            'dispatch_limit_area_code' => '配送限制区域代码',
            'created_at' => '创建时间',
            'updated_at' => '修改时间',
        ];
    }

    /**
     * 保存配送方式
     * @param int $id
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function saveDispatch(int $id = 0)
    {
        $post = RequestHelper::post();
        $post['sort'] = RequestHelper::post('sort', 0);

        if (empty($post['dispatch_name'])) {
            return error('配送方式名称不能为空');
        }

        // 保存
        if (empty($id)) {
            $dispatch = new self();
        } else {
            $dispatch = self::findOne(['id' => $id]);
            if (empty($dispatch)) {
                return error('配送方式不存在');
            }
        }
        if ($post['is_default']) {
            self::updateAll(['is_default' => 0]);
        }

        $dispatch->setAttributes($post);

        if (!$dispatch->save()) {
            return error('保存失败，' . $dispatch->getErrorMessage());
        }
        return true;
    }

    /**
     * 检测配送区域(此方法只允许运费模板商品进入)
     * @param int $templateId
     * @param array $dispatchTemplates
     * @param int $addressCode
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkDispatchArea(int $templateId, array $dispatchTemplates, int $addressCode): bool
    {

        // 读取商品运费模板->不配送模板
        $dispatch = [];
        if (isset($dispatchTemplates[$templateId])) {
            $dispatch = $dispatchTemplates[$templateId];
            $dispatch['dispatch_limit_area_code'] = Json::decode($dispatch['dispatch_limit_area_code']);
        }

        if (empty($dispatch['dispatch_limit_area_code'])) {
            return true;
        }

        if ($dispatch['dispatch_area_type'] == 0) { //不配送
            // 判断地址的区 是否在不配送的地址里面
            if (in_array($addressCode, $dispatch['dispatch_limit_area_code'])) {
                return false;
            }
        } else {
            // 判断地址的区 是否不在只配送的地址里面
            if (!in_array($addressCode, $dispatch['dispatch_limit_area_code'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * 获取最高运费
     * @param array $dispatchTemplates
     * @param array $address
     * @return array
     * @throws \Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function getMaxDispatch(array $dispatchTemplates, array $address)
    {
        $dispatchList = [];
        $maxDispatch = null;
        $maxDispatchId = 0;

        foreach ($dispatchTemplates as $item) {
            $newRule = [];
            $rule = self::getDispatchRule($item, $address);
            if (empty($rule)) {
                continue;
            }

            //转换格式，省的按件和按重量要算两遍
            $newRule['type'] = $item['calculate_type'];
            if ($newRule['type'] == 1) { //按件
                $newRule['first_num'] = $rule['start_num'];
                $newRule['first_price'] = $rule['start_num_price'];
                $newRule['second_num'] = $rule['add_num'];
                $newRule['second_price'] = $rule['add_num_price'];
            } else {//按重量
                $newRule['first_num'] = $rule['start_weight'];
                $newRule['first_price'] = $rule['start_weight_price'];
                $newRule['second_num'] = $rule['add_weight'];
                $newRule['second_price'] = $rule['add_weight_price'];
            }

            $dispatchList[$item['id']] = $newRule;
            $isMax = false;


            if ($maxDispatch == null) {
                $isMax = true;
            } else {
                //对比首重金额
                if ($maxDispatch['first_price'] < $newRule['first_price']) {
                    $isMax = true;
                } else if ($maxDispatch['first_price'] == $newRule['first_price']) {

                    //首重金额相同，对比首重平均
                    $maxFirstAverage = $maxDispatch['first_price'] / $maxDispatch['first_num'];
                    $ruleFirstAverage = $newRule['first_price'] / $newRule['first_num'];
                    if ($maxFirstAverage < $ruleFirstAverage) {
                        $isMax = true;
                    } else if ($maxFirstAverage == $ruleFirstAverage) {
                        //首重平均相同，对比续重
                        if ($maxDispatch['second_price'] < $newRule['second_price']) {
                            $isMax = true;
                        } else if ($maxDispatch['second_price'] == $newRule['second_price']) {
                            //续重相同，对比续重平均
                            $maxSecondAverage = $maxDispatch['second_price'] / $maxDispatch['second_num'];
                            $ruleSecondAverage = $newRule['second_price'] / $newRule['second_num'];
                            if ($maxSecondAverage < $ruleSecondAverage) {
                                $isMax = true;
                            }
                        }
                    }
                }
            }

            if ($isMax) {
                $maxDispatch = $newRule;
                $maxDispatchId = $item['id'];
            }
        }

        return [
            'dispatchList' => $dispatchList,
            'maxDispatch' => $maxDispatch,
            'maxDispatchId' => $maxDispatchId
        ];
    }

    /**
     * 根据收货地址获取运费规则
     * @param $dispatch array 运费模版
     * @param array $address 收货地址
     * @return bool|int|mixed|null
     * @throws \Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function getDispatchRule(array $dispatch, array $address)
    {
        if (empty($dispatch)) {
            return false;
        }

        $rule = false;
        if (!empty($address) && !empty($dispatch['dispatch_area'])) {

            $areas = Json::decode($dispatch['dispatch_area']);
            foreach ($areas as $area) {
                if (is_array($area['citys_code']) && in_array($address['area_code'], $area['citys_code'])) {
                    unset($area['citys_code'], $area['citys']);
                    $rule = $area;
                    break;
                }
            }
        }

        if (empty($rule)) {
            //如果未定义区域或未找到区域，则使用全国统一运费
            $rule = [
                'start_num' => $dispatch['start_num'],
                'start_num_price' => $dispatch['start_num_price'],
                'add_num' => $dispatch['add_num'],
                'add_num_price' => $dispatch['add_num_price'],

                //重量
                'start_weight' => $dispatch['start_weight'],
                'start_weight_price' => $dispatch['start_weight_price'],
                'add_weight' => $dispatch['add_weight'],
                'add_weight_price' => $dispatch['add_weight_price'],
            ];
        }

        return $rule;
    }

    /**
     * 获取首件/重运费
     * @param int $dispatchId
     * @return int|string
     * @author 青岛开店星信息技术有限公司
     */
    public static function getStartPrice(int $dispatchId)
    {
        $dispatch = self::findOne($dispatchId);
        if (empty($dispatch)) {
            return 0;
        }

        return $dispatch->calculate_type == 0 ? $dispatch->start_weight_price : $dispatch->start_num_price;
    }

    /**
     * 获取不配送区域
     * @param int $dispatchId
     * @return array|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function getNotDispatchArea(int $dispatchId)
    {
        $dispatch = self::findOne(['id' => $dispatchId]);
        if (empty($dispatch)) {
            return [];
        }

        // 配送区域类型 0不配送 1只配送
        $result['dispatch_area_type'] = !empty($dispatch->dispatch_area_type) ? $dispatch->dispatch_area_type : '';
        $result['dispatch_limit_area'] = !empty($dispatch->dispatch_limit_area) ? $dispatch->dispatch_limit_area : '';
        return $dispatch;
    }

    /**
     * 更新配送方式排序
     * @param int $enable
     * @param int $type
     * @author 青岛开店星信息技术有限公司
     */
    public static function updateSort(int $enable, int $type)
    {
        $sort = ShopSettings::get('dispatch.sort');

        // 只有设置过的时候重新排序，平台不重新排序
        if (!empty($sort)) {
            $sortArray = explode(',', $sort);
            if ($enable == 1) {
                // 开启
                if (!in_array($type, $sortArray)) {
                    $sortArray[] = $type;
                }

            } else if ($enable == 0) {
                // 关闭
                if (in_array($type, $sortArray)) {
                    $sortArray = ArrayHelper::deleteByValue($sortArray, $type);
                }
            }
            $sort = implode(',', $sortArray);

            ShopSettings::set('dispatch.sort', $sort);
        }
    }
}
