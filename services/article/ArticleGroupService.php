<?php

namespace shopstar\services\article;


use shopstar\constants\article\ArticleConstant;
use shopstar\constants\article\ArticleGroupConstant;
use shopstar\constants\article\ArticleLogConstant;
use shopstar\exceptions\article\ArticleGroupException;
use shopstar\models\article\ArticleGroupModel;
use shopstar\models\article\ArticleModel;
use shopstar\models\log\LogModel;
use yii\db\ActiveRecord;
use yii\helpers\Json;

class ArticleGroupService extends ArticleBaseService
{
    /**
     * 保存
     * @param string $data
     * @return bool
     * @throws ArticleGroupException
     * @author yuning
     */
    public function save(string $data = ''): bool
    {
        $data = Json::decode($data) ?? [];
        if (empty($data)) {
            throw new ArticleGroupException(ArticleGroupException::SAVE_PARAMS_ERROR);
        }

        // 检测并 创建model
        foreach ($data as $group) {

            if (($group['id']) && $group['id'] > 0) {
                $where['id'] = $group['id'];
                $oneModel = ArticleGroupModel::find()->where($where)->one();
                if (!$oneModel) {
                    throw new ArticleGroupException(ArticleGroupException::FIND_ERROR);
                }
                $logConst = ArticleLogConstant::ARTICLE_EDIT_GROUP;
            } else {
                $oneModel = new ArticleGroupModel();
                unset($group['id']);
                $logConst = ArticleLogConstant::ARTICLE_ADD_GROUP;
            }

            // name重复性检测
            $exist = ArticleGroupModel::find()->where(['name' => $group['name']])->select('id,name')->first();
            if ($exist && $exist['id'] != $oneModel['id']) {
                throw new ArticleGroupException(ArticleGroupException::SAVE_PARAMS_NAME_EXIST);
            }
            $oneModel->setAttributes($group);
            if (!$oneModel->save()) {
                throw new ArticleGroupException(ArticleGroupException::SAVE_ERROR);
            }

            // 日志
            $logPrimary = [
                'id' => $oneModel->id,
                'name' => $oneModel->name,
                'display_order' => $oneModel->display_order,
                'status' => $oneModel->status ? '显示' : '隐藏',
            ];
            LogModel::write(
                $this->userId,
                $logConst,
                ArticleLogConstant::getText($logConst),
                $oneModel->id,
                [
                    'log_data' => $oneModel->attributes,
                    'log_primary' => $oneModel->getLogAttributeRemark($logPrimary),
                    'dirty_identify_code' => [
                        ArticleLogConstant::ARTICLE_EDIT_GROUP,
                        ArticleLogConstant::ARTICLE_ADD_GROUP,
                    ]
                ]
            );
        }

        return true;
    }

    /**
     * 分组列表
     * @param string $field 查询字段
     * @param string $status 状态 0:隐藏 1: 显示
     * @param false $getArticleCount true: 获取分组下文章
     * @return array|int|string|ActiveRecord[]
     * @author yuning
     */
    public function getList(string $field = 'id,name,display_order,status', $status = ArticleGroupConstant::GROUP_STATUS_SHOW, bool $getArticleCount = false)
    {
        // 查询条件
        $param['select'] = $field;
        $param['orderBy'] = [
            'display_order' => SORT_DESC,
            'id' => SORT_DESC,
        ];

        // 不是查询全部, 添加条件
        if ($status != '' && $status != null) {
            $param['where']['status'] = $status;
        }


        $data = ArticleGroupModel::getColl($param, [
            'pager' => false,
        ]);

        // 获取分组下文章数量
        if ($getArticleCount) {
            $where = [
                'is_deleted' => ArticleConstant::ARTICLE_NOT_DELETE,
            ];
            foreach ($data['list'] as &$item) {
                $where['group_id'] = $item['id'];
                $item['article_count'] = ArticleModel::where($where)->count('id');
            }
        }

        return $data;
    }
}