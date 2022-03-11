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
use shopstar\constants\commission\CommissionLogConstant;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;

/**
 * This is the model class for table "{{%commission_level}}".
 *
 * @property int $id
 * @property string $name 等级名称
 * @property int $is_default 默认等级
 * @property int $level 等级权重
 * @property string $commission_1 一级佣金比例
 * @property string $commission_2 二级佣金比例
 * @property string $order_money 分销订单总额(完成的订单)
 * @property string $order_money_1 一级分销订单金额(完成的订单)
 * @property int $order_count 分销订单总数(完成的订单)
 * @property int $order_count_1 一级分销订单总数(完成的订单)
 * @property string $self_order_money 自购订单金额(完成的订单)
 * @property int $self_order_count 自购订单数量(完成的订单)
 * @property int $child_count 下线总人数(分销商+非分销商)
 * @property int $child_count_1 一级下线人数(分销商+非分销商)
 * @property int $child_agent_count 下级分销商总人数
 * @property int $child_agent_count_1 一级分销商人数
 * @property string $withdraw_money 已提现佣金总金额
 * @property string $goods_ids 指定商品
 * @property int $status 状态 0:不启用 1:启用
 * @property int $upgrade_type 升级方式 0 任意条件 1 满足全部条件
 */
class CommissionLevelModel extends BaseActiveRecord
{

    /**
     * 升级条件 除去
     * @var array
     */
    public static $upgradeCondition = [
        'order_money' => 0,
        'order_money_1' => 0,
        'order_count' => 0,
        'order_count_1' => 0,
        'self_order_money' => 0,
        'self_order_count' => 0,
        'child_count' => 0,
        'child_count_1' => 0,
        'child_agent_count' => 0,
        'child_agent_count_1' => 0,
        'withdraw_money' => 0,
        'goods_ids' => ''
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%app_commission_level}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_default', 'level', 'order_count', 'order_count_1', 'self_order_count', 'child_count', 'child_count_1', 'child_agent_count', 'child_agent_count_1', 'status', 'upgrade_type'], 'integer'],
            [['commission_1', 'commission_2', 'order_money', 'order_money_1', 'self_order_money', 'withdraw_money'], 'number'],
            [['goods_ids'], 'string', 'max' => 100],
            [['name'], 'string', 'max' => 128],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '等级名称',
            'is_default' => '默认等级',
            'level' => '等级权重',
            'commission_1' => '一级佣金比例',
            'commission_2' => '二级佣金比例',
            'order_money' => '分销订单总额(完成的订单)',
            'order_money_1' => '一级分销订单金额(完成的订单)',
            'order_count' => '分销订单总数(完成的订单)',
            'order_count_1' => '一级分销订单总数(完成的订单)',
            'self_order_money' => '自购订单金额(完成的订单)',
            'self_order_count' => '自购订单数量(完成的订单)',
            'child_count' => '下线总人数(分销商+非分销商)',
            'child_count_1' => '一级下线人数(分销商+非分销商)',
            'child_agent_count' => '下级分销商总人数',
            'child_agent_count_1' => '一级分销商人数',
            'withdraw_money' => '已提现佣金总金额',
            'goods_ids' => '指定商品',
            'status' => '状态 0:不启用 1:启用',
            'upgrade_type' => '升级方式 0 任意条件 1 满足全部条件',
        ];
    }

    public function logAttributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '等级名称',
            'is_default' => '默认等级',
            'level' => '等级权重',
            'commission_1' => '一级佣金比例',
            'commission_2' => '二级佣金比例',
            'order_money' => '分销订单总额(完成的订单)',
            'order_money_1' => '一级分销订单金额(完成的订单)',
            'order_count' => '分销订单总数(完成的订单)',
            'order_count_1' => '一级分销订单总数(完成的订单)',
            'self_order_money' => '自购订单金额(完成的订单)',
            'self_order_count' => '自购订单数量(完成的订单)',
            'child_count' => '下线总人数(分销商+非分销商)',
            'child_count_1' => '一级下线人数(分销商+非分销商)',
            'child_agent_count' => '下级分销商总人数',
            'child_agent_count_1' => '一级分销商人数',
            'withdraw_money' => '已提现佣金总金额',
            'goods_ids' => '指定商品',
            'status' => '状态',
            'upgrade_type' => '升级方式',
        ];
    }

    /**
     * 获取简单列表
     * @param array $options
     * @param string[] $select
     * @param  $orderBy
     * @return mixed
     * @author likexin
     */
    public static function getSimpleList(array $options = [], $select = [], $orderBy = ['level' => SORT_DESC])
    {
        $options = array_merge([
            'select' => $select ?: ['id', 'name'],
        ], $options);

        return self::getColl([
            'select' => $options['select'],
            'orderBy' => $orderBy,
        ], [
            'pager' => false,
            'onlyList' => true,
            'disableSort' => true,
        ]);
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
        // 分销层级
        $setLevel = CommissionSettings::get('set.commission_level');
        if ($post['level'] == '') {
            return error('等级不能为空');
        }
        // 等级名称
        if (empty($post['name'])) {
            return error('等级名称不能为空');
        }
        // 一级佣金比例必填
        if ($post['commission_1'] == '') {
            return error('一级佣金比例不能为空');
        }
        // 二级
        if ($setLevel > 1 && $post['commission_2'] == '') {
            return error('二级佣金比例不能为空');
        }

        if (empty($id)) {
            $level = new self();
        } else {
            $level = self::findOne(['id' => $id]);
            if (empty($level)) {
                return error('等级不存在');
            }
        }
        // 如果等级修改了 则检查是否存在
        if ($level->level != $post['level'] && self::isLevelExists($post['level'])) {
            return error('该等级已存在');
        }

        $level->setAttributes($post);
        if ($level->save() === false) {
            return error($level->getErrorMessage());
        }
        // 日志
        $logPrimaryData = [
            'id' => $level->id,
            'name' => $level->name,
            'level' => $level->level,
            'commission_1' => $level->commission_1,
            'commission_2' => $level->commission_2,
            'upgrade_type' => $level->upgrade_type ? '满足选中的全部条件' : '满足任意选中的条件',
        ];
        // 去掉空条件
        $levelCondition = ArrayHelper::arrayFilterEmpty(array_intersect_key($level->toArray(), self::$upgradeCondition));
        foreach ($levelCondition as $index => $item) {
            $logPrimaryData[$index] = $item;
        }
        $code = empty($id) ? CommissionLogConstant::LEVEL_ADD : CommissionLogConstant::LEVEL_EDIT;
        LogModel::write(
            $uid,
            $code,
            CommissionLogConstant::getText($code),
            $level->id,
            [
                'log_data' => $level->attributes,
                'log_primary' => $level->getLogAttributeRemark($logPrimaryData),
                'dirty_identity_code' => [
                    CommissionLogConstant::LEVEL_ADD,
                    CommissionLogConstant::LEVEL_EDIT,
                ]
            ]
        );

        return true;
    }

    /**
     * 检查等级是否存在
     * @param int $level
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function isLevelExists(int $level)
    {
        $isExists = self::findOne(['level' => $level]);
        // 不存在则返回false
        if (empty($isExists)) {
            return false;
        }
        return true;
    }


    /**
     * 获取默认等级id
     * @return array|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function getDefaultId()
    {
        $level = self::find()->select('id')->where(['is_default' => 1])->first();
        if (empty($level)) {
            return error('数据初始化错误');
        }
        return $level['id'];
    }

    /**
     * 获取所有已启用的等级
     * @param string $fields
     * @param array $andWhere
     * @return array|\yii\db\ActiveRecord[]
     * @author nizengchao
     */
    public static function getAllOpenLevel($fields = 'id, name', $andWhere = []): array
    {
        $fields = $fields ?: 'id, name';
        return self::find()
            ->select($fields)
            ->where(['status' => 1])
            ->andWhere($andWhere)
            ->orderBy(['is_default' => SORT_DESC, 'level' => SORT_DESC])
            ->asArray()
            ->all();
    }

}
