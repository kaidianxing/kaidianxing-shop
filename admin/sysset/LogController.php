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


use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\models\user\UserModel;
use shopstar\bases\KdxAdminApiController;
use yii\helpers\Json;

/**
 * 操作日志
 * Class LogController
 * @author 青岛开店星信息技术有限公司
 * @package shop\manage\sysset
 */
class LogController extends KdxAdminApiController
{
    /**
     * 列表
     * @return \yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionList()
    {
        $list = LogModel::getColl([
            'alias' => 'log',
            'leftJoin' => [UserModel::tableName() . ' user', 'user.id = log.uid'],
            'where' => [],
            'select' => [
                'log.id',
                'log.title',
                'log.created_at',
                'log.uid',
                'log.ip',
                'user.username'
            ],
            'searchs' => [
                ['log.created_at', 'between', 'created_at'],
                ['user.username', 'like', 'name']
            ],
            'orderBy' => ['log.created_at' => SORT_DESC]
        ]);
        return $this->success($list);
    }

    /**
     * 详情
     * @return array|int[]|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDetail()
    {
        $id = RequestHelper::getInt('id');
        $data = LogModel::findOne($id);
        if (empty($data)) {
            return $this->success();
        }

        $data = $data->toArray();
        $data['primary'] = Json::decode($data['primary']);
        $data['dirty_primary'] = Json::decode($data['dirty_primary']);
        $user = UserModel::findOne(['id' => $data['uid']]);
        $data['username'] = $user->username;

        return $this->success($data);
    }
}
