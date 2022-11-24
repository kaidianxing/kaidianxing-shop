<?php
namespace shopstar\services\creditSign;

use shopstar\constants\creditSign\CreditSignActivityConstant;
use shopstar\constants\creditSign\CreditSignLogConstant;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\QueueHelper;
use shopstar\helpers\RequestHelper;
use shopstar\jobs\creditSign\AutoCreditSignStopActivityJob;
use shopstar\models\creditSign\CreditSignModel;
use shopstar\models\log\LogModel;
use shopstar\models\sale\CouponModel;
use yii\db\ActiveRecord;
use yii\helpers\Json;

/**
 * 积分签到活动服务类
 * Class CreditSignActivityService
 * @package shopstar\services\creditSign
 * @author yuning
 */
class CreditSignActivityService
{
    /**
     * 获取列表数据
     * @return array|int|string|ActiveRecord[]
     * @author yuning
     */
    public static function getList()
    {
        $startTime = RequestHelper::get('start_time');
        $endTime = RequestHelper::get('end_time');

        $andWhere = [];

        // 日期筛选
        if (!empty($startTime) && !empty($endTime)) {
            $andWhere[] = [
                'or',
                [
                    'and',
                    ['>=', 'start_time', $startTime],
                    ['<=', 'start_time', $endTime],
                    ['>=', 'end_time', $startTime],
                    ['>=', 'end_time', $endTime],

                ],
                [
                    'and',
                    ['>=', 'start_time', $startTime],
                    ['<=', 'start_time', $endTime],
                    ['>=', 'end_time', $startTime],
                    ['<=', 'end_time', $endTime],
                ],
                [
                    'and',
                    ['<=', 'start_time', $startTime],
                    ['<=', 'start_time', $endTime],
                    ['>=', 'end_time', $startTime],
                    ['>=', 'end_time', $endTime],
                ],
                [
                    'and',
                    ['<=', 'start_time', $startTime],
                    ['<=', 'start_time', $endTime],
                    ['>=', 'end_time', $startTime],
                    ['<=', 'end_time', $endTime],
                ]
            ];
        }

        // 查询参数
        $params = [
            'searchs' => [
                ['activity.activity_name', 'like', 'activity_name'],
                ['activity.status', 'int', 'status'],
            ],
            'alias' => 'activity',
            'with' => [
                'signCount',
                'signPersonCountList',
                'signTotalNum',
            ],
            'andWhere' => $andWhere,
            'select' => [
                'activity.id',
                'activity.activity_name',
                'activity.stop_time',
                'activity.start_time',
                'activity.end_time',
                'activity.status',
            ],
            'where' => [
                'activity.is_deleted' => CreditSignActivityConstant::IS_DELETE_NO,
            ],
            'groupBy' => [
                'activity.id',
            ],
            'orderBy' => [
                'activity.created_at' => SORT_DESC,
            ],
        ];

        // 附加选项
        $options = [
            'callable' => function (&$row) {
                // 获取人数
                $row['sign_person_count'] = count($row['signPersonCountList']);
                unset($row['signPersonCountList']);

                // 处理签到次数
                $row['sign_count'] = empty($row['signCount']) ? 0 : count($row['signCount']);
                unset($row['signCount']);

                $row['credit_num'] = 0;
                $row['coupon_num'] = 0;
                // 处理积分/优惠券发放记录
                if (!empty($row['signTotalNum'])) {
                    $row['credit_num'] = array_sum(array_column($row['signTotalNum'], 'credit_num'));
                    $row['coupon_num'] = array_sum(array_column($row['signTotalNum'], 'coupon_num'));
                }
                unset($row['signTotalNum']);
            },
        ];

        return CreditSignModel::getColl($params, $options);
    }

    /**
     * 获取积分签到活动详情
     * @param int $activityId
     * @return array|ActiveRecord|null
     * @author 青岛开店星信息技术有限公司
     */
    public static function getActivityDetail( int $activityId)
    {
        $activityInfo = CreditSignModel::find()
            ->where([
                'id' => $activityId,
            ])->one();

        if (empty($activityInfo)) {
            return error('活动未找到');
        }

        $extFieldData = Json::decode($activityInfo['ext_field'], true);

        // 判断连续签到是否开启
        if ($extFieldData['continuity']['status'] == 1) {
            // 循环连续签到问题
            foreach ($extFieldData['continuity']['info'] as &$value) {
                if (empty($value['award']['coupon'])) {
                    $value['coupon_info'] = [];
                    continue;
                }

                $couponArray = explode(',', $value['award']['coupon']);
                $value['coupon_info'] = CouponModel::getCouponInfo( $couponArray);
            }
            unset($value);
        }
        $activityInfo['ext_field'] = $extFieldData;

        return $activityInfo;
    }

    /**
     * 获取正在进行中的活动
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getActivityOne(): array
    {
        $activityInfo = CreditSignModel::find()
            ->where([
                'is_deleted' => CreditSignActivityConstant::IS_DELETE_NO,
                'status' => CreditSignActivityConstant::STATUS_UNDERWAY,
            ])->first();

        if (empty($activityInfo)) {
            return error('未找到正在进行中的活动');
        }

        $activityInfo['ext_field'] = !empty($activityInfo['ext_field']) ? Json::decode($activityInfo['ext_field']) : [];

        return $activityInfo;
    }

    /**
     * 添加积分签到活动
     * @param int $userId
     * @return array|void
     * @author 青岛开店星信息技术有限公司
     */
    public static function addResult(int $userId)
    {
        return CreditSignModel::easyAdd([
            'attributes' => [
                'created_at' => DateTimeHelper::now(),
                'status' => CreditSignActivityConstant::STATUS_WAIT,
            ],
            'beforeSave' => function (&$data){
                // 活动时间不能冲突 查找时间段内的活动
                $isExists = self::checkExistsByTime( $data->start_time, $data->end_time);
                if ($isExists) {
                    return error('该时间段已存在活动');
                }

                // 活动参数校验
                $checkParams = self::saveCheckParams($data);
                if (is_error($checkParams)) {
                    return error($checkParams['message']);
                }

                // 字节跳动小程序渠道 特殊处理
                $clientType = $data->client_type;
                if (in_array('30', $clientType)) {
                    $clientType[] = '31';
                    $clientType[] = '32';
                }
                $data->client_type = implode(',', $clientType);
            },
            'afterSave' => function ($result) use ($userId) {
                /**
                 * @var CreditSignModel $result
                 */

                // 如果活动开始时间未到 添加队列
                if (strtotime($result->start_time) > time()) {
                    // 添加定时开启任务
                    QueueHelper::push(new AutoCreditSignStartActivityJob([
                        'id' => $result->id
                    ]), strtotime($result->start_time) - time());
                } else {
                    // 开始时间已经等于或小于当前时间就变为进行中
                    $result->status = CreditSignActivityConstant::STATUS_UNDERWAY;
                }

                // 添加定时关闭任务
                $jobId = QueueHelper::push(new AutoCreditSignStopActivityJob([
                    'id' => $result->id,
                ]), strtotime($result->end_time) - time());

                // 保存任务id
                $result->job_id = $jobId;
                $result->save();

                // 日志
                $clientType = explode(',', $result->client_type);
                $client_type = in_array('20', $clientType) ? '微信公众号,' : '';
                $client_type .= in_array('21', $clientType) ? '微信小程序,' : '';
                $client_type .= in_array('10', $clientType) ? '手机浏览器H5,' : '';
                $client_type .= in_array('30', $clientType) ? '头条/抖音小程序,' : '';

                $extField = Json::decode($result->ext_field);

                $logPrimary = [
                    '活动名称' => $result->activity_name,
                    '活动期限' => '起:' . $result->start_time . ',止:' . $result->end_time,
                    '活动渠道' => trim($client_type),
                    '日签奖励' => $extField['day_reward'],
                ];

                if ($extField['increasing']['status'] == 1) {
                    $logPrimary['递增奖励'] = '开启,第二天起递增奖励' . $extField['increasing']['integral'] . '积分且' . $extField['increasing']['day'] . '天不再递增';
                } else {
                    $logPrimary['递增奖励'] = '关闭';
                }

                if ($extField['continuity']['status'] == 1) {
                    $continuityLog = '';
                    foreach ($extField['continuity']['info'] as $item) {
                        $continuityLog .= '连签' . $item['day'] . '天赠送:';
                        $select = ArrayHelper::explode(',', $item['award']['select']) ?: [];
                        if (in_array('credit', $select)) {
                            $continuityLog .= "积分:" . $item['award']['credit'] . "个,";
                        }
                        if (in_array('coupon', $select)) {
                            if (empty($item['award']['coupon'])) {
                                $item['coupon_info'] = [];
                                continue;
                            }

                            $couponArray = explode(',', $item['award']['coupon']);
                            $couponInfo = CouponModel::getCouponInfo($couponArray);
                            $couponLog = '';
                            foreach ($couponInfo as $value) {
                                $couponLog .= $value['coupon_name'] . ',';
                            }
                            $continuityLog .= '优惠券' . count($couponInfo) . '张:(' . $couponLog . ')';
                        }
                    }

                    $logPrimary['连签奖励'] = '开启,' . trim($continuityLog);
                } else {
                    $logPrimary['连签奖励'] = '关闭';
                }

                if ($extField['supplementary']['status'] == 1) {
                    $logPrimary['补签设置'] = '开启,补签次数' . $extField['supplementary']['num'] . '次,每次消耗' . $extField['supplementary']['consume'] . '积分';
                } else {
                    $logPrimary['补签设置'] = '关闭';
                }
                $logPrimary['规则说明'] = $extField['rule_description']['rule'];
                $logPrimary['页面名称'] = $extField['page_setup']['link_name'];
                if ($extField['page_setup']['activity_link'] == 1) {
                    $logPrimary['活动链接'] = '开启,活动标题:' . $extField['page_setup']['activity_title'] . ';入口文案:' . $extField['page_setup']['content'] . ';跳转链接:' . $extField['page_setup']['link_url'];
                } else {
                    $logPrimary['活动链接'] = '关闭';
                }

                LogModel::write(
                    $userId,
                    CreditSignLogConstant::CREDIT_SIGN_ACTIVITY_ADD_LOG,
                    CreditSignLogConstant::getText(CreditSignLogConstant::CREDIT_SIGN_ACTIVITY_ADD_LOG),
                    $result->id,
                    [
                        'log_data' => $result->attributes,
                        'log_primary' => $logPrimary,
                        'dirty_identify_code' => [
                            CreditSignLogConstant::CREDIT_SIGN_ACTIVITY_ADD_LOG,
                            CreditSignLogConstant::CREDIT_SIGN_ACTIVITY_EDIT_LOG,
                        ],
                    ]
                );
            },
        ]);

    }

    /**
     * 检查时间段内是否有活动
     * @param string $startTime
     * @param string $endTime
     * @param int $activityId
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkExistsByTime(string $startTime, string $endTime, int $activityId = 0): bool
    {
        $where = [
            'and',
            ['is_deleted' => CreditSignActivityConstant::IS_DELETE_NO],
            ['stop_time' => 0],
            [
                'or', // 进行中/未开始/自动停止 或 手动停止
                [
                    'and', // 进行中/未开始/自动停止
                    [
                        'or',
                        ['status' => CreditSignActivityConstant::STATUS_WAIT],
                        ['status' => CreditSignActivityConstant::STATUS_UNDERWAY],
                        ['status' => CreditSignActivityConstant::STATUS_MANUAL_END],
                    ],
                    [
                        'or',
                        [ // 开始时间不能在时间段内
                            'and',
                            ['<=', 'start_time', $startTime],
                            ['>=', 'end_time', $startTime],
                        ],
                        [ // 结束时间不能在时间段内
                            'and',
                            ['<=', 'start_time', $endTime],
                            ['>=', 'end_time', $endTime],
                        ],
                        [ // 开始时间比现有小  结束时间比现有大
                            'and',
                            ['>=', 'start_time', $startTime],
                            ['<=', 'end_time', $endTime]
                        ]
                    ]
                ],
                [
                    'and',
                    ['status' => CreditSignActivityConstant::STATUS_MANUAL_STOP], // 手动停止
                    [
                        'or',
                        [ // 开始时间不能在 已创建的开始时间 和停止时间内
                            'and',
                            ['<=', 'start_time', $startTime],
                            ['>=', 'stop_time', $startTime],
                        ],
                        [ // 结束时间不能在 已创建的开始时间 和停止时间内
                            'and',
                            ['<=', 'start_time', $endTime],
                            ['>=', 'stop_time', $endTime],
                        ],
                        [ // 开始时间比现有小  结束时间比现有大
                            'and',
                            ['>=', 'start_time', $startTime],
                            ['<=', 'stop_time', $endTime],
                        ]
                    ]
                ]
            ]
        ];
        if (!empty($activityId)) {
            $where[] = ['<>', 'id', $activityId];
        }

        return CreditSignModel::find()->where($where)->exists();
    }

    /**
     * 检测积分签到活动参数
     * @param $data
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function saveCheckParams($data)
    {
        $day = DateTimeHelper::days($data['end_time'], $data['start_time']); // 活动周期

        // 活动名称判断
        if (empty($data['activity_name'])) {
            return error('活动名称不可为空');
        }

        // 活动时间判断
        if (empty($data['start_time']) || empty($data['end_time'])) {
            return error('活动时间不可为空');
        }
        if ($data['start_time'] > $data['end_time']) {
            return error('活动开始时间不得大于结束时间');
        }

        // 活动渠道
        if (empty($data['client_type'])) {
            return error('活动渠道不可为空');
        }

        // 规则判断
        if (empty($data['ext_field'])) {
            return error('活动规则不可为空');
        }

        // 解析规则
        $extFieldData = json_decode($data['ext_field'], true);

        // 日常签到奖励
        if (empty($extFieldData['day_reward']) || !isset($extFieldData['day_reward'])) {
            return error('日签奖励不可为空');
        }

        // 递增奖励判断设置项
        if (!isset($extFieldData['increasing']) || empty($extFieldData['increasing'])) {
            return error('递增奖励不可为空');
        }

        // 递增奖励开启判断设置项
        if (isset($extFieldData['increasing']['status']) && $extFieldData['increasing']['status'] == 1) {

            // 开启后奖励是否为空
            if (!isset($extFieldData['increasing']['integral']) || empty($extFieldData['increasing']['integral'])) {
                return error('递增奖励不可为空');
            }

            // 开启后天数设置是否为空
            if (!isset($extFieldData['increasing']['day']) || empty($extFieldData['increasing']['day'])) {
                return error('递增奖励天数设置不可为空');
            }

            // 判断天数设置是否超过活动周期
            if ($extFieldData['increasing']['day'] > $day) {
                return error('递增奖励天数必须小于活动周期');
            }
        }

        // 连续签到设置项判断
        if (!isset($extFieldData['continuity']) || empty($extFieldData['continuity'])) {
            return error('连续签到不可为空');
        }

        // 连续签到开启后设置项判断
        if (isset($extFieldData['continuity']['status']) && $extFieldData['continuity']['status'] == 1) {
            // 签到奖励判断
            if (!isset($extFieldData['continuity']['info']) || empty($extFieldData['continuity']['info'])) {
                return error('连续签到奖励不可为空');
            }

            // 签到奖励最多7组
            if (count($extFieldData['continuity']['info']) > 7) {
                return error('连续签到奖励最多设置7组');
            }
        }

        // 补签设置项判断
        if (!isset($extFieldData['supplementary']) || empty($extFieldData['supplementary'])) {
            return error('补签奖励不可为空');
        }

        // 补签设置开启后判断
        if (isset($extFieldData['supplementary']['status']) && $extFieldData['supplementary']['status'] == 1) {

            // 补签次数判断
            if (!isset($extFieldData['supplementary']['num']) || empty($extFieldData['supplementary']['num'])) {
                return error('补签次数不可为空');
            }

            // 补签积分消耗判断
            if (!isset($extFieldData['supplementary']['consume']) || empty($extFieldData['supplementary']['consume'])) {
                return error('积分消耗不可为空');
            }

            // 补签次数不可超过活动周期判断
            if ($extFieldData['supplementary']['num'] > $day) {
                return error('补签次数不可超过活动周期');
            }
        }

        // 规则说明判断
        if (!isset($extFieldData['rule_description']['rule']) || empty($extFieldData['rule_description']['rule'])) {
            return error('规则说明不可为空');
        }

        // 页面设置判断
        if (!isset($extFieldData['page_setup']) || empty($extFieldData['page_setup'])) {
            return error('页面设置不可为空');
        }

        // 页面名称判断
        if (!isset($extFieldData['page_setup']['link_name']) || empty($extFieldData['page_setup']['link_name'])) {
            return error('页面名称不可为空');
        }

        // 活动链接判断
        if (!isset($extFieldData['page_setup']['activity_link'])) {
            return error('活动链接不可为空');
        }

        // 活动链接判断
        if (isset($extFieldData['page_setup']['activity_link']) && $extFieldData['page_setup']['activity_link'] == 1) {
            // 标题判断
            if (!isset($extFieldData['page_setup']['activity_title']) || empty($extFieldData['page_setup']['activity_title'])) {
                return error('活动链接-活动标题不可为空');
            }

            // 文案判断
            if (!isset($extFieldData['page_setup']['content']) || empty($extFieldData['page_setup']['content'])) {
                return error('活动链接-入口文案不可为空');
            }

            // 链接判断
            if (!isset($extFieldData['page_setup']['link_url']) || empty($extFieldData['page_setup']['link_url'])) {
                return error('活动链接-调整链接不可为空');
            }
        }


        return true;
    }
}
