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

namespace shopstar\models\wechat;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\constants\poster\PosterTypeConstant;
use shopstar\exceptions\wechat\WechatException;
use shopstar\helpers\DateTimeHelper;
use shopstar\models\poster\PosterModel;
use shopstar\constants\wechat\WechatRuleKeywordConstant;
use yii\db\ActiveRecord;
use yii\db\Exception;

/**
 * This is the model class for table "{{%wechat_rule_keyword}}".
 *
 * @property int $id
 * @property int $rule_id 规则id
 * @property int $unionid 绑定开放平台的
 * @property string $keyword 关键字
 * @property int $type 匹配规则 1全匹 2模糊 3正则
 * @property int $displayorder 显示顺序
 * @property int $status 状态 0关闭 1开启
 * @property string $created_at 创建时间
 */
class WechatRuleKeywordModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%wechat_rule_keyword}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['rule_id', 'type', 'displayorder', 'status', 'unionid'], 'integer'],
            [['created_at'], 'safe'],
            [['keyword'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'rule_id' => '规则id',
            'unionid' => '绑定开放平台的',
            'keyword' => '关键字',
            'type' => '匹配规则 1全匹 2模糊 3正则',
            'displayorder' => '显示顺序',
            'status' => '状态 0关闭 1开启',
            'created_at' => '创建时间',
        ];
    }

    /**
     * 保存数据
     * @param array $params
     * @param int $ruleId
     * @return bool
     * @throws Exception
     * @throws WechatException
     * @author 青岛开店星信息技术有限公司
     */
    public static function addData(array $params, int $ruleId): bool
    {
        foreach ($params as $value) {
            $data[] = [
                'rule_id' => $ruleId,
                'unionid' => 0, // 后期流程可能会用到
                'keyword' => $value['keyword'] ?? '',
                'type' => $value['type'] ?? WechatRuleKeywordConstant::WECHAT_RULE_KEYWORD_TYPE_EVENT,
                'displayorder' => $value['displayorder'] ?? 0, // 后期流程可能会用到
                'status' => 1,
                'created_at' => DateTimeHelper::now(),
            ];
        }

        $result = self::batchInsert(array_keys($data[0]), $data);
        if (!$result) {
            throw new WechatException(WechatException::SAVE_FAILURE_ERROR);
        }

        return true;
    }

    /**
     * 检测关键词是否存在
     * @param $keyword
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkKeyword($keyword): bool
    {
        return self::find()->where(['keyword' => $keyword, 'status' => 1])->exists();
    }

    /**
     * 更新关键词
     * @param array $params
     * @param int $ruleId
     * @return bool
     * @throws WechatException
     * @author 青岛开店星信息技术有限公司
     */
    public static function updateData(array $params, int $ruleId): bool
    {
        $modelIds = [];

        foreach ($params as $value) {
            $info = self::findOne(['id' => $value['id'], 'rule_id' => $ruleId]);

            if (!$info) {
                $info = new self();
                $info->setAttributes([
                    'rule_id' => $ruleId,
                ]);
            }

            $info->setAttributes([
                'keyword' => $value['keyword'],
                'type' => $value['type'],
            ]);

            if (!$info->save()) {
                throw new WechatException(WechatException::UPDATE_FAILURE_ERROR);
            }

            $modelIds[] = $info->id;
        }

        self::deleteAll([
            'and',
            ['rule_id' => $ruleId],
            ['not in', 'id', $modelIds]
        ]);

        return true;
    }

    /**
     * 根据关键字查询
     * @param $keyword
     * @return array|int|string|ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    public static function getKeywordList($keyword)
    {
        if (is_null($keyword)) return [];

        $result = self::getColl([
            'alias' => 'rule_keyword',
            'leftJoin' => [WechatRuleModel::tableName() . ' rule', 'rule.id=rule_keyword.rule_id'],
            'where' => [
                'or',
                [
                    'rule_keyword.keyword' => $keyword,
                    'rule_keyword.type' => WechatRuleKeywordConstant::WECHAT_RULE_KEYWORD_TYPE_ALL, // 全匹
                ],
                [
                    'and',
                    ['like', 'rule_keyword.keyword', $keyword],
                    ['rule_keyword.type' => WechatRuleKeywordConstant::WECHAT_RULE_KEYWORD_TYPE_BLURRY], // 模糊
                ]
            ],
            'select' => [
                'rule_keyword.rule_id',
                'rule.module as type',
                'rule.event',
                'rule.containtype',
                'rule.reply_setting',
                'rule_keyword.keyword',
            ]
        ], [
            'onlyList' => true,
            'pager' => false,
        ]);

        if (is_array($result)) {
            foreach ($result as &$value) {
                // 判断是否是关注海报 适配海报的规则id
                if ($value['type'] == 'poster' && $value['event'] == 'SCAN') {
                    $posterInfo = PosterModel::findOne(['keyword' => $keyword, 'status' => 1, 'is_deleted' => 0, 'type' => PosterTypeConstant::POSTER_TYPE_ATTENTION]);
                    if (!$posterInfo) {
                        return [];
                    }
                    $value['rule_id'] = $posterInfo->id;
                }
            }
        }

        return $result ?? [];
    }

    /**
     * 海报关键词添加与更新
     * @param $keyword
     * @param $ruleId
     * @return bool
     * @throws Exception
     * @throws WechatException
     * @author 青岛开店星信息技术有限公司
     */
    public static function createOrUpdateRuleKeyword($keyword, $ruleId): bool
    {
        $model = self::findOne(['rule_id' => $ruleId]);

        if ($model) {
            $model->setAttributes([
                'keyword' => $keyword,
                'type' => WechatRuleKeywordConstant::WECHAT_RULE_KEYWORD_TYPE_ALL,
            ]);

            $model->save();
        } else {
            $params = [
                [
                    'keyword' => $keyword,
                    'type' => WechatRuleKeywordConstant::WECHAT_RULE_KEYWORD_TYPE_ALL,
                ]
            ];

            self::addData($params, $ruleId);
        }

        return true;
    }
}
