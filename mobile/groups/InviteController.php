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

namespace shopstar\mobile\groups;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\components\wechat\helpers\MiniProgramACodeHelper;
use shopstar\exceptions\groups\GroupsException;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\ShopUrlHelper;
use shopstar\models\activity\ShopMarketingModel;
use shopstar\models\groups\GroupsTeamModel;
use shopstar\services\groups\GroupsCrewService;
use yii\web\Response;

/**
 * 手机端邀请数据控制器
 * Class InviteController
 * @package shopstar\mobile\groups
 * @author likexin
 */
class InviteController extends BaseMobileApiController
{

    /**
     * 活动详情页面
     * @return array|int[]|\yii\web\Response
     * @throws GroupsException
     * @author likexin
     */
    public function actionIndex()
    {
        // 货权团ID
        $teamId = RequestHelper::getInt('team_id');
        if (empty($teamId)) {
            throw new GroupsException(GroupsException::GROUPS_TEAM_ID_NOT_EXISTS);
        }

        // 获取团队跟团队成员信息
        $teamDetail = GroupsTeamModel::find()
            ->where([
                'id' => $teamId,
            ])
            ->select([
                'id',
                'activity_id',
                'id',
                'leader_id',
                'end_time',
                'is_ladder',
                'ladder',
                'count',
                'success',
                'success_num',
            ])
            ->first();

        if (empty($teamDetail['activity_id'])) {
            throw new GroupsException(GroupsException::GROUPS_INVITE_TEAM_IS_EMPTY);
        }

        // 获取活动状态
        $activity = ShopMarketingModel::find()
            ->where([
                'id' => $teamDetail['activity_id'],
            ])
            ->select([
                'status',
            ])
            ->first();

        // 获取商品信息
        $goodsInfo = GroupsCrewService::getGoodsInfoByTeam($teamId, $teamDetail['activity_id']);

        // 获取团队成员信息
        $crewList = GroupsCrewService::getCrewByTeamId($teamId);

        // 判断当前登录用户是否参与了
        if (in_array($this->memberId, array_column($crewList, 'member_id'))) {

            // 如果是团长，给前端一个标识
            if ($this->memberId == $teamDetail['leader_id']) {
                $teamDetail['is_leader'] = 1;
                //如果不是但参与了，参与者身份
            } else {
                $teamDetail['is_join'] = 1;
            }

            foreach ($crewList as $crew) {
                if ($this->memberId == $crew['member_id']) {
                    $teamDetail['order_id'] = $crew['order_id'];
                }
            }

            $teamDetail['invite_url'] = ShopUrlHelper::wap('/pagesGoods/groups/detail', [
                'team_id' => $teamId,
                'inviter_id' => $this->memberId,
            ], true);
        }

        return $this->result([
            'goods' => $goodsInfo,
            'crew_list' => $crewList,
            'team_detail' => $teamDetail,
            'activity' => $activity,
        ]);
    }

    /**
     * 获取小程序二维码
     * @return Response
     * @author likexin
     */
    public function actionGetWxappQrcode(): Response
    {
        $teamId = RequestHelper::getInt('team_id');
        if (empty($teamId)) {
            return $this->error('参数错误 team_id为空');
        }

        //文件名
        $fileName = md5($teamId . '_' . $this->memberId) . '.jpg';
        // 保存地址文件夹
        $savePatchDir = SHOP_STAR_PUBLIC_TMP_PATH . '/wxapp_qrcode/';

        // 保存地址
        $savePatch = SHOP_STAR_PUBLIC_TMP_PATH . '/wxapp_qrcode/' . $fileName;

        // 访问地址
        $accessPatch = ShopUrlHelper::build('tmp/wxapp_qrcode/' . $fileName, [], true);

        // 如果不是文件  ||  生成时间大于一天
        if (!is_file($savePatch) || (filemtime($savePatch) && (time() - filemtime($savePatch)) > 86400)) {

            $result = MiniProgramACodeHelper::getUnlimited(http_build_query([
                'inviter_id' => $this->memberId,
                'team_id' => $teamId
            ]), [
                'page' => 'pagesGoods/groups/detail',
                'directory' => $savePatchDir,
                'fileName' => $fileName
            ]);

            if (is_error($result)) {
                return $this->result($result);
            }
        }

        return $this->success([
            'patch' => $accessPatch,
        ]);
    }
    
}