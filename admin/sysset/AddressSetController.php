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

namespace shopstar\admin\sysset;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\log\sysset\ExpressLogConstant;
use shopstar\exceptions\sysset\ExpressException;
use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\models\shop\ShopSettings;
use yii\db\Exception;
use yii\helpers\Json;

/**
 * 物流信息配置
 * Class IndexController
 * @package shop\manage\sysset\express
 */
class AddressSetController extends KdxAdminApiController
{
    public $configActions = [
        'postActions' => [
            'update',
        ]
    ];

    /**
     * 获取地址设置
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetInfo()
    {
        $set = ShopSettings::get('sysset.express.address');

        return $this->result($set);
    }

    /**
     * 更新地址设置
     * @throws ExpressException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionUpdate()
    {
        $data = [
            'deny_area' => RequestHelper::post('deny_area', ''), // 不配送区域
            'wechat_address' => RequestHelper::post('wechat_address', '0'), // 获取微信地址
            'delivery_type' => RequestHelper::post('delivery_type', '0'), // 配送类型
            'delivery_area' => RequestHelper::post('delivery_area', ''), // 只配送区域
        ];

        try {
            ShopSettings::set('sysset.express.address', $data);

            // 日志
            LogModel::write(
                $this->userId,
                ExpressLogConstant::ADDRESS_SET_EDIT,
                ExpressLogConstant::getText(ExpressLogConstant::ADDRESS_SET_EDIT),
                '0',
                [
                    'log_data' => $data,
                    'log_primary' => [
                        '不配送区域' => !empty(Json::decode($data['deny_area'])) ? Json::decode($data['deny_area'])['text'] : '',
                        '只配送区域' => !empty(Json::decode($data['delivery_area'])) ? Json::decode($data['delivery_area'])['text'] : '',
                        '获取微信共享收货地址' => $data['wechat_address'] == 1 ? '开启' : '关闭',
                        '配送类型' => $data['delivery_type'] == 1 ? '只配送区域' : '不配送区域',
                    ],
                    'dirty_identify_code' => [
                        ExpressLogConstant::ADDRESS_SET_EDIT,
                    ],
                ]
            );
        } catch (Exception $exception) {
            throw new ExpressException(ExpressException::ADDRESS_SET_SAVE_FAIL);
        }
        return $this->success();
    }
}