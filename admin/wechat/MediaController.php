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

namespace shopstar\admin\wechat;

use shopstar\bases\KdxAdminApiController;
use shopstar\components\wechat\helpers\OfficialAccountMediaHelper;
use shopstar\constants\wechat\WechatMediaTypeConstant;
use shopstar\constants\wechat\WechatSyncTaskTypeConstant;
use shopstar\exceptions\wechat\WechatException;
use shopstar\helpers\RequestHelper;
use shopstar\models\wechat\WechatMaterialModel;
use shopstar\models\wechat\WechatMaterialNewsModel;
use shopstar\models\wechat\WechatSyncTaskModel;
use shopstar\services\core\attachment\CoreAttachmentService;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
use yii\web\Response;

/**
 * 素材管理
 * Class MediaController.
 * @package shopstar\admin\wechat
 */
class MediaController extends KdxAdminApiController
{
    /**
     * 上传临时图片
     * @return array|int[]|Response
     * @throws WechatException
     * @throws InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionUploadImage()
    {
        $post = RequestHelper::post();
        if (empty($post['path'])) {
            throw new WechatException(WechatException::CHANNEL_MANAGE_WECHAT_MEDIA_UPLOAD_IMAGE_PARAMS_ERROR);
        }

        //上传小程序
        $result = OfficialAccountMediaHelper::uploadImage(CoreAttachmentService::getUrl($post['path']));

        return $this->result($result);
    }

    /**
     * 获取素材列表
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionList()
    {
        $type = RequestHelper::get('type');

        $list = WechatMaterialModel::getColl([
            'where' => [],
            'searchs' => [
                ['type', 'string'],
            ],
            'orderBy' => [
                'created_at' => SORT_DESC,
                'id' => SORT_DESC,
            ]
        ]);

        //如果等于图文
        if ($type == WechatMediaTypeConstant::WECHAT_MEDIA_TYPE_NEWS && !empty($list['list'])) {
            $news = WechatMaterialNewsModel::find()->where([
                'material_id' => array_column($list['list'], 'id'),
            ])->select([
                'id',
                'material_id',
                'index',
                'title',
                'author',
                'description',
                'url',
                'thumb_url'
            ])->orderBy(['index' => SORT_DESC])->get();

            //判断如果不为空
            if (!empty($news)) {
                foreach ($news as $item) {
                    foreach ($list['list'] as &$listItem) {
                        if ($listItem['id'] == $item['material_id']) {
                            $listItem['news_item'][] = $item;
                        }
                    }
                }
            }
        }

        //获取最后同步时间
        $list['last_sync_time'] = WechatSyncTaskModel::getLastSyncTime(3, $type);

        return $this->result($list);
    }

    /**
     * 上传素材
     * @return array|int[]|Response
     * @throws InvalidConfigException
     * @throws WechatException
     * @throws Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionUpload()
    {
        $post = RequestHelper::post();

        if (empty($post['type'])) {
            throw new WechatException(WechatException::CHANNEL_MANAGE_WECHAT_MEDIA_UPLOAD_IMAGE_PARAMS_ERROR);
        }

        //上传小程序
        $result = WechatMaterialModel::upload((int)$post['attachment_id'], $post['type'], [
            'video_title' => $post['video_title'] ?? '',
            'video_description' => $post['video_description'] ?? ''
        ]);

        //判断是否错误
        if (is_error($result)) {
            throw new WechatException(WechatException::WECHAT_FANS_MEDIA_UPLOAD_ERROR, $result['message']);
        }

        return $this->result(['data' => $result]);
    }

    /**
     * 同步素材
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSync()
    {
        //发送同步任务
        $taskId = WechatSyncTaskModel::sendTask(WechatSyncTaskTypeConstant::WECHAT_MEDIA, [
            'type' => RequestHelper::get('type')
        ]);

        return $this->result(['task_id' => $taskId]);
    }

    /**
     * 删除素材
     * @return array|int[]|Response
     * @throws InvalidConfigException
     * @throws WechatException
     * @throws StaleObjectException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $mediaId = RequestHelper::post('media_id');

        if (empty($mediaId)) {
            throw new WechatException(WechatException::WECHAT_MEDIA_DELETE_PARAMS_ERROR);
        }


        $model = WechatMaterialModel::findOne(['media_id' => $mediaId]);

        if (!empty($model)) {
            if ($model->type == WechatMediaTypeConstant::WECHAT_MEDIA_TYPE_NEWS) {
                WechatMaterialNewsModel::deleteAll(['material_id' => $model->id]);
            }

            $model->delete();
        }

        //判断删除
        $result = OfficialAccountMediaHelper::delete($mediaId);

        return $this->result($result);
    }
}
