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

namespace shopstar\admin\broadcast;

use shopstar\bases\KdxAdminApiController;
use shopstar\components\wechat\helpers\MiniProgramBroadcastGoodsHelper;
use shopstar\components\wechat\helpers\MiniProgramMediaHelper;
use shopstar\constants\broadcast\BroadcastLogConstant;
use shopstar\constants\goods\GoodsDeleteConstant;
use shopstar\constants\goods\GoodsStatusConstant;
use shopstar\exceptions\broadcast\BroadcastGoodsException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\FileHelper;
use shopstar\helpers\ImageHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\broadcast\BroadcastGoodsModel;
use shopstar\models\broadcast\BroadcastRoomGoodsMapModel;
use shopstar\models\goods\category\GoodsCategoryMapModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\goods\group\GoodsGroupMapModel;
use shopstar\models\log\LogModel;
use shopstar\services\core\attachment\CoreAttachmentService;
use yii\web\UploadedFile;

/**
 * 小程序商品库
 * Class GoodsController
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\admin\broadcast
 */
class GoodsController extends KdxAdminApiController
{

    /**
     * 列表
     * @return array|int[]|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionList()
    {
        $get = RequestHelper::get();

        $params = [
            'alias' => 'goods',
            'leftJoin' => [BroadcastGoodsModel::tableName() . ' bro_goods', 'bro_goods.goods_id=goods.id'],
            'where' => [
                'goods.status' => GoodsStatusConstant::GOODS_STATUS_PUTAWAY,
                'goods.is_deleted' => GoodsDeleteConstant::GOODS_IS_DELETE_NO,
            ],
            'searchs' => [
                ['goods.title', 'like', 'title'],
                ['bro_goods.status', 'int', 'bro_goods_status'],
                ['goods.type', 'int', 'goods_type'],
            ],
            'select' => [
                'goods.id as goods_id',
                'goods.title',
                'goods.status',
                'goods.thumb',
                'goods.type',
                'goods.price',
                'goods.has_option',
                'goods.stock',
                'goods.min_price',
                'goods.max_price',
                'goods.real_sales',
                'goods.status',
                'bro_goods.status as bro_goods_status',
                'bro_goods.broadcast_goods_id',
                'bro_goods.audit_id'
            ]
        ];

        //按照分类id查找
        if (!empty($get['category_id'])) {
            $goodsId = GoodsCategoryMapModel::getGoodsIdByCategoryId((array)$get['category_id']) ?: [];
            empty($goodsId) ? $params['where']['goods.id'] = 0 : $params['where']['goods.id'] = $goodsId;
        }

        //如果有分组id则根据分组id查找
        if ($get['group_id']) {
            $goodsId = GoodsGroupMapModel::getGoodsIdByGroupId((array)$get['group_id']) ?: [];
            empty($goodsId) ? $params['where']['goods.id'] = 0 : $params['where']['goods.id'] = $goodsId;
        }

        //如果有排序
        if (!empty($get['sort']) && !empty($get['by'])) {
            $params['orderBy']['goods.' . $get['sort']] = $get['by'] == 'asc' ? SORT_ASC : SORT_DESC;
            $params['orderBy']['goods.sort_by'] = SORT_DESC;
        } else {
            //追加排序
            $params['orderBy']['goods.sort_by'] = SORT_DESC;
            $params['orderBy']['goods.created_at'] = SORT_DESC;
        }

        $list = GoodsModel::getColl($params);

        return $this->result($list);
    }

    /**
     * 添加审核
     * @return array|int[]|\yii\web\Response
     * @throws \yii\base\InvalidConfigException
     * @throws BroadcastGoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAddAudit()
    {
        $goodsId = RequestHelper::postInt('goods_id');
        if (empty($goodsId)) {
            throw new BroadcastGoodsException(BroadcastGoodsException::BROADCAST_MANAGE_GOODS_ADD_AUDIT_PARAMS_ERROR);
        }

        $goods = GoodsModel::findOne([
            'id' => $goodsId,
            'status' => GoodsStatusConstant::GOODS_STATUS_PUTAWAY
        ]);

        if (empty($goods)) {
            throw new BroadcastGoodsException(BroadcastGoodsException::BROADCAST_MANAGE_GOODS_ADD_AUDIT_GOODS_NOT_EXIST_ERROR);
        }

        $thumbUrl = CoreAttachmentService::getUrl($goods->thumb);
        $filePatch = SHOP_STAR_PUBLIC_TMP_PATH . '/wxapp_media/' . md5($goods->thumb) . '.png';


        $fileContent = file_get_contents($thumbUrl);
        FileHelper::write($filePatch, $fileContent);

        // 临时文件信息
        $tmpFile = getimagesize($filePatch);

        // 组成Yii文件类
        $file = new UploadedFile([
            'name' => basename($filePatch),
            'size' => filesize($filePatch),
            'tempName' => $filePatch,
            'type' => $tmpFile['mime'],
        ]);

        //等比压缩图片
        ImageHelper::compress($file, ['width' => 300, 'height' => 300, 'fixed' => true]);

        //提交图片素材
        $thumbMediaId = MiniProgramMediaHelper::uploadImage($filePatch);

        //释放临时文件
        @unlink($filePatch);
        if (is_error($thumbMediaId)) {
            throw new BroadcastGoodsException(BroadcastGoodsException::BROADCAST_MANAGE_GOODS_ADD_AUDIT_GOODS_THUMB_UPLOAD_ERROR, $thumbMediaId['message']);
        }

        $tr = \Yii::$app->db->beginTransaction();
        try {
            $model = new BroadcastGoodsModel();
            $model->setAttributes([
                'goods_id' => $goodsId,
                'cover_img_url' => $goods->thumb,
                'cover_img_media_id' => $thumbMediaId['media_id'],
                'status' => 0,
                'created_at' => DateTimeHelper::now()
            ]);

            if (!$model->save()) {
                throw new \Exception('添加商品库失败');
            }

            $result = MiniProgramBroadcastGoodsHelper::add([
                'coverImgUrl' => $thumbMediaId['media_id'],
                'name' => mb_substr($goods->title, 0, 12) . (mb_strlen($goods->title) > 12 ? '...' : ''),
                'priceType' => $goods->has_option == 0 ? 1 : 2, //单规格1口价  多规格区间价
                'price' => $goods->price,
                'price2' => $goods->max_price,
                'url' => 'kdxGoods/detail/index?goods_id=' . $goods->id,
//                'url' => 'pages/index/index?goods_id=' . $goods->id,
            ]);

            if (is_error($result)) {
                switch ($result['error']) {
                    //图片尺寸过大
                    case 300018:
                        throw new \Exception('图片尺寸不能大于300*300');
                        break;
                    case 300002:
                        throw new \Exception('标题长度不合法,最长14个汉字,1个汉字相当于2个字符');
                        break;
                    case 300003:
                        throw new \Exception('商品价格不合法');
                        break;
                }

                throw new \Exception($result['message']);
            }

            //赋值商品库商品id和审核id
            $model->setAttributes([
                'broadcast_goods_id' => $result['goodsId'],
                'audit_id' => $result['auditId'],
            ]);

            $model->save();
            @unlink($filePatch);

            //  日志
            LogModel::write(
                $this->userId,
                BroadcastLogConstant::BROADCAST_GOODS_TO_EXAMINE_SUBMIT,
                BroadcastLogConstant::getText(BroadcastLogConstant::BROADCAST_GOODS_TO_EXAMINE_SUBMIT),
                $model->id,
                [
                    'log_data' => $model->attributes,
                    'log_primary' => [
                        '商品信息' => 'ID' . $goods->id . ',商品名称：' . $goods->title,
                        '价格' => $goods->has_option == 0 ? $goods->price : $goods->price . '-' . $goods->max_price,
                        '库存' => $goods->stock,
                        '销量' => $goods->real_sales
                    ]
                ]
            );

            $tr->commit();
        } catch (\Exception $exception) {
            $tr->rollBack();
            throw new BroadcastGoodsException(BroadcastGoodsException::BROADCAST_MANAGE_GOODS_ADD_AUDIT_ERROR, $exception->getMessage());
        }

        return $this->success();
    }

    /**
     * 撤销审核
     * @return array|int[]|\yii\web\Response
     * @throws BroadcastGoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionResetAudit()
    {
        $broadcastGoodsId = RequestHelper::postInt('broadcast_goods_id');
        $auditId = RequestHelper::postInt('audit_id');
        if (empty($broadcastGoodsId) || empty($auditId)) {
            throw new BroadcastGoodsException(BroadcastGoodsException::BROADCAST_MANAGE_GOODS_RESET_AUDIT_PARAMS_ERROR);
        }

        $result = MiniProgramBroadcastGoodsHelper::resetAudit([
            'goodsId' => $broadcastGoodsId,
            'auditId' => $auditId
        ]);

        if (is_error($result)) {
            throw new BroadcastGoodsException(BroadcastGoodsException::BROADCAST_MANAGE_GOODS_RESET_AUDIT_ERROR, $result['message']);
        }

        //撤销删除商品库
        BroadcastGoodsModel::deleteAll([
            'broadcast_goods_id' => $broadcastGoodsId,
        ]);

        return $this->result($result);
    }

    /**
     * 重新审核
     * @return array|int[]|\yii\web\Response
     * @throws BroadcastGoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionRepeatAudit()
    {
        $broadcastGoodsId = RequestHelper::postInt('broadcast_goods_id');
        if (empty($broadcastGoodsId)) {
            throw new BroadcastGoodsException(BroadcastGoodsException::BROADCAST_MANAGE_GOODS_REPEAT_AUDIT_PARAMS_ERROR);

        }

        $result = MiniProgramBroadcastGoodsHelper::audit(['goodsId' => $broadcastGoodsId]);
        if (is_error($result)) {
            throw new BroadcastGoodsException(BroadcastGoodsException::BROADCAST_MANAGE_GOODS_REPEAT_AUDIT_ERROR);
        }

        //修改状态
        BroadcastGoodsModel::updateAll([
            'status' => 0
        ], [
            'broadcast_goods_id' => $broadcastGoodsId,
        ]);

        return $this->result($result);
    }

    /**
     * 删除商品审核
     * @throws BroadcastGoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $broadcastGoodsId = RequestHelper::postInt('broadcast_goods_id');
        if (empty($broadcastGoodsId)) {
            throw new BroadcastGoodsException(BroadcastGoodsException::BROADCAST_MANAGE_GOODS_DELETE_PARAMS_ERROR);
        }
        // 确定是否删除小程序商品需求后 注释删除方法
        $result = MiniProgramBroadcastGoodsHelper::delete(['goodsId' => $broadcastGoodsId]);
        if (is_error($result)) {
            throw new BroadcastGoodsException(BroadcastGoodsException::BROADCAST_MANAGE_GOODS_DELETE_ERROR, $result['message']);
        }

        //删除商品映射
        BroadcastRoomGoodsMapModel::deleteAll(['broadcast_goods_id' => $broadcastGoodsId]);

        BroadcastGoodsModel::deleteAll(['broadcast_goods_id' => $broadcastGoodsId]);

        return $this->result($result);
    }
}
