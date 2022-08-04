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

namespace shopstar\admin\pc;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\pc\MenusConstant;
use shopstar\exceptions\pc\PcException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\pc\PcMenusModel;
use Throwable;
use yii\db\StaleObjectException;
use yii\web\Response;

class MenusController extends KdxAdminApiController
{
    /**
     * @var int 菜单类型
     */
    protected int $_menuType;

    /**
     * 检查菜单类型
     * @return void
     * @throws PcException
     * @author 青岛开店星信息技术有限公司
     */
    protected function checkTypes()
    {
        $menuType = RequestHelper::get('type');

        if (!$menuType) {
            $menuType = RequestHelper::post('type');
        }

        $menuTypes = [
            MenusConstant::PC_MENU_TYPE_TOP,
            MenusConstant::PC_MENU_TYPE_BOTTOM,
        ];

        if (!in_array($menuType, $menuTypes)) {
            throw new PcException(PcException::PC_MENU_TYPE_ERROR);
        }

        $this->_menuType = $menuType;
    }

    /**
     * 修改菜单状态
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChangeStatus()
    {
        $id = RequestHelper::postInt('id');
        $status = RequestHelper::postInt('status');

        $homeAdvertise = PcMenusModel::findOne($id);

        if ($homeAdvertise && in_array($status, [0, 1])) {

            $homeAdvertise->status = $status;
            $homeAdvertise->updated_at = DateTimeHelper::now();
            $homeAdvertise->save();
        }

        return $this->success();
    }

    /**
     * 获取菜单列表
     * @return array|int[]|Response
     * @throws PcException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionList()
    {
        $this->checkTypes();
        // 搜索
        $searchs = [
            ['name', 'like', 'name'],
        ];

        $andWhere = [];
        // 创建时间搜索
        $start_time = RequestHelper::get('start_time', '');
        $end_time = RequestHelper::get('end_time', '');

        if (!empty($start_time) && !empty($end_time)) {
            $andWhere[] = ['between', 'created_at', $start_time, $end_time];
        }

        $list = PcMenusModel::getColl([
            'searchs' => $searchs,
            'orderBy' => [
                'sort_order' => SORT_DESC
            ],
            'where' => [
                'type' => $this->_menuType,
            ],
            'andWhere' => $andWhere,
            'select' => [
                'id',
                'name',
                'sort_order',
                'status',
                'created_at',
                'url',
                'img',
            ]
        ]);

        return $this->result($list);
    }

    /**
     * 获取菜单详情
     * @return array|int[]|Response
     * @throws PcException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDetail()
    {
        $id = RequestHelper::getInt('id');
        if (!$id) {
            throw new PcException(PcException::MENUS_ID_EMPTY);
        }

        $one = PcMenusModel::find()->select(['id',
            'name',
            'sort_order',
            'status',
            'url',
            'img'
        ])->where(['id' => $id])->one();

        return $this->result(['data' => $one]);
    }

    /**
     * 添加菜单
     * @return array|int[]|Response
     * @throws PcException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAdd()
    {
        $this->checkTypes();

        // 如果是bottom menus,image必填
        if ($this->_menuType == MenusConstant::PC_MENU_TYPE_BOTTOM) {
            if (!RequestHelper::post('img')) {
                throw new PcException(PcException::PC_MENU_IMG_EMPTY);
            }
        }

        $result = PcMenusModel::easyAdd([
            'attributes' => [
                'created_at' => DateTimeHelper::now(),
                'type' => $this->_menuType,
            ],
        ]);

        return $this->result($result);
    }

    /**
     * 编辑菜单
     * @return array|int[]|Response
     * @throws PcException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit()
    {
        $this->checkTypes();

        // 如果是bottom menus，image必填
        if ($this->_menuType == MenusConstant::PC_MENU_TYPE_BOTTOM) {
            if (!RequestHelper::post('img')) {
                throw new PcException(PcException::PC_MENU_IMG_EMPTY);
            }
        }

        $result = PcMenusModel::easyEdit([
            'attributes' => [
                'created_at' => DateTimeHelper::now(),
                'type' => $this->_menuType,
            ],
        ]);

        return $this->result($result);
    }

    /**
     * 删除菜单
     * @return array|int[]|Response
     * @throws Throwable
     * @throws StaleObjectException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $result = PcMenusModel::easyDelete([]);

        return $this->result($result);
    }
}
