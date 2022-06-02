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

namespace shopstar\mobile;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\components\amap\AmapClient;
use shopstar\constants\ClientTypeConstant;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\ShopUrlHelper;
use shopstar\models\core\CoreAddressModel;
use shopstar\models\core\CoreSettings;
use shopstar\models\member\MemberSession;
use shopstar\models\notice\NoticeWechatSubscribeTemplateModel;
use shopstar\models\notice\NoticeWechatTemplateModel;
use shopstar\models\notice\NoticeWxappTemplateModel;
use shopstar\models\shop\ShopSettings;
use shopstar\models\statistics\StatisticsPageViewModel;
use shopstar\models\statistics\StatisticsUniqueViewModel;
use shopstar\services\core\attachment\CoreAttachmentService;
use shopstar\services\core\CoreAppService;
use yii\helpers\Json;

/**
 * Class IndexController
 * @package shop\client
 * @author 青岛开店星信息技术有限公司
 */
class IndexController extends BaseMobileApiController
{

    public $allowNotLoginController = '*';

    public $configActions = [
        'allowSessionActions' => ['*'],
        'allowShopCloseActions' => ['get-set', 'get-channel-status'],
    ];

    /**
     * 获取渠道状态
     * @return array|int[]|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetChannelStatus()
    {
        $channel = ShopSettings::get('channel');

        $setting = ShopSettings::get('channel_setting');
        $channel['wxapp_setting'] = [
            'maintain' => $setting['wxapp']['maintain'],
            'maintain_explain' => $setting['wxapp']['maintain_explain'],
            'show_commission' => $setting['wxapp']['show_commission'],
        ];

        $channel['byte_dance_setting'] = [
            'maintain' => $setting['byte_dance']['maintain'],
            'maintain_explain' => $setting['byte_dance']['maintain_explain'],
            'show_commission' => $setting['byte_dance']['show_commission'],
        ];
        return $this->success($channel);
    }

    /**
     * 获取地址库
     * @action get-address-list
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetAddressList()
    {
        $cache_key = 'areas_listall';
        $result = \Yii::$app->cache->get($cache_key);
        if ($result === false || $result === null) {
            $result = CoreAddressModel::getAll();
            \Yii::$app->cache->set($cache_key, Json::encode($result), 86400 * 7);
        } else {
            $result = Json::decode($result);
        }

        return $this->result(['result' => $result]);
    }

    /**
     * 获取地址库-pc
     * @action get-address-list
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetAddressList2()
    {
        $cache_key = 'areas_listall_manager';
        $result = \Yii::$app->cache->get($cache_key);
        if ($result === false || $result === null) {
            $result = CoreAddressModel::getResult();
            \Yii::$app->cache->set($cache_key, Json::encode($result), 86400 * 7);
        } else {
            $result = Json::decode($result);
        }
        return $this->result(['list' => $result]);
    }

    /**
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAddressCheck()
    {
        $list = CoreAddressModel::where(['sort' => null])->all();
        foreach ($list as $item) {
            $item->sort = strtoupper(mb_substr($item->letter, 0, 1));
            $item->save();
        }
        dd($list);
    }

    /**
     * 获取设置
     * @throws \yii\base\InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetSet()
    {
        $get = RequestHelper::get();

        // 登录用户信息
        $member = [];
        // 获取 Session-Id
        $this->sessionId = RequestHelper::header('Session-Id');
        if (!empty($this->sessionId)) {
            // 检测登录状态
            $member = MemberSession::get($this->sessionId, 'member');
        }

        // 基础设置
        $set['basic'] = ShopSettings::get('sysset.mall.basic');

        // 分享设置
        $shareSet = ShopSettings::get('sysset.mall.share');


        // 分享标题
        if ($shareSet['title_type'] == 1) {
            $set['share']['title'] = $set['basic']['name'];
        } else {
            $set['share']['title'] = $shareSet['title'];
        }
        // 分享logo
        if ($shareSet['logo_type'] == 1) {
            $set['share']['logo'] = $set['basic']['logo'];
        } else {
            $set['share']['logo'] = $shareSet['logo'];
        }

        $urlParams = [];
        if (!empty($member)) {
            $urlParams['inviter_id'] = $member['id'];
        }
        $url = ShopUrlHelper::wap('', $urlParams, true);


        if ($shareSet['link_type'] == 1) {
            $set['share']['link'] = $url;
        } else {
            if ($shareSet['link'][0] == '/') {
                // 如果是独立版，并且使用了相对路径
                $set['share']['link'] = ShopUrlHelper::wap($shareSet['link'], [
                    'inviter_id' => $member['id'] ?? 0,
                ], true);
            } else {
                $set['share']['link'] = $shareSet['link'];
            }
        }
        // 分享描述
        if ($shareSet['share_description_type'] == 1) {
            $set['share']['description'] = $set['basic']['description'];
        } else {
            $set['share']['description'] = $shareSet['share_description'];
        }
        // 积分设置
        $creditSet = ShopSettings::get('sysset.credit');
        $set['credit_text'] = $creditSet['credit_text']; // 积分文字
        $set['balance_text'] = $creditSet['balance_text']; // 余额文字
        $set['withdraw_state'] = $creditSet['withdraw_state']; // 是否开启提现
        $set['recharge_state'] = $creditSet['recharge_state']; // 是否开启余额充值
        // 交易设置
        $tradeSet = ShopSettings::get('sysset.trade');
        $set['strengthen_state'] = $tradeSet['strengthen_state']; // 是否开启交易增强
        // 是否开启共享微信地址
        $set['wechat_address'] = ShopSettings::get('sysset.express.address.wechat_address');

        // 插件列表 用来判断可不可以请插件接
        $set['plugins'] = CoreAppService::getAppEnableList();

        // 下单排序设置
        $set['dispatch_sort'] = ShopSettings::get('dispatch.sort');
        $set['dispatch_name'] = ShopSettings::get('dispatch.name');

        if (empty($set['dispatch_sort'])) {
            $setArray = [];
            $shopSet = ShopSettings::get('dispatch');
            // 快递开启
            if ($shopSet['express']['enable'] == 1) {
                $setArray[] = 10;
            }
            // 同城开启
            if ($shopSet['intracity']['enable'] == 1) {
                $setArray[] = 30;
            }
            $set['dispatch_sort'] = implode(',', $setArray);
        }
        // 核销检测权限
        $setArray = explode(',', $set['dispatch_sort']);
        // 如果没有核销设置 判断当前有没有核销
        if (in_array(20, $setArray)) { // 如果有核销配送方式  判断当前是否有权限
            $setArray = ArrayHelper::deleteByValue($setArray, 20);
        }
        $set['dispatch_sort'] = implode(',', $setArray);


        // 手机端loading设置
        $set['core_settings'] = [
            'mobile_loading' => ShopSettings::get('mobile_basic.mobile_loading'),
        ];

        // 获取独立储存的路径
        $set['shop_attachment_url'] = CoreAttachmentService::getRoot();
        $set['storage'] = ShopSettings::getImageCompressionRule();

        //店铺装修登录设置
        $set['login_auth_setting'] = ShopSettings::get('diypage.login_auth', []);

        //返回注册设置
        $setting = ShopSettings::get('channel_setting');

        $set['registry_settings'] = $setting['registry_settings'];

        //域名
        $set['shop_domain'] = ShopUrlHelper::wap('', [], true);

        // 主题色
        $set['theme_color'] = ShopSettings::get('diypage.theme_color');

        return $this->result($set);
    }

    /**
     * 记录 pv/uv
     * @author 青岛开店星信息技术有限公司
     */
    public function actionViewRecord()
    {
        $post = RequestHelper::post();

        try {
            $member = [];
            // 获取 Session-Id
            $this->sessionId = RequestHelper::header('Session-Id');
            if (!empty($this->sessionId)) {
                // 检测登录状态
                $member = MemberSession::get($this->sessionId, 'member');
            }

            // pv
            $pvData = [
                'member_id' => $member['id'] ?? 0,
                'url' => $post['url'] ?? '',
                'ip' => \Yii::$app->request->userIP,
                'page' => $post['page'] ?? '',
                'params' => Json::encode($post['params']), // 当前页所有参数
                'client_type' => $this->clientType ?? 0
            ];

            $pv = new StatisticsPageViewModel();

            $pv->setAttributes($pvData);

            if (!$pv->save()) {
                throw new \Exception($pv->getErrorMessage());
            }

            // uv
            if (!empty($member['id'])) {
                $uv = StatisticsUniqueViewModel::find()->where([
                    'member_id' => $member['id'],
                    'create_date' => date('Y-m-d')
                ])->one();

                // 如果为空 新建
                if (empty($uv)) {
                    $uv = new StatisticsUniqueViewModel();
                    $uv->member_id = $member['id'];
                    $uv->create_date = date('Y-m-d');
                }
                $uv->times = $uv->times + 1;

                if (!$uv->save()) {
                    throw new \Exception($uv->getErrorMessage());
                }
            }
        } catch (\Throwable $exception) {
            // 失败不作处理
        }

        return $this->success();
    }

    /**
     * 根据经纬度获取用户的详细地址
     * @return array|int[]|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetAddress()
    {
        $location = RequestHelper::get('location');

        $result = AmapClient::getAddressByLngAndLat($location);

        return $this->result($result);

    }

    /**
     * 获取消息通知的模板信息
     * @author nizengchao
     */
    public function actionGetNoticeTemplate()
    {
        $list = '';
        if ($this->clientType == ClientTypeConstant::CLIENT_WXAPP) {
            // 小程序
            $list = NoticeWxappTemplateModel::find()->select('scene_code,pri_tmpl_id')->get();
            if ($list) {
                $list = array_column($list, 'pri_tmpl_id', 'scene_code');
            }
        } elseif ($this->clientType == ClientTypeConstant::CLIENT_WECHAT) {
            // 公众号
            $type = RequestHelper::get('type', 'subscribe');
            if ($type == 'subscribe') {
                $list = NoticeWechatSubscribeTemplateModel::find()->select('scene_code,pri_tmpl_id')->get();
                if ($list) {
                    $list = array_column($list, 'pri_tmpl_id', 'scene_code');
                }
            } else {
                $list = NoticeWechatTemplateModel::find()->select('scene_code,template_id')->get();
                if ($list) {
                    $list = array_column($list, 'template_id', 'scene_code');
                }
            }
        }

        return $this->result(['data' => $list ?: '']);
    }

}
