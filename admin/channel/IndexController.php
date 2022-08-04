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

namespace shopstar\admin\channel;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\ClientTypeConstant;
use shopstar\constants\core\CoreAppTypeConstant;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\ShopUrlHelper;
use shopstar\models\byteDance\ByteDanceUploadLogModel;
use shopstar\models\shop\ShopSettings;
use shopstar\models\wxapp\WxappUploadLogModel;
use shopstar\services\core\attachment\CoreAttachmentService;
use shopstar\services\core\CoreAppService;

/**
 * 渠道首页接口
 * Class IndexController
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\admin\channel
 */
class IndexController extends KdxAdminApiController
{
    /**
     * @var array
     */
    public $configActions = [
        'allowPermActions' => [
            'get-channel'
        ],
    ];

    /**
     * 获取渠道
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetChannel()
    {
        $list = CoreAppService::getAppListCacheNew(CoreAppTypeConstant::TYPE_CHANNEL, []);

        foreach ($list['list'] as $listIndex => &$listItem) {
            $func = 'get' . ucfirst($listItem['identity']) . 'Info';
            $listItem['channel_info'] = $this->$func();
        }

        $list['list'] = array_values($list['list']);

        return $this->result($list);
    }

    /**
     * 获取信息
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    private function getWechatInfo(): array
    {
        // 未配置公众号
        $result = ShopSettings::get('channel_setting.' . ClientTypeConstant::getIdentify(ClientTypeConstant::CLIENT_WECHAT));
        // 判断是否配置了公众号信息
        if (!$result['app_id']) {
            return ['connect' => false, 'url' => ''];
        }

        // 二维码地址
        $qrcodeUrl = CoreAttachmentService::getUrl($result['qr_code']);

        // 返回公众号配置信息
        return [
            'connect' => true,
            'data' => [
                'logo' => $result['logo'],
                'qrcodeimgsrc' => $qrcodeUrl,
                'name' => $result['name'],
                'type' => $result['type'],
            ],
            'url' => '',
            'is_open' => ShopSettings::get('channel.' . ClientTypeConstant::getIdentify(ClientTypeConstant::CLIENT_WECHAT), 0),
        ];

    }

    /**
     * 获取小程序信息
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     * @author 青岛开店星信息技术有限公司
     */
    private function getWxappInfo(): array
    {
        return [
            'qrcode' => WxappUploadLogModel::getWxappUnlimitedQRcode(),//二维码
            'status' => ShopSettings::get('channel.' . ClientTypeConstant::getIdentify(ClientTypeConstant::CLIENT_WXAPP))
        ];
    }

    /**
     * 获取信息
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    private function getWapInfo(): array
    {
        return [
            'is_open' => ShopSettings::get('channel.' . ClientTypeConstant::getIdentify(ClientTypeConstant::CLIENT_H5), 0),
            'url' => ShopUrlHelper::wap('/', [], true) . '/', // /** @change likexin 手机端进入ios分享问题，必须是/wap/11/ 以斜杠结尾
        ];
    }


    /**
     * 头条
     * @return array|int[]
     * @author 青岛开店星信息技术有限公司
     */
    private function getToutiaoInfo(): array
    {
        return [
            'toutiao_qrcode' => ByteDanceUploadLogModel::getByteDanceQrcode(),
            'status' => ShopSettings::get('channel.byte_dance')
        ];
    }

    /**
     * 抖音
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    private function getDouyinInfo(): array
    {
        return [
            'douyin_qrcode' => ByteDanceUploadLogModel::getByteDanceQrcode('douyin'),
            'status' => ShopSettings::get('channel.byte_dance')
        ];
    }

    /**
     * 获取信息
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    private function getPcInfo()
    {
        return [
            'is_open' => ShopSettings::get('channel.' . ClientTypeConstant::getIdentify(ClientTypeConstant::CLIENT_PC), 0),
            'url' => ShopUrlHelper::pc('/', [], true) . '/',
        ];
    }

    /**
     * 设置店铺渠道状态
     * @return \yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSetStatus(): \yii\web\Response
    {
        $status = RequestHelper::postInt('status', 0);
        $clientType = RequestHelper::postInt('client_type', 0);

        ShopSettings::set('channel.' . ClientTypeConstant::getIdentify($clientType), $status);

        return $this->success();
    }
}
