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
use shopstar\constants\wechat\WechatSyncTaskStatusConstant;
use shopstar\constants\wechat\WechatSyncTaskTypeConstant;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\LogHelper;
use shopstar\helpers\QueueHelper;
use shopstar\jobs\wechat\WechatSyncJob;
use yii\base\Exception;

/**
 * This is the model class for table "{{%wechat_sync_task}}".
 *
 * @property int $id
 * @property int $type 任务类型
 * 1:粉丝
 * 2:粉丝标签
 * 3:素材
 * @property string $created_at 任务添加时间
 * @property int $status 状态 1成功 2失败 0未执行
 * @property string $finish_time 任务完成时间
 * @property string $material_type 素材类型
 */
class WechatSyncTaskModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%wechat_sync_task}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['type', 'status'], 'integer'],
            [['created_at', 'finish_time'], 'safe'],
            [['material_type'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'type' => '任务类型 1:粉丝 2:粉丝标签 3:素材',
            'created_at' => '任务添加时间',
            'status' => '状态 1成功 2失败 0未执行',
            'finish_time' => '任务完成时间',
            'material_type' => '素材类型',
        ];
    }

    /**
     * 投递异步任务
     * @param int $type
     * @param array $options
     * @return array|int
     * @author 青岛开店星信息技术有限公司.
     */
    public static function sendTask(int $type, array $options = [])
    {
        $model = new self();
        $model->setAttributes([
            'type' => $type,
            'created_at' => DateTimeHelper::now(),
            'material_type' => $options['type'] ?: ''
        ]);

        //保存
        if (!$model->save()) {
            return error('记录保存失败');
        }

        //发送队列任务
        QueueHelper::push(new WechatSyncJob([
            'data' => [
                'task_id' => $model->id,
                'options' => $options,
                'type' => $type
            ]
        ]));

        return $model->id;
    }

    /**
     * 同步
     * @param int $taskId
     * @param int $type
     * @param array $options
     * @return bool
     * @throws Exception
     * @author 青岛开店星信息技术有限公司.
     */
    public static function sync(int $taskId, int $type, array $options = []): bool
    {
        $status = WechatSyncTaskStatusConstant::SUCCESS;

        //执行同步
        $result = WechatSyncTaskTypeConstant::getClass($type)::sync($options);

        //判断是否错误
        if (is_error($result)) {

            $status = WechatSyncTaskStatusConstant::ERROR;

            LogHelper::error('[WECHAT SYNC FANS ERROR]', [
                'message' => $result['message']
            ]);
        }

        //修改同步任务状态
        WechatSyncTaskModel::updateAll([
            'status' => $status,
            'finish_time' => DateTimeHelper::now()
        ], [
            'id' => $taskId,
        ]);

        return true;
    }

    /**
     * @param int $type
     * @param string $materialType
     * @return string
     * @author 青岛开店星信息技术有限公司.
     */
    public static function getLastSyncTime(int $type, string $materialType = ''): string
    {

        $task = self::find()->where([
            'type' => $type
        ]);

        if (!empty($materialType)) {
            $task->andWhere([
                'material_type' => $materialType
            ]);
        }

        $task = $task->orderBy([
            'created_at' => SORT_DESC
        ])->first();

        return $task['created_at'] ?? DateTimeHelper::DEFAULT_DATE_TIME;
    }
}
