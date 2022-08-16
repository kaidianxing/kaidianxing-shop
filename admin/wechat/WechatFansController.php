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
use shopstar\models\wechat\WechatFansModel;
use shopstar\models\wechat\WechatFansTagMapModel;
use shopstar\models\wechat\WechatFansTagModel;
use shopstar\models\wechat\WechatSyncTaskModel;
use Throwable;
use Yii;
use yii\db\Exception;
use yii\web\Response;

/**
 * 公众号粉丝管理控制器
 * Class WechatFansController.
 * @package shopstar\admin\wechat
 */
class WechatFansController extends KdxAdminApiController
{
    /**
     * 同步粉丝
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSync()
    {
        //发送同步任务
        $taskId = WechatSyncTaskModel::sendTask(WechatSyncTaskTypeConstant::WECHAT_FANS, [
            'is_black' => false
        ]);

        return $this->result(['task_id' => $taskId]);
    }

    /**
     * 获取粉丝列表
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionList()
    {
        $list = WechatFansModel::getColl([
            'orderBy' => ['created_at' => SORT_DESC],
            'searchs' => [
                ['nickname', 'like'],
                ['is_black', 'int'],
            ]
        ]);

        if (!empty($list['list'])) {
            $fansId = array_column($list['list'], 'id');
            $tag = WechatFansTagModel::find()
                ->alias('tag')
                ->leftJoin(WechatFansTagMapModel::tableName() . ' tag_map', 'tag_map.wechat_tag_id = tag.wechat_tag_id')
                ->where([
                    'tag_map.fans_id' => $fansId,
                ])
                ->select(['tag.tag_name', 'tag_map.fans_id', 'tag_map.wechat_tag_id'])
                ->get();

            foreach ((array)$tag as $tagItem) {
                foreach ($list['list'] as &$listItem) {
                    if ($tagItem['fans_id'] == $listItem['id']) {
                        $listItem['tag'][] = $tagItem;
                    }
                }
            }
        }

        //获取最后同步时间
        $list['last_sync_time'] = WechatSyncTaskModel::getLastSyncTime(1);

        return $this->result($list);
    }

    /**
     * 粉丝打标签
     * @return array|int[]|Response
     * @throws Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChangeTag()
    {
        $fansId = RequestHelper::postArray('fans_id');
        $tagId = RequestHelper::postArray('wechat_tag_id');

        //获取标签
        $tag = WechatFansTagModel::find()->where([
            'wechat_tag_id' => $tagId,
        ])->indexBy('wechat_tag_id')->get();

        //获取粉丝
        $fans = WechatFansModel::find()->where([
            'id' => $fansId,
        ])->select(['id', 'open_id'])->get();

        //获取标签差集
        $allTagWechatId = WechatFansTagMapModel::find()->where([
            'fans_id' => $fansId,
        ])->select(['wechat_tag_id'])->column();

        //取出差集
        if (empty($tag) && empty($tagId)) {
            $diffTagId = $allTagWechatId;
        } else {
            $diffTagId = array_diff($allTagWechatId, array_column($tag, 'wechat_tag_id'));
        }

        //如果差集不为空则执行删除操作
        if (!empty($diffTagId)) {
            $diffTag = WechatFansTagModel::find()->where([
                'wechat_tag_id' => array_filter($diffTagId),
            ])->indexBy('wechat_tag_id')->get();

            $this->batchDelTag($fans, $diffTag);
        }

        //批量处理
        $result = $this->batchAddTag($fans, $tag);

        return $this->result($result);
    }

    /**
     * 删除用户差集标签
     * @param array $fans
     * @param array $diffTag
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    private function batchDelTag(array $fans, array $diffTag): void
    {
        foreach ($diffTag as $diffTagItem) {
            OfficialAccountFansHelper::untagUsers(array_column($fans, 'open_id'), $diffTagItem['wechat_tag_id']);
        }

        //删除对应关系
        WechatFansTagMapModel::deleteAll([
            'fans_id' => array_column($fans, 'id'),
            'wechat_tag_id' => array_column($diffTag, 'wechat_tag_id'),
        ]);
    }

    /**
     * 新增用户标签
     * @param array $fans
     * @param array $tag
     * @return array|bool|int
     * @throws Exception
     * @author 青岛开店星信息技术有限公司
     */
    private function batchAddTag(array $fans, array $tag)
    {
        $insertData = [];

        $date = DateTimeHelper::now();

        $tr = Yii::$app->db->beginTransaction();
        try {
            foreach ($tag as $tagItem) {
                foreach ($fans as $item) {
                    $insertData[] = [
                        'fans_id' => $item['id'],
                        'wechat_tag_id' => $tagItem['wechat_tag_id'],
                        'created_at' => $date
                    ];
                }

                //添加标签
                OfficialAccountFansHelper::tagUsers(array_column($fans, 'open_id'), $tagItem['wechat_tag_id']);
            }

            //删除
            WechatFansTagMapModel::deleteAll([
                'fans_id' => array_column($fans, 'id'),
            ]);
            $tr->commit();
        } catch (Throwable $exception) {
            $tr->rollBack();
            return error($exception->getMessage());
        }

        if (empty($insertData)) {
            return true;
        }

        return WechatFansTagMapModel::batchInsert(array_keys(current($insertData)), $insertData);
    }

    /**
     * 加入/取消 黑名单
     * @return array|int[]|Response
     * @throws WechatException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionBlack()
    {
        $fansId = RequestHelper::post('fans_id');
        $isBlack = RequestHelper::postInt('is_black');

        //判断参数
        if (empty($fansId)) {
            throw new WechatException(WechatException::WECHAT_FANS_BLACK_PARAMS_ERROR);
        }

        //修改状态
        $where = [
            'id' => $fansId
        ];

        $attributes = [
            'is_black' => $isBlack
        ];

        if ($isBlack) {
            $attributes['black_time'] = DateTimeHelper::now();
        }

        //修改黑名单状态
        WechatFansModel::updateAll($attributes, $where);

        //获取粉丝openid
        $fansOpenid = WechatFansModel::find()->where($where)->select(['open_id'])->column();

        if (!empty($fansOpenid)) {
            //微信操作
            $result = $isBlack ? OfficialAccountFansHelper::block($fansOpenid) : OfficialAccountFansHelper::unblock($fansOpenid);
        }

        return $this->result($result);
    }
}
