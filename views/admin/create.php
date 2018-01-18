<?php

    /* @var $this yii\web\View */
    /* @var $model asb\yii2\modules\modmgr_1_161205\models\CreateModule */

    use asb\yii2\common_2_170212\base\ModulesManager;

    use yii\helpers\Html;
    use yii\widgets\ActiveForm;


    $tc = $this->context->module->tcModule;

    $this->title = Yii::t($tc, 'Install module');
    $this->params['breadcrumbs'][] = ['label' => Yii::t($tc, 'Installed modules'), 'url' => ['index']];
    $this->params['breadcrumbs'][] = $this->title;

    $modulesList = ModulesManager::modulesNamesList(Yii::$app, false, true);

?>
<div class="modmgr-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="modmgr-form">
        <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>
            <?= $form->field($model, 'moduleClassFile', [
                    'labelOptions' => ['label' => Yii::t($tc, 'Select module class file') . ':'],
                ])->fileInput([
                    'class' => 'form-control',
            ]) ?>
            <br />
            <?= $form->field($model, 'parentUid', [
                    'labelOptions' => ['label' => Yii::t($tc, 'Select module container')],
                ])->dropDownList($modulesList, [
                    'id' => 'parent-uid',
                    'prompt' => '- ' . Yii::t($tc, 'application') . ' -',
                    'class' => 'form-control select-module-uid',
                    'title' => Yii::t($tc, 'Select module parent'),
                ]) ?>
            <br />
            <?= $form->field($model, 'moduleId', [
                    'labelOptions' => ['label' => Yii::t($tc, 'Enter module ID (alias)') . ':'],
                ])->textInput([
                    'maxlength' => true,
                    'class' => 'form-control',
            ]) ?>
            <br />
            <?= Html::submitButton(Yii::t($tc, 'Install'), ['class' => 'btn btn-success']) ?>
        <?php ActiveForm::end(); ?>
    </div>

</div>
