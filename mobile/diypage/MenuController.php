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

namespace shopstar\mobile\diypage;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\constants\diypage\DiypageMenuTypeConstant;
use shopstar\helpers\RequestHelper;
use shopstar\models\diypage\DiypageMenuModel;

/**
 * 底部菜单
 * Class MenuController
 * @package shopstar\mobile\diypage
 * @author 青岛开店星信息技术有限公司
 */
class MenuController extends BaseMobileApiController
{

    /**
     * @var string[] 允许不登录访问的Actions
     */
    public $configActions = [
        'allowActions' => [
            'get',
        ]
    ];

    /**
     * 获取菜单
     * @return array|int[]|\yii\web\Response
     * @author likexin
     */
    public function actionGet()
    {
        $id = RequestHelper::getInt('id');
        $type = RequestHelper::getInt('type', DiypageMenuTypeConstant::TYPE_SHOP);

        return $this->result([
            'menu' => DiypageMenuModel::getCacheMenu([
                'id' => $id,
                'type' => $type,
            ])
        ]);
    }

}