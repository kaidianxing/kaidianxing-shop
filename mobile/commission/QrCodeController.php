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

namespace shopstar\mobile\commission;

use shopstar\components\wechat\helpers\MiniProgramACodeHelper;
use shopstar\constants\ClientTypeConstant;
use shopstar\helpers\ShopUrlHelper;
use shopstar\models\member\MemberModel;
use shopstar\mobile\commission\CommissionClientApiController;

/**
 * 推广二维码
 * Class QrCodeController
 * @package apps\commission\client
 */
class QrCodeController extends CommissionClientApiController
{
    /**
     * 推广二维码
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGet()
    {
        $member = MemberModel::find()->select('nickname')->where(['id' => $this->memberId])->first();
        // 头像
        $avatar = $this->member['avatar'];
        // 微信小程序处理头像
        if ($this->clientType == ClientTypeConstant::CLIENT_WXAPP) {
            $fileDir = SHOP_STAR_PUBLIC_PATH . '/tmp/wxapp_avatar/';
            if (!is_dir($fileDir)) {
                mkdir($fileDir);
            }
            $avatar = $fileDir .  '_' . $this->member['id'] . '.png';
            // 下载头像
            if (!is_file($avatar)) {
                file_put_contents($avatar, file_get_contents($this->member['avatar']));
            }
            $avatar =  ShopUrlHelper::build('tmp/wxapp_avatar/' .  '_' . $this->member['id'] . '.png', [], true);
        }
        $data = [
            'id' => $this->memberId,
            'nickname' => $member['nickname'],
            'mobile' => $this->member['mobile'],
            'avatar' => $avatar,
            'url' => ShopUrlHelper::wap('/', [
                'inviter_id' => $this->memberId
            ], true),
        ];

        return $this->result($data);
    }

    /**
     * 获取小程序二维码
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetWxapp()
    {
        //文件名
        $fileName = md5('commission_' .  '_' . $this->memberId) . '.jpg';
        //保存地址文件夹
        $savePatchDir = SHOP_STAR_PUBLIC_TMP_PATH . '/wxapp_qrcode/';
        //保存地址
        $savePatch = SHOP_STAR_PUBLIC_TMP_PATH . '/wxapp_qrcode/' . $fileName;
        //访问地址
        $accessPatch = ShopUrlHelper::build('tmp/wxapp_qrcode/' . $fileName, [], true);


        //如果不是文件  ||  生成时间大于一天
        if (!is_file($savePatch) || (filemtime($savePatch) && (time() - filemtime($savePatch)) > 86400)) {
            $result = MiniProgramACodeHelper::getUnlimited(http_build_query([
                'inviter_id' => $this->memberId
            ]), [
                'directory' => $savePatchDir,
                'fileName' => $fileName
            ]);
            if (is_error($result)) {
                return $this->result($result);
            }
        }

        return $this->success(['patch' => $accessPatch]);
    }

}