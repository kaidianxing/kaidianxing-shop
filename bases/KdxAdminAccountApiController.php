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

namespace shopstar\bases;

use shopstar\bases\controller\BaseApiController;
use shopstar\traits\UserTrait;

/**
 * Class KdxAdminAccountApiController
 * @package shopstar\bases
 * @author 青岛开店星信息技术有限公司
 */
class KdxAdminAccountApiController extends BaseApiController
{

    /**
     * 应用Trait
     */
    use UserTrait;

    /**
     * @param \yii\base\Action $action
     * @return bool
     * @throws \shopstar\bases\exception\BaseApiException
     * @throws \shopstar\exceptions\UserException
     * @throws \yii\web\BadRequestHttpException
     * @author likexin
     */
    public function beforeAction($action)
    {
        // 开发模式
        if (YII_DEBUG) {
            parent::beforeAction($action);
        }

        // 检测客户端类型
        if (!isset($this->configActions['allowClientActions']) || !is_array($this->configActions['allowClientActions'])
            || (!in_array('*', $this->configActions['allowClientActions']) && !in_array($action->id, $this->configActions['allowClientActions']))
        ) {
            $this->checkClientType();
        }

        // 检测SessionId
        if (!isset($this->configActions['allowSessionActions']) || !is_array($this->configActions['allowSessionActions'])
            || (!in_array('*', $this->configActions['allowSessionActions']) && !in_array($action->id, $this->configActions['allowSessionActions']))
        ) {
            $this->checkSession();
        }

        // 检测访问状态
        $this->checkAccess($action, function () {
            // 检测用户(操作员)登录状态、禁用状态
            $this->checkUser();
        });

        return parent::beforeAction($action);
    }

}