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

use shopstar\helpers\RequestHelper;
use shopstar\models\member\MemberModel;
use shopstar\exceptions\commission\CommissionSetException;
use shopstar\models\commission\CommissionSettings;
use shopstar\bases\KdxAdminApiController;

/**
 * 通知设置
 * Class NoticeController
 * @package apps\commission\manage\settings
 */
class NoticeController extends KdxAdminApiController
{

    /**
     * @var string[] 需要POST请求的Actions
     */
    public $configActions = [
        'postActions' => [
            'set',
        ]
    ];

    /**
     * 获取设置
     * @return array|int[]|\yii\web\Response
     * @author likexin
     */
    public function actionGet()
    {
        $settings = CommissionSettings::get('notice');

        // 申请成为分销商 微信模板消息
        if (!empty($settings['seller']['apply']['template']['member_id']) && is_array($settings['seller']['apply']['template']['member_id'])) {
            $settings['seller']['apply']['template']['member_info'] = MemberModel::find()
                ->select('id, nickname, avatar, source')
                ->where(['in', 'id', $settings['seller']['apply']['template']['member_id']])
                ->get();
        }

        // 分销商提现通知
        if (!empty($settings['seller']['withdraw']['template']['member_id']) && is_array($settings['seller']['withdraw']['template']['member_id'])) {
            $settings['seller']['withdraw']['template']['member_info'] = MemberModel::find()
                ->select('id, nickname, avatar, source')
                ->where(['in', 'id', $settings['seller']['withdraw']['template']['member_id']])
                ->get();
        }

        return $this->result([
            'settings' => $settings,
            'template_list' => [], // 模板消息
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
        $data = RequestHelper::post();

        try {
            CommissionSettings::set('notice', $data);
        } catch (\Exception $exception) {
            throw new CommissionSetException(CommissionSetException::NOTICE_SET_SAVE_FAIL);
        }

        return $this->success();
    }

}