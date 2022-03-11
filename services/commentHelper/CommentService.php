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

namespace shopstar\services\commentHelper;

use shopstar\bases\exception\BaseException;
use shopstar\helpers\DateTimeHelper;
use shopstar\models\goods\GoodsModel;
use shopstar\models\goods\GoodsOptionModel;
use shopstar\models\log\LogModel;
use shopstar\models\member\MemberLevelModel;
use shopstar\models\member\MemberModel;
use shopstar\models\order\OrderGoodsCommentModel;
use shopstar\constants\commentHelper\CommentHelperLogConstant;
use shopstar\exceptions\commentHelper\CommentHelperException;
use yii\helpers\Json;

/**
 * 评论
 * Class CommentService
 * @package shopstar\services\commentHelper
 */
class CommentService
{
    /**
     * 手动创建评论
     * @param array $data
     * @return bool
     * @throws CommentHelperException
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function manualCreateComment(array $data)
    {
        $goods = GoodsModel::find()->select(['has_option'])->where(['id' => $data['goods_id'], 'is_deleted' => [0, 1]])->first();
        if (empty($goods)) {
            throw new CommentHelperException(CommentHelperException::MANUAL_CREATE_GOODS_NOT_EXISTS);
        }
        if ($goods['has_option'] == 1) {
            $option = GoodsOptionModel::find()->select(['title'])->where(['goods_id' => $data['goods_id']])->get();
            $optionCount = count($option);
        }
        
        // 查找会员
        if ($data['member_type'] == 0) {
            // 自定义 获取等级
            if ($data['level_id'] == 0) {
                // 随机获取
                $level = MemberLevelModel::find()->select('level_name, id')->orderBy('rand()')->first();
            } else {
                // 获取等级
                $level = MemberLevelModel::find()->select(['id', 'level_name'])->where(['id' => $data['level_id']])->first();
                
                if (empty($level)) {
                    throw new CommentHelperException(CommentHelperException::MANUAL_MEMBER_LEVEL_NOT_EXISTS);
                }
            }
        } else {
            // 选择会员
            $member = MemberModel::find()->select(['id', 'nickname', 'avatar', 'level_id'])->where(['id' => explode(',', $data['member_id'])])->get();
            $memberCount = count($member);
            $levelId = array_column($member, 'level_id');
            $levels = MemberLevelModel::find()->select(['id', 'level_name'])->where(['id' => $levelId])->indexBy('id')->get();
            foreach ($member as $index => $item) {
                $member[$index]['level_name'] = $levels[$item['level_id']]['level_name'];
            }
        }
        
        // 插入字段
        $field = ['content', 'level', 'created_at', 'goods_id', 'status', 'images', 'is_have_image', 'is_new', 'type', 'member_id', 'nickname', 'avatar', 'member_level_name', 'option_title', 'level_id'];
        // 插入数据
        $insertData = [];
        foreach ($data['comment_data'] as $index => $item) {
            // 组装数据
            $insertData[$index] = [
                mb_substr($item['content'], 0, 500),
                $data['level'],
                DateTimeHelper::getRandDate($data['start_time'], $data['end_time']),
                $data['goods_id'],
                $data['status'],
                Json::encode($item['images'] ?: []),
                !empty($item['images']) ? 1 : 0, // 是否有图
                1, // is_new
                2, // 手动创建
                $data['member_type'] == 0 ? 0 : $member[($index + $memberCount) % $memberCount]['id'],
                $data['member_type'] == 0 ? $data['nickname'] : $member[($index + $memberCount) % $memberCount]['nickname'],
                $data['member_type'] == 0 ? $data['avatar'] : $member[($index + $memberCount) % $memberCount]['avatar'],
                $data['member_type'] == 0 ? $level['level_name'] : $member[($index + $memberCount) % $memberCount]['level_name'],
                $goods['has_option'] == 0 ? '' : $option[($index + $optionCount) % $optionCount]['title'],
                $data['member_type'] == 0 ? $level['id'] : $member[($index + $memberCount) % $memberCount]['level_id'],
            ];
        }
        
        // 批量插入
        if (!empty($insertData)) {
            OrderGoodsCommentModel::batchInsert($field, $insertData);
        }
        return true;
    }
    
    /**
     * 编辑评价
     * @param array $data
     * @throws CommentHelperException
     * @author 青岛开店星信息技术有限公司
     */
    public function editComment(int $userId, array $data)
    {
        $comment = OrderGoodsCommentModel::findOne(['id' => $data['id']]);
        if (empty($comment)) {
            throw new CommentHelperException(CommentHelperException::EDIT_COMMENT_NOT_EXISTS);
        }
        
        $comment->content = mb_substr($data['content'],0, 500);
        $comment->created_at = $data['time'];
        $comment->nickname = $data['nickname'];
        $comment->avatar = $data['avatar'];
        $comment->member_level_name = $data['member_level_name'];
        $comment->level = $data['level'];
        $comment->status = $data['status'];
        $comment->sort_by = $data['sort_by'];
        $comment->is_choice = $data['is_choice'];
        $comment->images = Json::encode($data['images']);
        $comment->is_have_image = $data['images'] ? 1 : 0;
        $comment->level_id = $data['level_id'];
    
        // 保存
        if (!$comment->save()) {
            throw new CommentHelperException(CommentHelperException::EDIT_FAIL);
        }

        // 日志
        $logPrimary = [
            '评分等级' => $comment->level . '星',
            '精选评价' => $comment->is_choice == 0 ? '普通评价' : '精选评价',
            '评价内容' => $comment->content,
            '评价时间' => $comment->created_at,
            '评价会员' => $comment->nickname,
            '显示状态' => $comment->status == 1 ? '显示' : '隐藏',
        ];

        LogModel::write(
            $userId,
            CommentHelperLogConstant::COMMENT_HELPER_EDIT,
            CommentHelperLogConstant::getText(CommentHelperLogConstant::COMMENT_HELPER_EDIT),
            $comment->id,
            [
                'log_data' => $comment->attributes,
                'log_primary' => $logPrimary
            ]
        );
    }
    
}