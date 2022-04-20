<?php

namespace install\bases;

use install\helpers\FileCacheHelper;
use install\helpers\KdxCloudHelper;
use shopstar\helpers\RequestHelper;
use yii\base\InvalidRouteException;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\Response;

/**
 * 控制器基类
 * Class BaseController
 * @package install\bases
 * @author likexin
 */
class BaseController extends Controller
{

    /**
     * @var bool 禁用csrf
     */
    public $enableCsrfValidation = false;

    /**
     * @var array Controller配置
     */
    protected array $config = [];

    /**
     * @var array Controller默认配置
     */
    private $defaultConfig = [

        // 忽略登录限制的Actions(不验证auth但是token要必填)
        'ignoreAuthActions' => [],

        // 需要POST请求的Actions
        'postActions' => [],

        // 允许header参数在get中传的Actions
        'headerUseGetActions' => [],
    ];

    /**
     * 获取Controller设置
     * @return array
     * @author likexin
     */
    public function getConfig(): array
    {
        return ArrayHelper::merge($this->defaultConfig, $this->config);
    }

    /**
     * Action运行前事件
     * @param \yii\base\Action $action
     * @return bool
     * @throws InstallException
     * @throws \yii\web\BadRequestHttpException
     * @author likexin
     */
    public function beforeAction($action): bool
    {
        // 检测POST请求
        $this->checkPost();

        return parent::beforeAction($action);
    }

    /**
     * Action运行事件
     * @param string $id
     * @param array $params
     * @return mixed|Response|null
     * @author likexin
     */
    public function runAction($id, $params = [])
    {
        try {
            return parent::runAction($id, $params);
        } catch (InstallException $exception) {
            return $this->error($exception->getMessage(), $exception->getCode());
        } catch (InvalidRouteException $exception) {
            return $this->error($exception->getName(), 404);
        }
    }

    /**
     * 检测POST请求
     * @throws InstallException
     * @author likexin
     */
    private function checkPost()
    {
        if (!\Yii::$app->request->isPost && in_array($this->action->id, $this->getConfig()['postActions'])) {
            throw new InstallException(InstallException::BASE_CHECK_POST_FAIL);
        }
    }

    /**
     * 获取请求参数，自动判断Header还是Get
     * @param string $field
     * @return array|mixed|string
     * @author likexin
     */
    protected function getHeaderOrGet(string $field)
    {
        // 默认取header
        $value = RequestHelper::header($field);

        // 当前actionId在允许Auth列表中，跳出
        if (in_array($this->action->id, $this->getConfig()['headerUseGetActions'])) {
            $value = RequestHelper::get($field);
        }

        return $value;
    }

    /**
     * 返回信息
     * @param null $return 返回值
     * @param int $error 错误码
     * @return Response
     * @author likexin
     */
    public function result($return = null, int $error = 0): Response
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
                $result = array_merge($result, $return);
            } else if ($return !== true) {
                // 其他情况塞入message
                $result['message'] = $return;
            }
        }

        return $this->asJson($result);
    }

    /**
     * 返回错误
     * @param mixed $message 错误消息
     * @param int $error 错误码
     * @return Response
     * @author likexin
     */
    public function error($message = '', int $error = -1): Response
    {
        return $this->result($message, $error);
    }

}