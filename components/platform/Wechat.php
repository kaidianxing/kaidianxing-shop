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

namespace shopstar\components\platform;

use yii\base\Component;
use yii\helpers\Json;

/**
 * Class Wechat
 * @package shopstar\components\platform
 * @property \EasyWeChat\OfficialAccount\Application $app 微信SDK实例
 * @property \EasyWeChat\Payment\Application $payment 微信支付SDK实例
 * @property \EasyWeChat\MiniProgram\Application $miniProgram 微信小程序实例
 * @property \EasyWeChat\OpenPlatform\Application $openPlatform 微信开放平台(第三方平台)实例
 * @property \EasyWeChat\Work\Application $work 企业微信实例
 * @property \EasyWeChat\OpenWork\Application $openWork 企业微信开放平台实例
 * @author 青岛开店星信息技术有限公司
 */
class Wechat extends Component
{
    public static $errorCode = [
        '40164' => 'IP白名单配置错误',
        '-80082' => '“好物推荐”插件未授权',
        '40001' => '获取 access_token 时 AppSecret 错误，或者 access_token 无效',
        '40002' => '不合法的凭证类型',
        '40003' => '不合法的 OpenID',
        '40004' => '不合法的媒体文件类型',
        '40005' => '不合法的文件类型',
        '40006' => '不合法的文件大小',
        '40007' => '不合法的媒体文件 id',
        '40008' => '不合法的消息类型',
        '40009' => '不合法的图片文件大小',
        '40010' => '不合法的语音文件大小',
        '40011' => '不合法的视频文件大小',
        '40012' => '不合法的缩略图文件大小',
        '40013' => '不合法的 AppID',
        '40014' => '不合法的 access_token',
        '40015' => '不合法的菜单类型',
        '40016' => '不合法的按钮个数',
        '40017' => '不合法的按钮个数',
        '40018' => '不合法的按钮名字长度',
        '40019' => '不合法的按钮 KEY 长度',
        '40020' => '不合法的按钮 URL 长度',
        '40021' => '不合法的菜单版本号',
        '40022' => '不合法的子菜单级数',
        '40023' => '不合法的子菜单按钮个数',
        '40024' => '不合法的子菜单按钮类型',
        '40025' => '不合法的子菜单按钮名字长度',
        '40026' => '不合法的子菜单按钮 KEY 长度',
        '40027' => '不合法的子菜单按钮 URL 长度',
        '40028' => '不合法的自定义菜单使用用户',
        '40029' => '不合法的 oauth_code',
        '40030' => '不合法的 refresh_token',
        '40031' => '不合法的 openid 列表',
        '40032' => '不合法的 openid 列表长度',
        '40033' => '不合法的请求字符',
        '40035' => '不合法的参数',
        '40037' => '不合法的模板消息ID',
        '40038' => '不合法的请求格式',
        '40039' => '不合法的 URL 长度',
        '40050' => '不合法的分组 id',
        '40051' => '分组名字不合法',
        '40060' => '删除单篇图文时，指定的 article_idx 不合法',
        '40117' => '分组名字不合法',
        '40119' => 'button 类型错误',
        '40120' => 'button 类型错误',
        '40121' => '不合法的 media_id 类型',
        '40125' => 'appsecret 错误',
        '40132' => '微信号不合法',
        '40137' => '不支持的图片格式',
        '40155' => '请勿添加其他公众号的主页链接',
        '41001' => '缺少 access_token 参数',
        '41002' => '缺少 appid 参数',
        '41003' => '缺少 refresh_token 参数',
        '41004' => '缺少 secret 参数',
        '41005' => '缺少多媒体文件数据',
        '41006' => '缺少 media_id 参数',
        '41007' => '缺少子菜单数据',
        '41008' => '缺少 oauth code',
        '41009' => '缺少 openid',
        '42001' => 'access_token 超时',
        '42002' => 'refresh_token 超时',
        '42007' => '用户修改微信密码， accesstoken 和 refreshtoken 失效',
        '43001' => '需要 GET 请求',
        '43002' => '需要 POST 请求',
        '43003' => '需要 HTTPS 请求',
        '43004' => '需要接收者关注',
        '43005' => '需要好友关系',
        '43019' => '需要将接收者从黑名单中移除',
        '44001' => '多媒体文件为空',
        '44002' => 'POST 的数据包为空',
        '44003' => '图文消息内容为空',
        '44004' => '文本消息内容为空',
        '45001' => '多媒体文件大小超过限制',
        '45002' => '消息内容超过限制',
        '45003' => '标题字段超过限制',
        '45004' => '描述字段超过限制',
        '45005' => '链接字段超过限制',
        '45006' => '图片链接字段超过限制',
        '45007' => '语音播放时间超过限制',
        '45008' => '图文消息超过限制',
        '45009' => '接口调用超过限制',
        '45010' => '创建菜单个数超过限制',
        '45011' => 'API 调用太频繁，请稍候再试',
        '45015' => '回复时间超过限制',
        '45016' => '系统分组，不允许修改',
        '45017' => '分组名字过长',
        '45018' => '分组数量超过上限',
        '45026' => '模板消息数量超限',
        '45047' => '客服接口下行条数超过上限',
        '46001' => '不存在媒体数据',
        '46002' => '不存在的菜单版本',
        '46003' => '不存在的菜单数据',
        '46004' => '不存在的用户',
        '47001' => '解析 JSON/XML 内容错误',
        '48001' => 'api 功能未授权',
        '48002' => '粉丝拒收消息',
        '48004' => 'api 接口被封禁',
        '48005' => 'api 禁止删除被自动回复和自定义菜单引用的素材',
        '48006' => 'api 禁止清零调用次数',
        '48008' => '没有该类型消息的发送权限',
        '50001' => '用户未授权该 api',
        '50002' => '用户受限',
        '50005' => '用户未关注公众号',
        '61003' => '开放平台未授权',
        '61004' => '当前客户端ip未在开放平台白名单',
        '61007' => '通过第三方平台接入时，需要先授权第三方平台相关权限集合',
        '61023' => '授权已过期,请重新授权',
        '61450' => '系统错误 (system error)',
        '61451' => '参数错误 (invalid parameter)',
        '61452' => '无效客服账号 (invalid kf_account)',
        '61453' => '客服帐号已存在 (kf_account exsited)',
        '61454' => '客服帐号名长度超过限制 ( 仅允许 10 个英文字符，不包括 @ 及 @ 后的公众号的微信号 )(invalid kf_acount length)',
        '61455' => '客服帐号名包含非法字符 ( 仅允许英文 + 数字 )(illegal character in kf_account)',
        '61456' => '客服帐号个数超过限制 (10 个客服账号 )(kf_account count exceeded)',
        '61457' => '无效头像文件类型 (invalid file type)',
        '61500' => '日期格式错误',
        '65301' => '不存在此 menuid 对应的个性化菜单',
        '65302' => '没有相应的用户',
        '65303' => '没有默认菜单，不能创建个性化菜单',
        '65304' => 'MatchRule 信息为空',
        '65305' => '个性化菜单数量受限',
        '65306' => '不支持个性化菜单的帐号',
        '65307' => '个性化菜单信息为空',
        '65308' => '包含没有响应类型的 button',
        '65309' => '个性化菜单开关处于关闭状态',
        '65310' => '填写了省份或城市信息，国家信息不能为空',
        '65311' => '填写了城市信息，省份信息不能为空',
        '65312' => '不合法的国家信息',
        '65313'=>	'不合法的省份信息',
        '65314'=>	'不合法的城市信息',
        '65316'=>	'该公众号的菜单设置了过多的域名外跳（最多跳转到 3 个域名的链接）',
        '65317'=>	'不合法的 URL',
        '85001' => '微信号不存在或微信号设置为不可搜索',
        '85002' => '小程序绑定的体验者数量达到上限',
        '85003' => '微信号绑定的小程序体验者达到上限',
        '85004' => '微信号已经绑定',
        '85006' => '标签格式错误',
        '85007' => '页面路径错误',
        '85008' => '类目填写错误',
        '85009' => '已经有正在审核的版本',
        '85010' => 'item_list有项目为空',
        '85011' => '标题填写错误',
        '85012' => '无效的审核id',
        '85013' => '无效的自定义配置',
        '85014' => '无效的模版编号',
        '85015' => '版本输入错误',
        '85017' => '开放平台添加小程序业务域名',
        '85019' => '没有审核版本',
        '85020' => '审核状态未满足发布',
        '85021' => '状态不可变-5以内',
        '85022' => 'action非法',
        '85023' => '审核列表填写的项目数不在1-5以内',
        '85043' => '模版错误',
        '85044' => '代码包超过大小限制',
        '85045' => '导航设置错误,请重置导航再试,编号85045',//'ext_json有不存在的路径',
        '85046' => 'tabBar中缺少path',
        '85047' => 'pages字段为空',
        '85048' => '导航设置错误,请重置导航再试,编号85048',//'ext_json解析失败',
        '85066' => '链接错误',
        '85068' => '测试链接不是子链接',
        '85069' => '校验文件失败',
        '85070' => '链接为黑名单',
        '85071' => '已添加该链接，请勿重复添加',
        '85072' => '该链接已被占用',
        '85073' => '二维码规则已满',
        '85074' => '小程序未发布, 小程序必须先发布代码才可以发布二维码跳转规则',
        '85075' => '个人类型小程序无法设置二维码规则',
        '85076' => '链接没有ICP备案',
        '85077' => '小程序类目信息失效（类目中含有官方下架的类目，请重新选择类目）',
        '85079' => '小程序没有线上版本，不能进行灰度',
        '85080' => '小程序提交的审核未审核通过',
        '85081' => '无效的发布比例',
        '85082' => '当前的发布比例需要比之前设置的高',
        '85085' => '当前平台近7天提交审核的小程序数量过多，请耐心等待审核完毕后再次提交',
        '86000' => '不是由第三方代小程序进行调用',
        '86001' => '不存在第三方的已经提交的代码',
        '86002' => '小程序还未设置昵称、头像、简介。请先设置完后再重新提交',
        '87011' => '现网已经在灰度发布，不能进行版本回退',
        '87012' => '该版本不能回退，可能的原因：1:无上一个线上版用于回退 2:此版本为已回退版本，不能回退 3:此版本为回退功能上线之前的版本，不能回退',
        '87013' => '撤回次数达到上限（每天一次，每个月10次）',
        '89019' => '业务域名无更改，无需重复设置',
        '89020' => '尚未设置小程序业务域名，请先在第三方平台中设置小程序业务域名后在调用本接口',
        '89021' => '请求保存的域名不是第三方平台中已设置的小程序业务域名或子域名',
        '89029' => '业务域名数量超过限制',
        '89231' => '个人小程序不支持调用setwebviewdomain 接口',
        '89031' => '小程序绑定的体验者数量达到上限',
        '9001001'=>	'POST 数据参数不合法',
        '9001002'=>	'远端服务不可用',
        '9001003'=>	'Ticket 不合法',
        '9001004'=>	'获取摇周边用户信息失败',
        '9001005'=>	'获取商户信息失败',
        '9001006'=>	'获取 OpenID 失败',
        '9001007'=>	'上传文件缺失',
        '9001008'=>	'上传素材的文件类型不合法',
        '9001009'=>	'上传素材的文件尺寸不合法',
        '9001010'=>	'上传失败',
        '9001020'=>	'帐号不合法',
        '9001021'=>	'已有设备激活率低于 50% ，不能新增设备',
        '9001022'=>	'设备申请数不合法，必须为大于 0 的数字',
        '9001023'=>	'已存在审核中的设备 ID 申请',
        '9001024'=>	'一次查询设备 ID 数量不能超过 50',
        '9001025'=>	'设备 ID 不合法',
        '9001026'=>	'页面 ID 不合法',
        '9001027'=>	'页面参数不合法',
        '9001028'=>	'一次删除页面 ID 数量不能超过 10',
        '9001029'=>	'页面已应用在设备中，请先解除应用关系再删除',
        '9001030'=>	'一次查询页面 ID 数量不能超过 50',
        '9001031'=>	'时间区间不合法',
        '9001032'=>	'保存设备与页面的绑定关系参数错误',
        '9001033'=>	'门店 ID 不合法',
        '9001034'=>	'设备备注信息过长',
        '9001035'=>	'设备申请参数不合法',
        '9001036'=>	'查询起始值 begin 不合法',
        '9009098'=>	'请求参数错误',
        '9009099'=>	'系统错误',
        '9009203'=>	'openid与当前appid不匹配',
        '9009204'=>	'小程序或公众号类目不符合要求',
        '9009205'=>	'调用帐号需为已上线、已认证小程序帐号',
        '9009206'=>	'非法的请求帐号',
    ];

    /**
     * 接口异常
     * @param $result
     * @param string $flag
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function apiError($result, string $flag = '')
    {
        // 异常捕获
        if ($result instanceof \Exception) {
            if ($result->getCode() == 0 || $result->getCode() == -1) {
                preg_match_all("/\[{.*?\}]/is", $result->getMessage(), $matches);
                if (!empty(array_filter($matches))) {
                    $result = Json::decode($matches[0][1]);
                    return error($result['message'], $result['errcode'] ?: -1);
                }

                if (isset(self::$errorCode[$result->formattedResponse['errcode']])){

                    return error(self::$errorCode[$result->formattedResponse['errcode']]);
                }
                return error($result->getMessage());
            }

            return error($result->getMessage(), $result->getCode() ?: -1);
        } elseif (is_array($result)) {
            if ($result['return_code'] === 'FAIL') {
                return error($result['return_msg'] ?: '微信接口通信错误');
            } elseif ($result['result_code'] === 'FAIL') {
                return error($result['err_code_des'] ?: '微信操作失败', $result['err_code'] ?: -1);
            } elseif ((int)$result['errcode'] != 0) {

                if (isset(self::$errorCode[$result['errcode']])) {
                    return error(self::$errorCode[$result['errcode']]);
                }

                if ($result['errmsg']) {
                    return error($result['errmsg'], $result['errcode'] ?: -1);
                }
                return error($result['message'], $result['errcode'] ?: -1);
            } else {
                // 小程序交易组件需要拿到错误码进行下一步操作判断
                if ($flag != 'wx_transaction_component') {
                    unset($result['errcode'], $result['errmsg'], $result['result_code'], $result['return_msg'], $result['return_code'], $result['err_code_des']);
                }
            }
        }

        if (empty($result)) {
            return true;
        }

        return $result;
    }


}
