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

namespace shopstar\admin\sysset;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\log\sysset\ExpressLogConstant;
use shopstar\exceptions\sysset\ExpressException;
use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\models\shop\ShopSettings;
use yii\db\Exception;

/**
 * 物流信息配置
 * Class ExpressController
 * @package shopstar\admin\sysset\express
 * @author 青岛开店星信息技术有限公司
 */
class ExpressController extends KdxAdminApiController
{

    /**
     * @var array
     */
    public $configActions = [
        'postActions' => [
            'edit',
        ],
        'allowPermActions' => [
            'dispatch-enable'
        ]
    ];

    /**
     * 获取物流配置
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $data = ShopSettings::get('sysset.express.set');
        // 适配旧数据 处理null值
        self::process($data);
        return $this->result($data);
    }

    /**
     * 物流信息配置
     * @throws ExpressException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit()
    {
        $data = RequestHelper::post();
        $dataSet = [];
        $dataSet['express_type'] = $data['express_type'];
        // 快递鸟
        $dataSet['bird_set'] = [
            'api_type' => $data['api_type'] ?? 1, // 接口类型 1免费 2企业
            'express_bird_user_id' => $data['express_bird_user_id'],
            'express_bird_apikey' => $data['express_bird_apikey'],
            'express_bird_customer_name' => $data['express_bird_customer_name'],
            'express_bird_cache' => $data['express_bird_cache'],
        ];

        // 快递一百
        $dataSet['one_hundred_set'] = [
            'is_open' => $data['is_open'],
            'apikey' => $data['apikey'],
            'customer' => $data['customer'],
            'cache' => $data['cache'],
        ];

        // 阿里云
        $dataSet['aliyun_set'] = [
            'aliapp_code' => $data['aliapp_code'],
            'aliyun_catch' => $data['aliyun_catch']
        ];

        try {
            ShopSettings::set('sysset.express.set', $dataSet);
            // 日志
            $type = [
                '快递鸟',
                '快递一百',
                '阿里云'
            ];
            LogModel::write(
                $this->userId,
                ExpressLogConstant::EXPRESS_SET_EDIT,
                ExpressLogConstant::getText(ExpressLogConstant::EXPRESS_SET_EDIT),
                '0',
                [
                    'log_data' => $dataSet,
                    'log_primary' => [
                        '类型选择' => $type[$dataSet['express_type']],
                        '快递鸟' => [
                            '用户ID' => $dataSet['bird_set']['express_bird_user_id'] ?: '-',
                            'API Key' => $dataSet['bird_set']['express_bird_apikey'] ?: '-',
                            '数据缓存时间' => $dataSet['bird_set']['express_bird_cache'] ? $dataSet['bird_set']['express_bird_cache'] . ' 分钟' : '-',
                            '京东商家编码' => $dataSet['bird_set']['express_bird_customer_name'],
                        ],
                        '快递100' => [
                            '接口类型' => $dataSet['one_hundred_set']['is_open'] == 1 ? '免费接口' : '企业接口',
                            '授权密钥' => $dataSet['one_hundred_set']['apikey'] ?: '-',
                            '数据缓存时间' => $dataSet['one_hundred_set']['cache'] ? $dataSet['one_hundred_set']['cache'] . ' 分钟' : '-',
                            '公司编号(Customer)' => $dataSet['one_hundred_set']['customer'] ?: '-',
                        ],
                        '阿里云' => [
                            '阿里云APPCODE' => $dataSet['aliyun_set']['aliapp_code'] ?: '-',
                            '数据缓存时间' => $dataSet['aliyun_set']['aliyun_catch'] ? $dataSet['aliyun_set']['aliyun_catch'] . ' 分钟' : '-',
                        ]
                    ],
                    'dirty_identify_code' => [
                        ExpressLogConstant::EXPRESS_SET_EDIT,
                    ],
                ]
            );

        } catch (Exception $exception) {
            throw new ExpressException(ExpressException::EXPRESS_SAVE_FAIL);
        }

        return $this->success();
    }

    /**
     * 获取配送方式开启状态
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDispatchEnable()
    {
        $selffetch = ShopSettings::get('dispatch.selffetch.enable');
        $express = ShopSettings::get('dispatch.express.enable');
        $intracity = ShopSettings::get('dispatch.intracity.enable');
        $verify = ShopSettings::get('verify.base_setting.verify_is_open');


        $intracityDeliveryTime = ShopSettings::get('dispatch.intracity.delivery_time');
        $verifyDeliveryTime = ShopSettings::get('verify.base_setting.delivery_time');


        return $this->result([
            'data' => [
                'selffetch' => $selffetch ?? 0,
                'express' => $express,
                'intracity' => $intracity,
                'verify' => (int)$verify,
                'delivery_time_switch' => [        //增加时间返回
                    'intracity_delivery_time' => $intracityDeliveryTime,
                    'verify_delivery_time' => $verifyDeliveryTime,
                ]
            ]
        ]);
    }

    /**
     * 适配旧数据 处理null值
     * @param $data
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public static function process(&$data)
    {
        if ($data) {
            foreach ($data as $key => &$value) {
                if ($key == 'express_type') {
                    continue;
                }
                if (in_array('null', $value)) {
                    if (is_array($value)) {
                        foreach ($value as &$item) {
                            $item = str_replace('null', '', $item);
                        }
                    }
                }
            }
        }
    }
    
}
