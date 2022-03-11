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

namespace shopstar\traits;

use shopstar\bases\exception\BaseApiException;
use shopstar\components\permission\BasePermissionConfig;
use shopstar\components\permission\Permission;

/**
 * 权限
 * Trait PermTrait
 * @package shopstar\traits
 */
trait PermTrait
{

    /**
     * @var array 不验权的Action(子类可复写，传入*时当前Controller中全部Action都允许)
     */
    // public $allowPermActions = [];

    /**
     * 检测用户权限
     * @throws BaseApiException
     * @author likexin
     */
    public function checkPerm()
    {
        // 如果是店铺超管，直接跳过
        // TODO 调试需要关闭
        if ($this->isShopRoot) {
            return;
        }
        
        // 不验权
        if (isset($this->configActions['allowPermActions']) && is_array($this->configActions['allowPermActions'])) {
            if (in_array('*', $this->configActions['allowPermActions']) || in_array($this->action->id, $this->configActions['allowPermActions'])) {
                return;
            }
        }
        
        // 检测权限
        if (!Permission::check($this)) {
            throw new BaseApiException(-1, "未授权");
        
        }
    
    }

}