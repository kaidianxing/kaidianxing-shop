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

namespace shopstar\admin\sysset\mall;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\log\sysset\MallLogConstant;
use shopstar\constants\SyssetTypeConstant;
use shopstar\exceptions\sysset\MallException;
use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\models\shop\ShopSettings;
use yii\db\Exception;
use yii\web\Response;

/**
 * 分享设置
 * Class ShareController
 * @package shop\manage\sysset
 */
class ShareController extends KdxAdminApiController
{
    public $configActions = [
        'postActions' => [
            'edit',
        ]
    ];

    /**
     * 商城分享设置
     * @return Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $data = ShopSettings::get('sysset.mall.share');

        return $this->success($data);
    }

    /**
     * 修改商城分享设置
     * @return Response
     * @throws MallException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit()
    {
        $data = [
            'title_type' => RequestHelper::post('title_type', '1'), // 分享标题类型
            'title' => RequestHelper::post('title', ''), // 分享标题
            'logo_type' => RequestHelper::post('logo_type', '1'), // 分享图标类型
            'logo' => RequestHelper::post('logo', ''), // 分享图标
            'share_description_type' => RequestHelper::post('share_description_type', '1'), // 分享描述类型
            'share_description' => RequestHelper::post('share_description', ''), // 分享描述
            'link_type' => RequestHelper::post('link_type', '1'), // 分享链接类型
            'link' => RequestHelper::post('link', ''), // 分享链接
        ];

        // 验证
        // 自定义标题
        if ($data['title_type'] == SyssetTypeConstant::CUSTOMER_SHARE_TITLE && empty($data['title'])) {
            throw new MallException(MallException::SHARE_CUSTOMER_TITLE_NOT_EMPTY);
        }

        // 自定义logo
        if ($data['logo_type'] == SyssetTypeConstant::CUSTOMER_SHARE_LOGO && empty($data['logo'])) {
            throw new MallException(MallException::SHARE_CUSTOMER_LOGO_NOT_EMPTY);
        }

        // 自定义跳转链接
        if ($data['link_type'] == SyssetTypeConstant::CUSTOMER_SHARE_LINK && empty($data['link'])) {
            throw new MallException(MallException::SHARE_CUSTOMER_LINK_NOT_EMPTY);
        }

        // 自定义描述
        if ($data['share_description_type'] == SyssetTypeConstant::CUSTOMER_SHARE_DESCRIPTION && empty($data['share_description'])) {
            throw new MallException(MallException::SHARE_CUSTOMER_DESCRIPTION_NOT_EMPTY);
        }

        try {
            ShopSettings::set('sysset.mall.share', $data);

            // 记录日志
            LogModel::write(
                $this->userId,
                MallLogConstant::MALL_SHARE_SET,
                MallLogConstant::getText(MallLogConstant::MALL_SHARE_SET),
                '0',
                [
                    'log_data' => $data,
                    'log_primary' => [
                        '分享标题类型' => $data['title_type'] == 1 ? '商城名称' : '自定义名称', // 分享标题类型
                        '自定义标题' => $data['title'], // 分享标题
                        '分享图标类型' => $data['logo_type'] == 1 ? '商城LOGO' : '自定义图标', // 分享图标类型
                        '自定义图标' => $data['logo'], // 分享图标
                        '分享描述类型' => $data['share_description_type'] == 1 ? '商城默认描述' : '自定义描述', // 分享描述类型
                        '自定义分享描述' => $data['share_description'], // 分享描述
                        '分享链接类型' => $data['link_type'] == 1 ? '商城首页' : '自定义跳转链接', // 分享链接类型
                        '分享链接' => $data['link'], // 分享链接
                    ],
                    'dirty_identify_code' => [
                        MallLogConstant::MALL_SHARE_SET
                    ]
                ]
            );
        } catch (Exception $exception) {
            throw new MallException(MallException::SHARE_SAVE_FAIL);
        }

        return $this->success();
    }

}
