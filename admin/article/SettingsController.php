<?php

namespace shopstar\admin\article;

use shopstar\bases\KdxAdminApiController;
use shopstar\exceptions\article\ArticleSettingsException;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\ShopUrlHelper;
use shopstar\helpers\StringHelper;
use shopstar\services\article\ArticleSettingsService;
use yii\web\Response;

/**
 * 商家端设置
 * Class SettingsController
 * @package shopstar\admin\article
 * @author yuning
 */
class SettingsController extends KdxAdminApiController
{
    /**
     * 保存设置
     * @return array|int[]|Response
     * @throws ArticleSettingsException
     * @author yuning
     */
    public function actionSet()
    {
        $data = RequestHelper::post('data');
        if (empty($data)) {
            return $this->error('缺少参数');
        }
        if (!StringHelper::isJson($data)) {
            return $this->error('参数格式错误');
        }

        $ArticleSettingsService = new ArticleSettingsService($this->userId);
        $ArticleSettingsService->set($data);

        return $this->success();
    }

    /**
     * 获取配置
     * @author yuning
     */
    public function actionGet()
    {
        $ArticleSettingsService = new ArticleSettingsService($this->userId);
        return $this->result(['data' => $ArticleSettingsService->get()]);
    }
}