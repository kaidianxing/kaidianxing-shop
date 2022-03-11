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

namespace shopstar\models\core;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\helpers\HttpHelper;
use shopstar\models\shop\ShopSettings;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%core_express}}".
 *
 * @property int $id
 * @property string $name 物流公司名称
 * @property string $code 代码
 * @property string $key 快递编号
 * @property string $ali_key 阿里云快递接口编号
 * @property string $r_datas 模板样式
 */
class CoreExpressModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%core_express}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['r_datas'], 'required'],
            [['name', 'code', 'key', 'ali_key'], 'string', 'max' => 50],
            [['r_datas'], 'string', 'max' => 1000],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '物流公司名称',
            'code' => '代码',
            'key' => '快递编号',
            'ali_key' => '阿里云快递接口编号',
            'r_datas' => '模板样式',
        ];
    }

    /**
     * 物流运输状态
     * @var array
     */
    public static $expressStateText = [
        0 => '在途',
        1 => '揽件',
        2 => '疑难',
        3 => '签收',
        4 => '退签',
        5 => '派件',
        6 => '退回',
    ];

    /**
     * 物流查询
     * @param $expressSn //物流单号
     * @param $expressCode //物流公司代码
     * @param $expressEncoding //快递公司代码
     * @param $options //附加
     * @return array|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function queryExpress($expressSn, $expressCode, $expressEncoding = '', $options = [])
    {
        $options = array_merge([
            'buyer_mobile' => 0
        ], $options);

        //物流接口配置
        $expressSet = ShopSettings::get('sysset.express.set');
        //物流信息
        $expressData = [];
        if ($expressSet['express_type'] == 0) {
            $expressData = static::expressBirdQuery($expressSet['bird_set'], $expressSn, $expressCode, $expressEncoding, $options);
        } elseif ($expressSet['express_type'] == 1) {
            $expressData = static::expressHundredQuery($expressSet['one_hundred_set'], $expressSn, $expressCode);
        } elseif ($expressSet['express_type'] == 2) {
            $expressData = static::aliExpressQuery($expressSet['aliyun_set'], $expressSn, $expressCode, $options);
        }

        return $expressData;
    }

    /**
     * 处理物流信息时间格式
     * @param array $express
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function decodeExpressDate(array $express)
    {
        if (is_error($express) || empty($express['data'])) {
            return null;
        }

        //处理物流排序
        if (!empty($express['data'])) {
            array_multisort(array_column($express['data'], 'time'), SORT_DESC, $express['data']);
        }

        $express['state_text'] = static::$expressStateText[$express['state']];
        $express['data'] = array_map(function ($row) {
            unset($row['ftime'], $row['location']);
            $time = strtotime($row['time']);
            $row['date_time'] = $row['time'];
            $row['short_date'] = date('m-d', $time);
            $row['time'] = date('H:i', $time);
            return $row;
        }, $express['data']);
        return $express;
    }

    /**
     * 获取所有物流公司
     * @param bool $indexById 以id为下标
     * @param bool $refresh 刷新缓存
     * @return array|mixed|string|\yii\db\ActiveRecord[]
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function getAll($indexById = true, $refresh = false)
    {
        $express = CoreSettings::get('express');
        if (empty($express) || $refresh) {
            $express = self::find()->asArray()->all();
            $end = array_pop($express);
            array_unshift($express, $end);
            $express = array_column($express, null, 'id');
            CoreSettings::set('express', $express);
        }

        if (!$indexById) {
            $express = array_values($express);
        }

        return $express;
    }

    /**
     * 根据id获取物流公司名称
     * @param $id
     * @return string
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function getNameById($id)
    {
        $express = self::getAll();
        return $express[$id]['name'] ?: '';
    }

    /**
     * 根据标识获取物流公司名称
     * @param $code
     * @return string
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function getNameByCode($code)
    {
        $express = self::getAll();
        $express = array_column($express, null, 'key');
        return $express[$code]['name'];
    }

    /**
     * 根据物流公司标识获取物流公司id
     * @param $code
     * @return string
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function getIdByCode($code)
    {
        $express = self::getAll();
        $express = array_column($express, null, 'key');
        return $express[$code]['id'];
    }

    /**
     * 根据id获取物流公司代码
     * @param $id
     * @return string
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function getCodeById($id)
    {
        $express = self::getAll();
        return $express[$id]['code'] ?: '';
    }

    /**
     * 根据id获取物流公司信息
     * @param $id
     * @return array
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function getExpressById($id)
    {
        $express = self::getAll();
        return $express[$id] ?: [];
    }

    private static function cacheQuery($cacheTime, $expressSn, $expressCode)
    {
        if (!empty($cacheTime) && $cacheTime > 0) {
            // 查询缓存 如果缓存中有&&没过期直接使用
            $cacheData = CoreExpressCacheModel::find()->where(['express_sn' => $expressSn, 'express_code' => $expressCode])->one();
            return $cacheData;
        }
        return [];
    }

    /**
     *  快递100查询
     * @param array $expressSet 物流查询设置
     * @param string $expressSn 快递单号
     * @param string $expressCode 物流公司代码
     * @return array $expressList  物流信息列表
     * @author 青岛开店星信息技术有限公司
     */
    public static function expressHundredQuery($expressSet, $expressSn, $expressCode)
    {
        //是否使用接口
        $useApi = true;

        //快递100使用免费接口或者企业接口
        if ($expressSet['is_open'] == 0 || $expressSet['is_open'] == 1) {
            $cacheData = self::cacheQuery($expressSet['cache'], $expressSn, $expressCode);
            // 判断缓存是否过期可用
            if (!empty($cacheData) && (strtotime($cacheData->last_time) + ($expressSet['express_bird_cache'] * 60)) >= time()) {
                return Json::decode($cacheData['express_data'], true);
            }

            //免费
            if ($expressSet['is_open'] == 1) {
                $url = "http://api.kuaidi100.com/api?id={$expressSet['apikey']}&com={$expressCode}&num={$expressSn}";
                $params = array();
            } else {
                if (empty($expressSet['customer']) || empty($expressSet['apikey'])) {
                    return error('参数配置错误');
                }
                $url = "http://poll.kuaidi100.com/poll/query.do";
                $params = array('customer' => $expressSet['customer'], 'param' => json_encode(array('com' => $expressCode, 'num' => $expressSn)));
                $params['sign'] = md5($params["param"] . $expressSet['apikey'] . $params["customer"]);
                $params['sign'] = strtoupper($params["sign"]);
            }
            $response = HttpHelper::post($url, $params);
            $expressData = Json::decode($response, true);

        }

        //物流信息
        $expressList = array();

        if (!empty($expressData['data']) && is_array($expressData['data'])) {
            $expressList['state'] = $expressData['state'];
            foreach ($expressData['data'] as $index => $data) {
                $expressList['data'][] = array(
                    'time' => trim($data['time']),
                    'step' => trim($data['context'])
                );
            }
        }

        //使用了接口并且设置了缓存时间则更新缓存
        if ($useApi && $expressSet['cache'] > 0 && !empty($expressList)) {
            // 更新缓存
            if (empty($cacheData)) {
                $cacheData = new CoreExpressCacheModel();
                $cacheData->express_sn = $expressSn;
                $cacheData->express_code = $expressCode;
                $cacheData->express_data = Json::encode($expressList);
                $cacheData->last_time = date('Y-m-d H:i:s', time());
                if (!$cacheData->save()) {
                    return error('物流缓存更新失败');
                }
            } else {
                $cacheData->express_data = Json::encode($expressList);
                $cacheData->last_time = date('Y-m-d H:i:s', time());
                if (!$cacheData->save()) {
                    return error('物流缓存更新失败');
                }
            }
        }

        return $expressList;
    }

    /**
     * 快递鸟查询快递
     * @param array $expressSet 物流查询设置
     * @param string $expressSn 快递单号
     * @param string $expressCode 物流公司代码
     * @param string $expressEncoding 快递公司编码
     * @param array $options 快递公司编码
     * @return array $expressList  物流信息列表
     * @author 青岛开店星信息技术有限公司
     */
    public static function expressBirdQuery($expressSet, $expressSn, $expressCode, $expressEncoding, $options = [])
    {

        $options = array_merge([
            'buyer_mobile' => 0
        ], $options);

        if (empty($expressSet['express_bird_user_id']) || empty($expressSet['express_bird_apikey'])) {
            return error('参数配置错误');
        }

        $cacheData = self::cacheQuery($expressSet['express_bird_cache'], $expressSn, $expressCode);
        // 判断缓存是否过期可用
        if (!empty($cacheData) && (strtotime($cacheData->last_time) + ($expressSet['express_bird_cache'] * 60)) >= time()) {
            return Json::decode($cacheData['express_data'], true);
        }

        $url = 'http://api.kdniao.com/Ebusiness/EbusinessOrderHandle.aspx'; //正式

        $requestData = [
            'ShipperCode' => $expressEncoding,
            'LogisticCode' => $expressSn,
        ];

        //京东快递添加商户编码
        if ($expressEncoding == 'JD') {
            if (empty($expressSet['express_bird_customer_name'])) {
                return error('商户编码配置错误');
            }
            $requestData['CustomerName'] = $expressSet['express_bird_customer_name'];
        }

        if ($expressEncoding == 'SF') {
            if (!empty($options['buyer_mobile'])) {
                $requestData['CustomerName'] = substr($options['buyer_mobile'], -4);
            }
        }

        $requestData = Json::encode($requestData);

        $datas = array(
            'EBusinessID' => $expressSet['express_bird_user_id'],
            'RequestType' => isset($expressSet['api_type']) ? ($expressSet['api_type'] == 1 ? 1002 : 8001) : 1002, // 企业级 or 免费 默认走免费
            'RequestData' => urlencode($requestData),
            'DataType' => '2',
        );

        //获取签名
        $datas['DataSign'] = urlencode(base64_encode(md5($requestData . $expressSet['express_bird_apikey'])));
        $response = HttpHelper::post($url, $datas);
        if (is_error($response)) {
            return error($response['message']);
        }
        $expressData = Json::decode($response, true);
        //物流信息
        $expressList = array();

        if (!empty($expressData['Traces']) && is_array($expressData['Traces'])) {
            $expressList['state'] = $expressData['State'];
            foreach ($expressData['Traces'] as $index => $data) {
                $expressList['data'][] = array(
                    'time' => trim($data['AcceptTime']),
                    'step' => trim($data['AcceptStation'])
                );
            }
        }

        if ($expressSet['express_bird_cache'] > 0 && !empty($expressList)) {
            // 更新缓存
            if (empty($cacheData)) {
                $cacheData = new CoreExpressCacheModel();
                $cacheData->express_sn = $expressSn;
                $cacheData->express_code = $expressCode;
                $cacheData->express_data = Json::encode($expressList);
                $cacheData->last_time = date('Y-m-d H:i:s', time());
                if (!$cacheData->save()) {
                    return error('物流缓存更新失败');
                }
            } else {
                $cacheData->express_data = Json::encode($expressList);
                $cacheData->last_time = date('Y-m-d H:i:s', time());
                if (!$cacheData->save()) {
                    return error('物流缓存更新失败');
                }
            }
        }

        return $expressList;
    }

    /**
     * 阿里快递查询
     * @param array $expressSet 物流查询设置
     * @param string $expressSn 快递单号
     * @param string $expressCode 物流公司代码
     * @param array $options
     * @return array|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function aliExpressQuery($expressSet, $expressSn, $expressCode, $options = [])
    {
        if (empty($expressSet['aliapp_code'])) {
            return error('参数配置错误');
        }

        //缓存查询
        $cacheData = self::cacheQuery($expressSet['aliyun_catch'], $expressSn, $expressCode);
        // 判断缓存是否过期可用
        if (!empty($cacheData) && (strtotime($cacheData->last_time) + ($expressSet['express_bird_cache'] * 60)) >= time()) {
            return Json::decode($cacheData['express_data'], true);
        }

        $expressData = self::find()->where(['code' => $expressCode])->asArray()->one();
        if (empty($expressData) || empty($expressData['ali_key'])) {
            return error('无效的快递编码');
        }

        if ($expressData['ali_key'] == 'SF') {
            $expressSn .= ':' . substr($options['buyer_mobile'], -4);
        }

        $appcode = $expressSet['aliapp_code'];
        $url = "http://wdexpress.market.alicloudapi.com/gxali";
        $params = [
            'headers' => ['Authorization' => 'APPCODE ' . $appcode],
        ];

        $url = $url . "?n={$expressSn}&t={$expressData['ali_key']}";
        try {
            $response = HttpHelper::get($url, $params);
        } catch (\Throwable $throwable) {
            return [];
        }

        if (!empty($response['error'])) {
            return [];
        }

        $expressData = Json::decode($response, true);

        //物流信息
        $expressList = [];

        if (!empty($expressData['Traces']) && is_array($expressData['Traces'])) {

            $expressList['state'] = $expressData['State'];
            foreach ($expressData['Traces'] as $index => $data) {
                $expressList['data'][] = array(
                    'time' => trim($data['AcceptTime']),
                    'step' => trim($data['AcceptStation'])
                );
            }
            $expressList = array_reverse($expressList);
        }

        //使用了接口并且设置了缓存时间则更新缓存
//        if ($expressSet['data_cache_time'] > 0 && !empty($expressList)) {
//            // 更新缓存
//            if (empty($cacheData)) {
//                $cacheData = new CoreExpressCacheModel();
//                $cacheData->express_sn = $expressSn;
//                $cacheData->express_code = $expressCode;
//                $cacheData->express_data = Json::encode($expressList);
//                $cacheData->last_time = date('Y-m-d H:i:s', time());
//                if (!$cacheData->save()) {
//                    return error('物流缓存更新失败');
//                }
//            } else {
//                $cacheData->express_data = Json::encode($expressList);
//                $cacheData->last_time = date('Y-m-d H:i:s', time());
//                if (!$cacheData->save()) {
//                    return error('物流缓存更新失败');
//                }
//            }
//        }

        return $expressList;
    }
}
