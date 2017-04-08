<?php

use asb\yii2\modules\modmgr_1_161205\assets\AdminAsset;

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model asb\yii2\modules\modmgr_1_161205\models\Modmgr */

    $assets = AdminAsset::register($this);

    $tc = $this->context->module->tcModule;

    $this->title = Yii::t($tc, "Module '{name}' (#{id})", ['name' => $model->name, 'id' => $model->id]);
    $this->params['breadcrumbs'][] = ['label' => Yii::t($tc, 'Installed modules'), 'url' => ['index']];
    $this->params['breadcrumbs'][] = $this->title;

?>
<div class="modmgr-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t($tc, 'To list'), ['index', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
        <?= Html::a(Yii::t($tc, 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t($tc, 'Delete'), ['delete', 'id' => $model->id], ['class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t($tc, 'Are you sure you want to delete item #{id}?', ['id' => $model->id]),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        //'template' => '<tr><th>{label}</th><td>{value}</td></tr>', // default
        'template' => function ($attribute, $index, $widget) {//var_dump($attribute);exit;
            if ($attribute['attribute'] == 'config_add') $classes = "pre pre-scrollable";
            else $classes = '';
            $template = "<tr><th>{label}</th><td class=\"{$classes}\">{value}</td></tr>";
            return strtr($template, [
                '{label}' => $attribute['label'],
                '{value}' => $widget->formatter->format($attribute['value'], $attribute['format']),
            ]);
        },
        'attributes' => [
            'id',
            'name',

            //'parent_uid',
            [ 'attribute' => 'parent_uid',
              'value' => empty($model->parent_uid) ? ( '(' . Yii::t($tc, 'application') . ')' ) : $model->parent_uid,
            ],

            'module_id',
            'is_active:boolean',
            'module_class',
            'bootstrap',
            
            //'config_default:ntext',
            //'config_add:ntext',
            [ 'attribute' => 'config_add',
              'value' => var_export(unserialize($model->config_add), true),
            ],

            'update_at',
            'create_at',
        ],
    ]) ?>

</div>
