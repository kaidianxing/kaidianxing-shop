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


namespace shopstar\models\log;

use shopstar\bases\model\BaseActiveRecord;

use shopstar\helpers\ClientHelper;
use shopstar\helpers\DateTimeHelper;
use Yii;
use yii\helpers\Json;


/**
 * This is the model class for table "{{%operation_log}}".
 *
 * @property int $id 操作日志id
 * @property string $identify_code 标识码
 * @property string $title 标题
 * @property string $content 详细操作内容
 * @property string $created_at 创建时间
 * @property int $uid 用户id
 * @property string $relation_ids 日志关联类型的id
 * @property string $action 日志类型
 * @property string $ip 访问ip
 * @property string $primary 主要字段
 * @property string $dirtyPrimary 上一次主要字段
 */
class LogModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%operation_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['identify_code','title'], 'required'],
            [['content','primary','dirty_primary'], 'string'],
            [['created_at'], 'safe'],
            [['uid', 'identify_code'], 'integer'],
            [['title', 'relation_ids'], 'string', 'max' => 191],
            [['action'], 'string', 'max' => 50],
            [['ip'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '操作日志id',
            'identify_code' => '标识码',
            'title' => '标题',
            'content' => '详细操作内容',
            'created_at' => '创建时间',
            'uid' => '用户id',
            'relation_ids' => '日志关联类型的id',
            'action' => '日志类型',
            'ip' => '访问ip',
            'primary' => '主要字段',
            'dirty_primary' => '上一次主要字段',
        ];
    }

    /**
     * 手动写入日志
     * @param int $uid
     * @param int $identifyCode
     * @param array|string $title
     * @param string $relationIds
     * @param array $options 附加条件
     * @return bool|array
     * @author 青岛开店星信息技术有限公司
     */
    public static function write(int $uid, int $identifyCode, string $title, $relationIds = '0', array $options = [])
    {
        $options = array_merge([
            'log_data' => [], //attributes
            'log_primary' => [], //主要日志字段
            'dirty_identify_code' => 0, //额外上一条查询标识码
        ], $options);

        try {
            if (is_array($relationIds)) {
                $relationIds = implode(',', $relationIds);
            }

            //上一条日志
            $dirtyWhere = [
                'identify_code' => $identifyCode,
            ];

            //如果有更多标识 则重新赋值标识码条件
            if (!empty($options['dirty_identify_code'])) {
                $dirtyWhere['identify_code'] = array_unique(array_merge((array)$identifyCode, $options['dirty_identify_code']));
            }

            $dirtyLog = [];
            if (!empty($relationIds)) {
                $dirtyWhere['relation_ids'] = $relationIds;
                $dirtyLog = self::find()->where($dirtyWhere)->orderBy(['created_at' => SORT_DESC])->one();
            }

            //获取上一条日志
            $dirtyPrimary = Json::encode([]);
            if (!empty($dirtyLog)) {
                $dirtyPrimary = $dirtyLog->primary;
            }

            $data = [
                'identify_code' => $identifyCode,
                'title' => $title,
                'content' => is_array($options['log_data']) ? Json::encode($options['log_data']) : (string)$options['log_data'],
                'action' => Yii::$app->controller->module->id . '/' . Yii::$app->controller->id . '/' . Yii::$app->controller->action->id,
                'created_at' => DateTimeHelper::now(),
                'uid' => $uid,
                'action_type' => is_array($title) ? (string)$title['action'] : '',
                'relation_ids' => (string)$relationIds,
                'ip' => ClientHelper::getIp(),
                'primary' => Json::encode($options['log_primary']),
                'dirty_primary' => $dirtyPrimary,
            ];

            $model = new self;
            $model->setAttributes($data);
            $result = $model->save();
            if (is_error($result)) {
                return error($model->getErrorMessage());
            }

        } catch (\Throwable $throwable) {
            return error($throwable->getMessage());
        }


        return true;
    }
}
