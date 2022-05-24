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

namespace shopstar\admin\wxTransactionComponent;

use shopstar\bases\KdxAdminApiController;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\ShopUrlHelper;
use shopstar\helpers\StringHelper;
use shopstar\models\member\MemberModel;
use shopstar\models\shop\ShopSettings;
use yii\web\Response;

/**
 * 基础设置
 * Class SettingsController.
 * @package shopstar\admin\wxTransactionComponent
 */
class SettingsController extends KdxAdminApiController
{
    /**
     * 获取token
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetUrl()
    {
        $result = ShopSettings::get('wxTransactionComponent.bases');
        $data = [
            'url' => ShopUrlHelper::wap('/api/notify/wx-app/index', [], true), // 可能存在内外网地址不同的问题,所以地址需要实时获取
            'token' => $result['token'] ?: StringHelper::random(32),
            'encoding_aes_key' => $result['encoding_aes_key'] ?: StringHelper::random(43),
            'encryption_type' => 3, // 默认走安全模式
        ];

        $result = ShopSettings::set('wxTransactionComponent.bases', $data);

        if (is_error($result)) {
            return $this->error('保存失败');
        }

        unset($data['encryption_type']);
        return $this->success(['data' => $data]);
    }

    /**
     * 获取定向设置
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDevelopmentGet()
    {
        $result = ShopSettings::get('wxTransactionComponent.development');
        $result['memberList'] = MemberModel::find()->where(['id' => $result['member_id']])->select('avatar,id,nickname,source')->first();

        return $this->success(['data' => $result]);
    }

    /**
     * 设置定向设置
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDevelopmentSet()
    {
        $data = [
            'member_id' => RequestHelper::postInt('member_id', 0),
        ];

        $result = ShopSettings::set('wxTransactionComponent.development', $data);

        if (is_error($result)) {
            return $this->error('保存失败');
        }

        return $this->success();
    }
}
