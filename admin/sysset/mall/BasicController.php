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
use shopstar\exceptions\sysset\MallException;
use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\models\shop\ShopSettings;
use yii\db\Exception;
use yii\web\Response;


/**
 * 基础设置
 * Class BasicController
 * @package shopstar\admin\sysset
 * @author 青岛开店星信息技术有限公司
 */
class BasicController extends KdxAdminApiController
{

    /**
     * @var array
     */
    public $configActions = [
        'postActions' => [
            'edit',
        ],
        'allowPermActions' => [
            'index'
        ]
    ];

    /**
     * 获取商城基础设置
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $data = ShopSettings::get('sysset.mall.basic');

        return $this->success($data);
    }

    /**
     * 编辑商城基础设置
     * @return Response
     * @throws MallException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit(): Response
    {
        $data = [
            'mall_status' => RequestHelper::post('mall_status', '1'), // 商城状态
            'mall_close_url' => RequestHelper::post('mall_close_url', ''), // 关闭跳转链接
            'name' => RequestHelper::post('name', ''), // 商城名称
            'logo' => RequestHelper::post('logo', ''), // logo
            'login_show_img' => RequestHelper::post('login_show_img', ''), // 后台 登录页展示图
            'description' => RequestHelper::post('description', ''), // 简介
            'sale_out' => RequestHelper::post('sale_out', ''), // 售罄图标
            'loading' => RequestHelper::post('loading', ''), // 加载图标
            'photo_preview' => RequestHelper::post('photo_preview', ''), // 商城图片预览
            'agreement_name' => RequestHelper::post('agreement_name', ''), // 协议标题
            'agreement_content' => RequestHelper::post('agreement_content', ''), // 协议内容
            'icp_code' => RequestHelper::post('icp_code', ''), // ICP备案号
            'global_code' => RequestHelper::post('global_code', ''), // 全局统计代码
        ];

        try {
            ShopSettings::set('sysset.mall.basic', $data);
            // 记录日志
            LogModel::write(
                $this->userId,
                MallLogConstant::MALL_BASIC_SET,
                MallLogConstant::getText(MallLogConstant::MALL_BASIC_SET),
                '0',
                [
                    'log_data' => $data,
                    'log_primary' => [
                        '商城状态' => $data['mall_status'] == 1 ? '开启' : '关闭',
                        '关闭跳转链接' => $data['mall_close_url'],
                        '商城名称' => $data['name'],
                        '商城LOGO' => $data['logo'],
                        '简介' => $data['description'],
                        '售罄图标' => $data['sale_out'],
                        '加载图标' => $data['loading'],
                        '商城图片预览' => $data['photo_preview'] == 1 ? '开启' : '关闭',
                        '协议标题' => $data['agreement_name'],
                        '协议内容' => $data['agreement_content'],
                    ],
                    'dirty_identify_code' => [
                        MallLogConstant::MALL_BASIC_SET
                    ]
                ]
            );
        } catch (Exception $exception) {
            throw new MallException(MallException::BASIC_SAVE_FAIL);
        }

        return $this->success();
    }

}
