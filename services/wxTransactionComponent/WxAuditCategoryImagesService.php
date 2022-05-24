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

use shopstar\exceptions\wxTransactionComponent\WxAuditCategoryException;
use shopstar\models\wxTransactionComponent\WxAuditCategoryImagesModel;
use yii\db\ActiveRecord;

/**
 * Class WxAuditCategoryImagesService.
 * @package shopstar\services\wxTransactionComponent
 */
class WxAuditCategoryImagesService
{
    /**
     * 根据自定义交易组件的商品查询资质图片
     * @param int $id
     * @return array|ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    public static function getListByWxId(int $id): array
    {
        $result = WxAuditCategoryImagesModel::find()->where(['wx_id' => $id])->select(['path'])->asArray()->all();

        if ($result) {
            $result = array_column($result, 'path', null);
        }

        return $result;
    }

    /**
     * 根据类目审核表id查询信息
     * @param int $auditCategoryId
     * @return array|ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    public static function getInfoByAuditCategoryId(int $auditCategoryId): array
    {
        return WxAuditCategoryImagesModel::find()->where(['audit_category_id' => $auditCategoryId])->asArray()->all();
    }

    /**
     * 保存或更新数据
     * @param int $auditCategoryId
     * @param array $path
     * @return bool
     * @throws WxAuditCategoryException
     * @author 青岛开店星信息技术有限公司
     */
    public static function addData(int $auditCategoryId, array $path): bool
    {
        foreach ($path as $key => $value) {
            foreach ($value as $item) {
                $model = false;
                if (isset($item['id']) && !empty($item['id'])) {
                    $model = WxAuditCategoryImagesService::getInfoById($item['id']);
                }
                if (!$model) {
                    $model = new WxAuditCategoryImagesModel();
                    $model->setAttributes([
                        'audit_category_id' => $auditCategoryId,
                        'path' => $item['path'],
                        'type' => WxAuditCategoryService::$pathTypeMap[$key],
                    ]);
                } else {
                    $model->path = $item['path'];
                }
                if (!$model->save()) {
                    throw new WxAuditCategoryException(WxAuditCategoryException::CATEGORY_ADD_ERROR);
                }
                $ids[] = $model->id;
            }
        }

        // 删除多余数据
        if (isset($ids) && (count($ids) != 0)) {
            WxAuditCategoryImagesModel::deleteAll([
                'and',
                ['audit_category_id' => $auditCategoryId],
                ['not in', 'id', $ids]
            ]);
        }

        return true;
    }

    /**
     * 根据id查询信息
     * @param int $id
     * @return WxAuditCategoryImagesModel|null
     * @author 青岛开店星信息技术有限公司
     */
    public static function getInfoById(int $id): ?WxAuditCategoryImagesModel
    {
        return WxAuditCategoryImagesModel::findOne(['id' => $id]);
    }

    /**
     * 添加
     * 传值格式不同 拆分方法 不合并了
     * @param int $wxId
     * @param array $path
     * @return bool
     * @throws WxAuditCategoryException
     * @author 青岛开店星信息技术有限公司
     */
    public static function addDataByWxId(int $wxId, array $path): bool
    {
        foreach ([$path] as $key => $value) {
            foreach ($value as $item) {
                $model = false;
                if (!$model) {
                    $model = new WxAuditCategoryImagesModel();
                    $model->setAttributes([
                        'wx_id' => $wxId,
                        'path' => $item,
                        'type' => 10,
                    ]);
                } else {
                    $model->path = $item;
                }
                if (!$model->save()) {
                    throw new WxAuditCategoryException(WxAuditCategoryException::CATEGORY_ADD_ERROR);
                }
                $ids[] = $model->id;
            }
        }
        // 删除多余数据
        if (isset($ids) && (count($ids) != 0)) {
            WxAuditCategoryImagesModel::deleteAll([
                'and',
                ['wx_id' => $wxId],
                ['not in', 'id', $ids]
            ]);
        }
        return true;
    }

    /**
     * 删除
     * @param $auditCategoryId
     * @return false|void
     * @author 青岛开店星信息技术有限公司
     */
    public static function deleteData($auditCategoryId)
    {
        $result = WxAuditCategoryImagesModel::deleteAll(['audit_category_id' => $auditCategoryId]);
        if (is_error($result)) {
            return false;
        }
    }
}
