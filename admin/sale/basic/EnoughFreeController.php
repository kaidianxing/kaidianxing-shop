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

namespace shopstar\admin\sale\basic;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\log\sale\BasicLogConstant;
use shopstar\exceptions\sale\BasicException;
use shopstar\helpers\RequestHelper;
use shopstar\models\goods\GoodsModel;
use shopstar\models\log\LogModel;
use shopstar\models\shop\ShopSettings;
use yii\db\Exception;
use yii\helpers\Json;
use yii\web\Response;

/**
 * 满额包邮
 * Class EnoughFreeController
 * @package app\controllers\manage\sale\basic
 */
class EnoughFreeController extends KdxAdminApiController
{

    public $configActions = [
        'allowPermActions' => [
            'index',
        ]
    ];

    /**
     * 满额包邮
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $enoughFree = ShopSettings::get('sale.basic.enough_free');

        if (!empty($enoughFree['goods_ids'])) {
            $enoughFree['goods'] = GoodsModel::find()
                ->select('id, thumb, title, price,type,has_option')
                ->where(['id' => $enoughFree['goods_ids']])
                ->get();
        }

        return $this->success($enoughFree);
    }

    /**
     * 修改满额包邮
     * @return Response
     * @throws BasicException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit()
    {
        $post = RequestHelper::post();
        $data['state'] = $post['state'];
        if ($data['state'] == 1) {
            $data['goods_ids'] = $post['goods_ids'];
            $data['order_enough'] = bcadd($post['order_enough'], 0, 2);
            $data['enough_areas_code'] = $post['enough_areas_code'];
            $data['is_participate'] = $post['is_participate']; // 是否参与包邮的字段 0 不参与
            // 不能小于0
            if (!empty($data['enough_order']) && $data['enough_order'] < 0) {
                throw new BasicException(BasicException::ENOUGH_FREE_MONEY_ERROR);
            }
        }
        // 商品不为空 组装日志数据
        $logGoodsData = [];
        if (!empty($data['goods_ids'])) {
            $goods = GoodsModel::find()
                ->select('id, title')
                ->where(['id' => $data['goods_ids']])
                ->get();
            foreach ($goods as $item) {
                $logGoodsData[] = [
                    '商品id' => $item['id'],
                    '商品标题' => $item['title'],
                ];
            }
        }

        try {
            ShopSettings::set('sale.basic.enough_free', $data);
            // 日志
            $logPrimary['满额包邮'] = $data['state'] == 1 ? '开启' : '关闭';
            if ($data['state'] == 1) {
                $logPrimary['满额包邮设置'] = '单笔订单满 ' . $data['order_enough'] . ' 元包邮';
                $logPrimary['不参与包邮地区'] = Json::decode($data['enough_areas_code'])['text'] ?: '-';
                $logPrimary['不参与包邮的商品'] = $logGoodsData ?: '-';

            }
            LogModel::write(
                $this->userId,
                BasicLogConstant::SALE_ENOUGH_FREE_EDIT,
                BasicLogConstant::getText(BasicLogConstant::SALE_ENOUGH_FREE_EDIT),
                '0',
                [
                    'log_data' => $data,
                    'log_primary' => $logPrimary,
                    'dirty_identity_code' => [
                        BasicLogConstant::SALE_ENOUGH_FREE_EDIT,
                    ]
                ]
            );
        } catch (Exception $exception) {
            throw new BasicException(BasicException::ENOUGH_FREE_SAVE_FAIL);
        }

        return $this->success();
    }

}