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

namespace shopstar\services\goods;

use shopstar\constants\goods\GoodsBuyButtonConstant;
use shopstar\constants\goods\GoodsConstant;
use shopstar\constants\goods\GoodsMemberLevelDiscountConstant;
use shopstar\constants\goods\GoodsStatusConstant;
use shopstar\constants\goods\GoodsTypeConstant;
use shopstar\constants\log\goods\GoodsLogConstant;
use shopstar\constants\order\OrderConstant;
use shopstar\constants\order\OrderStatusConstant;
use shopstar\exceptions\goods\GoodsException;
use shopstar\exceptions\virtualAccount\VirtualAccountException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\QueueHelper;
use shopstar\helpers\StringHelper;
use shopstar\jobs\goods\AutoPutawayJob;
use shopstar\models\commission\CommissionGoodsModel;
use shopstar\models\goods\category\GoodsCategoryMapModel;
use shopstar\models\goods\GoodsCartModel;
use shopstar\models\goods\GoodsMemberLevelDiscountModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\goods\GoodsOptionModel;
use shopstar\models\goods\GoodsPermMapModel;
use shopstar\models\goods\label\GoodsLabelMapModel;
use shopstar\models\goods\spec\GoodsSpecItemModel;
use shopstar\models\goods\spec\GoodsSpecModel;
use shopstar\models\log\LogModel;
use shopstar\models\member\group\MemberGroupModel;
use shopstar\models\member\MemberLevelModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use shopstar\models\shop\ShopSettings;
use shopstar\models\virtualAccount\VirtualAccountModel;
use shopstar\services\core\attachment\CoreAttachmentService;
use shopstar\services\order\OrderService;
use yii\helpers\Json;

/**
 * @author 青岛开店星信息技术有限公司
 */
class GoodsCreator
{
    /**
     * 商品信息
     * @var array
     */
    private $goodsInfo = [];
    /**
     * 商品保存后id
     * @var int
     */
    private $saveGoodsId = 0;
    /**
     * 是否是多规格
     * @var int
     */
    private $hasOption = false;
    /**
     * 规格id
     * @var array
     */
    private $specId = [];
    /**
     * 规格项id
     * @var
     */
    private $specItemId = [];

    /**
     * uid
     * @author 青岛开店星信息技术有限公司
     * @var int
     */
    private $uid = 0;
    /**
     * 规格项映射
     * @var array
     */
    private $specsItemMap = [];

    /**
     * 规格价格
     * @var array
     */
    private $optionPrice = [];

    /**
     * 多规格id
     * @author 青岛开店星信息技术有限公司
     * @var array
     */
    private $optionId = [];

    /**
     * 多规格对应specID
     * @author Jason
     * @var array
     */
    private $optionSpecId = [];
    /**
     * 是否是修改
     * @author 青岛开店星信息技术有限公司
     * @var bool
     */
    private $isEdit = false;

    /**
     * 日志数据
     * @author 青岛开店星信息技术有限公司
     * @var array
     */
    private $logData = [];

    /**
     * 日志主要字段
     * @author 青岛开店星信息技术有限公司
     * @var array
     */
    private $logPrimary = [];
    /**
     * 刚添加的会员等级折扣id
     * @var array
     */
    private $levelDiscountId = [];

    /**
     * 多规格原价
     * @author 青岛开店星信息技术有限公司
     * @var array
     */
    private $optionOriginalPrice = [];

    /**
     * 多规格成本价
     * @author 青岛开店星信息技术有限公司
     * @var array
     */
    private $optionCostPrice = [];

    /**
     * @var bool 是否修改表单
     */
    private $changeForm = false;

    /**
     * @var bool 是否修改状态
     */
    private $changeStatus = false;

    /**
     * 扩展字段
     * @author 青岛开店星信息技术有限公司
     * @var array
     */
    private $extField = [];

    /**
     * 购买按钮设置
     * @author nizengchao
     * @var array
     */
    private $buyButtonSettings = [];

    /**
     * 允许开启购买按钮自定义功能的商品类型  实体/虚拟商品
     * @var array
     */
    private $allowOpenBuyButton = [
        GoodsTypeConstant::GOODS_TYPE_ENTITY,
        GoodsTypeConstant::GOODS_TYPE_VIRTUAL
    ];

    /**
     * @param int $uid
     * @param array $data
     * @param bool $isEdit
     */
    public function __construct(int $uid, array $data, bool $isEdit)
    {
        $this->isEdit = $isEdit;
        $this->uid = $uid;
        foreach ($data as $dataIndex => $dataItem) {
            $this->goodsInfo[$dataIndex] = is_string($dataItem) ? Json::decode($dataItem) : $dataItem;
        }
        // 购买按钮
        $this->goodsInfo['goods']['ext_field']['buy_button_settings'] = '';
        $this->goodsInfo['goods']['ext_field']['buy_button_type'] = '0';
        $this->goodsInfo['goods']['buy_button_settings'] = '';
        $this->goodsInfo['goods']['buy_button_type'] = '0';
        // 商品购买限制
        $this->goodsInfo['goods']['ext_field']['buy_limit'] = "0";
        // 会员折扣：系统默认
        $this->goodsInfo['goods']['member_level_discount_type'] = "1";
        // 分销
        $this->goodsInfo['goods']['goods_commission'] = [];
        $this->goodsInfo['goods_commission'] = [];

    }

    /**
     * 执行
     * @param array $data
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function init(array $data)
    {
        $tr = \Yii::$app->db->beginTransaction();
        try {
            $this->verifier()->format()->beforeSave()->saveGoods();

            if ($this->hasOption) {
                $this->saveSpec()->saveOptions();
            }

            // 保存会员等级折扣
            $this->logData['member_discount']['type'] = $this->logPrimary['member_discount']['type'] = GoodsMemberLevelDiscountConstant::getText($this->goodsInfo['goods']['member_level_discount_type']);
            if ($this->goodsInfo['goods']['member_level_discount_type'] == 2 || $this->goodsInfo['goods']['member_level_discount_type'] == 3) {
                $this->saveMemberLevelDiscount();
            }

            // 保存分销设置
            $this->logData['commission']['join'] = $this->logPrimary['commission']['join'] = $this->goodsInfo['goods']['is_commission'] == 1 ? '参与' : '不参与';
            $this->logData['commission']['commission_set'] = $this->logPrimary['commission']['commission_set'] = $this->goodsInfo['goods_commission']['type'] == 1 ? '系统设置' : '按商品设置佣金';
            if ($this->goodsInfo['goods']['is_commission'] == 1) {
                $this->saveCommission();
            }

            $this->afterSave();
            $tr->commit();
        } catch (\Exception $exception) {

            $tr->rollBack();
            throw new GoodsException($exception->getCode(), $exception->getMessage());
        }
    }

    /**
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    private function saveCommission()
    {
        // TODO 青岛开店星信息技术有限公司 多规格先不考虑
        $res = CommissionGoodsModel::saveGoodsCommission($this->goodsInfo['goods_commission'], $this->saveGoodsId, []);
        if (is_error($res)) {
            self::exception(GoodsException::GOODS_COMMISSION_SAVE_FAIL);
        }

        $commission = [];
        foreach ((array)$this->goodsInfo['goods_commission']['info'] as $commissionIndex => $commissionItem) {
            foreach ($commissionItem as $commissionItemIndex => $commissionItemItem) {
                $levelId = (int)trim($commissionItemIndex, 'level_');

                $levelType = $commissionItemItem['type'] == 1 ? '佣金比例' : '固定佣金';
                $levelCommission = $commissionItemItem['num'] ?: 0;
                if ($commissionIndex == 'commission_1') { //一级
                    $commission[$levelId]['one_type'] = $levelType;
                    $commission[$levelId]['one_commission'] = $levelCommission;
                } elseif ($commissionIndex == 'commission_2') { //二级
                    $commission[$levelId]['two_type'] = $levelType;
                    $commission[$levelId]['two_commission'] = $levelCommission;
                } else {                                      //三级
                    $commission[$levelId]['three_type'] = $levelType;
                    $commission[$levelId]['three_commission'] = $levelCommission;
                }
            }
        }

        $this->logData['commission']['goods_commission'] = $this->logPrimary['commission']['goods_commission'] = array_values($commission);
    }

    /**
     * 保存商品
     * @throws GoodsException
     * @throws \yii\base\InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    private function saveGoods(): GoodsCreator
    {
        $goods = $this->getParams('goods');
        //如果没有id 是新增 有id 是修改
        $goodsModel = !$this->isEdit ? new GoodsModel() : GoodsModel::findOne($goods['id']);

        //如果有商品id && 商品没有查到的时候
        (!empty($goods['id']) && empty($goodsModel)) && self::exception(GoodsException::GOODS_SAVE_GOODS_NOT_FOUND_ERROR);

        //设置字段参数
        $goods['min_price'] = $goods['price'];
        $goods['max_price'] = $goods['price'];
        empty($goods['id']) && $goods['created_at'] = DateTimeHelper::now();

        //content
        // 获取储存拼接路径
        $attachmentUrl = CoreAttachmentService::getRoot();
        $goods['content'] = StringHelper::htmlImages($goods['content'], $attachmentUrl);
        $goods['form_id'] = (int)$goods['form_id'];
        if ($this->isEdit) {
            // 修改了表单
            if (($goodsModel->form_id != $goods['form_id'] || $goodsModel->form_status != $goods['form_status'])) {
                $this->changeForm = true;
            }
            // 修改了状态
            if ($goodsModel->status != $goods['status']) {
                $this->changeStatus = true;
            }

        }
        $goodsModel->dispatch_verify = (int)$goods['dispatch_verify'];
        $goodsModel->is_all_verify = (int)$goods['is_all_verify'];
        if (isset($goods['video_type'])) {
            $goodsModel->video_type = $goods['video_type'];
        }
        // 虚拟卡密,单规格,储存卡密库id
        if ($goods['type'] == GoodsTypeConstant::GOODS_TYPE_VIRTUAL_ACCOUNT && $goods['virtual_account_id'] && !$this->hasOption) {
            $goodsModel->virtual_account_id = $goods['virtual_account_id'];
        }
        if (isset($goods['virtual_account_id']) && empty($goods['virtual_account_id'])) {
            $goods['virtual_account_id'] = 0;
        }
        // 判断
        if ($goods['type'] == GoodsTypeConstant::GOODS_TYPE_VIRTUAL_ACCOUNT && $this->isEdit && $goods['has_option'] == 1) {
            $goods['virtual_account_id'] = 0;
        }
        if ($goods['give_credit_status'] == 0) {
            $goods['give_credit_num'] = 0;
        }

        $goodsModel->setAttributes($goods);
        //如果保存失败 则抛出异常
        !$goodsModel->save() && self::exception(GoodsException::GOODS_SAVE_GOODS_SAVE_ERROR);

        //日志字段
        $this->logData['goods'] = $this->logPrimary['goods'] = $goodsModel->attributes;
        //设置商品id
        $this->saveGoodsId = $goodsModel->id;

        return $this;
    }

    /**
     * 获取参数
     * @param string $params
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public function getParams(string $params = 'goods'): array
    {
        return $this->goodsInfo[$params];
    }

    /**
     * @throws GoodsException
     */
    private static function exception(int $code)
    {
        throw new GoodsException($code);
    }

    /**
     * 保存前
     * @author 青岛开店星信息技术有限公司
     */
    private function beforeSave(): GoodsCreator
    {
        //是否是多规格
        $this->hasOption = $this->goodsInfo['goods']['has_option'] == 1;

        return $this;
    }

    /**
     * 格式化
     * @author 青岛开店星信息技术有限公司
     */
    private function format(): GoodsCreator
    {
        //格式化图片
        $this->goodsInfo['goods']['thumb_all'] = Json::encode($this->goodsInfo['goods']['thumb_all'] ?: []);
        //格式化扩展字段
        // 价格面议数据载入到扩展字段
        $this->loadBuyButtonData();
        $this->extField = $this->goodsInfo['goods']['ext_field'];
        $this->goodsInfo['goods']['ext_field'] = Json::encode($this->goodsInfo['goods']['ext_field'] ?: []);

        //格式化图片
        $this->goodsInfo['goods']['params'] = Json::encode($this->goodsInfo['goods']['params'] ?: []);

        return $this;
    }

    /**
     * 价格面议数据载入到扩展字段
     * @author nizengchao
     */
    private function loadBuyButtonData()
    {
        $this->goodsInfo['goods']['ext_field']['buy_button_settings'] = $this->goodsInfo['goods']['buy_button_settings'] ?? [];
        $this->goodsInfo['goods']['ext_field']['buy_button_type'] = $this->goodsInfo['goods']['buy_button_type'] ?? GoodsBuyButtonConstant::GOODS_BUY_BUTTON_TYPE_DEFAULT;
    }

    /**
     * 验证参数
     * @throws GoodsException|VirtualAccountException
     * @author 青岛开店星信息技术有限公司
     */
    private function verifier(): GoodsCreator
    {
        //商品是否为空
        empty($this->goodsInfo['goods']) && self::exception(GoodsException::GOODS_SAVE_GOODS_EMPTY_PARAMS_ERROR);
        //商品标题不能为空
        empty($this->goodsInfo['goods']['title']) && self::exception(GoodsException::GOODS_SAVE_TITLE_EMPTY_PARAMS_ERROR);
        //商品首图不能为空
        empty($this->goodsInfo['goods']['thumb']) && self::exception(GoodsException::GOODS_SAVE_THUMB_EMPTY_PARAMS_ERROR);
        //购买按钮样式
        $this->goodsInfo['goods']['buy_button_type'] = $this->goodsInfo['goods']['buy_button_type'] ?? 0;
        !in_array((int)$this->goodsInfo['goods']['buy_button_type'], [GoodsBuyButtonConstant::GOODS_BUY_BUTTON_TYPE_DEFAULT, GoodsBuyButtonConstant::GOODS_BUY_BUTTON_TYPE_CUSTOM]) && self::exception(GoodsException::GOODS_SAVE_BUY_BUTTON_EMPTY_PARAMS_EMPTY);

        //如果存在多规格
        if ($this->goodsInfo['goods']['has_option'] == 1) {
            //规格是否为空
            empty($this->goodsInfo['spec']) && self::exception(GoodsException::GOODS_SAVE_LACK_SPEC_ERROR);
            //规格商品是否为空
            empty($this->goodsInfo['options']) && self::exception(GoodsException::GOODS_SAVE_LACK_OPTIONS_ERROR);

            array_walk($this->goodsInfo['options'], function ($result) {
                //判断规格商品标题不能为空
                empty($result['title']) && self::exception(GoodsException::GOODS_SAVE_EMPTY_OPTIONS_TITLE_ERROR);
                //判断规格商品价格不能为空
//                empty($result['price']) && self::exception(GoodsException::GOODS_SAVE_EMPTY_OPTIONS_PRICE_ERROR);
            });
        } else {
            //商品价格不能为0
//            empty($this->goodsInfo['goods']['price']) && self::exception(GoodsException::GOODS_SAVE_PRICE_EMPTY_PARAMS_ERROR);
        }

        // 会员折扣，非默认或不支持类型则不能为空  不指定则默认为不支持
        $discountType = $this->goodsInfo['goods']['member_level_discount_type'] ?? GoodsMemberLevelDiscountConstant::GOODS_MEMBER_LEVEL_DISCOUNT_NOT_SUPPORTED;
        if ($discountType == GoodsMemberLevelDiscountConstant::GOODS_MEMBER_LEVEL_DISCOUNT_GOODS || $discountType == GoodsMemberLevelDiscountConstant::GOODS_MEMBER_LEVEL_DISCOUNT_OPTION) {
            // 获取所有等级
            $levels = MemberLevelModel::getAllLevel();
            // 等级为空 不能设置该类型
            if (empty($levels)) {
                self::exception(GoodsException::MEMBER_LEVEL_EMPTY);
            }
            // 指定会员等级类型
            if ($discountType == 2) {
                if (empty($this->goodsInfo['member_level_discount'])) {
                    self::exception(GoodsException::MEMBER_LEVEL_DISCOUNT_PARAMS_ERROR);
                }
                // 检查数据
                $this->checkMemberLevelDiscountData($levels, $this->goodsInfo['member_level_discount']);
            } else if ($discountType == 3) {
                // 多规格类型
                if ($this->goodsInfo['goods']['has_option'] != 1) {
                    // 未开启多规格，重新选择类型
                    self::exception(GoodsException::MEMBER_LEVEL_DISCOUNT_TYPE_NOT_EXISTS_OPTION);
                }
                foreach ($this->goodsInfo['member_level_discount'] as $item) {
                    if (empty($item)) {
                        self::exception(GoodsException::MEMBER_LEVEL_DISCOUNT_PARAMS_ERROR);
                    }
                    // 检查数据
                    $this->checkMemberLevelDiscountData($levels, $item);
                }
            }
        }

        //退换货仅支持退款
        if ($this->goodsInfo['goods']['type'] == GoodsTypeConstant::GOODS_TYPE_VIRTUAL) {
            $this->goodsInfo['goods']['ext_field']['return'] = 0;
            $this->goodsInfo['goods']['ext_field']['exchange'] = 0;
        } elseif ($this->goodsInfo['goods']['type'] == GoodsTypeConstant::GOODS_TYPE_VIRTUAL_ACCOUNT) {
            // 虚拟卡密 不支持退款退货
            $this->goodsInfo['goods']['ext_field']['return'] = 0;
            $this->goodsInfo['goods']['ext_field']['exchange'] = 0;
            $this->goodsInfo['goods']['ext_field']['refund'] = 0;
            // 如果是多规格商品
            if ($this->goodsInfo['goods']['has_option'] == 1) {
                foreach ($this->goodsInfo['options'] as $optionValue) {
                    // 卡密库id不存在
                    if (empty($optionValue['virtual_account_id'])) {
                        throw new VirtualAccountException(VirtualAccountException::VIRTUAL_ACCOUNT_NOT_NULL);
                    }
                    $virtualAccountInfo = VirtualAccountModel::existsInfo($optionValue['virtual_account_id']);

                    // 卡密库不存在
                    if (!$virtualAccountInfo) {
                        throw new VirtualAccountException(VirtualAccountException::VIRTUAL_ACCOUNT_NOT_NULL);
                    }
                    // 卡密库库存不足
                    if ($virtualAccountInfo->total_count <= 0) {
                        throw new VirtualAccountException(VirtualAccountException::VIRTUAL_ACCOUNT_STOCK_NOT_NULL);
                    }
                }
            } else {
                // 卡密库id不存在
                if (empty($this->goodsInfo['goods']['virtual_account_id'])) {
                    throw new VirtualAccountException(VirtualAccountException::VIRTUAL_ACCOUNT_NOT_NULL);
                }
                $virtualAccountInfo = VirtualAccountModel::existsInfo($this->goodsInfo['goods']['virtual_account_id']);

                // 卡密库不存在
                if (!$virtualAccountInfo) {
                    throw new VirtualAccountException(VirtualAccountException::VIRTUAL_ACCOUNT_NOT_NULL);
                }
                // 卡密库库存不足
                if ($virtualAccountInfo->total_count <= 0) {
                    throw new VirtualAccountException(VirtualAccountException::VIRTUAL_ACCOUNT_STOCK_NOT_NULL);
                }
            }

        } else {

            //实体商品校验配送参数
            if (is_null($this->goodsInfo['goods']['dispatch_express']) && is_null($this->goodsInfo['goods']['dispatch_intracity']) && is_null($this->goodsInfo['goods']['dispatch_verify'])) {

                self::exception(GoodsException::DISPATCH_MODE_NOT_NULL);
            } else {

                //同城配送仅支持实体商品
                if ($this->goodsInfo['goods']['dispatch_intracity'] == 1 && $this->goodsInfo['goods']['type'] == GoodsTypeConstant::GOODS_TYPE_VIRTUAL) {
                    self::exception(GoodsException::DISPATCH_INTRACITY_ONLY_ENTITY_GOODS);
                }

                // 校验是否开启配送方式
                $expressEnable = ShopSettings::get('dispatch.express.enable');
                $intracityEnable = ShopSettings::get('dispatch.intracity.enable');

                // TODO 判断核销
                if (empty($expressEnable) && empty($intracityEnable)) {
                    self::exception(GoodsException::DISPATCH_ALL_NOT_ENABLE);
                }
            }
        }

        // 自定义按钮验证
        $this->buyButtonVerifier();

        return $this;
    }

    /**
     * 价格面议verifier
     * @throws GoodsException
     * @author nizengchao
     */
    private function buyButtonVerifier()
    {
        // 开启了价格面议
        if ($this->goodsInfo['goods']['buy_button_type'] == GoodsBuyButtonConstant::GOODS_BUY_BUTTON_TYPE_CUSTOM) {
            // 只有实体/虚拟商品, 能开启价格面议
            if (!in_array($this->goodsInfo['goods']['type'], $this->allowOpenBuyButton)) {
                self::exception(GoodsException::GOODS_SAVE_BUY_BUTTON_OPEN_GOODS_TYPE_ERROR);
            }

            if (empty($this->goodsInfo['goods']['buy_button_settings'])) {
                self::exception(GoodsException::GOODS_SAVE_BUY_BUTTON_SETTINGS_EMPTY_PARAMS_EMPTY);
            }

            $buyButtonSettings = $this->goodsInfo['goods']['buy_button_settings'];
            // 按钮名称
            if (empty($buyButtonSettings['name'])) {
                self::exception(GoodsException::GOODS_SAVE_BUY_BUTTON_SETTINGS_PARAMS_ERROR);
            }

            // 点击方式 1: 立即下单(无需处理) 2: 自定义(价格面议), 锁下单和加购功能
            if (!in_array($buyButtonSettings['click_type'], [GoodsBuyButtonConstant::GOODS_BUY_BUTTON_CLICK_TYPE_DEFAULT, GoodsBuyButtonConstant::GOODS_BUY_BUTTON_CLICK_TYPE_CUSTOM])) {
                self::exception(GoodsException::GOODS_SAVE_BUY_BUTTON_SETTINGS_PARAMS_ERROR);
            }

            // 走自定义点击方式
            if ($buyButtonSettings['click_type'] == GoodsBuyButtonConstant::GOODS_BUY_BUTTON_CLICK_TYPE_CUSTOM) {
                // 价格文字
                if (empty($buyButtonSettings['price_text'])) {
                    self::exception(GoodsException::GOODS_SAVE_BUY_BUTTON_SETTINGS_PARAMS_ERROR);
                }

                // 点击交互 1:弹窗 2:跳转链接 3:电话
                if (!in_array($buyButtonSettings['click_style'], [GoodsBuyButtonConstant::GOODS_BUY_BUTTON_CLICK_STYLE_POP, GoodsBuyButtonConstant::GOODS_BUY_BUTTON_CLICK_STYLE_JUMP, GoodsBuyButtonConstant::GOODS_BUY_BUTTON_CLICK_STYLE_PHONE])) {
                    self::exception(GoodsException::GOODS_SAVE_BUY_BUTTON_SETTINGS_PARAMS_ERROR);
                }

                // 点击交互样式分别验证
                // 弹窗内容
                if ($buyButtonSettings['click_style'] == GoodsBuyButtonConstant::GOODS_BUY_BUTTON_CLICK_STYLE_POP && empty($buyButtonSettings['click_pop_content'])) {
                    self::exception(GoodsException::GOODS_SAVE_BUY_BUTTON_SETTINGS_POP_PARAMS_EMPTY);
                }
                // 跳转链接
                if ($buyButtonSettings['click_style'] == GoodsBuyButtonConstant::GOODS_BUY_BUTTON_CLICK_STYLE_JUMP && empty($buyButtonSettings['click_jump_url'])) {
                    self::exception(GoodsException::GOODS_SAVE_BUY_BUTTON_SETTINGS_JUMP_URL_PARAMS_EMPTY);
                }
                // 电话
                if ($buyButtonSettings['click_style'] == GoodsBuyButtonConstant::GOODS_BUY_BUTTON_CLICK_STYLE_PHONE) {
                    // 电话号码类型 1:走商城设置 2: 自定义
                    if (!in_array($buyButtonSettings['click_telephone_type'], [GoodsBuyButtonConstant::GOODS_BUY_BUTTON_CLICK_TELEPHONE_TYPE_DEFAULT, GoodsBuyButtonConstant::GOODS_BUY_BUTTON_CLICK_TELEPHONE_TYPE_CUSTOM])) {
                        self::exception(GoodsException::GOODS_SAVE_BUY_BUTTON_CLICK_TELEPHONE_TYPE_ERROR);
                    }
                    // 自定义的电话号码
                    if ($buyButtonSettings['click_telephone_type'] == GoodsBuyButtonConstant::GOODS_BUY_BUTTON_CLICK_TELEPHONE_TYPE_CUSTOM && empty($buyButtonSettings['click_telephone'])) {
                        self::exception(GoodsException::GOODS_SAVE_BUY_BUTTON_SETTINGS_TELEPHONE_PARAMS_EMPTY);
                    }
                    // 电话走读取商城电话配置, 尝试获取电话
                    if ($buyButtonSettings['click_telephone_type'] == GoodsBuyButtonConstant::GOODS_BUY_BUTTON_CLICK_TELEPHONE_TYPE_DEFAULT) {
                        GoodsService::getShopDefaultTel(true);
                    }
                }
            }


            // 电话格式验证
            // 全部放开,不需要验证了
//            if ($buyButtonSettings['click_type'] == 3) {
//                if (!ValueHelper::isMobile($buyButtonSettings['click_telephone']) && !ValueHelper::isTelephone($buyButtonSettings['click_telephone']) && !ValueHelper::isTelephone($buyButtonSettings['click_telephone'], false) && !ValueHelper::is400($buyButtonSettings['click_telephone']) && !ValueHelper::is400($buyButtonSettings['click_telephone'], 2)) {
//                    self::exception(GoodsException::GOODS_SAVE_BUY_BUTTON_SETTINGS_TELEPHONE_PARAMS_ERROR);
//                }
//            }
        } else {
            // 未开启, 重置数据
            $this->goodsInfo['goods']['buy_button_type'] = (string)GoodsBuyButtonConstant::GOODS_BUY_BUTTON_TYPE_DEFAULT;
            $this->goodsInfo['goods']['buy_button_settings'] = [];
        }
    }

    /**
     * 检查会员等级折扣数据
     * @param array $levels 等级
     * @param array $data 要检查的数据
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    private function checkMemberLevelDiscountData(array $levels, array $data)
    {
        foreach ($levels as $level) {
            foreach ($data as $key => $value) {
                if (empty($value['discount']) && $value['discount'] == "") {
                    self::exception(GoodsException::MEMBER_LEVEL_DISCOUNT_NOT_NULL);
                }
                // 如果匹配到该等级的折扣信息 且金额不为空
                if ($key == $level->id && !empty($value['discount'])) {
                    // 折扣类型
                    if ($value['type'] == GoodsConstant::MEMBER_DISCOUNT_TYPE_SCALE) {
                        if (bccomp($value['discount'], 0.01, 2) < 0 || bccomp($value['discount'], 9.99, 2) > 0) {
                            self::exception(GoodsException::MEMBER_LEVEL_DISCOUNT_NUM_ERROR);
                        }
                    } else if ($value['type'] == GoodsConstant::MEMBER_DISCOUNT_TYPE_PRICE) {
                        // 价格类型
                        if (bccomp($value['discount'], 0, 2) < 0 || bccomp($value['discount'], 9999999.99, 2) > 0) {
                            self::exception(GoodsException::MEMBER_LEVEL_DISCOUNT_NUM_ERROR);
                        }
                    }
                }
            }
        }
    }

    /**
     * 保存规格商品
     * @return $this
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    private function saveOptions(): GoodsCreator
    {
        //获取参数值
        $options = $this->getparams('options');
        foreach ($options as $option) {
            //如果没有id则是新增 如果有id则是修改
            $model = empty($option['id']) ? new GoodsOptionModel() : GoodsOptionModel::findOne($option['id']);
            //解析穿过来的规格项id
            $specId = array_flip($option['specs']);
            $oldSpecs = $option['specs'];

            //获取交集key
            $specsItemMap = array_values(array_intersect_key($this->specsItemMap, $specId));
            asort($specsItemMap);
            //按顺序拼接规格项id
            $specsItemMap = implode(',', $specsItemMap);
            //设置属性值
            $option['specs'] = $specsItemMap;
            $option['goods_id'] = $this->saveGoodsId;
            $tmpId = $option['tmpid'];
            unset($option['tmpid']);
            unset($options['id']);
            $model->setAttributes($option);
            //设置日志数据
            $this->logPrimary['goods']['options'][] = [
                'title' => $option['title'],
                'price' => $option['price'],
                'stock' => $option['stock'],
            ];

            $this->logData['options'][] = $model->attributes;


            //如果保存失败则抛出异常
            !$model->save() && self::exception(GoodsException::GOODS_SAVE_GOODS_OPTIONS_SAVE_ERROR);
            //设置规格商品价格
            $this->optionPrice[] = $option['price'];
            $this->optionOriginalPrice[] = $option['original_price'];
            $this->optionCostPrice[] = $option['cost_price'];

            //设置规格商品id
            $this->optionId[] = [
                'id' => $model->id,
                'temp_id' => $tmpId
            ];
        }

        return $this;
    }

    /**
     * 保存规格
     * @return $this
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    private function saveSpec(): GoodsCreator
    {
        //获取参数
        $specs = self::getParams('spec');
        foreach ($specs as $specIndex => $spec) {
            if (empty($spec['items'])) {
                continue;
            }

            //保存规格
            $specModel = empty($spec['id']) || !is_numeric($spec['id']) ? new GoodsSpecModel() : GoodsSpecModel::findOne($spec['id']);
            $spec['goods_id'] = $this->saveGoodsId;
            //规格项另存
            $items = $spec['items'];
            unset($spec['id'], $spec['items']);

            $specModel->setAttributes($spec);
            //设置日志数据
            $this->logData['spec'][] = $specModel->attributes();

            //如果保存失败的话则抛出异常
            !$specModel->save() && self::exception(GoodsException::GOODS_SAVE_GOODS_SPEC_SAVE_ERROR);
            $this->specId[] = $specModel->id;

            //保存规格项
            foreach ($items as $specItemIndex => $specItem) {
                $specItemModel = empty($specItem['id']) || !is_numeric($specItem['id']) ? new GoodsSpecItemModel() : GoodsSpecItemModel::findOne($specItem['id']);
                //设置属性
                $specItem['goods_id'] = $this->saveGoodsId;
                $specItem['spec_id'] = $specModel->id;
                $specItemKeyId = $specItem['id'];
                $specItemModel->setAttributes($specItem);
                //如果保存失败的话则抛出异常
                !$specItemModel->save() && self::exception(GoodsException::GOODS_SAVE_GOODS_SPEC_ITEM_SAVE_ERROR);
                //添加规格项映射
                $this->specsItemMap[$specItemKeyId] = $specItemModel->id;
                $this->specItemId[] = $specItemModel->id;
            }
        }

        return $this;
    }

    /**
     * 保存会员等级折扣
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function saveMemberLevelDiscount()
    {

        //日志特殊处理
        $logMemberData = [];

        // 指定会员折扣
        if ($this->goodsInfo['goods']['member_level_discount_type'] == 2) {
            foreach ($this->goodsInfo['member_level_discount'] as $key => $value) {
                if (empty($value['id'])) {
                    $discount = new GoodsMemberLevelDiscountModel();
                } else {
                    $discount = GoodsMemberLevelDiscountModel::findOne(['id' => $value['id']]);
                }
                $discount->type = $value['type'];
                $discount->goods_id = $this->saveGoodsId;
                $discount->level_id = $key;
                $discount->discount = $value['discount'];
                if ($discount->save() === false) {
                    throw new GoodsException(GoodsException::MEMBER_LEVEL_DISCOUNT_SAVE_FAIL, $discount->getErrorMessage());
                }
                $this->levelDiscountId[] = $discount->id;

                $logMemberData[] = [
                    'level_id' => $discount->level_id,
                    'option_id' => 0,
                    'type' => $discount->type,
                    'discount' => $discount->discount
                ];
            }
        } else {
            // 多规格
            foreach ($this->goodsInfo['member_level_discount'] as $key => $value) {
                // 如果下标不是数字 说明是新增的规格
                if (!is_numeric($key)) {
                    foreach ($this->optionId as $option) {
                        if ($option['temp_id'] == $key) {
                            $optionId = $option['id'];
                        }
                    }
                } else {
                    $optionId = $key;
                }
                foreach ($value as $index => $item) {
                    if (empty($item['id'])) {
                        $discount = new GoodsMemberLevelDiscountModel();
                    } else {
                        $discount = GoodsMemberLevelDiscountModel::findOne(['id' => $item['id']]);
                    }
                    $discount->goods_id = $this->saveGoodsId;
                    $discount->level_id = $index;
                    $discount->type = $item['type'];
                    $discount->discount = $item['discount'];

                    $discount->option_id = $optionId;
                    if ($discount->save() === false) {
                        self::exception(GoodsException::MEMBER_LEVEL_DISCOUNT_SAVE_FAIL);
                    }
                    $this->levelDiscountId[] = $discount->id;


                    $logMemberData[] = [
                        'level_id' => $discount->level_id,
                        'option_id' => $discount->option_id,
                        'type' => $discount->type,
                        'discount' => $discount->discount
                    ];
                }
            }
        }


        //处理日志
        if (!empty($logMemberData)) {
            $levelId = array_column($logMemberData, 'level_id');
            $optionId = array_column($logMemberData, 'option_id');
            $memberLevel = MemberLevelModel::find()->where(['id' => $levelId])->indexBy('id')->select(['id', 'level_name'])->asArray()->all();
            $options = GoodsOptionModel::find()->where(['id' => $optionId])->indexBy('id')->select(['id', 'title'])->asArray()->all();
            foreach ($logMemberData as $logMemberDataIndex => &$logMemberDataItem) {
                $logMemberDataItem['option_name'] = $options[$logMemberDataItem['option_id']]['title'] ?: '';
                $logMemberDataItem['level_name'] = $memberLevel[$logMemberDataItem['level_id']]['level_name'] ?: '';
                $logMemberDataItem['type'] = $logMemberDataItem['type'] == GoodsConstant::MEMBER_DISCOUNT_TYPE_SCALE ? GoodsConstant::getText(GoodsConstant::MEMBER_DISCOUNT_TYPE_SCALE) : GoodsConstant::getText(GoodsConstant::MEMBER_DISCOUNT_TYPE_PRICE);
                unset($logMemberDataItem['option_id'], $logMemberDataItem['level_id']);
            }
        }

        $this->logPrimary['member_discount']['rules'] = $logMemberData;
    }

    /**
     * 保存后
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    private function afterSave()
    {
        $goods = $this->getParams('goods');

        //如果是多规格
        if ($this->hasOption) {
            //全部规格商品库存
            $goodsStock = array_sum(array_column($this->getParams('options'), 'stock'));

            // 虚拟卡密 去重库存数量
            if ($goods['type'] == GoodsTypeConstant::GOODS_TYPE_VIRTUAL_ACCOUNT) {
                $goodsStock = array_sum(array_column($this->getParams('options'), 'stock', 'virtual_account_id'));
            }

            //修改多规格商品的最大值和最小值
            GoodsModel::updateAll([
                'max_price' => max($this->optionPrice),//规格最大价格
                'min_price' => min($this->optionPrice),//规格最小价格
                'price' => min($this->optionPrice),//规格最小价格
                'cost_price' => min($this->optionCostPrice),//规格最小成本价格
                'original_price' => min($this->optionOriginalPrice),//规格最小原价价格
                'stock' => $goodsStock,//总库存
            ], ['id' => $this->saveGoodsId]);
        }

        //删除无效的规格
        GoodsSpecModel::deleteAll([
            'and',
            ['goods_id' => $this->saveGoodsId],
            ['not in', 'id', $this->specId]
        ]);

        //删除无效的规格项
        GoodsSpecItemModel::deleteAll([
            'and',
            ['goods_id' => $this->saveGoodsId],
            ['not in', 'id', $this->specItemId]
        ]);

        //删除无效的规格商品
        GoodsOptionModel::deleteAll([
            'and',
            ['goods_id' => $this->saveGoodsId],
            ['not in', 'id', array_column($this->optionId, 'id')]
        ]);

        // 关闭订单 商品规格发生变化的
        if ($this->hasOption) { // 有规格 查找订单中无规格或规格不存在的未付款的订单
            $andWhere = [
                'or',
                ['option_id' => 0],
                ['not in', 'option_id', array_column($this->optionId, 'id')],
            ];
        } else {
            // 单规格 查找有规格的订单那 都关闭
            $andWhere = ['<>', 'option_id', 0];
        }

        // 查找订单
        $orderGoods = OrderGoodsModel::find()
            ->select(['order_id'])
            ->where(['goods_id' => $this->saveGoodsId, 'status' => OrderStatusConstant::ORDER_STATUS_WAIT_PAY, 'shop_goods_id' => 0])
            ->andWhere($andWhere)
            ->get();

        if (!empty($orderGoods)) {
            $orderIds = array_unique(array_column($orderGoods, 'order_id'));
            foreach ($orderIds as $orderId) {
                $order = OrderModel::findOne(['id' => $orderId]);
                // 关闭订单;
                $res = OrderService::closeOrder($order, OrderConstant::ORDER_CLOSE_TYPE_SYSTEM_AUTO_CLOSE, $this->uid, [
                    'cancel_reason' => '修改商品信息关闭订单',
                    'un_update_stock' => 1,
                ]);
            }
        }

        // 修改购物车状态
        if ($this->hasOption) {
            // 单规格
            GoodsCartModel::updateAll(
                ['is_selected' => 0, 'is_reelect' => 1],
                [
                    'and',
                    ['goods_id' => $this->saveGoodsId],
                    [
                        'or',
                        ['option_id' => 0],
                        ['not in', 'option_id', array_column($this->optionId, 'id')],
                    ]
                ]
            );
        } else {
            // 单规格
            GoodsCartModel::updateAll(
                ['is_selected' => 0, 'is_reelect' => 1],
                [
                    'and',
                    ['goods_id' => $this->saveGoodsId],
                    ['<>', 'option_id', 0],
                ]
            );
        }

        // 修改表单 全部重选
        if ($this->changeForm) {
            GoodsCartModel::updateAll(['is_selected' => 0, 'is_reelect' => 1], ['goods_id' => $this->saveGoodsId,]);
        }
        // 修改上架状态
        if ($this->isEdit && $this->goodsInfo['goods']['status'] == 0) {
            // 更新购物车状态
            GoodsCartModel::updateAll(['is_lose_efficacy' => 1, 'is_selected' => 0], ['goods_id' => $this->saveGoodsId]);
        }

        // 删除无效会员等级折扣数据
        if ($this->goodsInfo['goods']['member_level_discount_type'] == GoodsMemberLevelDiscountConstant::GOODS_MEMBER_LEVEL_DISCOUNT_GOODS
            || $this->goodsInfo['goods']['member_level_discount_type'] == GoodsMemberLevelDiscountConstant::GOODS_MEMBER_LEVEL_DISCOUNT_OPTION) {

            GoodsMemberLevelDiscountModel::deleteAll([
                'and',
                ['goods_id' => $this->saveGoodsId],
                ['not in', 'id', $this->levelDiscountId]
            ]);
        }

        //重建商品与分类映射关系
        GoodsCategoryMapModel::deleteAll(['goods_id' => $this->saveGoodsId]);
        empty($goods['category_id']) ?: GoodsCategoryMapModel::batchInsert(
            ['goods_id', 'category_id'], $this->batchInsertHandle($this->saveGoodsId, GoodsCategoryMapModel::getIdCoverParent($goods['category_id'])));

        //重建商品与标签映射关系
        GoodsLabelMapModel::deleteAll(['goods_id' => $this->saveGoodsId]);
        empty($goods['label_id']) ?: GoodsLabelMapModel::batchInsert(
            ['goods_id', 'label_id'], $this->batchInsertHandle($this->saveGoodsId, $goods['label_id']));

        //先删除所有权限
        GoodsPermMapModel::deleteAll(['goods_id' => $this->saveGoodsId]);

        //商品浏览权限(会员等级)
        if ($goods['browse_level_perm'] == 1 && !empty($goods['browse_level_perm_ids'])) {
            GoodsPermMapModel::setGoodsPermById($this->saveGoodsId, $goods['browse_level_perm_ids']);
            $this->logPrimary['browse_perm']['member_level']['name'] = MemberLevelModel::find()->where(['id' => $goods['browse_level_perm_ids']])->select('level_name')->column();
        }

        //商品购买权限(会员等级)
        if ($goods['buy_level_perm'] == 1 && !empty($goods['buy_level_perm_ids'])) {
            GoodsPermMapModel::setGoodsPermById($this->saveGoodsId, $goods['buy_level_perm_ids'], GoodsPermMapModel::PERM_BUY);
            $this->logPrimary['buy_perm']['member_level']['name'] = MemberLevelModel::find()->where(['id' => $goods['buy_level_perm_ids']])->select('level_name')->column();
        }

        //商品浏览权限(会员标签)
        if ($goods['browse_tag_perm'] == 1 && !empty($goods['browse_tag_perm_ids'])) {
            GoodsPermMapModel::setGoodsPermById($this->saveGoodsId, $goods['browse_tag_perm_ids'], GoodsPermMapModel::PERM_VIEW, GoodsPermMapModel::MEMBER_TYPE_TAG);
            $this->logPrimary['browse_perm']['member_label']['name'] = MemberGroupModel::find()->where(['id' => $goods['browse_tag_perm_ids']])->select('group_name')->column();
        }

        //商品购买权限(会员标签)
        if ($goods['buy_tag_perm'] == 1 && !empty($goods['buy_tag_perm_ids'])) {
            GoodsPermMapModel::setGoodsPermById($this->saveGoodsId, $goods['buy_tag_perm_ids'], GoodsPermMapModel::PERM_BUY, GoodsPermMapModel::MEMBER_TYPE_TAG);
            $this->logPrimary['buy_perm']['member_label']['name'] = MemberLevelModel::find()->where(['id' => $goods['buy_tag_perm_ids']])->select('level_name')->column();
        }

        //定时上架
        if ($this->extField['auto_putaway'] == 1 && !empty($this->extField['putaway_time']) && (strtotime($this->extField['putaway_time']) - time()) > 0) {
            QueueHelper::push(new AutoPutawayJob([
                'goodsId' => $this->saveGoodsId,
            ]), strtotime($this->extField['putaway_time']) - time());
        }

        //营销
        $this->logPrimary['sales'] = [
            'deduction_credit_type' => $this->logPrimary['goods']['deduction_credit_type'] == 0 ? '关闭' : ($this->logPrimary['goods']['deduction_credit_type'] == 1 ? '不限制' : '自定义'),
            'deduction_balance_type' => $this->logPrimary['goods']['deduction_balance_type'] == 0 ? '关闭' : ($this->logPrimary['goods']['deduction_balance_type'] == 1 ? '不限制' : '自定义'),
            'single_full_unit_switch' => $this->logPrimary['goods']['single_full_unit_switch'] == 1 ? '开启' : '关闭',
            'single_full_unit' => empty($this->logPrimary['goods']['single_full_unit']) ? '' : $this->logPrimary['goods']['single_full_unit'],
            'single_full_quota_switch' => $this->logPrimary['goods']['single_full_quota_switch'] == 1 ? '开启' : '关闭',
            'single_full_quota' => empty($this->logPrimary['goods']['single_full_quota']) ? '' : $this->logPrimary['goods']['single_full_quota'],
            'give_credit_status' => $this->logPrimary['goods']['give_credit_status'] == 1 ? '开启' : '关闭',
            'give_credit_num' => $this->logPrimary['goods']['give_credit_num'],
        ];

        // 购买按钮日志
        $this->logPrimary['buy_button']['buy_button_type'] = $this->extField['buy_button_type'] == GoodsBuyButtonConstant::GOODS_BUY_BUTTON_TYPE_CUSTOM ? '自定义(价格面议)' : '读取装修配置';
        if (!empty($this->extField['buy_button_settings'])) {
            $settings = $this->getBuyButtonSettingsLog($this->extField['buy_button_settings']);
            $this->logPrimary['buy_button'] = array_merge($this->logPrimary['buy_button'], $settings);
        }
        //日志
        $goodsModel = new GoodsModel();
        $this->logPrimary['goods']['status'] = GoodsStatusConstant::getText($this->logPrimary['goods']['status']);
        $this->logPrimary['goods']['type'] = GoodsTypeConstant::getText($this->logPrimary['goods']['type']);
        $this->logPrimary['goods']['reduction_type'] = $this->logPrimary['goods']['reduction_type'] == 0 ? '下单减库存' : ($this->logPrimary['goods']['reduction_type'] == 1 ? '付款减库存' : '永不减库存');
        $this->logPrimary['goods']['dispatch_type'] = $this->logPrimary['goods']['dispatch_type'] == 0 ? '包邮' : ($this->logPrimary['goods']['dispatch_type'] == 1 ? '运费模板' : '统一运费');
        unset($this->logPrimary['goods']['is_new'], $this->logPrimary['goods']['is_recommand'], $this->logPrimary['goods']['is_deleted'], $this->logPrimary['goods']['max_price'], $this->logPrimary['goods']['min_price']);
        $this->logPrimary = $goodsModel->getLogAttributeRemark($this->logPrimary);


        $goodsLogConst = $this->isEdit ? GoodsLogConstant::GOODS_EDIT : GoodsLogConstant::GOODS_ADD;

        //添加脏数据
        LogModel::write(
            $this->uid,
            $goodsLogConst,
            GoodsLogConstant::getText($goodsLogConst),
            $this->saveGoodsId,
            [
                'log_data' => $this->logData,
                'log_primary' => $this->logPrimary,
                'dirty_identify_code' => [
                    GoodsLogConstant::GOODS_EDIT,
                    GoodsLogConstant::GOODS_ADD
                ]
            ]
        );
    }

    /**
     * 获取价格面议设置的log信息
     * @param $settings
     * @return array
     * @author nizengchao
     */
    private function getBuyButtonSettingsLog($settings): array
    {
        $log = [
            'name' => $settings['name'],
            'click_type' => $settings['click_type'] == GoodsBuyButtonConstant::GOODS_BUY_BUTTON_CLICK_TYPE_DEFAULT ? '立即下单' : '价格面议',
            'price_text' => $settings['price_text'] ?? '',
        ];
        $clickStyle = '';
        if ($settings['click_style'] == GoodsBuyButtonConstant::GOODS_BUY_BUTTON_CLICK_STYLE_POP) {
            $clickStyle = '弹窗';
        } elseif ($settings['click_style'] == GoodsBuyButtonConstant::GOODS_BUY_BUTTON_CLICK_STYLE_JUMP) {
            $clickStyle = '跳转链接';
        } elseif ($settings['click_style'] == GoodsBuyButtonConstant::GOODS_BUY_BUTTON_CLICK_STYLE_PHONE) {
            $clickStyle = '拨打电话';
        }
        $log['click_style'] = $clickStyle;
        return $log;
    }

    /**
     * 组装结构
     * @param int $goodsId
     * @param array $typeId
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    private static function batchInsertHandle(int $goodsId, array $typeId): array
    {
        $data = [];
        foreach ((array)$typeId as $typeIdIndex => $typeIdItem) {
            $data[] = [$goodsId, $typeIdItem];
        }

        return $data;
    }

    /**
     * 设置参数
     * @param string $params
     * @param array $value
     * @return bool
     * @author 青岛开店星信息技术有限公司.
     */
    public function setParams(string $params, array $value = []): bool
    {
        $this->goodsInfo[$params] = $value;
        return true;
    }

    /**
     * 是否商品保存后
     * @return bool
     * @author 青岛开店星信息技术有限公司.
     */
    private function isGoodsSaveAfter(): bool
    {
        return $this->saveGoodsId != 0;
    }

}