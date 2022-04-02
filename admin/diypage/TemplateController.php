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

namespace shopstar\admin\diypage;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\diypage\DiypageTypeConstant;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\ImageHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\diypage\DiypageTemplateModel;

/**
 * 模板管理
 * Class TemplateController
 * @package shopstar\admin\diypage
 */
class TemplateController extends KdxAdminApiController
{

    /**
     * @var array 需要POST请求的Actions
     */
    public $configActions = [
        'postActions' => [
            'delete',
        ]
    ];

    /**
     * 页面另存为模板
     * @return array|\yii\web\Response
     * @author likexin
     */
    public function actionAdd()
    {
        $result = DiypageTemplateModel::easyAdd([
            'attributes' => [
                'status' => 1,
                'created_at' => DateTimeHelper::now(),
            ],
            'filterPostField' => ['system_id'],
            'beforeSave' => function (DiypageTemplateModel $model) {
                // 检测页面类型
                $checkType = DiypageTypeConstant::getOneByCode($model->type);
                if (is_null($checkType)) {
                    return error('错误的模板类型');
                }

                // 保存缩略图
                $model->thumb = $this->saveThumb($model->thumb);
            },
        ]);

        return $this->result($result);
    }

    /**
     * 获取模板
     * @return array|\yii\web\Response
     * @author likexin
     */
    public function actionGet()
    {
        $id = RequestHelper::getInt('id');
        if (empty($id)) {
            return $this->error('参数错误');
        }

        // 查询模板
        $template = DiypageTemplateModel::find()
            ->where([
                'and',
                ['id' => $id],
                ['>', 'system_id', 0],
                ['status' => 1],
            ])
            ->select(['id', 'name', 'type', 'thumb', 'common', 'content', 'system_id'])
            ->first();
        if (empty($template)) {
            return $this->error('模板不存在');
        }

        return $this->result([
            'data' => $template,
        ]);
    }

    /**
     * 删除模板
     * @return array|\yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @author likexin
     */
    public function actionDelete()
    {
        $result = DiypageTemplateModel::easyDelete([
            'andWhere' => [
                'system_id' => 0,
            ],
        ]);

        return $this->result($result);
    }

    /**
     * @param string $base64
     * @return string
     * @throws \yii\base\Exception
     * @author likexin
     */
    private function saveThumb(string $base64): string
    {
        if (empty($base64)) {
            return '';
        }

        // 文件存储路径
        $path = SHOP_STAR_PUBLIC_DATA_PATH . '/diypage/template/thumb_' . md5($base64) . '.jpg';

        // 转存图片
        ImageHelper::createFromBase64($base64, $path);

        return '/data/diypage/template/thumb_' . md5($base64) . '.jpg';
    }

}