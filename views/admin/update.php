<?php

    /* @var $this yii\web\View */
    /* @var $model asb\yii2\modules\modmgr_1_161205\models\Modmgr */

    use asb\yii2\modules\modmgr_1_161205\assets\AdminAsset;

    use asb\yii2\common_2_170212\base\ModulesManager;

    use yii\helpers\Html;
    use yii\helpers\Url;
    use yii\widgets\ActiveForm;


    $textareaRows = 10;
    $tc = $this->context->module->tcModule;

    $assets = AdminAsset::register($this);

    $this->title = Yii::t($tc, 'Update installed module') . " '{$model->name}' (#{$model->id}) ";
    $this->params['breadcrumbs'][] = ['label' => Yii::t($tc, 'Installed modules'), 'url' => ['index']];
    $this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
    $this->params['breadcrumbs'][] = Yii::t('yii', 'Update');

    $modulesList = ModulesManager::modulesNamesList(Yii::$app, false, true);

    $messageRemove = addslashes(Yii::t($tc, 'Deinstall module {module}?', ['module' => $model->module_class]));

?>
<div class="modmgr-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="modmgr-form">

        <?php $form = ActiveForm::begin(); ?>
        <table class="table">
        <tr>
            <td colspan="4">
                <label><?= Yii::t($tc, 'Module class') ?></label>
                &nbsp;
                <?= $model->module_class ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'name', [
                    'labelOptions' => ['label' => Yii::t($tc, 'Name of module')],
                ])->textInput(['maxlength' => true]) ?>
            </td>
            <td>&nbsp;</td>
            <td colspan="2">
                <?= $form->field($model, 'bootstrap', [
                    'labelOptions' => ['label' => Yii::t($tc, "Bootstrap class or input '+' for use uniqueId as modules bootstrap")],
                ])->textInput(['maxlength' => true]) ?>
            </td>
        </tr>
        <tr>
            <td class="col-xs-4">
                <?= $form->field($model, 'parent_uid', [
                        'labelOptions' => ['label' => Yii::t($tc, 'Module unique ID: contaiter UID ...')],
                    ])->dropDownList($modulesList, [
                        'id' => 'parent-uid',
                        'prompt' => '- ' . Yii::t($tc, 'application') . ' -',
                        'class' => 'form-control select-module-uid',
                        'title' => Yii::t($tc, 'Select module parent'),
                    ]) ?>
            </td>
            <td style="vertical-align:middle;text-align:center;font-weight:bold;width:1%">/</td>
            <td colspan="2">
                <?= $form->field($model, 'module_id',[
                        'labelOptions' => ['label' => Yii::t($tc, '+ module ID')],
                    ])->textInput(['maxlength' => true]) ?>
            </td></tr>
        <tr>
            <td><?= $form->field($model, 'config_text', [
                        'labelOptions' => ['label' => Yii::t($tc, 'Additional module config')],
                    ])->textarea([
                        'rows' => $textareaRows,
                        'title' => Yii::t($tc, 'Added here parameters will overrite default collected config'),
                    ]) ?></td>
            <td>&nbsp;</td>
            <td>
                <label><?= Yii::t($tc, '(full config collected from config files of parents)') ?></label>
                <pre id="config-default" style="max-height:300px"><?= $model->config_default ?></pre>
                <?php
                /*  echo $form->field($model, 'config_default', [
                        'labelOptions' => ['label' => Yii::t($tc, '(full config collected from config files of parents)')],
                    ])->textarea([
                        'id' => 'config-default',
                        'rows' => $textareaRows,
                        'readonly' => true,
                    ])
                */
                ?>
            </td>
            <td class="input-group-btn text-left">
                <?= Html::button('', [
                        'id' => 'rebuild-config-default',
                        'class' => 'btn glyphicon glyphicon-refresh',
                        'title' => Yii::t($tc, 'Press to rebuild collected config (after change config files)'),
                    ]) ?>
                <?= Html::img("{$assets->baseUrl}/img/load.gif", [
                        'id ' => 'config-load',
                        'class' => 'collapse', //'style' => 'display:none',
                    ]) ?>
            </td>

        </tr>

        <tr><td colspan="4">
            <div class="form-group">
                <?= Html::submitButton(Yii::t($tc, 'Update'), ['class' => 'btn btn-primary']) ?>
                <?= Html::buttonInput(Yii::t($tc, 'Remove'), [
                      'id' => 'button-remove',
                      'title' => $messageRemove,
                      'class' => 'btn pull-right btn-danger',
                    ]) ?>
            </div>
        </td></tr>

        </table>
        <?php ActiveForm::end(); ?>

    </div>

</div>

<?php
    $actionChangeDefConfig = Url::toRoute(['rebuild-default-config', 'id' => $model->id]);
    $actionRemove = Url::toRoute(['delete', 'id' => $model->id]);

    $this->registerJs("
        jQuery('#rebuild-config-default').bind('click', function() {
            jQuery('#rebuild-config-default').hide();
            jQuery('#config-load').show();
            jQuery('#config-default').load('{$actionChangeDefConfig}', {}, function() {
                jQuery('#config-load').hide();
                jQuery('#rebuild-config-default').show();
            });
        });
        jQuery('#button-remove').bind('click', function() {
            if (confirm('{$messageRemove}')) {
                this.form.action = '{$actionRemove}';
                this.form.submit();
            }
        });
    ");
?>
