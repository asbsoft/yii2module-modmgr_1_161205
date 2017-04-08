<?php

use asb\yii2\modules\modmgr_1_161205\assets\AdminAsset;
use asb\yii2\modules\modmgr_1_161205\models\Modmgr;
use asb\yii2\modules\modmgr_1_161205\models\ModulesManager;

use asb\yii2\common_2_170212\widgets\Alert;

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

    $gridViewId = 'modmgr-grid';
    $gridHtmlClass = 'modmgr-list-grid';

    $tc = $this->context->module->tcModule;
    $formName = basename(Modmgr::className());

    $this->title = Yii::t($tc, 'Installed modules');

    $this->params['breadcrumbs'][] = $this->title;

    $assets = AdminAsset::register($this);

    $pager = $dataProvider->getPagination();
    $paramSort = Yii::$app->request->get('sort', '');
    $paramSearch = Yii::$app->request->get($formName, []);
    foreach ($paramSearch as $key => $val) if (empty($val)) unset($paramSearch[$key]);
    $this->params['buttonOptions'] = ['data' => [
        'search' => $paramSearch,
        'sort' => $paramSort,
        'page' => $pager->page + 1,
    ]];

    Yii::$app->i18n->translations[$tc]->forceTranslation = true;

?>
<div class="modmgr-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= Alert::widget(); ?>

    <div class="pull-right small"><?= Yii::t($tc, '[INSTRUCTION]') ?></div>

    <p>
        <?= Html::a(Yii::t($tc, 'Install new module'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'id' => $gridViewId,
        'options' => ['class' => $gridHtmlClass],
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'module_id',
                'value' => function($model, $key, $index, $column) {
                    $muid = ModulesManager::uniqueIdFromDbId($model->id);
                    return $muid;
                },
            ],
            'name',
          /*
            [
                'attribute' => 'parent_uid',
                'value' => function ($model, $key, $index, $column) use ($tc) {
                    if (empty($model->parent_uid)) {
                        return '(' . Yii::t($tc, 'application') . ')';
                    } else {
                        return ModulesManager::fromnumberModuleUniqueId($model->parent_uid);
                    }
                },
            ],
          /**/
            //'module_id',
            'module_class',
            'bootstrap',
            // 'config_default:ntext',
            // 'config:ntext',
            // 'create_at',
            // 'update_at',

            // 'is_active',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '<div class="text-nowrap">{change-active} {view} {update} {add-submodule} {delete}</div>',
                'header' => Yii::t($tc, 'Actions'),
                'buttons' => [
                    'change-active' => function($url, $model, $key) use($tc, $pager, $formName) {
/*
                        $icon  = $model->is_active
                                 ? ($model->hasUnactiveContainer() ? 'ban-circle' : 'ok' )
                                 : 'minus';
                        $act   = $model->is_active ? Yii::t($tc, 'deactivate') : Yii::t($tc, 'activate');
                        $title = $model->is_active ? Yii::t($tc, 'Change to unactive') : Yii::t($tc, 'Change to active');
*/
                        if (!$model->is_active) {
                            $title = Yii::t($tc, 'Unactive') . '. ' . Yii::t($tc, 'Change to active');
                            $icon  = 'minus';
                            $act   = Yii::t($tc, 'activate');
                        } elseif ($model->hasUnactiveContainer()) {
                            $title = Yii::t($tc, 'Active but locked by parent') . '. ' . Yii::t($tc, 'Change to unactive');
                            $icon  = 'ban-circle';
                            $act   = Yii::t($tc, 'deactivate');
                        } else {
                            $title = Yii::t($tc, 'Active') . '. ' . Yii::t($tc, 'Change to unactive');
                            $icon  = 'ok';
                            $act   = Yii::t($tc, 'deactivate');
                        }
                        $options = array_merge([
                            'title' => $title,
                            'aria-label' => $title,
                            'data-pjax' => '0',
                            'data-confirm' => Yii::t($tc, "Are you sure you want to {action} this item #{id}?",
                                                  ['action' => $act, 'id' => $model->id]
                                              ),
                        ], $this->params['buttonOptions']);
                        $url = Url::to(['change-active'
                          , 'id' => $model->id
                          , 'sort' => $this->params['buttonOptions']['data']['sort']
                          , $formName => $this->params['buttonOptions']['data']['search']
                          , 'page' => $pager->page + 1
                        ]);//var_dump($url);
                        return Html::a("<span class='glyphicon glyphicon-{$icon}'></span>", $url, $options);
                    },
                    'add-submodule' => function($url, $model, $key) use($tc, $pager, $formName) {
                        $title = Yii::t($tc, 'Add submodule');
                        $options = array_merge([
                            'title' => $title,
                            'aria-label' => $title,
                            'data-pjax' => '0',
                        ], $this->params['buttonOptions']);
                        $url = Url::to(['create'
                          , 'parent' => $model->id
                          , 'sort' => $this->params['buttonOptions']['data']['sort']
                          , $formName => $this->params['buttonOptions']['data']['search']
                          , 'page' => $pager->page + 1
                        ]);//var_dump($url);
                        return Html::a("<span class='glyphicon glyphicon-plus'></span>", $url, $options);
                    },
                ],
            ],
            'id',
        ],
    ]); ?>

</div>

<?php
    $currentId = Yii::$app->request->get('id', '0');
    $this->registerJs("
        var currentId = '{$currentId}';
        jQuery('.{$gridHtmlClass} table tr').each(function(index) {
            var elem = jQuery(this);
            var id = elem.attr('data-key');
            if (id == currentId) elem.css({'background-color': '#DFD'});
        });
    ");
?>
