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

namespace shopstar\mobile\member;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\constants\member\MemberCreditRecordStatusConstant;
use shopstar\constants\member\MemberCreditRecordTypeConstant;
use shopstar\helpers\RequestHelper;
use shopstar\models\member\MemberCreditRecordModel;
use shopstar\models\order\OrderModel;
use shopstar\models\shop\ShopSettings;

/**
 * 余额明细
 * Class BalanceLogController
 * @package shopstar\mobile\member
 * @author 青岛开店星信息技术有限公司
 */
class BalanceLogController extends BaseMobileApiController
{
    /**
     * 获取余额积分明细
     * @return \yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionList()
    {
        $type = RequestHelper::get('type', MemberCreditRecordTypeConstant::RECORD_TYPE_INTEGRAL);

        $params = [
            'select' => 'record.id, record.num, record.created_at, record.remark, record.status,record.order_id,order.order_no,order.user_delete',
            'where' => [
                'record.member_id' => $this->memberId,
                'record.type' => $type,
            ],
            'orderBy' => 'record.created_at desc',
            'alias' => 'record',
            'leftJoins' => [
                [OrderModel::tableName() . ' order', 'order.id = record.order_id']
            ]
        ];

        // 积分设置
        $creditSet = ShopSettings::get('sysset.credit');

        $list = MemberCreditRecordModel::getColl($params, [
            'callable' => function (&$row) use ($type, $creditSet) {
                if ($type == MemberCreditRecordTypeConstant::RECORD_TYPE_INTEGRAL) {
                    $statusText = str_replace(
                        MemberCreditRecordTypeConstant::getMessage(MemberCreditRecordTypeConstant::RECORD_TYPE_INTEGRAL),
                        $creditSet['credit_text'],
                        MemberCreditRecordStatusConstant::getMessage($row['status']));
                } else {
                    $statusText = str_replace(
                        MemberCreditRecordTypeConstant::getMessage(MemberCreditRecordTypeConstant::RECORD_TYPE__BALANCE),
                        $creditSet['balance_text'],
                        MemberCreditRecordStatusConstant::getMessage($row['status']));
                }
                $row['status_text'] = $statusText;
            }
        ]);

        return $this->result(['data' => $list]);
    }
}