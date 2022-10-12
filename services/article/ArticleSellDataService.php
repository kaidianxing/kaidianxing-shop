<?php

namespace shopstar\services\article;


use shopstar\constants\article\ArticleSellDataConstant;
use shopstar\exceptions\article\ArticleException;
use shopstar\exceptions\article\ArticleSellDataException;
use shopstar\models\article\ArticleModel;
use shopstar\models\article\ArticleSellDataModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\member\MemberModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\sale\CouponMemberModel;
use yii\db\Exception;

class ArticleSellDataService extends ArticleBaseService
{
    /**
     * 获取列表
     * @param int $articleId 文章id
     * @param int $type 类型 1:商品 2:优惠券
     * @return array
     * @throws ArticleException
     * @throws ArticleSellDataException
     * @author yuning
     */
    public function getList(int $articleId = 0, $type = 0): array
    {
        if (!in_array($type, [ArticleSellDataConstant::TYPE_GOODS, ArticleSellDataConstant::TYPE_COUPON])) {
            throw new ArticleSellDataException(ArticleSellDataException::TYPE_ERROR);
        }
        // 获取文章
        ArticleModel::getModel($articleId, 'id');

        $groupBy = $andWhere = $leftJoins = [];

        // 查询条件
        if ($type == ArticleSellDataConstant::TYPE_GOODS) {
            // 商品

            $select = ArticleSellDataModel::$goodsField;
            // 连表
            $leftJoins = [
                [GoodsModel::tableName() . ' goods', 'goods.id = sell.goods_id'],
            ];
            $groupBy = ['sell.goods_id'];
        } else {
            // 优惠券

            $select = ArticleSellDataModel::$couponField;
            // 连表
            $leftJoins = [
                [CouponMemberModel::tableName() . ' coupon_member', 'sell.coupon_member_id = coupon_member.id'],
                [MemberModel::tableName() . ' member', 'sell.member_id = member.id'],
            ];
        }

        // 拼装params
        $params = [
            'select' => $select,
            'alias' => 'sell',
            'where' => [
                'sell.article_id' => $articleId,
                'sell.type' => $type,
            ],
            'andWhere' => $andWhere,
            'leftJoins' => $leftJoins,
            'groupBy' => $groupBy,
            'orderBy' => [
                'sell.id' => SORT_DESC,
            ],

        ];

        // 获取list
        $where = [
            'article_id' => $articleId,
            'type' => $type,
        ];
        return ArticleSellDataModel::getColl($params, [
            'callable' => function (&$item) use ($type, $where) {
                // 商品 需要填充用户和引导金额合计
                if ($type == ArticleSellDataConstant::TYPE_GOODS) {
                    $where['goods_id'] = $item['goods_id'];

                    // 金额
                    $res = ArticleSellDataModel::find()->select('sum(money) as money')->where($where)->first();
                    $item['money'] = $res['money'] ?? 0;
                    unset($res);

                    // 人数
                    $res = ArticleSellDataModel::find()->select('id')->where($where)->groupBy('member_id')->get();
                    $item['member_count'] = count($res);
                }
            },
            'asArray' => true,
            'pager' => $type == ArticleSellDataConstant::TYPE_COUPON,
        ]);

    }

    /**
     * 保存销售数据
     * @param int $memberId
     * @param int $type
     * @param int $articleId
     * @param int $indexId
     * @param int $couponId
     * @return array|bool
     * @throws ArticleException
     * @throws Exception
     * @author yuning
     */
    public static function saveSellData(int $memberId = 0, int $type = 0, int $articleId = 0, int $indexId = 0, int $couponId = 0)
    {
        // 获取文章
        $article = ArticleModel::getModel($articleId, 'id,goods_ids,coupon_ids', false)->toArray();
        if (!$article) {
            return error(ArticleException::getMessages(ArticleException::SAVE_SELL_DATA_ARTICLE_ERROR), ArticleException::SAVE_SELL_DATA_ARTICLE_ERROR);
        }

        if ($type == ArticleSellDataConstant::TYPE_COUPON) {
            // 优惠券, 直接保存信息

            // 获取文章的coupon_ids
            if (empty($article['coupon_ids'])) {
                return true;
            }

            // 验证是否存在文章的coupon_ids中
            $articleCouponIds = explode(',', $article['coupon_ids']);
            if (!in_array($couponId, $articleCouponIds)) {
                return true;
            }

            $data = [
                'article_id' => $articleId,
                'member_id' => $memberId,
                'type' => ArticleSellDataConstant::TYPE_COUPON,
                'coupon_member_id' => $indexId,
            ];

            // 保存
            $ArticleSellDataModel = new ArticleSellDataModel();
            $ArticleSellDataModel->setAttributes($data);

            //如果保存失败 则抛出异常
            if (!$ArticleSellDataModel->save()) {
                return error('销售数据保存失败');
            }
        } elseif ($type == ArticleSellDataConstant::TYPE_GOODS) {
            // 商品, 获取订单商品,保存信息

            // 获取文章的goods_ids
            if (empty($article['goods_ids'])) {
                return true;
            }

            $articleGoodsIds = explode(',', $article['goods_ids']);

            // 获取订单下商品
            $orderGoods = OrderGoodsModel::find()->where(['member_id' => $memberId, 'order_id' => $indexId])->select('goods_id,price')->get();
            $orderGoodsIds = $orderGoods ? array_column($orderGoods, 'goods_id') : [];


            // 交集数据
            $goodsIds = array_intersect($articleGoodsIds, $orderGoodsIds);
            if (!empty($goodsIds)) {
                // 获取金额, 保存数据
                $orderGoods = array_column($orderGoods, null, 'goods_id');
                $data = [];
                // 生成数据
                foreach ($goodsIds as $goodsId) {
                    $money = $orderGoods[$goodsId]['price'];

                    $one = [
                        'article_id' => $articleId,
                        'member_id' => $memberId,
                        'type' => ArticleSellDataConstant::TYPE_GOODS,
                        'goods_id' => $goodsId,
                        'order_id' => $indexId,
                        'money' => $money,
                    ];
                    $data[] = $one;
                }

                // 保存
                if (!empty($data) && !ArticleSellDataModel::batchInsert(array_keys(current($data)), $data)) {
                    return error('销售数据保存失败');
                }
            }
        }

        return true;
    }
}