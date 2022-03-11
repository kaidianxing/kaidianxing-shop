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
use shopstar\components\amap\AmapClient;
use shopstar\helpers\MathHelper;
use shopstar\helpers\RequestHelper;
use shopstar\constants\diypage\DiypageTypeConstant;
use shopstar\models\diypage\DiypageModel;

/**
 * 装修页面
 * Class PageController
 * @package apps\diypage\client
 */
class PageController extends BaseMobileApiController
{

    /**
     * @var string[] 允许不登录访问的Actions
     */
    public $configActions = [
        'allowNotLoginActions' => [
            'get',
            'get-distance',
        ]
    ];

    /**
     * 获取页面
     * @return array|int[]|\yii\web\Response
     * @throws \shopstar\exceptions\member\MemberException
     * @author likexin
     */
    public function actionGet()
    {

        $get = RequestHelper::get();

        $id = $get['id'] ?? 0;

        $type = (int)$get['type'] ?? DiypageTypeConstant::TYPE_HOME;

        $memberLat = $get['lat'] ?? 0;

        $memberlng = $get['lng'] ?? 0;

        // 读取缓存数据
        $result = DiypageModel::getCachePage([
            'id' => $id,
            'type' => $type,
            'member_id' => $this->memberId,
            'member_level_id' => (int)$this->member['level_id'],
            'member_address' => [
                'lat' => $memberLat ?? 0,
                'lng' => $memberlng ?? 0,
            ],
        ]);

        return $this->result($result);
    }

}