<?php

namespace shopstar\bases\controller;

use shopstar\exceptions\creditSign\CreditSignException;
use shopstar\services\creditSign\CreditSignActivityService;

/**
 * 积分签到移动端基类
 * Class BaseCreditSignMobileApiController
 * @package shopstar\bases\controller
 * @author yuning
 */
class BaseCreditSignMobileApiController extends BaseMobileApiController
{

    public function beforeAction($action)
    {
        parent::beforeAction($action);

        // 获取正在进行中的数据
        $activityInfo = CreditSignActivityService::getActivityOne();
        if (is_error($activityInfo)) {
            throw new CreditSignException(CreditSignException::CREDIT_SIGN_ACTIVITY_NOT_ERROR);
        }
        return true;
    }
}