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

namespace shopstar\admin\order;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\log\order\DispatchLogConstant;
use shopstar\exceptions\sysset\IntracityException;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\core\CoreAddressModel;
use shopstar\models\log\LogModel;
use shopstar\models\shop\ShopSettings;
use shopstar\services\shop\ShopSettingIntracityLogic;
use yii\base\InvalidConfigException;
use yii\web\Response;

/**
 * 同城配送控制器
 * Class IntracityController.
 * @package shopstar\admin\order
 */
class IntracityController extends KdxAdminApiController
{
    public $configActions = [
        'allowPermActions' => [
            'get',
        ],
    ];

    /**
     * 获取设置
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGet()
    {
        $dispatch = ShopSettingIntracityLogic::get();

        return $this->result($dispatch);
    }

    /**
     * 保存设置
     * @return array|int[]|Response
     * @throws IntracityException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSet()
    {
        $args = RequestHelper::post();

        ShopSettingIntracityLogic::set($args);

        return $this->success();
    }

    /**
     * 修改同城配送开启状态
     * @return array|int[]|Response
     * @throws IntracityException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEnable()
    {
        $args = [
            'enable' => RequestHelper::postInt('enable'),
        ];

        $result = ShopSettingIntracityLogic::enable($args);

        LogModel::write(
            $this->userId,
            DispatchLogConstant::INTRACITY_ENABLE_SETTING,
            DispatchLogConstant::getText(DispatchLogConstant::INTRACITY_ENABLE_SETTING),
            0,
            [
                'log_data' => ['enable' => $args['enable']],
                'log_primary' => [
                    '状态' => $args['enable'] == 1 ? '开启' : '关闭'
                ],
                'dirty_identify_code' => [
                    DispatchLogConstant::INTRACITY_ENABLE_SETTING,
                ]
            ]
        );

        return $this->result($result);
    }

    /**
     * 获取店铺所在地的行政区域
     * @return array|int[]|Response
     * @throws IntracityException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionConfigDistance()
    {
        $cityCode = ShopSettings::get('contact.address.city_code');

        if (empty($cityCode)) {
            throw new IntracityException(IntracityException::SHOP_SETTINGS_CITY_CODE_INVALID);
        }

        //查找下级code名称
        $returnData = CoreAddressModel::getChildCode($cityCode);

        if (empty($returnData)) {
            throw new IntracityException(IntracityException::SHOP_SETTINGS_CITY_CODE_FIND_INVALID);
        }

        return $this->result(['data' => array_values($returnData)]);
    }

    /**
     * 获取达达配送城市code
     * @return array|int[]|Response
     * @throws InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetDadaCity()
    {
        $args = RequestHelper::post();

        $params = ArrayHelper::only($args, [
            'app_key',
            'app_secret',
            'source_id',
            'shop_no'
        ]);

        $result = ShopSettingIntracityLogic::getDadaCity($params);

        if (is_error($result)) {
            return $this->result($result);
        }

        return $this->result(['data' => $result]);
    }
}
