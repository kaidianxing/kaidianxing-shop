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

namespace shopstar\admin\sysset\mall;

use shopstar\bases\KdxAdminApiController;
use shopstar\exceptions\sysset\MallException;
use shopstar\helpers\RequestHelper;
use shopstar\models\shop\ShopSettings;

/**
 * 联系我们
 * Class ContactController
 * @package shopstar\admin\sysset\mall
 * @author 青岛开店星信息技术有限公司
 */
class ContactController extends KdxAdminApiController
{

    /**
     * 获取联系人详情
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDetail()
    {
        $addressDetail = ShopSettings::get('contact');

        return $this->result(['data' => $addressDetail]);
    }

    /**
     * 保存联系人
     * @throws MallException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSave()
    {
        $params = RequestHelper::post();

        // 参数校验
        $contact = [];


        if (empty($params['contact'])) {
            throw new MallException(MallException::LINK_CONTACT_INVALID);
        } else {
            $contact['contact'] = $params['contact'];
        }

        if (empty($params['tel1'])) {
            throw new MallException(MallException::LINK_TEL_INVALID);
        } else {
            $contact['tel1'] = $params['tel1'];
        }
        $contact['tel2'] = !empty($params['tel2']) ? $params['tel2'] : '';

        // 高德地图 高德Web端(JS API)安全密钥
        $contact['amap_code'] = $params['amap_code'];

        if (empty($params['amap_key'])) {
            throw new MallException(MallException::AMAP_KEY_INVALID);
        } else {
            $contact['amap_key'] = $params['amap_key'];
        }

        if (empty($params['web_key'])) {
            throw new MallException(MallException::WEB_KEY_INVALID);
        } else {
            $contact['web_key'] = $params['web_key'];
        }

        if (empty($params['province']) || empty($params['city']) || empty($params['area']) ||
            empty($params['detail']) || empty($params['lng']) || empty($params['lat'])) {
            throw new MallException(MallException::LINK_ADDRESS_INVALID);
        } else {
            $contact['address']['province'] = $params['province'];
            $contact['address']['province_code'] = $params['province_code'];
            $contact['address']['city'] = $params['city'];
            $contact['address']['city_code'] = $params['city_code'];
            $contact['address']['area'] = $params['area'];
            $contact['address']['area_code'] = $params['area_code'];
            $contact['address']['detail'] = $params['detail'];
            $contact['address']['lng'] = $params['lng'];
            $contact['address']['lat'] = $params['lat'];
        }
        $address = $contact['address'];
        $originAddress = ShopSettings::get('contact.address');
        if (array_diff($originAddress, $address)) {
            //修改地址后配送区域信息清空
            $originDispatch = ShopSettings::get('dispatch.intracity');
            $setArgs = [
                'enable' => $originDispatch['enable'],
                'third_party' => $originDispatch['third_party'],
                'amap_key' => $originDispatch['amap_key'],
            ];
            ShopSettings::set('dispatch.intracity', $setArgs);
        }
        ShopSettings::set('contact', $contact);

        return $this->success();
    }

}