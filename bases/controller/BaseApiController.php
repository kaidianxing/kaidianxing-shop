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

namespace shopstar\bases\controller;

use shopstar\bases\exception\BaseApiException;
use shopstar\bases\exception\BaseException;
use shopstar\bases\model\BaseActiveRecord;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\LiYangHelper;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\ResponseHelper;
use shopstar\helpers\StringHelper;
use yii\base\Action;
use yii\base\InvalidRouteException;
use yii\filters\Cors;

/**
 * 所有端接口基类(定义接口常用方法)
 * Class BaseApiController
 * @package shopstar\bases\controller
 * @author 青岛开店星信息技术有限公司
 */
class BaseApiController extends BaseController
{
    /**
     * 关闭csrf
     * @var bool
     */
    public $enableCsrfValidation = false;

    /**
     * @var float|int 请求签名过期时间(2分钟，子类可复写)
     */
    public $requestSignExpireTime = 60 * 2;

    /**
     * @var string SessionId
     */
    public $sessionId = '';

    /**
     * @var string 客户端类型
     */
    public $clientType = 0;

    /**
     * @param Action $action
     * @throws \yii\web\BadRequestHttpException
     * @throws BaseApiException
     * @author likexin
     */
    public function beforeAction($action)
    {
        // 开发模式
        if (YII_DEBUG) {
            parent::beforeAction($action);
        }

        // post请求判断
        if (!RequestHelper::isPost() &&
            (
                isset($this->configActions['postActions']) && is_array($this->configActions['postActions']) &&
                in_array($action->id, $this->configActions['postActions'])
            )
        ) {
            throw new BaseApiException(BaseApiException::REQUEST_MUST_POST);
        }

        // 判断是否已经安装
        if (!StringHelper::exists($this->route, 'install/index')) {
            LiYangHelper::checkInstall(false);
        }

        return parent::beforeAction($action);
    }

    /**
     * 检测Session
     * @throws BaseApiException
     * @author likexin
     */
    public function checkSession()
    {
        $this->sessionId = $this->getHeaderOrGet('Session-Id');
        if (empty($this->sessionId)) {
            throw new BaseApiException(BaseApiException::REQUEST_SESSION_ID_EMPTY);
        }
    }

    /**
     * 检测客户端类型
     * @param array $limitType 限制客户端类型
     * @throws BaseApiException
     * @author likexin
     */
    protected function checkClientType(array $limitType = [])
    {
        $this->clientType = $this->getHeaderOrGet('Client-Type');
        if (empty($this->clientType)) {
            throw new BaseApiException(BaseApiException::REQUEST_CLIENT_TYPE_EMPTY);
        } elseif (!empty($limitType)) {
            if (!in_array($this->clientType, $limitType)) {
                throw new BaseApiException(BaseApiException::REQUEST_CLIENT_TYPE_INVALID);
            }
        }
    }

    /**
     * 检测允许访问
     * @param Action $action 当前访问的Action对象
     * @param callable $function 自定义检测访问的匿名方法
     * @author likexin
     */
    public function checkAccess($action, callable $function)
    {
        // 允许访问Action，跳过
        if (isset($this->configActions['allowActions']) && is_array($this->configActions['allowActions'])) {
            if (in_array('*', $this->configActions['allowActions']) || in_array($action->id, $this->configActions['allowActions'])) {
                return;
            }
        }

        $function();
    }

    /**
     * 获取请求参数，自动判断Header还是Get
     * @param string $field
     * @return array|mixed|string
     * @author likexin
     */
    protected function getHeaderOrGet(string $field)
    {
        $value = RequestHelper::header($field);

        if (empty($value)) {
            if (YII_DEBUG ||
                (
                    (isset($this->configActions['allowHeaderActions']) && is_array($this->configActions['allowHeaderActions']))
                    &&
                    (in_array('*', $this->configActions['allowHeaderActions']) || in_array($this->action->id, $this->configActions['allowHeaderActions']))
                )
            ) {
                $value = RequestHelper::get($field);
            }
        }

        return $value;
    }

    /**
     * @param string $id
     * @param array $params
     * @return ResponseHelper|mixed
     * @throws \yii\base\InvalidRouteException
     * @author likexin
     */
    public function runAction($id, $params = [])
    {
        try {
            return parent::runAction($id, $params);
        } catch (BaseException $exception) {

            return $this->error($exception->getMessage(), $exception->getCode() !== 0 ? $exception->getCode() : -1);
        } catch (InvalidRouteException $exception) {

            return $this->error($exception->getName(), 404);

        } catch (\Exception $exception) {
            return $this->error(YII_DEBUG ? $exception->getTraceAsString() : $exception->getMessage(), 500);
        }
    }

    /**
     * 输出结果
     * @param string|array|\Exception $return 返回数据
     * @param int $error 错误代码
     * @param bool $response 是否输出到浏览器
     * @return \yii\web\Response|array
     * @author likexin
     */
    public function result($return = null, int $error = 0, bool $response = true)
    {
        if ($return instanceof \Throwable) {
            $return = [
                'error' => $return->getCode(),
                'message' => $return->getMessage(),
            ];
        }

        if (is_error($return)) {
            return $this->asJson($return);
        }

        $result = [
            'error' => $error
        ];

        if (!empty($return)) {
            if (is_array($return)) {
                // 如果传入数组，结构合并
                $result = \shopstar\helpers\ArrayHelper::merge($result, $return);
            } elseif ($return instanceof BaseActiveRecord) {
                // 如果返回的ActiveRecord对象，包一层data
                $result = \shopstar\helpers\ArrayHelper::merge($result, [
                    'data' => $return->toArray(),
                ]);
            } else if (!empty($return) && $return !== true) {
                // 其他情况塞入message
                $result['message'] = $return;
            }
        }

        return $response ? $this->asJson($result) : $result;
    }

    /**
     * 输出错误
     * @param string $message
     * @param int $errcode
     * @param bool $response 是否输出到浏览器
     * @return \yii\web\Response
     * @author likexin
     */
    public function error($message = '', int $errcode = -1, bool $response = true)
    {
        return $this->result($message, $errcode, $response);
    }

    /**
     * 输出成功
     * @param mixed $message
     * @param bool $response
     * @return \yii\web\Response
     * @author likexin
     */
    public function success($message = '', bool $response = true)
    {
        return $this->result($message, 0, $response);
    }

    /**
     * 跨域
     * @return array
     * @author 青岛开店星信息技术有限公司
     * @func behaviors
     */
    public function behaviors(): array
    {
        $behaviors = [];

        // 在debug模式，允许跨域
        if (YII_DEBUG) {
            $behaviors[] = [
                'class' => Cors::class,
                'cors' => [
                    'Origin' => [\Yii::$app->request->getHeaders()['origin']],
                    'Access-Control-Allow-Credentials' => true,
                    'Access-Control-Allow-Origin' => [\Yii::$app->request->getHeaders()['origin']],
                    'Access-Control-Max-Age' => 3600,
                    'Access-Control-Request-Method' => ['GET', 'HEAD', 'POST', 'OPTIONS'],
                    'Access-Control-Request-Headers' => ['token', 'Client-Type', 'Session-Id', 'shop-id', 'inviter-id'],
                ]
            ];
        }

        return ArrayHelper::merge(parent::behaviors(), $behaviors);
    }
}
