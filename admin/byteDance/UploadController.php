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

namespace shopstar\admin\byteDance;

use shopstar\helpers\CacheHelper;
use shopstar\helpers\CloudServiceHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\LiYangHelper;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\StringHelper;
use shopstar\models\core\attachment\CoreAttachmentModel;
use shopstar\models\shop\ShopSettings;
use shopstar\exceptions\byteDance\ByteDanceException;
use shopstar\models\byteDance\ByteDanceUploadLogModel;
use shopstar\bases\KdxAdminApiController;
use shopstar\services\core\attachment\CoreAttachmentService;
use yii\helpers\Url;

/**
 * 上传1
 * Class UploadController
 * @package apps\byteDance\manage
 */
class UploadController extends KdxAdminApiController
{
    public $configActions = [
        'allowPermActions' => [
            'logout'
        ]
    ];

    /**
     * 上传记录
     * @author 青岛开店星信息技术有限公司
     */
    public function actionLog()
    {
        $list = ByteDanceUploadLogModel::getColl([
            'where' => [],
            'orderBy' => ['publish_time' => SORT_DESC]
        ]);
        return $this->result($list);
    }

    /**
     * 初始化
     * @return array|int[]|\yii\web\Response
     * @throws ByteDanceException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionInit()
    {
        $data = [];

        $setting = ShopSettings::get('channel_setting.byte_dance');
        $status = ShopSettings::get('channel.byte_dance'); // 渠道开启状态
        if ($status == 0) {
            throw new ByteDanceException(ByteDanceException::UPLOAD_GET_CHANNEL_STATUS);
        }


        // 请求远端获取
        $latestVersion = CloudServiceHelper::get(LiYangHelper::ROUTE_BD_APP_UPLOAD_INIT, [
            'app_id' => $setting['appid']
        ]);
        if (is_error($latestVersion)) {
            throw new ByteDanceException(ByteDanceException::CLOUD_INIT_ERROR, $latestVersion['message']);
        }

        // 云端小程序写入缓存
        CacheHelper::set('byte_dance_latest_version_', $latestVersion);

        $data['server'] = [
            'version' => $latestVersion['latest_version']['version'],
            'publish_time' => $latestVersion['latest_version']['release_time'], //服务端发布时间
        ];
        // 二维码地址
        $data['douyin_code_path'] = ByteDanceUploadLogModel::getByteDanceQrcode('douyin');
        $data['toutiao_code_path'] = ByteDanceUploadLogModel::getByteDanceQrcode('toutiao');
        // 最后一次上传
        $data['last_audit'] = ByteDanceUploadLogModel::find()
            ->orderBy(['publish_time' => SORT_DESC])
            ->select(['version', 'publish_time', 'describe', 'server_version',])
            ->asArray()
            ->one();
        $data['preview_qrcode'] = CacheHelper::get('byte_dance_preview_qrcode_'  . '_' . $setting['appid']);

        return $this->result($data);
    }

    /**
     * 获取验证码
     * @throws ByteDanceException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetCaptcha()
    {
        $result = CloudServiceHelper::get(LiYangHelper::ROUTE_BD_APP_UPLOAD_LOGIN_GET_CAPTCHA);
        if (is_error($result)) {
            // 处理错误
            throw new ByteDanceException(ByteDanceException::GET_CAPTCHA_FAIL, $result['message']);
        }
        return $this->result($result);
    }

    /**
     * 短信登录
     * @throws ByteDanceException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSmsLogin()
    {
        $post = RequestHelper::post();
        if (empty($post['captcha']) || empty($post['mobile']) || empty($post['code'])) {
            throw new ByteDanceException(ByteDanceException::SMS_LOGIN_PARAMS_ERROR);
        }
        $result = CloudServiceHelper::post(LiYangHelper::ROUTE_BD_APP_UPLOAD_LOGIN_SMS_LOGIN, [
            'captcha' => $post['captcha'],
            'mobile' => $post['mobile'],
            'code' => $post['code'],
        ]);
        if (is_error($result)) {
            // 处理错误
            throw new ByteDanceException(ByteDanceException::SMS_LOGIN_FAIL, $result['message']);
        }
        // 登录成功 缓存一天
        CacheHelper::set('byte_dance_session_key_', $result['session_key'], 24 * 60 * 60);

        return $this->result($result);
    }

    /**
     * 发送验证码
     * @throws ByteDanceException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSendSms()
    {
        $post = RequestHelper::post();
        if (empty($post['captcha']) || empty($post['mobile'])) {
            throw new ByteDanceException(ByteDanceException::SEND_SMS_PARAMS_ERROR);
        }
        $result = CloudServiceHelper::post(LiYangHelper::ROUTE_BD_APP_UPLOAD_LOGIN_SEND_SMS_CODE, [
            'captcha' => $post['captcha'],
            'mobile' => $post['mobile'],
        ]);
        if (is_error($result)) {
            // 处理错误
            throw new ByteDanceException(ByteDanceException::SEND_SMS_FAIL, $result['message']);
        }

        return $this->success();
    }

    /**
     * 邮箱登录
     * @throws ByteDanceException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEmailLogin()
    {
        $post = RequestHelper::post();
        if (empty($post['email']) || empty($post['password']) || empty($post['captcha'])) {
            throw new ByteDanceException(ByteDanceException::EMAIL_LOGIN_PARAMS_ERROR);
        }
        $result = CloudServiceHelper::post(LiYangHelper::ROUTE_BD_APP_UPLOAD_LOGIN_EMAIL_LOGIN, [
            'email' => $post['email'],
            'password' => $post['password'],
            'captcha' => $post['captcha'],
        ]);
        if (is_error($result)) {
            // 处理错误
            throw new ByteDanceException(ByteDanceException::EMAIL_LOGIN_FAIL, $result['message']);
        }
        // 缓存一天
        CacheHelper::set('byte_dance_session_key_', $result['session_key'], 24 * 60 * 60);

        return $this->success();
    }

    /**
     * 获取登录状态
     * @throws ByteDanceException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetLoginStatus()
    {
        $sessionKey = CacheHelper::get('byte_dance_session_key_');
        if (empty($sessionKey)) {
            throw new ByteDanceException(ByteDanceException::GET_LOGIN_STATUS_UN_LOGIN);
        }
        return $this->success();
    }

    /**
     * 退出登录
     * @author 青岛开店星信息技术有限公司
     */
    public function actionLogout()
    {
        CacheHelper::delete('byte_dance_session_key_');
        return $this->success();
    }

    /**
     * 上传
     * @throws ByteDanceException|\yii\base\InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionUpload()
    {
        $sessionKey = CacheHelper::get('byte_dance_session_key_');
        if (empty($sessionKey)) {
            // 重新登录
            throw new ByteDanceException(ByteDanceException::UPLOAD_UN_LOGIN);
        }
        $post = RequestHelper::post();
        if (empty($post['version']) || empty($post['desc']) || empty($post['server_version'])) {
            throw new ByteDanceException(ByteDanceException::UPLOAD_PARAMS_ERROR);
        }
        if (mb_strlen($post['desc']) > 191) {
            throw new ByteDanceException(ByteDanceException::UPLOAD_DESC_TO_LONG);
        }
        // 获取配置
        $setting = ShopSettings::get('channel_setting.byte_dance');

        $cloudData = CacheHelper::get('byte_dance_latest_version_');
        if (empty($cloudData)) {
            // 刷新页面 重新初始化
            throw new ByteDanceException(ByteDanceException::UPLOAD_REFRESH_PAGE);
        }

        $baseUrl = str_replace('/addons/kdx_shop/public', '', Url::base(true));

        $result = CloudServiceHelper::post(LiYangHelper::ROUTE_BD_APP_UPLOAD_UPLOAD, [
            'mini_app_id' => $cloudData['mini_app']['id'],
            'patch_id' => $cloudData['latest_version']['id'],
            'session_key' => $sessionKey,
            'version' => $post['version'],
            'describe' => $post['desc'],
            'app_id' => $setting['appid'],
            'api_url' => $baseUrl . "/wap/api",
            'attachment_url' => CoreAttachmentService::getRoot(),
            'wap_dist_url' => $baseUrl . '/static/dist/shop/kdx_wap/',
            'public_url' => $baseUrl . '/',
        ]);
        if (is_error($result)) {
            throw new ByteDanceException(ByteDanceException::UPLOAD_CLOUD_ERROR, $result['message']);
        }

        //暂存上传数据缓存
        CacheHelper::set('audit_byte_dance_data' .  '_' . $result['upload_id'], [
            'version' => $post['version'],
            'server_version' => $post['server_version'],
            'describe' => $post['desc'],
            'publish_time' => DateTimeHelper::now(),
            'upload_id' => $result['upload_id'],
        ]);

        return $this->success([
            'upload_id' => $result['upload_id']
        ]);
    }

    /**
     * 获取上传状态
     * @throws ByteDanceException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetUploadStatus()
    {
        $uploadId = RequestHelper::get('upload_id');
        if (empty($uploadId)) {
            // 参数错误
            throw new ByteDanceException(ByteDanceException::GET_UPLOAD_STATUS_PARAMS_ERROR);
        }

        $result = CloudServiceHelper::get(LiYangHelper::ROUTE_BD_APP_UPLOAD_UPLOAD_GET_STATUS, [
            'upload_id' => $uploadId,
        ]);
        if (is_error($result)) {
            throw new ByteDanceException(ByteDanceException::GET_UPLOAD_STATUS_ERROR);
        }
        if ($result['status'] == 31 && StringHelper::exists($result['status_text'], '未登录')) {
            // 未登录
            // 删除session
            CacheHelper::delete('byte_dance_session_key_');
        } else if ($result['status'] == 32) { // 上传成功
            $setting = ShopSettings::get('channel_setting.byte_dance');
            CacheHelper::set('byte_dance_preview_qrcode_' . '_' . $setting['appid'], $result['preview_qr_code']);
            $auditData = CacheHelper::get('audit_byte_dance_data' . '_' . $uploadId);
            if (!empty($auditData)) {
                //添加上传记录
                $model = new ByteDanceUploadLogModel();
                $model->setAttributes($auditData);
                if (!$model->save()) {
                    // 保存失败
                    throw new ByteDanceException(ByteDanceException::GET_UPLOAD_STATUS_SAVE_LOG_FAIL, $model->getErrorMessage());
                }
                //删除缓存
                CacheHelper::delete('audit_byte_dance_data' . '_' . $uploadId);
            }
        }
        return $this->success(['status_text' => $result['status_text'], 'status' => $result['status']]);
    }

}