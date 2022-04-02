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

namespace shopstar\admin\sale\basic;

use shopstar\constants\log\sale\BasicLogConstant;

use shopstar\exceptions\sale\BasicException;
 
use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\models\shop\ShopSettings;
use shopstar\bases\KdxAdminApiController;
use yii\db\Exception;
use yii\web\Response;

/**
 * 满额立减
 * Class EnoughController
 * @package shopstar\admin\sale\basic
 */
class EnoughController extends KdxAdminApiController
{
    public $configActions = [
        'allowPermActions' => [
            'index',
        ]
    ];
    /**
     * 满额立减
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $enough = ShopSettings::get('sale.basic.enough');
        return $this->success($enough);
    }

    /**
     * 修改满额立减
     * @return Response
     * @throws BasicException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit()
    {
        $data['state'] = RequestHelper::post('state');
        // 日志数据
        $logData = [];
        if ($data['state'] == 1) {
            $data['set'] = RequestHelper::postArray('set');
            // 保存重复值
            $temp = [];
            foreach ($data['set'] as $key => $value) {
                if (empty($value['enough']) || empty($value['deduct'])) {
                    // 不能为空
                    throw new BasicException(BasicException::FULL_DEDUCT_MONEY_NOT_EMPTY);
                }
                if ($value['enough'] < 0 || $value['deduct'] < 0) {
                    // 不能小于0
                    throw new BasicException(BasicException::FULL_DEDUCT_MONEY_NOT_MINUS);
                }
                if (bccomp($value['enough'], $value['deduct'], 2) < 0) {
                    // 抵扣不能大于满减
                    throw new BasicException(BasicException::FULL_DEDUCT_MONEY_BIG);
                }
                $logData[$key + 1] = '单笔订单满 ' . $value['enough'] . ' 元减 ' . $value['deduct'] . ' 元';
                // 用值做下标
                $temp[$value['enough']]++;
            }
            // 判重
            foreach ($temp as $value) {
                if ($value > 1) {
                    throw new BasicException(BasicException::FULL_DEDUCT_MONEY_NOT_REPEAT);
                }
            }
        }

        try {
            ShopSettings::set('sale.basic.enough', $data);
            // 日志
            $logPrimary['满额立减'] = $data['state'] == 1 ? '开启' : '关闭';
            if ($data['state'] == 1) {
                $logPrimary['立减条件'] = $logData;
            }
            LogModel::write(
                $this->userId,
                BasicLogConstant::SALE_ENOUGH_EDIT,
                BasicLogConstant::getText(BasicLogConstant::SALE_ENOUGH_EDIT),
                '0',
                [
                    'log_data' => $data,
                    'log_primary' => $logPrimary,
                    'dirty_identity_code' => [
                        BasicLogConstant::SALE_ENOUGH_EDIT,
                    ],
                ]
            );
        } catch (Exception $exception) {
            throw new BasicException(BasicException::FULL_DEDUCT_SAVE_FAIL);
        }

        return $this->success();
    }
}