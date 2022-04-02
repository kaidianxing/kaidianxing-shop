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

namespace shopstar\models\commission;


use shopstar\bases\model\BaseActiveRecord;
use shopstar\constants\commission\CommissionRelationLogConstant;
use shopstar\exceptions\commission\CommissionRelationLogException;
use shopstar\helpers\RequestHelper;
use shopstar\models\member\MemberModel;

/**
 * This is the model class for table "{{%commission_relation_log}}".
 *
 * @property int $id
 * @property int $member_id 会员id
 * @property int $parent_id 上级的会员id
 * @property int $old_parent_id 旧的上级id
 * @property int $type 类型   类型 10: 正常绑定上级 11: 竞争绑定上级 12: 手动绑定上级 13: 后台手动绑定换绑上级 20: 正常解绑上级 21: 竞争解绑上级 22: 手动解绑上级 23: 后台手动绑定换绑上级 24: 取消分享商资格解绑上级 25: 删除会员解绑上级
 * @property int $is_agent 是否是分销商
 * @property int $is_find 类型  按member_id查询时, 是否可获取 1可以
 * @property string $created_at
 * @property string $updated_at
 */
class CommissionRelationLogModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%commission_relation_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'member_id', 'parent_id', 'old_change_id', 'type', 'is_agent', 'is_find'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member_id' => '会员id',
            'parent_id' => '上级id',
            'old_change_id' => '变动的上级id 绑定保存的是旧上级id 解绑保存的是新上级id',
            'type' => '类型 
10: 正常绑定上级
11: 竞争绑定上级 
12: 手动绑定上级
13: 后台手动绑定换绑上级
20: 正常解绑上级
21: 竞争解绑上级
22: 手动解绑上级 
23: 后台手动绑定换绑上级
24: 取消分享商资格解绑上级
25: 删除会员解绑上级',
            'is_agent' => '是否是分销商',
            'is_find' => '类型  按member_id查询时, 是否可获取 1可以',
            'created_at' => 'Create Time',
            'updated_at' => 'Update Time',
        ];
    }


    /**
     * 保存分销关系日志
     * @param array $data 保存的数据 二维数据
     * @throws \yii\db\Exception
     * @author nizengchao
     */
    public static function saveLog(array $data = [])
    {
        if (!self::batchInsert(array_keys(current($data)), $data)) {
            return error(CommissionRelationLogException::getMessage(CommissionRelationLogException::LOG_SAVE_ERROR), CommissionRelationLogException::LOG_SAVE_ERROR);
        }

        return true;
    }

    /**
     * 获取总数
     * @param array $andWhere
     * @param int $type 1: 按次数获取 2: 按人数获取
     * @return int|string
     * @author nizengchao
     */
    public static function getCount(array $where = [], int $type = 1)
    {
        $self = self::find()
            ->where($where);
        if ($type == 1) {
            return $self->count('1');
        } else {
            return $self->groupBy('member_id')->count('1');
        }

    }

    /**
     * 获取用户变更日志
     * @return array|int|string|\yii\db\ActiveRecord[]
     * @author nizengchao
     */
    public static function getMemberLog()
    {

        // 会员信息
        $memberId = RequestHelper::getInt('id');
        if (!$memberId) {
            throw new CommissionRelationLogException(CommissionRelationLogException::COMMISSION_INFO_MEMBER_ID_EMPTY);
        }
        $member = MemberModel::getMemberDetail($memberId);
        if (is_error($member)) {
            throw new CommissionRelationLogException(CommissionRelationLogException::DETAIL_MEMBER_NOT_EXISTS);
        }

        // 查询字段
        $select = [
            'log.id',
            'log.member_id',
            'log.parent_id',
            'log.created_at',
            'log.type',
            'log.is_agent',
            'parent_member.nickname as parent_nickname',
            'parent_member.avatar as parent_avatar',
            'parent_member.source as parent_source'
        ];

        // 排序
        $orderBy = [
            'log.id' => SORT_DESC,
        ];

        // 连表
        $leftJoins = [
            [MemberModel::tableName() . ' parent_member', 'parent_member.id = log.parent_id'],
        ];

        // 参数
        $params = [
            'select' => $select,
            'alias' => 'log',
            'where' => [
                'log.member_id' => $memberId,
                'log.is_find' => 1,// 可获取的
            ],
            'leftJoins' => $leftJoins,
            'orderBy' => $orderBy,
        ];

        // 获取列表
        return CommissionRelationLogModel::getColl($params, [
            'callable' => function (&$row) {
                // 处理变更原因
                $row['relation_reason'] = CommissionRelationLogConstant::getText($row['type']);

                // 没有上级似的处理
                if (!$row['parent_nickname']) {
                    $row['parent_nickname'] = $row['parent_mobile'] = $row['parent_avatar'] = '';
                    // 分销商为总店
                    if ($row['is_agent']) {
                        $row['parent_nickname'] = '总店';
                    } else {
                        // 非分销商为-
                        $row['parent_nickname'] = '-';
                    }
                }
            },
            'pager' => false,
        ]);

    }

}