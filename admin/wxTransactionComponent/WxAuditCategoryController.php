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

namespace shopstar\admin\wxTransactionComponent;

use Exception;
use shopstar\bases\KdxAdminApiController;
use shopstar\exceptions\wxTransactionComponent\WxAuditCategoryException;
use shopstar\exceptions\wxTransactionComponent\WxTransactionComponentException;
use shopstar\helpers\RequestHelper;
use shopstar\models\wxTransactionComponent\WxAuditCategoryModel;
use shopstar\services\wxTransactionComponent\WxAuditCategoryImagesService;
use shopstar\services\wxTransactionComponent\WxAuditCategoryService;
use yii\web\Response;

/**
 * 类目审核
 * Class WxAuditCategoryController.
 * @package shopstar\admin\wxTransactionComponent
 */
class WxAuditCategoryController extends KdxAdminApiController
{
    /**
     * 类目列表
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $data = WxAuditCategoryModel::getColl([
            'select' => [
                'id',
                'audit_id',
                'category_id',
                'status',
            ],
            'orderBy' => [
                'create_time' => SORT_DESC
            ]
        ], [
            'callable' => function (&$row) {
                $catInfo = WxAuditCategoryService::getCatByLastId($row['category_id']);
                $row['category_name'] = $catInfo['first_cat_name'] . '/' . $catInfo['second_cat_name'] . '/' . $catInfo['third_cat_name'];
            }
        ]);

        return $this->success(['data' => $data]);
    }

    /**
     * 添加类目审核或更新
     * @return array|int[]|Response
     * @throws WxAuditCategoryException
     * @throws WxTransactionComponentException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAdd()
    {
        $params = self::verification();
        $params['id'] = RequestHelper::post('id');

        // 获取类目详情
        $result = WxAuditCategoryService::getCatByLastId($params['cat_id']);
        if (!$result) {
            throw new WxAuditCategoryException(WxAuditCategoryException::CATEGORY_NOT_EXIST_ERROR);
        }

        // 查询是否已经审核通过的数据
        $exist = WxAuditCategoryService::getExists($result['third_cat_id']);
        if ($exist) {
            throw new WxAuditCategoryException(WxAuditCategoryException::CATEGORY_ALREADY_EXISTS_ERROR);
        }

        $params['category'] = $result;

        // 上传类目审核
        $result = WxAuditCategoryService::uploadAudit($params);

        return $this->result($result);
    }

    /**
     * 参数验证器
     * @return array|mixed|string|null
     * @throws WxAuditCategoryException
     * @throws WxTransactionComponentException
     * @author 青岛开店星信息技术有限公司
     */
    private static function verification()
    {
        $params = RequestHelper::post();

        if (empty($params['cat_id']) || empty($params['path']) || empty($params['path']['license']) || empty($params['path']['certificate'])) {
            throw new WxTransactionComponentException(WxTransactionComponentException::PARAMS_ERROR);
        }

        foreach ($params['path'] as $value) {
            if ((count($value) > 50)) {
                throw new WxAuditCategoryException(WxAuditCategoryException::CATEGORY_LICENSE_NUMBER_ERROR);
            }
        }

        return $params;
    }

    /**
     * 编辑信息
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit()
    {
        $id = RequestHelper::postInt('id');

        $result = WxAuditCategoryService::getInfoById($id);
        $wxImagesList = WxAuditCategoryImagesService::getInfoByAuditCategoryId($result->id);

        // 拼装数据
        $data = [
            'id' => $result->id,
            'category_id' => $result->category_id,
        ];

        // 拼装后 path的下级数组中的key是id
        $pathTypeMap = array_flip(WxAuditCategoryService::$pathTypeMap);
        if ($wxImagesList) {
            foreach ($wxImagesList as $value) {
                $data['path'][$pathTypeMap[$value['type']]][] = [
                    'id' => $value['id'],
                    'path' => $value['path']
                ];
            }
        }

        return $this->result(['data' => $data]);
    }

    /**
     * 删除
     * @return array|int[]|Response
     * @throws WxAuditCategoryException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $id = RequestHelper::postInt('id');

        if ($id == 0) {
            throw new WxAuditCategoryException(WxAuditCategoryException::CATEGORY_PARAMS_ERROR);
        }

        WxAuditCategoryService::deleteData($id);

        return $this->success();
    }

    /**
     * 同步审核状态
     * @return array|int[]|Response
     * @throws Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetStatus()
    {
        $page = RequestHelper::get('page', 1);
        $pageSize = RequestHelper::get('page_size', 10);

        try {
            WxAuditCategoryService::getStatus($page, $pageSize);
        } catch (WxAuditCategoryException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return $this->success();
    }
}
