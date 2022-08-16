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

namespace shopstar\admin\wechat;

use shopstar\bases\KdxAdminApiController;
use shopstar\helpers\RequestHelper;
use shopstar\models\wechat\WechatRuleKeywordModel;
use yii\web\Response;

/**
 * 公众号关键字控制器
 * Class WechatRuleKeywordController.
 * @package shopstar\admin\wechat
 */
class WechatRuleKeywordController extends KdxAdminApiController
{
    /**
     * 查询关键字是否重复
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionCheckKeyword()
    {
        $keyword = RequestHelper::post('keyword');

        $result = WechatRuleKeywordModel::checkKeyword($keyword);

        if ($result) {
            return $this->error('关键词重复');
        }

        return $this->success();
    }

    /**
     * 简单关键字列表
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSimpleList()
    {
        $list = WechatRuleKeywordModel::getColl([
            'where' => [
                'and',
                ['status' => 1],
                ['!=', 'type', 0]
            ],
            'select' => [
                'id',
                'keyword',
                'type'
            ],
            'orderBy' => [
                'displayorder' => SORT_DESC,
                'created_at' => SORT_DESC
            ]
        ], [
            'pager' => false
        ]);

        return $this->result($list);
    }
}
