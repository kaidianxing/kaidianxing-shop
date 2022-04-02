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

namespace shopstar\mobile;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\helpers\RequestHelper;
use shopstar\models\sysset\NoticeModel;

/**
 * 公告
 * Class NoticeController
 * @author 青岛开店星信息技术有限公司
 * @package shop\client
 */
class NoticeController extends BaseMobileApiController
{
    public $configActions = [
        'allowNotLoginActions' => [
            'get-list',
            'get-notice',
        ]
    ];

    /**
     * 获取公告
     * @return array|int[]|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetList()
    {
        $get = RequestHelper::get();
        $where['status'] = 1;

        if ($get['id']) {
            $where['id'] = $get['id'];
        }

        $params = [
            'searchs' => [
                ['status', 'int', 'status'],
                ['title', 'like', 'keyword'],
            ],
            'where' => $where,
            'select' => [
                'id',
                'sort_by',
                'title',
                'link',
            ],
            'orderBy' => [
                'sort_by' => SORT_DESC,
                'id' => SORT_DESC,
            ],
            'limit' => $get['limit']
        ];


        $list = NoticeModel::getColl($params, [
            'pager' => false
        ]);
        return $this->result($list);
    }

    /**
     * 获取公告
     * @return array|int[]|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetNotice()
    {
        $id = RequestHelper::get('id');
        if (empty($id)) {
            return $this->error('参数错误');
        }
        $detail = NoticeModel::findOne(['id' => $id]);
        if (empty($detail)) {
            return $this->error('公告不存在');
        }

        return $this->result($detail);
    }
}
