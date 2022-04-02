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

namespace shopstar\mobile\notify;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\helpers\FileHelper;
use yii\helpers\Json;

/**
 * 第三方配送回调
 * Class DispatchController
 * @package shop\client\notify
 * @author 青岛开店星信息技术有限公司
 */
class DispatchController extends BaseMobileApiController
{
    /**
     * @var array
     */
    public $configActions = [
        'allowSessionActions' => ['*'],
        'allowActions' => ['*'],
        'allowClientActions' => ['*'],
        'allowShopCloseActions' => ['index'],
    ];

    /**
     * @return bool
     * @throws \yii\base\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex(): bool
    {
        FileHelper::createDirectory(SHOP_STAR_TMP_PATH . '/logs/');
        $notify = file_get_contents('php://input');

        file_put_contents(SHOP_STAR_TMP_PATH . '/logs/dispatch_notify_' . date('Y-m-d') . '.log', date('Y-m-d H:i:s') . ' > ' . $notify . PHP_EOL, FILE_APPEND);

        $notifyArr = Json::decode($notify);

        return true;
    }


}