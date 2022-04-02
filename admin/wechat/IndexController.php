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

use EasyWeChat\Factory;
use shopstar\bases\KdxAdminApiController;
use shopstar\constants\ClientTypeConstant;
use shopstar\exceptions\wechat\WechatException;
use shopstar\helpers\FileHelper;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\ShopUrlHelper;
use shopstar\helpers\StringHelper;
use shopstar\models\shop\ShopSettings;

/**
 * 公众号配置信息
 * Class IndexController
 * @package shopstar\admin\wechat
 * @author 青岛开店星信息技术有限公司
 */
class IndexController extends KdxAdminApiController
{

    /**
     * 获取公众号配置
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGet()
    {
        $info = ShopSettings::get('channel_setting.wechat');
        $info['status'] = (string)ShopSettings::get('channel.' . ClientTypeConstant::getIdentify(ClientTypeConstant::CLIENT_WECHAT));
        foreach (ShopSettings::$wechatTypeMap as $value) {
            if ($value['key'] == $info['type']) {
                $info['type_name'] = $value['value'];
            }
        }

        return $this->result(['data' => $info]);
    }

    /**
     * 保存公众号配置
     * @return \yii\web\Response
     * @throws WechatException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSet(): \yii\web\Response
    {
        $params = self::process();
        $data = [
            'name' => $params['name'],
            'type' => $params['type'],
            'app_id' => $params['app_id'],
            'secret' => $params['secret'],
            'logo' => $params['logo'],
            'qr_code' => $params['qr_code'],
        ];

        // 编辑配置
        if (RequestHelper::post('edit') == 'edit') {
            // 编辑配置防止绕过限制修改app_id
            $wechatInfo = ShopSettings::get('channel_setting.wechat');
            $data['app_id'] = $wechatInfo['app_id'];
            $data['bases'] = $wechatInfo['bases'];
        }
        $result = ShopSettings::set('channel_setting.wechat', $data);
        if (is_error($result)) {
            return $this->error('保存失败');
        }

        return $this->success();
    }

    /**
     * 获取公众号类型
     * @return \yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetType(): \yii\web\Response
    {
        return $this->success(['data' => ShopSettings::$wechatTypeMap]);
    }

    /**
     * 设置url-token-encoding_aes_key等
     * (已废弃  后期确定无用后删除)
     * @return \yii\web\Response
     * @throws WechatException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSetUrl(): \yii\web\Response
    {
        // 验证
        $params = self::processUrl();

        $data = [
            'url' => $params['url'],
            'token' => $params['token'],
            'encoding_aes_key' => $params['encoding_aes_key'],
            'encryption_type' => 3, // 默认走安全模式
        ];
        $result = ShopSettings::set('channel_setting.wechat.bases', $data);
        if (is_error($result)) {
            return $this->error('保存失败');
        }
        return $this->success();
    }

    /**
     * 引导页获取token
     * @return \yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetUrl(): \yii\web\Response
    {
        $sign = RequestHelper::post('sign', 'not');
        $result = [
            'url' => '',
            'token' => '',
            'encoding_aes_key' => '',
        ];
        if ($sign == 'not') {
            $result = ShopSettings::get('channel_setting.wechat.bases');
        }
        $data = [
            'url' => ShopUrlHelper::wap('/api/notify/response/independent', [], true), // 可能存在内外网地址不同的问题,所以地址需要实时获取
            'token' => $result['token'] ?: StringHelper::random(32),
            'encoding_aes_key' => $result['encoding_aes_key'] ?: StringHelper::random(43),
            'encryption_type' => 3, // 默认走安全模式
        ];
        $result = ShopSettings::set('channel_setting.wechat.bases', $data);
        if (is_error($result)) {
            return $this->error('保存失败');
        }
        unset($data['encryption_type']);

        return $this->success(['data' => $data]);
    }

    /**
     * 生成密钥
     * (已废弃  后期确定无用后删除)
     * @return array|\yii\web\Response
     * @throws WechatException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGenerate()
    {
        $type = RequestHelper::post('type');
        if (empty($type)) {
            throw new WechatException(WechatException::CHANNEL_MANAGE_WECHAT_MEDIA_UPLOAD_IMAGE_PARAMS_ERROR);
        }
        $data = [];
        switch ($type) {
            case 'token':
                $data['token'] = StringHelper::random(32);
                break;
            case 'encoding_aes_key':
                $data['encoding_aes_key'] = StringHelper::random(43);
                break;
            case 'all':
                $data['token'] = StringHelper::random(32);
                $data['encoding_aes_key'] = StringHelper::random(43);
                break;
        }
        return $this->result(['data' => $data]);
    }

    /**
     * 验证字段数据
     * @return array|mixed
     * @throws WechatException
     * @author 青岛开店星信息技术有限公司
     */
    public static function process()
    {
        $params = RequestHelper::post();
        if (empty($params['name']) || empty($params['type'] || empty($params['app_id']) || empty($params['secret']) || empty($params['logo']) || empty($params['qr_code']))) {
            throw new WechatException(WechatException::CHANNEL_MANAGE_WECHAT_MEDIA_UPLOAD_IMAGE_PARAMS_ERROR);
        }
        // 文件选填
        $file = RequestHelper::post('file', 'file');
        if ($file) {
            if (isset($_FILES[$file])) {
                $filename = $_FILES[$file]['name'];
                $tmpname = $_FILES[$file]['tmp_name'];
                if (empty($tmpname)) {
                    throw new WechatException(WechatException::FILE_FORMAT_ERROR);
                }
                $ext = FileHelper::getExtension($filename);
                if ($ext != 'txt') {
                    throw new WechatException(WechatException::FILE_FORMAT_ERROR);
                }
                $uploadfile = SHOP_STAR_PUBLIC_PATH . '/' . $filename;
                $result = move_uploaded_file($tmpname, $uploadfile);
                if (!$result) {
                    // 上传失败
                    throw new WechatException(WechatException::FILE_UPLOAD_ERROR);
                }
            }
        }
        return $params;
    }

    /**
     * 验证url
     * @return array|mixed
     * @throws WechatException
     * @author 青岛开店星信息技术有限公司
     */
    public static function processUrl()
    {
        $params = RequestHelper::post();

        // 验证不能为空
        if (empty($params['url']) || empty($params['token']) || empty($params['encoding_aes_key']) || empty($params['encryption_type'])) {
            throw new WechatException(WechatException::CHANNEL_MANAGE_WECHAT_MEDIA_UPLOAD_IMAGE_PARAMS_ERROR);
        }

        // 验证长度
        if (!StringHelper::exists($params['url'], ['http://', 'https://'], StringHelper::SEL_OR) || (3 > StringHelper::length($params['token']) || StringHelper::length($params['token']) > 32) || StringHelper::length($params['encoding_aes_key']) != 43) {
            throw new WechatException(WechatException::CHANNEL_MANAGE_WECHAT_MEDIA_UPLOAD_IMAGE_PARAMS_ERROR);
        }

        // 验证消息密钥的组成规则
        if (!preg_match("/^[A-Za-z0-9]+$/", $params['encoding_aes_key'])) {
            throw new WechatException(WechatException::CHANNEL_MANAGE_WECHAT_MEDIA_UPLOAD_IMAGE_PARAMS_ERROR);
        }

        return $params;
    }

    /**
     * 测试接口
     * @return \yii\web\Response
     * @throws WechatException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionTest(): \yii\web\Response
    {
        $params = RequestHelper::post();
        if (empty($params['app_id']) || empty($params['secret'])) {
            throw new WechatException(WechatException::CHANNEL_MANAGE_WECHAT_MEDIA_UPLOAD_IMAGE_PARAMS_ERROR);
        }
        $config = [
            'app_id' => $params['app_id'],
            'secret' => $params['secret'],
            'response_type' => 'array',
        ];
        try {
            $app = Factory::officialAccount($config);
            $accessToken = $app->access_token->getToken(true);
            if ($accessToken['access_token']) {
                return $this->success();
            }
        } catch (\Exception $e) {
            return $this->error('测试失败,参数错误');
        }

        return $this->success();
    }
}
