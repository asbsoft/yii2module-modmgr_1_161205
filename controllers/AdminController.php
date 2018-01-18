<?php

namespace asb\yii2\modules\modmgr_1_161205\controllers;

use asb\yii2\common_2_170212\controllers\BaseAdminMulangController;

use asb\yii2\modules\modmgr_1_161205\models\CreateModule;
use asb\yii2\modules\modmgr_1_161205\models\Modmgr;
use asb\yii2\modules\modmgr_1_161205\models\ModulesManager;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

/**
 * AdminController implements the CRUD actions for Modmgr model.
 */
class AdminController extends BaseAdminMulangController
{
    public $pageSize = 20; // default, can change in config
    public static $tcCommon = '';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        static::$tcCommon = $this->tcModule;

        if (!empty($this->module->params['pageSizeAdmin']) && intval($this->module->params['pageSizeAdmin']) > 0) {
            $this->pageSize = intval($this->module->params['pageSizeAdmin']);
        }
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ]);
    }

    /**
     * Lists all Modmgr models.
     * @return mixed
     */
    public function actionIndex($page = 1)
    {
        $sort = [];
        $params = Yii::$app->request->queryParams;
        if (empty($params['sort'])) {
            $sort['defaultOrder'] = ['parent_uid' => SORT_ASC, 'module_id' => SORT_ASC];
        } else {
            if($params['sort'] == 'module_id') {
                $sort['defaultOrder'] = ['parent_uid' => SORT_ASC, 'module_id' => SORT_ASC];
                unset($params['sort']);
            } else if ($params['sort'] == '-module_id') {
                $sort['defaultOrder'] = ['parent_uid' => SORT_DESC, 'module_id' => SORT_DESC];
                unset($params['sort']);
            }
            Yii::$app->request->setQueryParams($params);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => Modmgr::find(),
            'sort' => $sort,
        ]);

        $pager = $dataProvider->getPagination();
        $pager->pageSize = $this->pageSize;
        $pager->page = $page - 1; //! from 0

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Modmgr model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Rebuild default module config.
     * Use in ajax request.
     * @param integer $id
     * @return string
     */
    public function actionRebuildDefaultConfig($id)
    {
        $model = $this->findModel($id);
        $configDefault = $model->buildDefaultConfig($model->module_class);
        $model->config_default = var_export($configDefault, true);
        $model->save(true, ['config_default']);
        return $model->config_default;
    }

    /**
     * Creates a new Modmgr model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($parent = 0)
    {
        $installModel = new CreateModule(['tc' => $this->module->tcModule]);

        $post = Yii::$app->request->post();
        if (empty($post)) {
            $installModel->loadDefaultValues();
            $installModel->parentUid = ModulesManager::numberedIdFromDbId($parent);
        } else {
            $installModel->load($post);
            $installModel->moduleClassFile = UploadedFile::getInstance($installModel, 'moduleClassFile');
            $result = $installModel->validate();
            if ($result) {
                //$modelManager = new Modmgr(['tc' => $this->module->tcModule]);
                $modelManager = new Modmgr();
                $modelManager->loadData($installModel);
                if (!$modelManager->hasErrors() && $modelManager->save()) {
                    //return $this->redirect(['view', 'id' => $modelManager->id]);
                    return $this->redirect(['update', 'id' => $modelManager->id]);
                }

                // merge errors to $installModel
                foreach ($modelManager->errors as $attribute => $errors) {
                    if ($attribute == 'module_id') {
                        foreach ($errors as $error) $installModel->addError('moduleId', $error);
                    } elseif ($attribute == 'module_class') {
                        foreach ($errors as $error) $installModel->addError('moduleClassFile', $error);
                    } else { //?? need error message not attached to any field
                        foreach ($errors as $error) $installModel->addError('moduleClassFile', $error);
                    }
                }
            }
        }
        return $this->render('create', [
            'model' => $installModel,
        ]);
    }

    /**
     * Updates an existing Modmgr model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $oldSubmodulesParentUid = $model->parent_uid . '/{' . $model->id . '}';

        $post = Yii::$app->request->post();
        $loaded = $model->load($post);
        if ($loaded && $model->save()) {
            $newSubmodulesParentUid = $model->parent_uid . '/{' . $model->id . '}';
            $model->correctParents($oldSubmodulesParentUid, $newSubmodulesParentUid);

            //return $this->redirect(['view', 'id' => $model->id]);
            return $this->redirect(['index', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Modmgr model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        if ($model->is_active == false) {
            $model->delete();
            Yii::$app->session->setFlash('success', Yii::t($this->tcModule,"Module has been deleted"));
        } else {
            Yii::$app->session->setFlash('error', Yii::t($this->tcModule,"Can't delete active module"));
        }

        $params = Yii::$app->request->getQueryParams();
        $params['id'] = $model->id;
        $params[] = 'index';
        return $this->redirect($params);
    }

    /**
     * Change is_active model attribute.
     * @param integer $id
     * @return mixed
     */
    public function actionChangeActive($id)
    {
        $model = $this->findModel($id);
        $model->is_active = $model->is_active ? false: true;
        $model->save();
        Yii::$app->session->setFlash('success', Yii::t($this->tcModule,"Module's activity has been changed"));

        $params = Yii::$app->request->getQueryParams();
        $params['id'] = $model->id;
        $params[] = 'index';
        return $this->redirect($params);
    }

    /**
     * Finds the Modmgr model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Modmgr the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Modmgr::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
