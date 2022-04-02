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

namespace shopstar\services\commentHelper;

use shopstar\constants\commentHelper\CommentHelperConstant;
use shopstar\constants\core\CoreAttachmentSceneConstant;
use shopstar\constants\core\CoreAttachmentTypeConstant;
use shopstar\exceptions\commentHelper\CommentHelperException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\LiYangHelper;
use shopstar\helpers\StringHelper;
use shopstar\models\core\CoreSettings;
use shopstar\models\goods\GoodsModel;
use shopstar\models\goods\GoodsOptionModel;
use shopstar\models\member\MemberLevelModel;
use shopstar\models\order\OrderGoodsCommentModel;
use shopstar\models\shop\ShopSettings;
use shopstar\services\core\attachment\CoreAttachmentService;
use yii\helpers\Json;

/**
 * 抓取评价
 * Class CommentGrabService
 * @package shopstar\services\commentHelper
 * @author 青岛开店星信息技术有限公司
 */
class CommentGrabService
{

    /**
     * @var int 操作员id
     */
    private $userId;

    /**
     * @var mixed 商品id
     */
    private $goodsId;

    /**
     * @var string 抓取类型
     */
    private $type;

    /**
     * @var array 接口返回数据 本次
     */
    private $resData = [];

    /**
     * @var array 用户填写数据
     */
    private $paramsData;

    /**
     * @var int 接口返回条数
     */
    private $resTotal;

    /**
     * @var int 商品规格
     */
    private $option = [];

    /**
     * @var int 商品规格个数
     */
    private $optionCount = 0;

    /**
     * @var int 会员等级
     */
    private $memberLevel = [];

    /**
     * @var int 会员等级个数
     */
    private $memberLevelCount = 0;

    /**
     * 每个渠道每次抓取条数不同
     * @var int[] 类型对应条数
     */
    private $totalMap = [
        'jd' => 10,
        'suning' => 10,
        'taobao' => 20,
        'tmall' => 20
    ];

    /**
     * @var int 最后一次请求时间
     */
    private $lastRequestTime = 0;

    /**
     * 每个渠道值不同
     * @var \string[][] 渠道对应筛选值
     */
    private $sortMap = [
        'jd' => [ // 京东
            '0' => '0', // 全部
            '1' => '3', // 仅抓取好评
            '2' => '4', // 仅抓取带图
            '3' => '0', // 仅抓取文字
        ],
        'suning' => [ // 苏宁
            '0' => 'total', // 全部
            '1' => 'good', // 仅抓取好评
            '2' => 'show', // 仅抓取带图
            '3' => 'total', // 仅抓取文字
        ],
        'tmall' => [ // 天猫 (没用 新版接口没有这个筛选
            '0' => '10', // 全部
            '1' => '1', // 仅抓取好评
            '2' => '3', // 仅抓取带图
            '3' => '10', // 仅抓取文字
        ],
        'taobao' => [ // 淘宝
            '0' => '10', // 全部
            '1' => '1', // 仅抓取好评
            '2' => '3', // 仅抓取带图
            '3' => '10', // 仅抓取文字
        ],
    ];

    /**
     * 初始化
     * CommentGrabService constructor.
     */
    public function __construct(int $userId, array $data)
    {
        $this->userId = $userId; // 操作员id
        $this->goodsId = $data['goods_id']; // 商品id
        $this->type = $data['type']; // 抓取类型
        $this->paramsData = $data; // 参数
        $this->resTotal = $this->totalMap[$data['type']]; // 接口返回条数

        // 会员等级 随机
        if ($data['level_id'] == 0) {
            $this->memberLevel = MemberLevelModel::find()->select(['id', 'level_name'])->get();
            $this->memberLevelCount = count($this->memberLevel);
        } else {
            $this->memberLevel = MemberLevelModel::find()->select(['level_name'])->where(['id' => $data['level_id']])->first();
        }

        // 判断商品是否多规格
        // 获取商品信息
        $goods = GoodsModel::find()->select(['has_option'])->where(['id' => $data['goods_id'], 'is_deleted' => [0, 1]])->first();
        if (empty($goods)) {
            throw new CommentHelperException(CommentHelperException::GRAB_API_GOODS_NOT_EXISTS);
        }
        if ($goods['has_option'] == 1) {
            $this->option = GoodsOptionModel::find()->select(['title'])->where(['goods_id' => $data['goods_id']])->get();
            $this->optionCount = count($this->option);
        }
    }

    /**
     * 抓取
     * @throws CommentHelperException|\yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function grab()
    {
        // 获取抓取商品id
        $goodsData = $this->getGoodsId();
        if (empty($goodsData['goods_id']) || !is_numeric($goodsData['goods_id'])) {
            throw new CommentHelperException(CommentHelperException::GRAB_API_GOODS_ID_EMPTY);
        }

        // apikey
        $apiKey = ShopSettings::get('commentHelper.api_key');

        // 插入字段
        $field = ['content', 'level', 'created_at', 'goods_id', 'status', 'images', 'is_have_image', 'is_new', 'type', 'nickname', 'avatar', 'member_level_name', 'option_title', 'grab_url', 'level_id'];

        $page = 1; // 页码
        $count = 0; // 累计已抓取评论数量
        $consume = 0; // 接口调用次数 (消耗抓取次数
        // 调用次数  接口每次返回$resTotal条
        for ($i = $this->paramsData['num']; $i > 0; $i -= $this->resTotal) {

            // 组装参数
            $params = [
                'goods_id' => $goodsData['goods_id'],
                'api_key' => $apiKey,
                'type' => $this->type,
                'page' => $page, // 当前页数
                'sort' => $this->sortMap[$this->type][$this->paramsData['content_type']], // 筛选 每个渠道不一样
            ];

            try {
                // 控制每秒请求一次 太短等待
                // 获取当前时间
                $currentTime = time();
                if ($currentTime - $this->lastRequestTime < 1) {
                    sleep(1);
                }

                // 调用接口
                $result = $this->getComment($params);
                if (is_error($result)) {
                    // 如果大于0条 不抛异常
                    if ($count > 0) {
                        break;
                    } else {
                        // 一条都没成功时 抛异常
                        return $result;
                    }
                }
                // 异常判断 系统错误
                if ($result['retcode'] == 4016) {
                    if ($count == 0) {
                        return error('余额不足, 请联系管理员');
                    } else {
                        // 如果抓到一部分 跳出
                        break;
                    }
                } else if ($result['retcode'] > 2000) {
                    continue;
                } else if ($result['retcode'] == 2000) {
                    // 没有评论了
                    break;
                }

                // 赋值
                $this->resData = $result['data'];

                // 获取插入数据  参数为 还剩能抓多少条
                $insertData = $this->getInsertData($this->paramsData['num'] - $count);

                // 插入评论
                OrderGoodsCommentModel::batchInsert($field, $insertData);

                // 成功抓取条数
                $count += count($insertData);

                // 没有下一页了 跳出
                if (!$result['hasNext']) {
                    break;
                }

                // 页码+1
                $page++;
            } catch (\Throwable $exception) {
                // 有异常不再执行
                break;
            } finally {
                // 调用次数加一
                $consume++;
            }
        }

        return ['count' => $count];
    }

    /**
     * 京东数据
     * @param int $num 剩余能抓多少条
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    private function getInsertData(int $num)
    {
        $insertData = [];
        // 不能用下标统计
        $count = 0;

        foreach ($this->resData as $item) {
            // 判断抓取数量 当剩余抓取数量 小于 每页返回数量时 跳出
            if ($num <= $count) {
                break;
            }

            // 处理各个渠道字段不一致的问题
            $unityInfo = $this->unityInfo($item);

            // 本地图片路径
            $imagesPath = [];
            // 如果选择带图 但抓取的没图  过滤
            if ($this->type == CommentHelperConstant::CONTENT_TYPE_IMAGES && empty($unityInfo['images'])) {
                continue;
            }

            // 如果带图 需要上传图片
            if ($this->paramsData['content_type'] != CommentHelperConstant::CONTENT_TYPE_TEXT && !empty($unityInfo['images'])) {
                // 调试关闭 不然慢
                $imagesPath = $this->uploadImage($unityInfo['images']);
            }

            $randNum = rand(0, $this->memberLevelCount - 1);
            // 组装插入数据
            $insertData[] = [
                mb_substr($unityInfo['content'], 0, 500),
                $this->paramsData['level'],
                DateTimeHelper::getRandDate($this->paramsData['start_time'], $this->paramsData['end_time']),
                $this->goodsId,
                $this->paramsData['status'],
                Json::encode($imagesPath),
                !empty($imagesPath) ? 1 : 0, // 是否有图
                1, // is_new
                3, // 抓取
                $unityInfo['nickname'],
                $unityInfo['avatar'],
                $this->paramsData['level_id'] == 0 ? $this->memberLevel[$randNum]['level_name'] : $this->memberLevel['level_name'],
                !empty($this->option) ? $this->option[rand(0, $this->optionCount - 1)]['title'] : '',
                $this->paramsData['url'],
                $this->paramsData['level_id'] != 0 ? $this->paramsData['level_id'] : $this->memberLevel[$randNum]['id'],
            ];

            $count++;
        }

        return $insertData;
    }

    /**
     * 统一各个渠道字段
     * 每个渠道返回的字段不一致
     * @author 青岛开店星信息技术有限公司
     */
    private function unityInfo(array $data): array
    {
        $info = [];
        switch ($this->type) {
            case 'jd': // 京东
                $info['avatar'] = 'https://' . $data['userAvatar'];
                $info['nickname'] = $data['nickname'];
                $info['images'] = $data['images'];
                $info['content'] = $data['content'];
                break;
            case 'suning': // 苏宁
                $info['avatar'] = $data['userInfo']['imgUrl'];
                $info['nickname'] = $data['userInfo']['nickName'];
                $info['content'] = $data['content'];
                if (!empty($item['picVideInfo']['imageInfo'])) {
                    foreach ($item['picVideInfo']['imageInfo'] as $item) {
                        $info['images'][] = $item['url'];
                    }
                }
                break;
            case 'tmall': // 天猫
                $info['avatar'] = $data['userAvatar'];
                $info['nickname'] = $data['userNick'];
                $info['images'] = $data['feedPicList'];
                $info['content'] = $data['feedback'];
            case 'taobao': // 淘宝
                $info['avatar'] = $data['userAvatar'];
                $info['nickname'] = $data['userNick'];
                $info['images'] = $data['images'];
                $info['content'] = $data['content'];
                break;
        }

        return $info;
    }

    /**
     * 上传图片
     * @param array $images
     * @return array
     * @throws \ReflectionException
     * @throws \yii\base\Exception
     */
    private function uploadImage(array $images): array
    {
        $path = [];
        foreach ($images as $item) {
            $thumb = CoreAttachmentService::upload([
                'type' => CoreAttachmentTypeConstant::TYPE_IMAGE,
                'group_id' => -2, // 评价助手
                'account_id' => $this->userId,
                'remote' => StringHelper::exists($item, ['http://', 'https://'],
                    StringHelper::SEL_OR) ? $item : 'http:' . $item
            ], CoreAttachmentSceneConstant::SCENE_MANAGE);
            if (!is_error($thumb)) {
                $path[] = $thumb['path'];
            }
        }
        return $path;
    }

    /**
     * 获取评价
     * @author 青岛开店星信息技术有限公司
     */
    private function getComment(array $params)
    {
        $accountName = ShopSettings::get('channel_setting.wechat.name', '');

        $params = [
            'type' => $params['type'],
            'goods_id' => $params['goods_id'],
            'api_key' => ShopSettings::get('commentHelper.api_key'),
            'site_id' => CoreSettings::get('auth.site_id'),
            'wechat_name' => $accountName,
            'is_comment' => 1,
            'itemId' => $params['goods_id'],
            'page' => $params['page'],
            'sort' => $params['sort'],
            'filter' => $params['sort'],
        ];


        $res = CloudServiceHelper::post(LiYangHelper::ROUTE_OPEN_API_GOODS_HELPER_GET, $params);
        if (is_error($res)) {
            return $res;
        }
        // 设置最后一次调接口时间
        $this->lastRequestTime = time();

        return $res['data'];
    }

    /**
     * 正则获取商品id
     * @author 青岛开店星信息技术有限公司
     */
    private function getGoodsId(): array
    {
        // 苏宁
        if ($this->type == 'suning') {
            $url = parse_url($this->paramsData['url']);
            $param = explode('/', $url['path']);
            $returnData['goods_id'] = str_replace(strrchr($param[2], "."), "", $param[2]);
        } elseif ($this->type == 'jd') {
            // 京东
            $url = parse_url($this->paramsData['url']);
            $param = explode('/', $url['path']);

            if (strstr($param[1], '.html')) {
                $returnData['goods_id'] = str_replace(strrchr($param[1], "."), "", $param[1]);
            }
        } else {
            // 淘宝 天猫
            $regx = '/.*[&|\?]' . 'id' . '=([^&]*)(.*)/';
            preg_match($regx, $this->paramsData['url'], $data);
            $returnData['goods_id'] = $data[1];
        }

        return $returnData;
    }

}