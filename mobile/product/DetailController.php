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

namespace shopstar\mobile\product;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\components\byteDance\helpers\ByteDanceQrcodeHelper;
use shopstar\components\wechat\helpers\MiniProgramACodeHelper;
use shopstar\constants\ClientTypeConstant;
use shopstar\constants\goods\GoodsDeleteConstant;
use shopstar\constants\goods\GoodsDispatchTypeConstant;
use shopstar\constants\goods\GoodsLabelGroupStatusConstant;
use shopstar\constants\goods\GoodsStatusConstant;
use shopstar\exceptions\goods\GoodsException;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\ShopUrlHelper;
use shopstar\helpers\VideoHelper;
use shopstar\models\activity\MarketingViewLogModel;
use shopstar\models\broadcast\BroadcastRoomGoodsMapModel;
use shopstar\models\form\FormLogModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\goods\GoodsOptionModel;
use shopstar\models\goods\GoodsPermMapModel;
use shopstar\models\goods\label\GoodsLabelGroupModel;
use shopstar\models\goods\label\GoodsLabelModel;
use shopstar\models\goods\spec\GoodsSpecModel;
use shopstar\models\member\MemberBrowseFootprintModel;
use shopstar\models\member\MemberFavoriteModel;
use shopstar\models\order\DispatchModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\shop\ShopSettings;
use shopstar\services\goods\GoodsDetailsActivityHandler;
use shopstar\services\goods\GoodsService;
use shopstar\services\sale\CouponService;
use shopstar\services\shop\ShopSettingIntracityLogic;
use yii\helpers\Json;

/**
 * @author 青岛开店星信息技术有限公司
 * Class DetailController
 * @package shop\client\goods
 */
class DetailController extends BaseMobileApiController
{
    /**
     * 允许不登录
     * @author 青岛开店星信息技术有限公司
     * @var array
     */
    public $configActions = [
        'allowNotLoginActions' => [
            'get-detail',
            'get-option',
        ]
    ];


    public $goodsTypeMap = [
        2 => 'virtualAccount',
    ];

    /**
     * @return \yii\web\Response
     * @throws GoodsException|\yii\base\InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetDetail(): \yii\web\Response
    {
        $id = RequestHelper::getInt('id');
        if (empty($id)) {
            throw new GoodsException(GoodsException::CLIENT_DETAIL_GET_DETAIL_PARAMS_ERROR);
        }

        $buyPermResult = false;
        if (!empty($this->memberId)) {
            //检测权限
            $permResult = GoodsPermMapModel::checkGoodsPerm($id, $this->memberId);
            if (!$permResult) {
                throw new GoodsException(GoodsException::CLIENT_DETAIL_GET_DETAIL_PERM_ERROR);
            }

            //检测购买权限
            $buyPermResult = GoodsPermMapModel::checkGoodsPerm($id, $this->memberId, GoodsPermMapModel::PERM_BUY);

        }

        $options = [
            'where' => [
                'and',
                ['status' => [GoodsStatusConstant::GOODS_STATUS_PUTAWAY, GoodsStatusConstant::GOODS_STATUS_PUTAWAY_NOT_DISPLAY]],
                ['is_deleted' => GoodsDeleteConstant::GOODS_IS_DELETE_NO]
            ]
        ];

        //查找商品
        $goods = GoodsService::getGoods($id, $options);

        //释放真实销量
        $goods['sales'] += $goods['real_sales'];
        unset($goods['real_sales']);

        if (empty($goods)) {
            throw new GoodsException(GoodsException::CLIENT_DETAIL_GET_DETAIL_GOODS_NOT_FOUND_ERROR);
        }

        //商品活动构建器
        $goodsDetailsActivityHandler = GoodsDetailsActivityHandler::init($goods, $this->memberId ?: 0, $this->clientType, [
            'team_id' => RequestHelper::getInt('team_id') ?? 0,
            'activity_type' => RequestHelper::get('activity_type'),
        ]);

        //自动化
        $goodsDetailsActivityHandler->automation();

        //获取商品可参与活动规则
        $activity = $goodsDetailsActivityHandler->getActivity();

        //增加浏览量
        GoodsModel::updateAllCounters(['pv_count' => 1], [
            'id' => $id,
        ]);

        // 自定义购买按钮status, 影响加购按钮及价格文字显示
        $goods['buy_button_status'] = GoodsService::getBuyButtonStatus($goods['ext_field']['buy_button_type'], $goods['ext_field']['buy_button_settings']);
        // 处理自定义按钮电话
        $tel = GoodsService::getBuyButtonTelephone($goods['ext_field']['buy_button_type'], $goods['ext_field']['buy_button_settings']);
        if ($tel) {
            $goods['ext_field']['buy_button_settings']['click_telephone'] = $tel;
        }
        //插件处理
        $this->pluginDispose($goods, $activity);

        //获取商品优惠券 TODO 优化 根据活动查 不然多余执行
        $categoryId = isset($goods['category_id'][0]) ? $goods['category_id'][0] : 0;
        $goodsCoupon = CouponService::getGoodsCoupon($this->memberId, $id, $categoryId);

        $expressEnable = ShopSettings::get('dispatch.express.enable');

        //获取同城配送设置
        $intracity = [
            'dispatch_price' => ShopSettingIntracityLogic::getDispatchPrice(),
            'dispatch_area' => ShopSettingIntracityLogic::getDispatchArea(),
            'shop_address' => ShopSettings::get('contact'),
            'express_enable' => $expressEnable,
            'intracity_enable' => ShopSettings::get('dispatch.intracity.enable'),
        ];
        //添加足迹
        $this->memberId && MemberBrowseFootprintModel::saveFootprint($id, $this->memberId);

        $pluginAccount = [];
        // 判断子商户是否拥有部分插件权限
        if (isset($this->goodsTypeMap[$goods['type']])) {
            $pluginAccount[$this->goodsTypeMap[$goods['type']]] = true;
        }
        // 处理主图视频
        if (!empty($goods['content'])) {
            $goods['content'] = VideoHelper::parseRichTextTententVideo($goods['content']);
        }
        // 处理封面视频
        if (!empty($goods['video']) && preg_match("/http[s]?:\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is", $goods['video'])) {
            $goods['video'] = VideoHelper::getTententVideo($goods['video']);
        }
        $result = [
            'data' => [
                'goods' => $goods,
            ],
            'perm' => [
                'buy' => $buyPermResult ? 1 : 0
            ],
            'activity' => $activity,

            //是否收藏
            'is_favorite' => (int)$this->memberId && MemberFavoriteModel::getIsFavorite($id, $this->memberId),

            //海报url
            'poster_url' => ShopUrlHelper::wap('/kdxGoods/detail/index', [
                'inviter_id' => $this->memberId,
                'goods_id' => $id
            ], true),

            // 商品优惠券
            'goods_coupon' => $goodsCoupon,

            // 同城配送
            'intracity' => $intracity,

            // 子商户是否拥有插件权益
            'plugin_account' => $pluginAccount ?? [],
        ];

        //额外处理
        $this->more($result);
        return $this->success($result);
    }

    /**
     * 更多返回
     * @param $result
     * @author 青岛开店星信息技术有限公司
     */
    private function more(&$result)
    {
        //运费模板运费
        if ($result['data']['goods']['dispatch_type'] == GoodsDispatchTypeConstant::GOODS_DISPATCH_TYPE_TEMPLATE) {
            // 获取配送的设置
            $dispatchInfo = DispatchModel::getNotDispatchArea($result['data']['goods']['dispatch_id']);
            $result['data']['dispatch_template'] = [
                'dispatch_price' => DispatchModel::getStartPrice($result['data']['goods']['dispatch_id']),
                'delivery_type' => $dispatchInfo['dispatch_area_type'] ?? '',
                'not_dispatch' => $dispatchInfo['dispatch_limit_area'] ?? '',
            ];
        } else {
            // 获取配送类型
            $deliveryType = ShopSettings::get('sysset.express.address.delivery_type');

            $deliveryType == 0 ? $denyArea = ShopSettings::get('sysset.express.address.deny_area') : $deliveryArea = ShopSettings::get('sysset.express.address.delivery_area');

            $areaInfo = isset($denyArea) ? Json::decode($denyArea) : Json::decode($deliveryArea);
            $result['data']['dispatch_template'] = [
                'not_dispatch' => $areaInfo['text'] ?? '',
                'delivery_type' => $deliveryType, // 配送类型
            ];
        }

        //添加已购买商品个数
        $result['data']['buy_num'] = 0;
        if (!empty($this->memberId)) {
            $result['data']['buy_num'] = OrderGoodsModel::getBuyTotal($this->memberId, $result['data']['goods']['id']) ?: 0;
        }

        //商品标签
        $label = GoodsLabelModel::find()
            ->alias('label')
            ->leftJoin(GoodsLabelGroupModel::tableName() . ' label_group', 'label.group_id = label_group.id')
            ->where([
                'label.id' => $result['data']['goods']['label_id'],
                'label_group.status' => GoodsLabelGroupStatusConstant::GOODS_LABEL_GROUPS_STATUS_ENABLE
            ])->select(['label.id', 'label.name', 'label.desc', 'label.content'])->asArray()->all();

        //默认标签
        if (!empty($result['data']['goods']['label_id'])) {
            foreach (GoodsLabelGroupModel::RECOMMEND as $labelKey => $labelItem) {
                if (in_array($labelItem['id'], $result['data']['goods']['label_id'])) {
                    array_unshift($label, $labelItem);
                }
            }
        }

        !empty($label) && $result['data']['goods']['label'] = $label;

        $formData = FormLogModel::where([
            'goods_id' => $result['data']['goods']['id'],
            'member_id' => $this->memberId,
        ])->orderBy([
            'updated_at' => SORT_DESC
        ])->first();

        if ($result['data']['goods']['form_data']['md5'] == $formData['md5']) {
            $formData['content'] = Json::decode($formData['content']);
            $result['data']['form_data'] = $formData['content'];
        }
    }

    /**
     * 插件处理
     * @param $goods
     * @param array $activity
     * @author 青岛开店星信息技术有限公司
     */
    private function pluginDispose(&$goods, array $activity)
    {
        //如果有小程序直播 && 是微信小程序
        if ($this->clientType == ClientTypeConstant::CLIENT_WXAPP) {
            $roomId = RequestHelper::getInt('room_id');
            if (!empty($roomId)) {
                //增加小程序直播商品浏览量
                BroadcastRoomGoodsMapModel::updateAllCounters(['pv_count' => 1], [
                    'room_id' => $roomId,
                    'goods_id' => $goods['id']
                ]);
            }
        }

        // 需要统计的活动类型
        $activityType = [
            'seckill', // 秒杀
            'groups', // 拼团
        ];

        $activityKey = array_keys($activity);
        $intersect = array_intersect($activityKey, $activityType);
        // 遍历插入
        if (!empty($intersect)) {
            foreach ($intersect as $item) {
                // 活动浏览记录 注: 参数用活动上的 分别统计店铺自己的活动
                MarketingViewLogModel::insertViewLog($activity[$item]['id'], $this->memberId, $goods['id'], $item);
            }
        }
    }

    /**
     * 获取规格信息
     * @return \yii\web\Response
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetOption(): \yii\web\Response
    {
        $goodsId = RequestHelper::getInt('goods_id');
        $isOriginalBuy = RequestHelper::getInt('is_original_buy');
        $type = RequestHelper::getInt('type');
        if (empty($goodsId)) {
            throw new GoodsException(GoodsException::CLIENT_DETAIL_GET_OPTION_PARAMS_ERROR);
        }

        $spec = GoodsSpecModel::getSpaceById($goodsId);
        $options = GoodsOptionModel::find()
            ->where(['goods_id' => $goodsId])
            ->asArray()->indexBy('specs')->all();

        if (empty($options)) {
            throw new GoodsException(GoodsException::CLIENT_DETAIL_GET_DETAIL_OPTION_EMPTY_ERROR);
        }

        //商品活动构建器
        $goodsDetailsActivityHandler = GoodsDetailsActivityHandler::init($goodsId, $this->memberId ?: 0, $this->clientType, [
            'team_id' => RequestHelper::getInt('team_id') ?? 0,
            'activity_type' => RequestHelper::get('activity_type'),
        ]);
//        //自动化
//        $goodsDetailsActivityHandler->memberPrice($options);
        //自动化
        $goodsDetailsActivityHandler->automation($options, $isOriginalBuy);
        //获取商品可参与活动规则
        $activity = $goodsDetailsActivityHandler->getActivity();

        if (!empty($activity)) {
            //组成会员价
            if (!empty($activity['member_price'])) {
                $discountPrice = $activity['member_price'];
                foreach ($options as &$item) {

                    //其余商品按照规格塞入
                    if (isset($discountPrice[$item['id']])) {
                        $item['activity']['member_price'] = $discountPrice[$item['id']];
                    }
                }
            }

            // 预售
            if (!empty($activity['presell'])) {
                $discountPrice = $activity['presell'];
                foreach ($options as &$item) {
                    if (isset($discountPrice['goods_info'][$item['id']])) {
                        $item['activity']['presell'] = $discountPrice['goods_info'][$item['id']];
                    }
                }
            }

            // 秒杀
            if (!empty($activity['seckill'])) {
                $discountPrice = $activity['seckill'];
                foreach ($options as &$item) {
                    if (isset($discountPrice['goods_info'][$item['id']])) {
                        $item['activity']['seckill'] = $discountPrice['goods_info'][$item['id']];
                    }
                }
            }

            //拼团
            if (!empty($activity['groups'])) {
                $discountPrice = $activity['groups'];
                foreach ($options as &$item) {
                    if (isset($discountPrice['goods_info'][$item['id']])) {
                        $item['activity']['groups'] = $discountPrice['goods_info'][$item['id']];
                    }
                }
            }

        }

        $data = [
            'spec' => empty($spec) ? [] : $spec,
            'options' => empty($options) ? [] : $options
        ];

        return $this->success($data);
    }

    /**
     * 获取小程序二维码
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetWxappQrcode()
    {
        $goodsId = RequestHelper::getInt('goods_id');

        //文件名
        $fileName = md5('_' . $goodsId . '_' . $this->memberId) . '.jpg';
        //保存地址文件夹
        $savePatchDir = SHOP_STAR_PUBLIC_TMP_PATH . '/wxapp_qrcode/';
        //保存地址
        $savePatch = SHOP_STAR_PUBLIC_TMP_PATH . '/wxapp_qrcode/' . $fileName;
        //访问地址
        $accessPatch = ShopUrlHelper::build('tmp/wxapp_qrcode/' . $fileName, [], true);

        //如果不是文件  ||  生成时间大于一天
        if (!is_file($savePatch) || (filemtime($savePatch) && (time() - filemtime($savePatch)) > 86400)) {
            $result = MiniProgramACodeHelper::getUnlimited(http_build_query([
                'inviter_id' => $this->memberId,
                'goods_id' => $goodsId
            ]), [
                'page' => 'kdxGoods/detail/index',
                'directory' => $savePatchDir,
                'fileName' => $fileName
            ]);

            if (is_error($result)) {
                return $this->result($result);
            }
        }

        // 头像
        // 微信小程序处理头像
        $fileDir = SHOP_STAR_PUBLIC_PATH . '/tmp/wxapp_avatar/';
        if (!is_dir($fileDir)) {
            mkdir($fileDir);
        }
        $avatar = $fileDir . '_' . $this->member['id'] . '.png';
        // 下载头像
        if (!is_file($avatar)) {
            file_put_contents($avatar, file_get_contents($this->member['avatar']));
        }
        $avatar = ShopUrlHelper::build('/tmp/wxapp_avatar/' . '_' . $this->member['id'] . '.png' . $fileName, [], true);

        return $this->success(['patch' => $accessPatch, 'avatar' => $avatar]);
    }

    /**
     * 获取字节跳动小程序二维码
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetByteDanceQrcode()
    {
        $appName = RequestHelper::get('app_name');
        $goodsId = RequestHelper::getInt('goods_id');

        $appid = ShopSettings::get('channel_setting.byte_dance')['appid'];
        //文件名
        $fileName = md5('_' . $goodsId . '_' . $appName . '_' . $appid) . '.jpg';
        //保存地址文件夹
        $savePatchDir = SHOP_STAR_PUBLIC_TMP_PATH . '/byte_dance_qrcode/';
        //保存地址
        $savePatch = SHOP_STAR_PUBLIC_TMP_PATH . '/byte_dance_qrcode/' . $fileName;
        //访问地址
        $accessPatch = ShopUrlHelper::build('/tmp/byte_dance_qrcode/' . $fileName, [], true);

        //如果不是文件  ||  生成时间大于一天
        if (!is_file($savePatch) || (filemtime($savePatch) && (time() - filemtime($savePatch)) > 86400)) {
            $result = ByteDanceQrcodeHelper::getCode(
                'kdxGoods/detail/index',
                ['appname' => $appName, 'inviter_id' => $this->memberId, 'goods_id' => $goodsId],
                ['directory' => $savePatchDir, 'file_name' => $fileName]
            );

            if (is_error($result)) {
                return $this->result($result);
            }
        }

        return $this->success(['patch' => $accessPatch]);
    }


    /**
     * 收藏
     * @return array|\yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChangeFavorite()
    {
        $goodsId = RequestHelper::postInt('goods_id');
        $isAdd = RequestHelper::postInt('is_add');
        if (empty($goodsId)) {
            throw new GoodsException(GoodsException::CLIENT_DETAIL_CHANGE_FAVORITE_PARAMS_ERROR);
        }

        $result = MemberFavoriteModel::changeFavorite($isAdd == 1 ? true : false, $goodsId, $this->memberId);
        return $this->result($result);
    }

}
