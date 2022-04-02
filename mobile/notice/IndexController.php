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

namespace shopstar\mobile\notice;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\helpers\RequestHelper;
use shopstar\models\notice\NoticeWxappTemplateModel;
use shopstar\models\shop\ShopSettings;

/**
 * @author 青岛开店星信息技术有限公司
 */
class IndexController extends BaseMobileApiController
{
    /**
     * 获取订阅消息pri_tmp_id
     * @return array|int[]|\yii\web\Response
     * @throws \Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $get = RequestHelper::post();
        if (empty($get['type_code'])) {
            throw new \Exception('缺少type_code');
        }

        $data = [];
        foreach ((array)$get['type_code'] as $item) {
            $setting = ShopSettings::get('plugin_notice.send.' . $item . '.wxapp');
            if (!empty($setting)) {
                $model = NoticeWxappTemplateModel::findOne(['id' => $setting['template_id']]);
                $data[] = $model->pri_tmpl_id;
            }
        }

        $data = array_values(array_filter($data));
        return $this->success(['data' => $data]);
    }
}
