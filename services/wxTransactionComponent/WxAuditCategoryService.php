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

namespace shopstar\services\wxTransactionComponent;

use Exception;
use shopstar\components\wechat\helpers\MiniProgramWxTransactionComponentHelper;
use shopstar\constants\wxTransactionComponent\WxAuditCategoryConstant;
use shopstar\exceptions\wxTransactionComponent\WxAuditCategoryException;
use shopstar\helpers\DateTimeHelper;
use shopstar\models\wxTransactionComponent\WxAuditCategoryModel;
use shopstar\services\core\attachment\CoreAttachmentService;
use Yii;

/**
 * Class WxAuditCategoryService.
 * @package shopstar\services\wxTransactionComponent
 */
class WxAuditCategoryService
{
    /**
     * 上传路径映射值
     * @var array|int[]
     */
    public static array $pathTypeMap = [
        'license' => 10,
        'certificate' => 20,
    ];

    /**
     * 根据三级id查询类目详细信息
     * @param int $catId
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getCatByLastId(int $catId): array
    {
        $result = [];
        $res = MiniProgramWxTransactionComponentHelper::getCategory();
        if (is_error($res)) {
            return $res;
        }

        if ($res['errcode'] == 0 && !empty($res['third_cat_list'])) {
            $res = array_column($res['third_cat_list'], null, 'third_cat_id');
            $result = !is_null($res[$catId]) ? $res[$catId] : [];
        }

        return $result;
    }

    /**
     * 验证是否存在
     * @param int $categoryId
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function getExists(int $categoryId): bool
    {
        return WxAuditCategoryModel::find()->where(['category_id' => $categoryId, 'status' => WxAuditCategoryConstant::STATUS_SUCCESS])->exists();
    }

    /**
     * 添加审核或更新审核
     * @param array $params
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function uploadAudit(array $params)
    {
        $model = false;
        $data = [];
        $db = Yii::$app->db->beginTransaction();

        try {
            if (isset($params['id']) && !empty($params['id'])) {
                $model = WxAuditCategoryService::getInfoById($params['id']);
                $data = [
                    'audit_id' => '',
                    'status' => 0,
                ];
            }

            if (!$model) {
                $model = new WxAuditCategoryModel();
                $data = [
                    'category_id' => $params['category']['third_cat_id'],
                    'audit_id' => '',
                    'status' => 0,
                    'create_time' => DateTimeHelper::now(),
                ];
            }

            $model->setAttributes($data);
            // 入库数据缓存
            $oldPath = $params['path'];
            foreach ($params['path'] as &$value) {
                foreach ($value as &$item) {
                    $temporaryImgRes = MiniProgramWxTransactionComponentHelper::uploadImg([
                        'resp_type' => 1, // 0:此参数返回media_id，目前只用于品牌申请品牌和类目，推荐使用1：返回临时链接
                        'upload_type' => 1, // 0:图片流，1:图片url
                        'img_url' => CoreAttachmentService::getUrl($item['path']),
                    ]);
                    $item = $temporaryImgRes['img_info']['temp_img_url'];
                }
            }

            // 组装数据
            $data = [
                'audit_req' => [
                    'license' => current($params['path']['license']),  // 营业执照
                    'category_info' => [
                        "level1" => $params['category']['first_cat_id'], // 一级类目
                        "level2" => $params['category']['second_cat_id'], // 二级类目
                        "level3" => $params['category']['third_cat_id'], // 三级类目
                        "certificate" => $params['path']['certificate'] // 资质材料
                    ]
                ]
            ];
            $result = self::getStatusByAuditId($model->audit_id);
            if (isset($result['data']) && $result['data']['status'] == '1') {
                throw new WxAuditCategoryException(WxAuditCategoryException::CATEGORY_ALREADY_SUCCESS_NOT_EDIT_ERROR);
            }

            $result = MiniProgramWxTransactionComponentHelper::uploadAuditCategory($data);

            // 同步审核返回id
            if (isset($result['audit_id']) && $result['errcode'] == 0) {
                $model->audit_id = $result['audit_id'];

                if (!$model->save()) {
                    throw new WxAuditCategoryException(WxAuditCategoryException::CATEGORY_ADD_ERROR);
                }

                WxAuditCategoryImagesService::addData($model->id, $oldPath);
                $db->commit();
                return true;
            } else if ($result['error'] == '1050003') {
                // 类目已审核通过 禁止重复申请
                throw new WxAuditCategoryException(WxAuditCategoryException::CATEGORY_ALREADY_SUCCESS_NOT_ADD_ERROR);
            }

            return $result;
        } catch (Exception $exception) {
            $db->rollBack();
            return error($exception->getMessage(), $exception->getCode() ?: -1);
        }
    }

    /**
     * 根据id查询信息
     * @param int $id
     * @return WxAuditCategoryModel|null
     * @author 青岛开店星信息技术有限公司
     */
    public static function getInfoById(int $id): ?WxAuditCategoryModel
    {
        return WxAuditCategoryModel::findOne(['id' => $id]);
    }

    /**
     * 获取单一审核状态
     * @param $auditId
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function getStatusByAuditId($auditId)
    {
        $data = [
            'audit_id' => $auditId
        ];
        return MiniProgramWxTransactionComponentHelper::getAuditCategory($data);
    }

    /**
     * 删除
     * @param int $id
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function deleteData(int $id)
    {
        $db = Yii::$app->db->beginTransaction();
        try {
            $model = self::getInfoById($id);

            $result = self::getStatusByAuditId($model->audit_id);

            // 已审核成功的商品禁止删除
            if (isset($result['data']) && $result['data']['status'] != '9') {
                throw new WxAuditCategoryException(WxAuditCategoryException::CATEGORY_ALREADY_SUCCESS_NOT_DELETE_ERROR);
            }

            if ($model) {
                $model->delete();

                WxAuditCategoryImagesService::deleteData($id);
                $db->commit();

                return true;
            }

            return false;
        } catch (Exception $exception) {
            $db->rollBack();
            return error($exception->getMessage(), $exception->getCode() ?: -1);
        }
    }

    /**
     * 同步审核状态
     * @param int $page
     * @param int $pageSize
     * @return bool
     * @throws WxAuditCategoryException
     * @author 青岛开店星信息技术有限公司
     */
    public static function getStatus(int $page, int $pageSize): bool
    {
        $list = WxAuditCategoryModel::getColl([
            'andWhere' => [
                ['<>', 'audit_id', ''],
            ],
            'select' => ['audit_id', 'id']
        ], [
            'page' => $page,
            'pageSize' => $pageSize,
            'pager' => false,
            'onlyList' => true
        ]);

        if ($list) {
            foreach ($list as $value) {
                $result = self::getStatusByAuditId($value['audit_id']);

                if ($result['errcode'] == 0) {
                    WxAuditCategoryModel::updateAll(['status' => $result['data']['status']], ['id' => $value['id']]);
                } else {
                    throw new WxAuditCategoryException(WxAuditCategoryException::CATEGORY_SYNCHRONIZE_STATUS_ERROR);
                }
            }
        }

        return true;
    }
}
