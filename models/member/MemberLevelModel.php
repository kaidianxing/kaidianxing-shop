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

namespace shopstar\models\member;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\constants\log\member\MemberLogConstant;
use shopstar\constants\member\MemberLevelConstant;
use shopstar\constants\order\OrderStatusConstant;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\ValueHelper;
use shopstar\models\log\LogModel;
use yii\helpers\Json;


/**
 * This is the model class for table "{{%member_level}}".
 *
 * @property int $id
 * @property int $level 等级排序
 * @property string $level_name 等级名称
 * @property int $state 状态 0:不启用;1:启用;
 * @property int $order_count 订单总数
 * @property string $order_money 订单金额
 * @property string $goods_ids 指定商品(多选)
 * @property string $discount 折扣
 * @property int $update_condition 升级条件 0:无;1:按订单总数;2:按订单金额;3:指定商品;4:设为默认等级;
 * @property string $created_at 创建时间
 * @property string $updated_at 修改时间
 * @property int $is_default 是否默认
 * @property int $is_discount 是否折扣 0否 1自定义
 */
class MemberLevelModel extends BaseActiveRecord
{
    /**
     * 状态
     * @var array
     */
    public static $stateText = [
        '0' => '禁用',
        '1' => '启用'
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%member_level}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['level', 'state', 'order_count', 'update_condition', 'is_default', 'is_discount'], 'integer'],
            [['order_money', 'discount'], 'number'],
            [['goods_ids'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['level_name'], 'string', 'max' => 255],
        ];
    }

    public function logAttributeLabels()
    {
        return [
            'id' => '等级ID',
            'level' => '等级排序',
            'level_name' => '等级名称',
            'upgrade_condition' => '升级条件',
            'discount' => '折扣权益',
            'state' => '状态',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'level' => '等级排序',
            'level_name' => '等级名称',
            'state' => '状态 0:不启用;1:启用;',
            'order_count' => '订单总数',
            'order_money' => '订单金额',
            'goods_ids' => '指定商品(多选)',
            'discount' => '折扣',
            'update_condition' => '升级条件 0:无;1:按订单总数;2:按订单金额;3:指定商品;4:设为默认等级;',
            'created_at' => '创建时间',
            'updated_at' => '修改时间',
            'is_default' => '是否默认',
            'is_discount' => '是否折扣 0否 1自定义',
        ];
    }

    /**
     * 保存等级
     * @param int $uid
     * @param int $id
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function saveLevel(int $uid, int $id = 0)
    {
        $post = RequestHelper::post();
        // 等级
        if ($post['level'] == '') {
            return error('等级不能为空');
        }
        // 等级名称
        if (empty($post['level_name'])) {
            return error('等级名称不能为空');
        }
        // 等级折扣
        $post['discount'] = bcadd($post['discount'], 0, 2);
        if ($post['discount'] != 0 && (bccomp($post['discount'], 0.01, 2) < 0 && bccomp(9.99, $post['discount'], 2) < 0)) {
            return error('折扣请输入0~10之间的数字');
        }
        if ($post['update_condition'] == MemberLevelConstant::LEVEL_UPGRADE_GOODS) {
            if (empty($post['goods_ids'])) {
                return error('指定商品不能为空');
            }
            if (count($post['goods_ids']) > 10) {
                return error('最多添加10个商品');
            }
            $post['goods_ids'] = Json::encode($post['goods_ids']);
        }

        if (empty($id)) {
            $level = new self();
            // 检查等级
            if (!self::checkLevel($post['level'])) {
                return error('等级已存在');
            }
        } else {
            $level = self::findOne(['id' => $id]);
            if (empty($level)) {
                return error('会员等级不存在');
            }
            // 如果编辑的level有变化  则检查
            if ($post['level'] != $level->level) {
                if (!self::checkLevel($post['level'])) {
                    return error('等级已存在');
                }
            }
        }

        $level->setAttributes($post);
        if ($level->save() === false) {
            return error('等级保存失败');
        }

        // 保存日志
        $logPrimaryData = [
            'id' => $level->id,
            'level' => $post['level'],
            'level_name' => $post['level_name'],
            'upgrade_condition' => MemberLevelConstant::getText($post['update_condition']),
            'discount' => $post['is_discount'] == 0 ? '无' : ValueHelper::delZero($post['discount']) . '折',
            'state' => self::$stateText[$post['state']],
        ];
        $code = empty($id) ? MemberLogConstant::MEMBER_LEVEL_ADD : MemberLogConstant::MEMBER_LEVEL_EDIT;
        LogModel::write(
            $uid,
            $code,
            MemberLogConstant::getText($code),
            $level->id,
            [
                'log_data' => $level->attributes,
                'log_primary' => $level->getLogAttributeRemark($logPrimaryData),
                'dirty_identify_code' => [
                    MemberLogConstant::MEMBER_LEVEL_EDIT,
                    MemberLogConstant::MEMBER_LEVEL_ADD
                ],
            ]
        );

        return true;
    }

    /**
     * 检查等级是否可用 等级不允许重复
     * @param int $level
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkLevel(int $level)
    {
        $isExists = self::find()
            ->where(['level' => $level])
            ->one();
        if (!empty($isExists)) {
            return false;
        }
        return true;
    }

    /**
     * 获取所有等级下的用户
     * @return array|\yii\db\ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    public static function getMemberCount()
    {
        return MemberModel::find()
            ->select('level_id, count(id) count')
            ->where(['is_deleted' => 0])
            ->groupBy('level_id')
            ->indexBy('level_id')
            ->get();
    }

    /**
     * 获取公众号下所有等级
     * @return MemberLevelModel[]
     * @author 青岛开店星信息技术有限公司
     */
    public static function getAllLevel()
    {
        return MemberLevelModel::find()
            ->orderBy(['is_default' => SORT_DESC])
            ->orderBy(['level' => SORT_DESC])
            ->asArray()
            ->all();
    }

    /**
     * 获取所有已启用的等级
     * @param string $fields
     * @return array|\yii\db\ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    public static function getAllOpenLevel($fields = 'id, level_name')
    {
        return MemberLevelModel::find()
            ->select($fields)
            ->andWhere(['state' => 1])
            ->orderBy(['is_default' => SORT_DESC])
            ->orderBy(['level' => SORT_DESC])
            ->asArray()
            ->all();
    }

    /**
     * 获取默认等级id
     * @return int|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function getDefaultLevelId()
    {
        $default = self::find()
            ->select('id')
            ->where(['is_default' => 1])
            ->first();
        return $default['id'] ?? 0;
    }

    /**
     * 获取默认等级
     * @return array|\yii\db\ActiveRecord|null
     * @author 青岛开店星信息技术有限公司
     */
    public static function getDefaultLevel()
    {
        return self::find()
            ->where([
                'is_default' => 1
            ])
            ->asArray()
            ->one();
    }

    /**
     * 根据会员ID获取会员等级
     * @param int $memberId
     * @author 青岛开店星信息技术有限公司
     */
    public static function getMemberLevelNameByMemberId(int $memberId)
    {


        $memberLevel = MemberModel::find()
            ->alias('member')
            ->leftJoin(MemberLevelModel::tableName() . ' member_level', 'member.level_id=member_level.id')
            ->where(['member.id' => $memberId])
            ->select(['member_level.level_name'])
            ->asArray()
            ->first();

        return $memberLevel['level_name'];
    }

}
