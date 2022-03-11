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

namespace shopstar\admin\notice;

use shopstar\components\wechat\helpers\OfficialAccountMessageHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use GuzzleHttp\Exception\GuzzleException;
use shopstar\constants\notice\NoticeLogConstant;
use shopstar\models\notice\NoticeWechatTemplateModel;
use shopstar\bases\KdxAdminApiController;
use yii\helpers\Json;

class WechatTemplateController extends KdxAdminApiController
{

    public $configActions = [
        'postActions' => [
            'add-template',
            'delete',
        ]
    ];

    /**
     * 获取微信模板列表
     * @return string
     * @throws GuzzleException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        // 获取列表
        $list = OfficialAccountMessageHelper::getPrivateTemplates();
        
        $industry = OfficialAccountMessageHelper::getIndustry();

        // 定义数组
        $list['industry'] = [];

        foreach ($industry as $item) {
            if (!empty($item['first_class'])) {
                $list['industry'][] = $item['first_class'].' / '.$item['second_class'];
            }
        }
        
        return $this->result($list);
    }

    /**
     * 根据模板id添加到微信
     * @return array|int[]|\yii\web\Response
     * @throws GuzzleException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAddTemplate()
    {
        $id = RequestHelper::post('id');
        if (empty($id)) {
            return $this->error('模板id不能为空');
        }

        $result = OfficialAccountMessageHelper::addTemplate($id);
        // 日志
        if (!is_error($result)) {
            LogModel::write(
                $this->userId,
                NoticeLogConstant::NOTICE_TEMPLATE_WECHAT_ADD,
                NoticeLogConstant::getText(NoticeLogConstant::NOTICE_TEMPLATE_WECHAT_ADD),
                $result['template_id'],
                [
                    'log_data' => [
                        'template_id_short' => $id,
                        'template_id' => $result['template_id']
                    ],
                    'log_primary' => [
                        '模板编号' => $id,
                        '模板ID' => $result['template_id']
                    ]
                ]
            );
        }

        return $this->result($result);
    }

    /**
     * 删除/批量删除模板
     * @return \yii\web\Response
     * @throws GuzzleException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $templateId = RequestHelper::post('id');
        if (empty($templateId)) {
            $templateIds = RequestHelper::post('ids');
            if (is_array($templateIds)) {
                foreach ($templateIds as $value) {
                    OfficialAccountMessageHelper::deletePrivateTemplate($value);
                }

                //删除消息通知模板
                NoticeWechatTemplateModel::deleteAll(['template_id' => $templateIds]);

                // 日志
                LogModel::write(
                    $this->userId,
                    NoticeLogConstant::NOTICE_TEMPLATE_WECHAT_BATCH_DEL,
                    NoticeLogConstant::getText(NoticeLogConstant::NOTICE_TEMPLATE_WECHAT_BATCH_DEL),
                    0,
                    [
                        'log_data' => ['template_id' => Json::encode($templateIds)],
                        'log_primary' => [
                            '模板ID' => implode('、', $templateIds)
                        ]
                    ]
                );
            }
        } else {
            OfficialAccountMessageHelper::deletePrivateTemplate($templateId);
            NoticeWechatTemplateModel::deleteAll(['template_id' => $templateId]);

            // 日志
            LogModel::write(
                $this->userId,
                NoticeLogConstant::NOTICE_TEMPLATE_WECHAT_DEL,
                NoticeLogConstant::getText(NoticeLogConstant::NOTICE_TEMPLATE_WECHAT_DEL),
                0,
                [
                    'log_data' => ['template_id' => $templateId],
                    'log_primary' => [
                        '模板ID' => $templateId
                    ]
                ]
            );
        }

        return $this->success();
    }

    /**
     * 查看模板详情
     * @throws GuzzleException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDetail()
    {
        $templateId = RequestHelper::get('template_id');
        if (empty($templateId)) {
            return $this->error('模板id不能为空');
        }

        $list = OfficialAccountMessageHelper::getPrivateTemplates();

        if (is_error($list)){
            return $this->error($list['message']);
        }

        $detail = [];
        foreach ($list['template_list'] as $item) {
            if ($item['template_id'] == $templateId) {
                $detail = $item;
            }
        }
        return $this->result($detail);
    }

}
