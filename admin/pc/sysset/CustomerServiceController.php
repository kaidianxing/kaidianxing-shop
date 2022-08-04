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

namespace shopstar\admin\pc\sysset;

use Exception;
use shopstar\bases\KdxAdminApiController;
use shopstar\constants\pc\PcLogConstant;
use shopstar\exceptions\pc\PcException;
use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\models\shop\ShopSettings;
use yii\web\Response;

class CustomerServiceController extends KdxAdminApiController
{
    /**
     * 获取设置
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGet()
    {
        $data = ShopSettings::get('pc.sysset.service');

        return $this->success(['data' => $data]);
    }

    /**
     * 保存设置
     * @return array|int[]|Response
     * @throws PcException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSet()
    {
        $data = [
            'status' => RequestHelper::post('status', '1'), // 客服管理状态
            'name' => RequestHelper::post('name', ''), // 客服名称
            'title' => RequestHelper::post('title', ''), // 客服标题

            'service_name_show' => RequestHelper::post('service_name_show', '1'), // 姓名显示
            'service_name' => RequestHelper::post('service_name', ''), // 客服姓名

            'qq_show' => RequestHelper::post('qq_show', '1'), // QQ客服状态
            'qq_title' => RequestHelper::post('qq_title', ''), // qq标题
            'qq_number' => RequestHelper::post('qq_number', ''), // QQ号码

            'wechat_show' => RequestHelper::post('wechat_show', '1'), // 微信客服显示
            'wechat_title' => RequestHelper::post('wechat_title', ''), // 微信标题
            'wechat_qrcode' => RequestHelper::post('wechat_qrcode', ''), // 微信二维码图

            'service_phone_show' => RequestHelper::post('service_phone_show', '1'), // 服务热线显示
            'service_phone_title' => RequestHelper::post('service_phone_title', ''), // 服务热线-标题
            'service_phone_number' => RequestHelper::post('service_phone_number', ''), // 服务热线-号码
        ];

        try {
            ShopSettings::set('pc.sysset.service', $data);

            // 记录日志
            LogModel::write(
                $this->userId,
                PcLogConstant::PC_SYSSET_SERVICE,
                PcLogConstant::getText(PcLogConstant::PC_SYSSET_SERVICE),
                '0',
                [
                    'log_data' => $data,
                    'log_primary' => [
                        '客服管理状态' => $data['status'] == 1 ? '开启' : '关闭',
                        '客服名称' => $data['name'],
                        '客服标题' => $data['title'],

                        '姓名显示' => $data['service_name_show'] == 1 ? '显示' : '隐藏',
                        '客服姓名' => $data['service_name'],

                        'QQ客服状态' => $data['qq_show'] == 1 ? '显示' : '隐藏',
                        'QQ标题' => $data['qq_title'],
                        'QQ号码' => $data['qq_number'],

                        '微信客服显示' => $data['wechat_show'] == 1 ? '显示' : '隐藏',
                        '微信标题' => $data['wechat_title'],
                        '微信二维码图' => $data['wechat_qrcode'],

                        '服务热线显示' => $data['service_phone_show'] == 1 ? '显示' : '隐藏',
                        '服务热线-标题' => $data['service_phone_title'],
                        '服务热线-号码' => $data['service_phone_number'],
                    ],
                    'dirty_identify_code' => [
                        PcLogConstant::PC_SYSSET_SERVICE
                    ]
                ]
            );
        } catch (Exception $exception) {
            throw new PcException(PcException::SERVICE_SAVE_FAIL);
        }

        return $this->success();
    }
}
