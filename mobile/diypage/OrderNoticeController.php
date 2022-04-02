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

namespace shopstar\mobile\diypage;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\helpers\RequestHelper;
use shopstar\models\member\MemberModel;
use shopstar\models\order\OrderModel;

/**
 * 下单提醒
 * Class OrderNoticeController
 * @package shopstar\mobile\diypage
 * @author 青岛开店星信息技术有限公司
 */
class OrderNoticeController extends BaseMobileApiController
{

    /**
     * @var string[] 允许不登录访问的Actions
     */
    public $configActions = [
        'allowActions' => [
            'get',
        ]
    ];

    /**
     * 获取下单提醒
     * @return array|int[]|\yii\web\Response
     * @author likexin
     */
    public function actionGet()
    {
        // 数据类型 0: 随机会员 1: 读取系统订单
        $type = RequestHelper::getInt('type');
        if (!in_array($type, [0, 1])) {
            return $this->error('错误的类型');
        }

        // 限制条数
        $limit = 5;

        if ($type == 0) {
            $list = MemberModel::getColl([
                'where' => ['<>', 'id', $this->memberId],
                'select' => ['nickname', 'avatar'],
                'limit' => $limit,
                'orderBy' => 'RAND()',
            ], [
                'onlyList' => true,
                'pager' => false,
            ]);
        } else {
            $list = OrderModel::getColl([
                'alias' => 'order',
                'leftJoin' => [MemberModel::tableName() . ' as member', 'member.id = order.member_id'],
                'where' => [],
                'select' => ['member.nickname', 'member.avatar', 'order.created_at'],
                'limit' => $limit,
                'orderBy' => [
                    'order.created_at' => SORT_DESC,
                ],
            ], [
                'onlyList' => true,
                'pager' => false,
            ]);
        }

        // 遍历列表计算秒数
        if (!empty($list)) {

            // 接收随机时间
            $startSecond = RequestHelper::getInt('start_second');
            $endSecond = RequestHelper::getInt('end_second');
            if ($endSecond <= $startSecond) {
                $endSecond = $endSecond + rand(10, 60);
            }
            $time = time();

            foreach ($list as &$row) {
                if ($type == 0) {
                    $row['second'] = $time = rand($startSecond, $endSecond);;
                } else {
                    $row['second'] = abs(strtotime($row['created_at']) - $time);
                    unset($row['created_at']);
                }
            }
        }

        return $this->result([
            'list' => $list,
        ]);
    }

}