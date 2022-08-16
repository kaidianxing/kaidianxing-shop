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
use shopstar\components\wechat\helpers\OfficialAccountFansHelper;
use shopstar\constants\wechat\WechatSyncTaskTypeConstant;
use shopstar\exceptions\wechat\WechatException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\wechat\WechatFansTagMapModel;
use shopstar\models\wechat\WechatFansTagModel;
use shopstar\models\wechat\WechatSyncTaskModel;
use yii\web\Response;

/**
 * 公众号粉丝标签控制器
 * Class WechatFansTagController.
 * @package shopstar\admin\wechat
 */
class WechatFansTagController extends KdxAdminApiController
{
    /**
     * 同步粉丝标签
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSync()
    {
        //发送同步任务
        $taskId = WechatSyncTaskModel::sendTask(WechatSyncTaskTypeConstant::WECHAT_FANS_TAG);

        return $this->result(['task_id' => $taskId]);
    }

    /**
     * 获取粉丝标签列表
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionList()
    {
        $list = WechatFansTagModel::getColl([
            'alias' => 'fans_tag',
            'leftJoins' => [
                [WechatFansTagMapModel::tableName() . ' fans_tag_map', 'fans_tag_map.wechat_tag_id = fans_tag.wechat_tag_id'],
            ],
            'where' => [],
            'orderBy' => [
                'fans_tag.created_at' => SORT_DESC,
                'fans_tag.id' => SORT_DESC
            ],
            'groupBy' => 'fans_tag.id',
            'searchs' => [
                ['fans_tag.tag_name', 'like', 'tag_name']
            ],
            'select' => [
                'fans_tag.id',
                'fans_tag.wechat_tag_id',
                'fans_tag.tag_name',
                'fans_tag.created_at',
                'count(fans_tag_map.id) as fans_num'
            ]
        ],[
            'pager' => (bool)RequestHelper::getInt('pager', 1)
        ]);

        //获取最后同步时间
        $list['last_sync_time'] = WechatSyncTaskModel::getLastSyncTime(2);

        return $this->result($list);
    }

    /**
     * 添加修改粉丝标签
     * @return array|int[]|Response
     * @throws WechatException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSave()
    {
        $name = RequestHelper::post('name');

        if (empty($name)) {
            throw new WechatException(WechatException::WECHAT_FANS_TAG_ADD_PARAMS_ERROR);
        }

        $wechatTagId = RequestHelper::postInt('wechat_tag_id');

        //查询是否存在
        $tag = WechatFansTagModel::find()->where(['tag_name' => $name])->exists();

        if(!empty($tag)){
            throw new WechatException(WechatException::WECHAT_FANS_TAG_ADD_NAME_EXIST_ERROR);
        }

        //新增
        if (empty($wechatTagId)) {
            //添加微信标签
            $result = OfficialAccountFansHelper::createTag($name);

            $wechatTagId = $result['tag']['id'];

            //判断微信通讯是否错误
            if (is_error($result)) {
                throw new WechatException(WechatException::WECHAT_FANS_TAG_ADD_WECHAT_ERROR, $result['message']);
            }

            //获取标签表model
            $model = new WechatFansTagModel();
        } else {
            //修改
            $model = WechatFansTagModel::find()->where([
                'wechat_tag_id' => $wechatTagId,
            ])->one();

            if (empty($model)) {
                throw new WechatException(WechatException::WECHAT_FANS_TAG_SAVE_TAG_NOT_FOUND_ERROR);
            }

            //添加微信标签
            $result = OfficialAccountFansHelper::updateTag($wechatTagId, $name);

            //判断微信通讯是否错误
            if (is_error($result)) {
                throw new WechatException(WechatException::WECHAT_FANS_TAG_SAVE_TAG_EDIT_WECHAT_ERROR, $result['message']);
            }
        }

        $model->setAttributes([
            'tag_name' => $name,
            'created_at' => DateTimeHelper::now(),
            'wechat_tag_id' => $wechatTagId,
        ]);

        if (!$model->save()) {
            throw new WechatException(WechatException::WECHAT_FANS_TAG_ADD_ERROR,$model->getErrorMessage());
        }

        return $this->result($result);
    }

    /**
     * 删除粉丝标签
     * @return array|int[]|Response
     * @throws WechatException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $wechatTagId = RequestHelper::PostInt('wechat_tag_id');

        if (empty($wechatTagId)) {
            throw new WechatException(WechatException::WECHAT_FANS_TAG_DELETE_PARAMS_ERROR);
        }

        //删除标签
        $result = OfficialAccountFansHelper::deleteTag($wechatTagId);

        if (is_error($result)) {
            throw new WechatException(WechatException::WECHAT_FANS_TAG_DELETE_WECHAT_ERROR, $result['message']);
        }

        //删除标签
        WechatFansTagModel::deleteAll([
            'wechat_tag_id' => $wechatTagId,
        ]);

        //删除标签索引
        WechatFansTagMapModel::deleteAll([
            'wechat_tag_id' => $wechatTagId,
        ]);

        return $this->result();
    }
}
