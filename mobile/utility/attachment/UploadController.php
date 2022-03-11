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

namespace shopstar\mobile\utility\attachment;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\constants\core\CoreAttachmentSceneConstant;
use shopstar\helpers\RequestHelper;
use shopstar\models\core\attachment\CoreAttachmentGroupModel;
use shopstar\models\core\attachment\CoreAttachmentModel;
use shopstar\services\core\attachment\CoreAttachmentService;

/**
 * 附件上传
 * Class UploadController
 * @package modules\utility\client\attachment
 */
class UploadController extends BaseMobileApiController
{

    /**
     * @var array 需要POST的Action
     */
    public $configActions = [
        'postActions' => [
            'index',
        ],
    ];


    /**
     * 上传附件
     * @return array|\yii\web\Response
     * @throws \ReflectionException
     * @throws \yii\base\Exception
     * @author likexin
     */
    public function actionIndex()
    {
        // 附件类型
        $type = RequestHelper::postInt('type');
        if (empty($type)) {
            return $this->error('附件类型错误');
        }

        // 调用上传方法
        $result = CoreAttachmentService::upload([
            'type' => $type,
            'account_id' => $this->memberId,
        ], CoreAttachmentSceneConstant::SCENE_MOBILE);

        return $this->result($result);
    }

    /**
     * 获取设置
     * @return array|\yii\web\Response
     * @author likexin
     */
    public function actionGetSettings()
    {
        return $this->result([
            'settings' => CoreAttachmentService::getAttachmentSettings(),
        ]);
    }

    /**
     * 删除附件
     * @return array|\yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @author likexin
     */
    public function actionDelete()
    {
        $andWhere = [
            'account_id' => $this->memberId,
            'scene' => CoreAttachmentSceneConstant::SCENE_MOBILE,
        ];

        // 执行删除
        $result = CoreAttachmentModel::easyDelete([
            'andWhere' => $andWhere,
            'afterDelete' => function (CoreAttachmentModel $model) {
                // 删除文件时对应分组数量-1
                if (!empty($model->group_id)) {
                    CoreAttachmentGroupModel::updateAllCounters(['total' => -1], [
                        'and',
                        ['id' => $model->group_id],
                        ['>', 'total', 0],
                    ]);
                }
                // 删除文件
                CoreAttachmentService::remove($model->path);
            },
        ]);

        return $this->result($result);
    }

}