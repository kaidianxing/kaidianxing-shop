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

namespace shopstar\components\notice;

use shopstar\components\notice\bases\NoticeMakeType;
use shopstar\components\notice\interfaces\MakeInterface;
use shopstar\components\notice\sends\SendNoticeInterface;
use shopstar\constants\core\CoreAppTypeConstant;
use shopstar\helpers\CacheHelper;
use shopstar\helpers\LogHelper;
use shopstar\models\member\MemberModel;
use shopstar\models\shop\ShopSettings;
use shopstar\services\core\CoreAppService;
use Yii;
use yii\base\Component;

/**
 * 消息通知组件
 * Class MessageComponents
 * @author 青岛开店星信息技术有限公司
 */
class NoticeComponent extends Component
{

    /**
     * 格式化完的数据
     * @author 青岛开店星信息技术有限公司
     */
    private $messageData;

    /**
     * 原始数据
     * @author 青岛开店星信息技术有限公司
     * @var
     */
    private $originalMessageData;

    /**
     * 场景值
     * @author 青岛开店星信息技术有限公司
     * @var
     */
    private $sceneCode;

    /**
     * 获取消息通知实例化
     * @param string $sceneCode
     * @param array $messageData
     * @param string $pluginName
     * @return NoticeComponent|array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getInstance(string $sceneCode, array $messageData = [], string $pluginName = '')
    {

        try {
            $self = new self();
            $self->sceneCode = $sceneCode;
            $class = NoticeMakeType::getMakeTypeClass($sceneCode, $pluginName);
            if (is_error($class)) {
                throw new \Exception("{$sceneCode}: Invalid Message Scene Code");
            }

            if (!class_exists($class)) {
                throw new \Exception('Short Message Component');
            }

            //实例化解析工厂
            /**
             * @var $class MakeInterface
             */
            $class = Yii::createObject($class);

            if (!method_exists($class, 'makeMessageData')) {
                throw new \Exception('Short Message Component Method');
            }

            //赋值原始数据
            $self->originalMessageData = $messageData;

            //格式化数据
            $self->messageData = $class->makeMessageData($messageData);
            return $self;
        } catch (\Throwable $throwable) {
            return error($throwable->getMessage());
        }
    }

    /**
     * 发送消息提醒
     * @param array | int $memberId 会员id
     * @param array $options
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public function sendMessage($memberId = [], array $options = []): bool
    {
        $setting = ShopSettings::get('plugin_notice.send')[$this->sceneCode] ?: [];
        //获取需要发送的会员
        $member = [];
        if (!empty($memberId)) {
            $member = MemberModel::getMemberAllInfo($memberId);
            if (empty($member)) return false;
        }

        //发送
        foreach ($setting as $key => $item) {
            if ($item['status'] == 1) {
                try {
                    $messageClass = Yii::createObject([
                        'class' => 'shopstar\components\notice\sends\\Send' . ucfirst($key) . 'Notice',
                        'messageData' => $this->messageData, //发送数据
                        'templateId' => $item['template_id'], // 模板id
                        'options' => array_merge($options, ['type_code' => $this->sceneCode]),
                    ]);
                    LogHelper::info('BUYER_ORDER_PAY222', ['shopstar\components\notice\sends\\Send' . ucfirst($key) . 'Notice']);
                    //获取原始数据属性是否存在
                    if (property_exists($messageClass, 'originalMessageData')) {
                        $messageClass->originalMessageData = $this->originalMessageData;
                    }

                    /**
                     * 发送前检测
                     * @var $messageClass SendNoticeInterface
                     */
                    $result = $messageClass->sendBefore();
                    if ($result === false) {
                        throw new \Exception('Send Before error');
                    }

                    //组装
                    $messageClass->makeTemplateField();

                    //组装To User
                    $messageClass->makeToUser($member ?: $item, empty($member));

                    //发送消息通知
                    $messageClass->sendMessage();
                    LogHelper::info('BUYER_ORDER_PAY3333', ['shopstar\components\notice\sends\\Send' . ucfirst($key) . 'Notice']);
                } catch (\Exception $exception) {
                    LogHelper::error('[SEND SMS CODE ERROR]:', $exception->getMessage());

//                    return error($exception->getMessage());
                }

            }
        }

        return true;
    }

    /**
     * 发送验证码
     * @param int $mobile 手机号
     * @param array $options 接收默认数据,
     * $options[sms][data] 短信替换内容
     * $options[sms][sms_tpl_id] 短信模板id
     * $options[signature][content] 签名
     *
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public function sendVerifyCode(int $mobile, array $options = []): bool
    {
        $options = array_merge([], $options);

        try {
            $setting = ShopSettings::get('plugin_notice.send')[$this->sceneCode] ?: [];
            $setting = $setting['sms'];
            //没有开启短信
            if (!isset($setting) || $setting['status'] == 0) throw new \Exception('sms not open');
            //组成
            $messageClass = Yii::createObject([
                'class' => 'shopstar\components\notice\sends\\' . ucfirst('smsVerifyCode') . 'Notice',
                'sceneCode' => $this->sceneCode,
                'templateId' => $setting['template_id'],
                'mobile' => $mobile,
                'messageData' => $this->messageData, //发送数据
                'options' => [], //附加参数，可扩展
                'sms' => $options['sms'] ?? null,
                'signature' => $options['signature'] ?? null,
            ]);

            //获取原始数据属性是否存在
            if (property_exists($messageClass, 'originalMessageData')) {
                $messageClass->originalMessageData = $this->originalMessageData;
            }

            /**
             * 发送前检测
             * @var $messageClass SendNoticeInterface
             */
            $messageClass->sendBefore();

            //组装
            $messageClass->makeTemplateField();

            //发送消息通知
            $messageClass->sendMessage();

        } catch (\Throwable $throwable) {
            LogHelper::error('[SEND SMS VERIFY CODE ERROR]:', $throwable->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 验证验证码
     * @param $key
     * @param $mobile
     * @param $code
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkVerifyCode($key, $mobile, $code): bool
    {
        $key = '_' . $key . '_' . $mobile;
        if (!$code) {
            return false;
        }
        $smsCode = CacheHelper::get($key);
        return (int)$smsCode === (int)$code;
    }

    /**
     * 根据发送渠道获取场景值
     * @param string $client
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getTypeCodeByClient(string $client = 'sms'): array
    {
        $list = CoreAppService::getAppListNew(CoreAppTypeConstant::TYPE_APP);
        foreach ($list as $item) {
            $appFilePath = \Yii::getAlias('@shopstar') . '/config/apps/' . $item['identity'] . '/NoticeSceneGroup.php';
            if (is_file($appFilePath)) {

                $className = "\\shopstar\\config\\apps\\{$item['identity']}\\NoticeSceneGroup";

                $sceneGroupMap[$item['identity']] = (new $className)::getSceneGroupMap();
            }
        }

        $sceneCode = [];
        foreach ($sceneGroupMap as $item) {
            foreach ($item as $itemIndex => $itemItem) {
                foreach ($itemItem as $itemItemIndex => $itemItemItem) {
                    foreach ($itemItemItem as $itemItemItemIndex => $itemItemItemItem) {
                        if (in_array($client, $itemItemItemItem['item'])) {
                            $sceneCode[] = [
                                'scene_code' => $itemItemItemIndex,
                                'title' => $itemItemItemItem['title']
                            ];
                        }
                    }
                }
            }
        }

        return $sceneCode;
    }

}
