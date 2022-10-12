<?php

namespace shopstar\services\article;

use shopstar\constants\article\ArticleLogConstant;
use shopstar\exceptions\article\ArticleSettingsException;
use shopstar\models\log\LogModel;
use shopstar\models\shop\ShopSettings;
use yii\helpers\Json;

/**
 * 基础设置服务类
 * Class ArticleSettingsService
 * @package shopstar\services\article
 * @author yuning
 */
class ArticleSettingsService extends ArticleBaseService
{
    /**
     * 设置值的key
     * @var string
     */
    private string $key = 'article';
    private int $bannerNum = 5;

    /**
     * 设置配置
     * @param string $data
     * @return bool
     * @throws ArticleSettingsException
     * @author yuning
     */
    public function set(string $data = ''): bool
    {
        $data = Json::decode($data);
        // title必填
        if (!isset($data['title'])) {
            throw new ArticleSettingsException(ArticleSettingsException::SETTINGS_PARAMS_TITLE_EMPTY);
        }
        // 奖励时间10-60
        if (!isset($data['reward_time_limit'])) {
            throw new ArticleSettingsException(ArticleSettingsException::SETTINGS_PARAMS_REWARD_TIME_LIMIT_EMPTY);
        }
        if ($data['reward_time_limit'] > 60 || $data['reward_time_limit'] < 10) {
            throw new ArticleSettingsException(ArticleSettingsException::SETTINGS_PARAMS_REWARD_TIME_LIMIT_ERROR);
        }
        // 图片0-5
        if (count($data['banner']) > $this->bannerNum) {
            throw new ArticleSettingsException(ArticleSettingsException::SETTINGS_PARAMS_BANNER_NUM_ERROR);
        }

        // 日志
        $logPrimary = [
            '专题页面自定义名称' => $data['title'],
            '专题模板' => $data['template_type'] == 1 ? '小图模式' : '瀑布流',
            '转发奖励时间(秒)' => $data['reward_time_limit'],
        ];
        $logData = [
            'title' => $data['title'],
            'template_type' => $data['template_type'],
            'reward_time_limit' => $data['reward_time_limit'],
        ];
        LogModel::write(
            $this->userId,
            ArticleLogConstant::ARTICLE_SETTINGS_OPEN_STATUS,
            ArticleLogConstant::getText(ArticleLogConstant::ARTICLE_SETTINGS_OPEN_STATUS),
            1,
            [
                'log_data' => $logData,
                'log_primary' => $logPrimary,
            ]
        );

        ShopSettings::set($this->key, $data);
        return true;
    }

    /**
     * 获取设置
     * @return array|mixed|string
     * @author yuning
     */
    public function get()
    {
        return ShopSettings::get($this->key);
    }
}