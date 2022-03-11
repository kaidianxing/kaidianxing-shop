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

namespace shopstar\admin\virtualAccount;

use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\models\shop\ShopSettings;
use shopstar\constants\virtualAccount\VirtualAccountLogConstant;
use shopstar\exceptions\virtualAccount\VirtualAccountException;
use shopstar\bases\KdxAdminApiController;

/**
 * 卡密库-设置
 * Class SettingController
 * @package apps\virtualAccount\manage
 */
class SettingController extends KdxAdminApiController
{
    /**
     * 基础设置
     * @author 青岛开店星信息技术有限公司
     * @return \yii\web\Response
     */
    public function actionGet()
    {
        $result = ShopSettings::get('virtual_setting');

        return $this->result(['data' => $result]);
    }

    /**
     * 添加基础设置
     * @throws VirtualAccountException
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     * @return array|\yii\web\Response
     */
    public function actionSet()
    {
        $params = RequestHelper::post();
        if (!$params) {
            throw new VirtualAccountException(VirtualAccountException::PARAMS_ERROR);
        }
        $data = [
            'close_type' => $params['close_type'],
            'close_time' => (int)$params['close_time'],
        ];
        ShopSettings::set('virtual_setting', $data);

        // 日志
        LogModel::write(
            $this->userId,
            VirtualAccountLogConstant::VIRTUAL_ACCOUNT_SETTING_EDIT_CLOSE_TIME,
            VirtualAccountLogConstant::getText(VirtualAccountLogConstant::VIRTUAL_ACCOUNT_SETTING_EDIT_CLOSE_TIME),
            0,
            [
                'log_data' => [],
                'log_primary' => [
                    '未付款订单关闭时间' => !$data['close_type'] ? '默认系统' : '自定义',
                ],
            ]
        );
        return $this->result();
    }

}