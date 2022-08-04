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

namespace shopstar\admin\pc;

use shopstar\bases\KdxAdminApiController;
use shopstar\exceptions\pc\PcException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\pc\PcHomeAdvertiseModel;
use Throwable;
use yii\db\StaleObjectException;
use yii\web\Response;

class HomeAdsController extends KdxAdminApiController
{
    /**
     * 获取广告列表
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionList()
    {
        $list = PcHomeAdvertiseModel::getColl([
            'orderBy' => [
                'sort_order' => SORT_DESC
            ],
        ]);

        return $this->result($list);
    }

    /**
     * 获取广告详情
     * @return array|int[]|Response
     * @throws PcException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDetail()
    {
        $id = RequestHelper::getInt('id');
        if (!$id) {
            throw new PcException(PcException::HOME_ADVERTISE_ID_EMPTY);
        }

        $one = PcHomeAdvertiseModel::find()->select(['id',
            'name',
            'sort_order',
            'url',
            'img'
        ])->where(['id' => $id])->one();

        return $this->result(['data' => $one]);
    }

    /**
     * 添加广告
     * @return array|int[]|Response
     * @throws PcException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAdd()
    {
        $count = PcHomeAdvertiseModel::getColl([], ['onlyCount' => true]);

        if ((int)$count > 20) {
            throw new PcException(PcException::HOME_ADVERTISE_MAX_COUNT_20);
        }

        $result = PcHomeAdvertiseModel::easyAdd([
            'attributes' => [
                'created_at' => DateTimeHelper::now(),
            ],
        ]);

        return $this->result($result);
    }

    /**
     * 编辑广告
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit()
    {
        $result = PcHomeAdvertiseModel::easyEdit([
            'attributes' => [
                'updated_at' => DateTimeHelper::now(),
            ],
        ]);

        return $this->result($result);
    }

    /**
     * 删除广告
     * @return array|int[]|Response
     * @throws Throwable
     * @throws StaleObjectException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $result = PcHomeAdvertiseModel::easyDelete([]);

        return $this->result($result);
    }
}
