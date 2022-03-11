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

namespace shopstar\admin\account;

use shopstar\models\user\UserSession;
use shopstar\bases\KdxAdminAccountApiController;

/**
 * 登录
 * Class IndexController
 * @package modules\account\manage
 */
class IndexController extends KdxAdminAccountApiController
{
    public $configActions = [
        'allowActions' => ['*'], // 允许不登录访问的Actions
        'allowSessionActions' => ['*'],   // 允许不携带Session头访问
    ];
    /**
     * 获取 Session-Id
     * @return array|int[]|\yii\web\Response
     * @author likexin
     */
    public function actionGetSessionId()
    {
        $sessionid = UserSession::createSessionId();

        UserSession::baseSet($sessionid, '', '', 0, [], [
            'client_type' => $this->clientType,
        ]);

        return $this->result([
            'session_id' => $sessionid,
        ]);
    }

    /**
     * 获取运行环境
     * @return array|int[]|\yii\web\Response
     * @author 青岛开店星信息技术有限公司.
     */
    public function actionGetAppEnter()
    {

    }


}