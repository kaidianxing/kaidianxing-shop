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

use Exception;
use ReflectionException;
use shopstar\bases\model\BaseActiveRecord;
use shopstar\exceptions\wechat\WechatException;
use shopstar\helpers\DateTimeHelper;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%wechat_rule}}".
 *
 * @property int $id
 * @property int $unionid 绑定开放平台的
 * @property string $name 规则名称
 * @property string $module 模块
 * @property string $event 事件
 * @property string $event_key 事件扩展参数
 * @property int $displayorder 显示顺序
 * @property int $reply_setting 回复设置 1回复首条 2随机一条 3全部
 * @property int $status 状态 0关闭 1开启
 * @property string $containtype 包含类型
 * @property string $created_at 创建时间
 */
class WechatRuleModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%wechat_rule}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['unionid', 'displayorder', 'reply_setting', 'status'], 'integer'],
            [['created_at'], 'safe'],
            [['name', 'module'], 'string', 'max' => 191],
            [['event', 'containtype'], 'string', 'max' => 100],
            [['event_key'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'unionid' => '绑定开放平台的',
            'name' => '规则名称',
            'module' => '模块',
            'event' => '事件',
            'event_key' => '事件扩展参数',
            'displayorder' => '显示顺序',
            'reply_setting' => '回复设置 1回复首条 2随机一条 3全部',
            'status' => '状态 0关闭 1开启',
            'containtype' => '包含类型',
            'created_at' => '创建时间',
        ];
    }

    /**
     * 事件专用查询
     * 根据类型查询回复内容
     * @param $event
     * @param string $module
     * @param string $eventKey
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getEvent($event, string $module = 'wechat', string $eventKey = ''): array
    {
        // 扫码事件特殊处理
        if ($event == 'subscribe') {
            $eventKeys = explode('_', $eventKey);
            $eventKey = $eventKeys[1] ?: '';
            // 默认值 前端第一次传值 没有结果，限制null 页面显示空 给默认值 正常显示
            $result = [
                'type' => 'text'
            ];
        }

        // 因为海报的event_key是活动id 如果用海报id生成二维码ticket每个会员生成的ticket都一样 所以将海报id作为前缀穿过来
        if ($module == 'poster') {
            $eventKeys = explode('-', $eventKey);
            $eventKey = $eventKeys[0] ?: '';
        }

        $where = ['module' => $module, 'status' => 1, 'event_key' => $eventKey];

        if ($module != 'poster') {
            $where['event'] = $event;
        }

        $query = self::findOne($where);
        if ($module == 'poster') {
            return [
                [
                    'rule_id' => substr($query->name, 19),
                    'type' => $module,
                ]
            ];
        }


        if ($query->containtype) {
            $containtype = array_filter(explode(',', $query->containtype));
            switch ($containtype[0]) {
                case 'text':
                    $result = WechatRuleTextReplyModel::getinfoByRuleId($query->id, 'text');
                    break;
                case 'images':
                    $result = WechatRuleImagesReplyModel::getinfoByRuleId($query->id, 'images');
                    break;
            }
        }

        // 为了适配外层多匹配解读方法
        return [[$result]];
    }

    /**
     * 删除
     * @param int $id
     * @return bool
     * @throws WechatException
     * @author 青岛开店星信息技术有限公司
     */
    public static function deleteData(int $id): bool
    {
        $transaction = Yii::$app->db->beginTransaction();
        // 物理删除
        try {
            // 删除规则表
            self::deleteAll(['id' => $id]);
            // 删除相关联的关键词
            WechatRuleKeywordModel::deleteAll(['rule_id' => $id]);
            // 删除相关联的关键词回复文字内容
            WechatRuleTextReplyModel::deleteAll(['rule_id' => $id]);
            // 删除相关联的关键词回复图片内容
            WechatRuleImagesReplyModel::deleteAll(['rule_id' => $id]);
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            throw new WechatException(WechatException::DELETE_FAILURE_ERROR);
        }

        return true;
    }

    /**
     * 关键词添加
     * @param array $params
     * @return bool
     * @throws WechatException
     * @author 青岛开店星信息技术有限公司
     */
    public static function addAllData(array $params): bool
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $data = [
                'unionid' => 0,
                'name' => $params['name'],
                'module' => $params['module'],
                'event' => $params['event'],
                'event_key' => '',
                'displayorder' => 0,
                'reply_setting' => $params['reply_setting'],
                'status' => 1,
                'containtype' => implode(',', array_unique($params['containtype'])),
                'created_at' => DateTimeHelper::now(),
            ];

            $model = new self();
            $model->setAttributes($data);
            if (!$model->save()) {
                throw new WechatException(WechatException::CHANNEL_MANAGE_WECHAT_MEDIA_UPLOAD_IMAGE_PARAMS_ERROR, $model->getErrorMessage());
            }

            // 关键词
            WechatRuleKeywordModel::addData($params['ruleKeywordData'], $model->id);

            foreach ($params['containtype'] as $value) {
                // 文本
                if ($value == 'text') {
                    WechatRuleTextReplyModel::addData($params['ruleContent'], $model->id);
                }
                // 图片
                if ($value == 'images') {
                    WechatRuleImagesReplyModel::addData($params['ruleContent'], $model->id);
                }
            }

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            throw new WechatException(WechatException::SAVE_FAILURE_ERROR, $e->getMessage());
        }

        return true;
    }

    /**
     * 更新
     * @param int $ruleId
     * @param array $params
     * @param string $flag
     * @return bool
     * @throws InvalidConfigException
     * @throws WechatException
     * @throws ReflectionException
     * @author 青岛开店星信息技术有限公司
     */
    public static function updateAllData(int $ruleId, array $params, string $flag = ''): bool
    {
        $model = self::findOne(['id' => $ruleId]);

        $model->setAttributes([
            'name' => $params['name'],
            'reply_setting' => $params['reply_setting'],
            'containtype' => implode(',', array_unique($params['containtype'])),
        ]);

        if (!$model->save()) {
            throw new WechatException(WechatException::UPDATE_FAILURE_ERROR);
        }

        // 关键词
        WechatRuleKeywordModel::updateData($params['ruleKeywordData'], $ruleId);

        // 文本
        foreach ($params['ruleContent'] as $value) {
            if ($value['containtype'] == 'text') {
                WechatRuleTextReplyModel::updateData($params['ruleContent'], $ruleId, $flag);
            } elseif ($value['containtype'] == 'images') {
                // 图片
                WechatRuleImagesReplyModel::updateData($params['ruleContent'], $ruleId, $flag, (int)$value['attachment_id']);
            }
        }

        return true;
    }

    /**
     * 获取单一信息
     * @param int $ruleId
     * @return array|int|string|ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    public static function getInfo(int $ruleId)
    {
        $result = self::getColl([
            'where' => [
                'id' => $ruleId,
            ],
            'select' => [
                'id',
                'name',
                'reply_setting',
                'containtype',
            ]
        ], [
            'onlyList' => true,
            'pager' => false,
            'callable' => function (&$row) {
                $row['containtype'] = explode(',', $row['containtype']);
                // 获取关键词
                $row['rule_keyword_data'] = WechatRuleKeywordModel::getColl([
                    'where' => [
                        'rule_id' => $row['id'],
                    ],
                    'select' => [
                        'id',
                        'keyword',
                        'type',
                    ]
                ], [
                    'onlyList' => true,
                    'pager' => false,
                ]);
                foreach ($row['containtype'] as $value) {
                    // 获取回复内容
                    if ($value == 'text') {
                        $text = WechatRuleTextReplyModel::getColl([
                            'where' => [
                                'rule_id' => $row['id'],
                            ],
                            'select' => [
                                'id',
                                'content',
                            ]
                        ], [
                            'onlyList' => true,
                            'pager' => false,
                            'callable' => function (&$row) {
                                $row['containtype'] = 'text';
                            }
                        ]);
                    }
                    if ($value == 'images') {
                        $images = WechatRuleImagesReplyModel::getColl([
                            'where' => [
                                'rule_id' => $row['id'],
                            ],
                            'select' => [
                                'id',
                                'path as content',
                            ]
                        ], [
                            'onlyList' => true,
                            'pager' => false,
                            'callable' => function (&$row) {
                                $row['containtype'] = 'images';
                            }
                        ]);
                    }
                }
                $row['rule_content'] = array_merge($text ?? [], $images ?? []);
            },
        ]);
        return $result[0] ?? [];
    }

    /**
     * 获取关键词回复内容
     * @param $keyword
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getKeywordContent($keyword): array
    {
        $result = WechatRuleKeywordModel::getKeywordList($keyword);
        // 匹中多个规则
        foreach ($result as $item) {
            $containtype = explode(',', $item['containtype']);
            foreach ($containtype as $value) {
                //回复首条
                if ($item['reply_setting'] == 1) {
                    if ($value == 'text') {
                        $textReplyInfo = WechatRuleTextReplyModel::getinfoByRuleId($item['rule_id'], $value);
                    }
                    if ($value == 'images') {
                        $imagesReplayList = WechatRuleImagesReplyModel::getinfoByRuleId($item['rule_id'], $value);
                    }
                } else {
                    // 随机回复一条或回复全部
                    if ($value == 'text') {
                        $textReplyList = WechatRuleTextReplyModel::getinfoByRuleId($item['rule_id'], $value, 'all');
                    }
                    if ($value == 'images') {
                        $imagesReplayList = WechatRuleImagesReplyModel::getinfoByRuleId($item['rule_id'], $value, 'all');
                    }
                    $replyContent2 = array_merge($textReplyList ?? [], $imagesReplayList ?? []);
                }
            }

            // 解读回复设置
            if ($item['reply_setting'] == 1) {
                $replyContent[] = [$textReplyInfo ?? $imagesReplayList];
            } elseif ($item['reply_setting'] == 2) {
                $replyContent[] = [$replyContent2[array_rand($replyContent2)]];
            } elseif ($item['reply_setting'] == 3) {
                $replyContent[] = $replyContent2;
            }
        }
        return $replyContent;
    }
}
