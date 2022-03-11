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
use shopstar\bases\exception\BaseApiException;
use shopstar\constants\ClientTypeConstant;
 
use shopstar\traits\PermTrait;
use shopstar\traits\UserTrait;

/**
 * 业务端接口
 * Class KdxAdminApiController
 * @package shop\bases
 */
class KdxAdminApiController extends BaseApiController
{
    /**
     * @var int 店铺类型
     * @author 青岛开店星信息技术有限公司
     */
    public $shopType = 0;

    /**
     * @var array|null 当前店铺的基础信息
     */
    public $shop;

    /**
     * 引用Trait
     */
    use UserTrait, PermTrait;

    /**
     * @param \yii\base\Action $action
     * @return bool
     * @throws \ReflectionException
     * @throws \yii\web\BadRequestHttpException
     * @throws \shopstar\bases\exception\BaseApiException
     * @throws \shopstar\exceptions\UserException
     * @author likexin
     */
    public function beforeAction($action)
    {
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

            // 检测用户登录状态、禁用状态
            $this->checkUser();

            // 检测操作员登录状态
            $this->checkShopManage();

            // 检测权限
            $this->checkPerm();
        });

        return parent::beforeAction($action);
    }

}
