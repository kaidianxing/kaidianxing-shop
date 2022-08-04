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

class CopyrightController extends KdxAdminApiController
{
    /**
     * 获取设置
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGet()
    {
        $data = ShopSettings::get('pc.sysset.copyright');

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
            'navigation_1' => RequestHelper::post('navigation_1', ''), // 自定义导航1
            'navigation_1_url' => RequestHelper::post('navigation_1_url', ''), // 自定义导航1-跳转链接
            'navigation_2' => RequestHelper::post('navigation_2', ''), // 自定义导航2
            'navigation_2_url' => RequestHelper::post('navigation_2_url', ''), // 自定义导航2-跳转链接
            'copyright_status' => RequestHelper::post('copyright_status', '1'), // 底部版权状态
            'copyright_info' => RequestHelper::post('copyright_info', ''), // 底部版权状态
        ];
        try {
            ShopSettings::set('pc.sysset.copyright', $data);

            // 记录日志
            LogModel::write(
                $this->userId,
                PcLogConstant::PC_SYSSET_COPYRIGHT,
                PcLogConstant::getText(PcLogConstant::PC_SYSSET_COPYRIGHT),
                '0',
                [
                    'log_data' => $data,
                    'log_primary' => [
                        '自定义导航1' => $data['navigation_1'],
                        '自定义导航1-跳转链接' => $data['navigation_1_url'],
                        '自定义导航2' => $data['navigation_2'],
                        '自定义导航2-跳转链接' => $data['navigation_2_url'],
                        '底部版权' => $data['copyright_status'] == 1 ? '开启' : '关闭',
                        '底部版权状态' => $data['copyright_info'],
                    ],
                    'dirty_identify_code' => [
                        PcLogConstant::PC_SYSSET_COPYRIGHT
                    ]
                ]
            );

        } catch (Exception $exception) {
            throw new PcException(PcException::COPYRIGHT_SAVE_FAIL);
        }

        return $this->success();
    }
}
