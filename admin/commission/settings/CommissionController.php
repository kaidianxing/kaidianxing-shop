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

namespace shopstar\admin\commission\settings;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\commission\CommissionLogConstant;
use shopstar\constants\commission\CommissionSettingsConstant;
use shopstar\exceptions\commission\CommissionSetException;
use shopstar\helpers\RequestHelper;
use shopstar\models\commission\CommissionSettings;
use shopstar\models\goods\GoodsModel;
use shopstar\models\log\LogModel;

/**
 * 分销设置
 * Class CommissionController
 * @package shopstar\admin\commission\settings
 */
class CommissionController extends KdxAdminApiController
{

    /**
     * @var array
     */
    public $configActions = [
        'postActions' => [
            'set',
        ],
        'allowPermActions' => [
            'get',
            'get-level',
        ]
    ];

    /**
     * 获取分销等级
     * @return array|int[]|\yii\web\Response
     * @author likexin
     */
    public function actionGetLevel()
    {
        $set = CommissionSettings::get('set.commission_level');

        return $this->result([
            'level' => $set,
        ]);
    }

    /**
     * 获取设置
     * @return array|int[]|\yii\web\Response
     * @author likexin
     */
    public function actionGet()
    {
        $settings = CommissionSettings::get('set');

        return $this->result([
            'settings' => $settings,
        ]);
    }

    /**
     * 保存设置
     * @return array|int[]|\yii\web\Response
     * @throws CommissionSetException
     * @author likexin
     */
    public function actionSet()
    {
        $commission_level = RequestHelper::postInt('commission_level', 2);
        if (!in_array($commission_level, [0, 1, 2])) {
            throw new CommissionSetException(CommissionSetException::COMMISSION_CENG_LEVEL_ERROR);
        }

        // 接收设置参数
        $settings = [
            'commission_type' => RequestHelper::postInt('commission_type', CommissionSettingsConstant::TYPE_NORMAL), // 分销模式 1传统模式 2:竞争分销
            'compete_safe_type' => RequestHelper::postInt('compete_safe_type'), // 竞争保护期模式 0:无保护期 1: 自定义保护期
            'compete_safe_days' => RequestHelper::postInt('compete_safe_days'), // 竞争保护期天数 须大于0
            'commission_level' => $commission_level, // 分销层级
            'self_buy' => RequestHelper::postInt('self_buy', 0), // 内购
            'banner' => RequestHelper::post('banner', ''), // 申请页面顶部图片
            'become_condition' => RequestHelper::postInt('become_condition', 0), // 成为条件 0无条件  1商品  2金额  3数量  4申请
            'become_goods_ids' => RequestHelper::post('become_goods_ids', ''), // 购买商品
            'become_order_money' => RequestHelper::postFloat('become_order_money', 2, 0), // 消费金额
            'become_order_count' => RequestHelper::postInt('become_order_count', 0), // 订单数量
            'show_agreement' => RequestHelper::postInt('show_agreement', 0), // 是否显示协议
            'become_order_status' => RequestHelper::postInt('become_order_status', 0), // 统计方式  1 付款  2完成
            'is_audit' => 0, // 是否需要审核
            'write_info' => RequestHelper::postInt('write_info', 1), // 完善资料
            'child_condition' => RequestHelper::postInt('child_condition', 1), // 成为下线条件
            'show_commission' => RequestHelper::postInt('show_commission', 1), // 显示佣金
            'show_commission_level_type' => RequestHelper::postInt('show_commission_level_type', 1), // 显示佣金
            'show_commission_level' => RequestHelper::postInt('show_commission_level', 1), // 显示分销商等级
        ];

        // 成为分销商条件 和 成为下线条件冲突
        if ($settings['become_condition'] == 0 && $settings['child_condition'] == 2) {
            throw new CommissionSetException(CommissionSetException::COMMISSION_SET_CHILD_AND_BECOME_ERROR);
        }

        // 切换到竞争分销的时间传递
        $lastCompeteSwitchTime = CommissionSettings::get('set.compete_switch_time');
        if ($lastCompeteSwitchTime) {
            $settings['compete_switch_time'] = $lastCompeteSwitchTime;
        }

        // 检查数据
        // 分销模式检测
        if ($settings['commission_type'] == CommissionSettingsConstant::TYPE_NORMAL) {//传统模式, 初始化数据
            $settings['compete_safe_type'] = CommissionSettingsConstant::COMPETE_SAFE_TYPE_NONE;
            $settings['compete_safe_days'] = 0;
        } else {
            throw new CommissionSetException(CommissionSetException::COMMISSION_SET_COMMISSION_TYPE_ERROR);
        }

        // 成为分销商条件
        $becomeConditionText = '';
        $condition = [];
        switch ($settings['become_condition']) {
            case '1': // 购买商品
                // 商品不能为空
                if (empty($settings['become_goods_ids'])) {
                    throw new CommissionSetException(CommissionSetException::COMMISSION_BECOME_GOODS_NOT_EMPTY);
                }
                $goodsIdArray = explode(',', $settings['become_goods_ids']);
                // 商品最多5个
                if (count($goodsIdArray) > 5) {
                    throw new CommissionSetException(CommissionSetException::COMMISSION_BECOME_GOODS_MAX_ERROR);
                }
                // 拼接商品日志
                $becomeConditionText = '购买商品';
                $condition['商品id'] = $settings['become_goods_ids'];
                $goods = GoodsModel::find()->select('title')->where(['id' => $goodsIdArray])->get();
                $goodsTitle = array_column($goods, 'title');
                $condition['商品名称'] = implode(',', $goodsTitle);
                break;
            case '2': // 消费金额
                if (empty($settings['become_order_money'])) {
                    throw new CommissionSetException(CommissionSetException::COMMISSION_BECOME_MONEY_NOT_EMPTY);
                }
                $becomeConditionText = '消费金额';
                $condition['消费金额'] = $settings['become_order_money'];
                break;
            case '3': // 支付订单数量
                if (empty($settings['become_order_count'])) {
                    throw new CommissionSetException(CommissionSetException::COMMISSION_BECOME_COUNT_NOT_EMPTY);
                }
                $becomeConditionText = '支付订单数量';
                $condition['支付订单数量'] = $settings['become_order_count'];
                break;
            case '4': // 申请
                $becomeConditionText = '申请';
                $condition['申请协议'] = $settings['show_agreement'] ? '显示' : '隐藏';
                break;
            default: // 默认无条件
                $becomeConditionText = '无条件';
                break;
        }

        try {
            CommissionSettings::set('set', $settings);
            // 日志
            $logPrimary = array_merge([
                '分销层级' => $settings['commission_level'],
                '分销内购' => $settings['self_buy'] ? '开启' : '关闭',
                '成为分销商条件' => $becomeConditionText,
            ], $condition, [
                '统计方式' => $settings['become_order_status'] == 1 ? '订单付款后' : '订单完成后',
                '申请页面顶部图片' => $settings['banner'],
                '是否需要审核' => $settings['is_audit'] ? '需要' : '不需要',
                '完善资料' => $settings['write_info'] ? '需要' : '不需要',
                '成为下线条件' => $settings['child_condition'] == 1 ? '首次点击分享链接' : '首次付款',
                '关系保护' => $settings['compete_safe_type'] == CommissionSettingsConstant::COMPETE_SAFE_TYPE_NONE ? '无保护期' : '自定义保护期' . $settings['compete_safe_days'] . '天',
                '佣金显示' => $settings['show_commission'] ? '开启' : '关闭',
            ]);
            LogModel::write(
                $this->userId,
                CommissionLogConstant::COMMISSION_SETTING_EDIT,
                CommissionLogConstant::getText(CommissionLogConstant::COMMISSION_SETTING_EDIT),
                '0',
                [
                    'log_data' => $settings,
                    'log_primary' => $logPrimary,
                    'dirty_identity_code' => [
                        CommissionLogConstant::COMMISSION_SETTING_EDIT,
                    ]

                ]
            );

        } catch (\Throwable $exception) {
            throw new CommissionSetException(CommissionSetException::COMMISSION_SET_SAVE_FAIL, $exception->getMessage());
        }

        return $this->success();
    }

}