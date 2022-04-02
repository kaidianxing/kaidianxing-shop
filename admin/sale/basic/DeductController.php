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

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\log\sale\BasicLogConstant;
use shopstar\exceptions\sale\BasicException;
use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\models\shop\ShopSettings;
use yii\db\Exception;
use yii\web\Response;

/**
 * 抵扣设置
 * Class DeductController
 * @package shopstar\admin\sale\basic
 */
class DeductController extends KdxAdminApiController
{

    /**
     * @var array
     */
    public $configActions = [
        'allowPermActions' => [
            'index'
        ]
    ];

    /**
     * 获取抵扣设置
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $enough = ShopSettings::get('sale.basic.deduct');

        return $this->result($enough);
    }

    /**
     * 修改抵扣设置
     * @return Response
     * @throws BasicException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit(): Response
    {
        $data = [
            'credit_state' => RequestHelper::post('credit_state', 0), // 积分抵扣
            'credit_num' => RequestHelper::post('credit_num', 0), // 积分抵扣比例
            'balance_state' => RequestHelper::post('balance_state', 0), // 余额抵扣设置
        ];

        if ($data['credit_state'] == 1) {
            $data['credit_num'] = bcadd($data['credit_num'], 0, 2);
            if (bccomp($data['credit_num'], 0.01, 2) < 0) {
                throw new BasicException(BasicException::DEDUCT_CREDIT_NUM_ERROR);
            }
        }

        try {
            ShopSettings::set('sale.basic.deduct', $data);

            // 日志
            LogModel::write(
                $this->userId,
                BasicLogConstant::SALE_DEDUCT_EDIT,
                BasicLogConstant::getText(BasicLogConstant::SALE_DEDUCT_EDIT),
                '0',
                [
                    'log_data' => $data,
                    'log_primary' => [
                        '积分抵扣' => $data['credit_state'] == 1 ? '开启' : '关闭',
                        '积分抵扣比例' => '1 积分抵扣 ' . $data['credit_num'] . ' 元',
                        '余额抵扣' => $data['balance_state'] == 1 ? '开启' : '关闭',
                    ],
                    'dirty_identity_code' => [
                        BasicLogConstant::SALE_DEDUCT_EDIT,
                    ]
                ]
            );
        } catch (Exception $exception) {
            throw new BasicException(BasicException::DEDUCT_SAVE_FAIL);
        }

        return $this->success();
    }
}