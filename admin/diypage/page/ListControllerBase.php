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

namespace shopstar\admin\diypage\page;

use shopstar\helpers\RequestHelper;
use shopstar\constants\diypage\DiypageTypeConstant;
use shopstar\models\diypage\DiypageModel;
use shopstar\bases\BaseManageApiController;

/**
 * 页面列表
 * Class ListController
 * @package apps\diypage\manage\page
 */
class ListControllerBase extends BaseManageApiController
{

    /**
     * @var array 需要POST请求的Actions
     */
    public $postActions = [
        'change-status',
        'delete',
    ];

    /**
     * 商城页面列表
     * @return array|\yii\web\Response
     * @author likexin
     */
    public function actionGetShop()
    {
        $type = RequestHelper::getInt('type');

        // 此方法只允许查询商城页面列表
        if (!empty($type) && !in_array($type, DiypageTypeConstant::$pageShopMap)) {
            return $this->error('页面类型错误');
        } elseif (empty($type)) {
            // type不传将查询所有的商城页面
            $type = DiypageTypeConstant::$pageShopMap;
        }

        // 获取页面列表
        $result = DiypageModel::getListResult($type);

        return $this->result($result);
    }

    /**
     * 应用页面列表
     * @return array|\yii\web\Response
     * @author likexin
     */
    public function actionGetApp()
    {
        $type = RequestHelper::getInt('type');

        // 此方法只允许查询应用页面列表
        if (!empty($type) && !in_array($type, DiypageTypeConstant::$pageAppMap)) {
            return $this->error('页面类型错误');
        } elseif (empty($type)) {
            // type不传将查询所有的应用页面
            $type = DiypageTypeConstant::$pageAppMap;
        }

        // 获取页面列表
        $result = DiypageModel::getListResult($type);

        return $this->result($result);
    }

    /**
     * 添加页面
     * @return array|\yii\web\Response
     * @throws \ReflectionException
     * @author likexin
     */
    public function actionAdd()
    {
        $type = RequestHelper::isPost() ? RequestHelper::post('type') : RequestHelper::get('type');
        if (is_null($type)) {
            return $this->error('页面类型错误');
        }

        // 检测类型是否合法
        $checkType = DiypageTypeConstant::getOneByCode($type);
        if (is_null($checkType)) {
            return $this->error('不支持的页面类型');
        }

        $result = DiypageModel::getAddResult($type);

        return $this->result($result);
    }

    /**
     * 编辑页面
     * @return array|\yii\web\Response
     * @throws \ReflectionException
     * @author likexin
     */
    public function actionEdit()
    {
        $type = RequestHelper::isPost() ? RequestHelper::post('type') : RequestHelper::get('type');
        if (is_null($type)) {
            return $this->error('页面类型错误');
        }

        // 检测类型是否合法
        $checkType = DiypageTypeConstant::getOneByCode($type);
        if (is_null($checkType)) {
            return $this->error('不支持的页面类型');
        }

        $result = DiypageModel::getEditResult($type);

        return $this->result($result);
    }

    /**
     * 修改状态
     * @return array|\yii\web\Response
     * @throws \ReflectionException
     * @author likexin
     */
    public function actionChangeStatus()
    {
        $type = RequestHelper::post('type');
        if (is_null($type)) {
            return $this->error('页面类型错误');
        }

        // 检测类型是否合法
        $checkType = DiypageTypeConstant::getOneByCode($type);
        if (is_null($checkType)) {
            return $this->error('不支持的页面类型');
        }

        $result = DiypageModel::getChangeStatusResult($type);

        return $this->result($result);
    }

    /**
     * 删除页面
     * @return array|\yii\web\Response
     * @throws \ReflectionException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @author likexin
     */
    public function actionDelete()
    {
        $type = RequestHelper::post('type');
        if (is_null($type)) {
            return $this->error('页面类型错误');
        }

        // 检测类型是否合法
        $checkType = DiypageTypeConstant::getOneByCode($type);
        if (is_null($checkType)) {
            return $this->error('不支持的页面类型');
        }

        $result = DiypageModel::getDeleteResult($type);

        return $this->result($result);
    }
}