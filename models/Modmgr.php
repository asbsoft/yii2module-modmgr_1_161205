<?php

namespace asb\yii2\modules\modmgr_1_161205\models;

use asb\yii2\modules\modmgr_1_161205\controllers\AdminController;

use asb\yii2\common_2_170212\base\ModulesManager as BaseModulesManager;
use asb\yii2\common_2_170212\base\UniModule;
use asb\yii2\common_2_170212\models\DataModel;
use asb\yii2\common_2_170212\helpers\ConfigsBuilder;

use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\base\BootstrapInterface;

use Exception;
use ReflectionClass;

/**
 * This is the model class for table "{{%modmgr}}".
 *
 * @property integer $id
 * @property string $module_id
 * @property string $parent_uid is string (not integer) to able to combine static and dynamic submodules
 * @property string $name
 * @property integer $is_active
 * @property string $module_class
 * @property string $config_default
 * @property string $config_add
 * @property string $create_at
 * @property string $update_at
 */
class Modmgr extends DataModel
{
    //const TABLE_NAME = 'modmgr'; // use in DataModel::tableName() // deprecated
    
    public $config_text;
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        //if (empty($this->tc))
        $this->tc = AdminController::$tcCommon;

    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_active'], 'boolean'],
            [['create_at'], 'required'],
            [['create_at', 'update_at'], 'safe'],
            [['module_id', 'parent_uid', 'name', 'module_class'], 'string', 'max' => 255],
            [['config_default', 'config_add', 'bootstrap'], 'string'],
            ['module_class', 'unique',
                'message' => Yii::t($this->tc, 'module class "{value}" already installed'),
            ],
            ['module_id', 'unique',
                'targetAttribute' => ['parent_uid', 'module_id'],
                'message' => Yii::t($this->tc, 'such module ID (alias) already installed in this module-container')
            ],
            ['module_id', 'match',
                //'pattern' => '|^[^/]+$|',
                //'message' => Yii::t($this->tc, "you can't use '/' in module alias")
                'pattern' => '/^[a-z0-9_\-\.]+$/i',
                'message' => Yii::t($this->tc, 'Only latin letters, digits, hyphen, underline and point')
            ],
            ['module_id', // can't be equal to static modules
              function ($attribute, $params) {
/*
                  $staticModulesIdsList = array_keys(Yii::$app->modules);
                  if (in_array($this->module_id, $staticModulesIdsList)) {
                      $this->addError($attribute, Yii::t($this->tc, 'already exists static module with same ID: ') . $this->module_id);
                  }
*/
                  //$loadedModulesUidsList = array_keys(ModulesManager::$_dynModulesCache);
                  $loadedModulesUidsList = array_keys(BaseModulesManager::modulesNamesList(null, false));
                  $uid = (empty($this->parent_uid) ? '' : ($this->parent_uid . '/')) . $this->module_id;
                  $changed = $this->isAttributeChanged('module_id') || $this->isAttributeChanged('parent_uid');
                  if ($changed && in_array($uid, $loadedModulesUidsList)) {
                      $this->addError($attribute, Yii::t($this->tc, 'already exists module with same uniqueId: ') . $uid);
                  }
              }
            ],
            ['parent_uid', //!! prevent modules loop
              function ($attribute, $params) {
                  $numModId = ModulesManager::tonumberModuleId($this->module_id);
                  if (strpos($this->parent_uid, $numModId) !== false) {
                      $this->addError($attribute, Yii::t($this->tc, "can't set itself as module-container"));
                  }
              }
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'             => 'ID',
            'module_id'      => Yii::t($this->tc, 'Module ID (alias)'),
            'parent_uid'     => Yii::t($this->tc, 'Module-container'),
            'name'           => Yii::t($this->tc, 'Name'),
            'is_active'      => Yii::t($this->tc, 'Active?'),
            'module_class'   => Yii::t($this->tc, 'Module class'),
            'bootstrap'      => Yii::t($this->tc, 'Bootstrap'),
            'config_default' => Yii::t($this->tc, 'Default config'),
          //'config_add'     => Yii::t($this->tc, 'Config'),
            'config_text'    => Yii::t($this->tc, 'Config'),
            'create_at'      => Yii::t($this->tc, 'Create at'),
            'update_at'      => Yii::t($this->tc, 'Update at'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function load($data, $formName = null)
    {
        $scope = $formName === null ? $this->formName() : $formName;
        if (parent::load($data, $formName)) {
            if (isset($data[$scope]['config_text'])) {
                $this->config_text = $data[$scope]['config_text'];
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {

        if (!parent::beforeSave($insert)) {
            return false;
        } else {
            // check additional config
            try {
                //?? how todo error processing
                $config = @eval("return {$this->config_text};");
                if ($config !== false) $this->config_add = serialize($config);
            } catch(Exception $e) {
                $this->addError('config_add', '(eval config_text) ' . $e->getMessage());
                return false;
            }

            // setup empty $this->bootstrap
            if ($insert && empty($this->bootstrap)) {
                // set bootstrap property
                $rc = new ReflectionClass($this->module_class);
                $bootstrapFile = dirname($rc->getFileName()) . '/Bootstrap.php';
                if (is_file($bootstrapFile)) {
                    $this->bootstrap = $bootstrapFile; // exists file Bootstrap.php
                } elseif ($rc->implementsInterface('yii\base\BootstrapInterface')) {
                    $this->bootstrap = '+'; // module class implements BootstrapInterface
                }

                try { //?? how to catch yii\base\ErrorException Missing argument
                    if (!empty($config)) {
                        $configDefault = @eval("return {$this->config_default};");
                        $config = ArrayHelper::merge($configDefault, $config);
                        $config['class'] = $this->module_class;
                        $id = $this->module_id;
                        if (!empty($this->parent_uid)) $id = $this->parent_uid . '/' . $id;
                        $config['id'] = $id;
                    }
                } catch(Exception $e) {
                    $this->addError('config_default', '(eval config_default) ' . $e->getMessage());
                    return false;
                }
            }

            // check bootstrap class
            if (!empty($this->bootstrap) && $this->bootstrap != '+') {
                try { //?? how to catch yii\base\ErrorException Class '...' not found
                    $tmp = new $this->bootstrap;
                } catch(Exception $e) {
                    $this->addError('bootstrap', '(new bootstrap) ' . $e->getMessage());
                    return false;
                }
            }

            if (empty($this->bootstrap)) $this->bootstrap = null; // to proper show in yii\i18n\Formatter->nullDisplay
            
            return true;
        }
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();

        $this->config_text = var_export(unserialize($this->config_add),true);
    }

    /** Load data from install model */
    public function loadData($installModel)
    {

        $this->is_active = false;
        $this->create_at = new Expression('NOW()'); // server time
        $this->module_id = $installModel->moduleId;
        $this->parent_uid = $installModel->parentUid;

        $fileBody = file_get_contents($installModel->moduleClassFile->tempName);

        //$regexp = "/namespace[ \t]+([A-Za-z0-9\\_]+);/"; //??
        $regexp = "/namespace[ \t]+([^;]+);/";
        $n = preg_match($regexp, $fileBody, $found);
        if ($n < 1) {
            $this->addError('module_class', $error = Yii::t($this->tc, "Can't find namespace in module class file"));
            return;
        } else {
            $this->module_class = $found[1] . "\\" . basename($installModel->moduleClassFile->name, '.php');
            $cmd = "return {$this->module_class}::className();";
            if(function_exists('runkit_lint') && !runkit_lint($cmd)) {
                $this->addError('module_class', $error = Yii::t($this->tc, 'Module class file has errors'));
                return;
            }
//*??
            try {
                $className = @eval($cmd);
                if ($className != $this->module_class) {
                    $this->addError('module_class', $error = Yii::t($this->tc, 'Bad module class file'));
                    return;
                }
            } catch (Exception $e) { // not catch syntax error
                $this->addError('module_class', $error = Yii::t($this->tc, $e->getMessage()));
                return;
            }
/**/
            $configDefault = $this->buildDefaultConfig($this->module_class);
            $this->config_default = var_export($configDefault, true);

            $this->name = empty($configDefault['params']['label'])
                ? ('Module ' . dirname($this->module_class))
                : $configDefault['params']['label'];

            $config = [];
            if (isset($configDefault['params'])) {
                $config['params'] = $configDefault['params'];
            }
            if (isset($configDefault['routesConfig'])) {
                $config['routesConfig'] = $configDefault['routesConfig'];
            } else {
                //$config['routesConfig'] = []; //!! error if not-UniModule
            }
            $this->config_text = var_export($config, true);

        }
    }

    public function buildDefaultConfig($moduleClass)
    {
        $module = UniModule::getModuleByClassname($moduleClass, true); // load as anonimous
        $configDefault = ConfigsBuilder::getConfig($module);

        $params = ConfigsBuilder::getConfig($module, 'params');
        if (!empty($configDefault['params'])) {
            $params = ArrayHelper::merge($configDefault['params'], $params);
        }
        $configDefault['params'] = $params;

        return $configDefault;
    }

    /** db-table-id => object */
    protected static $_modulesData = [];
    /** Check if active dynamicly attached module */
    protected static function isActive($dbId)
    {
        if (!isset(static::$_modulesData[$dbId])) {
            $result = static::findOne($dbId);
            static::$_modulesData[$dbId] = $result;
        }
        if (empty(static::$_modulesData[$dbId])) {
            return false;
        } else {
            $isActive = static::$_modulesData[$dbId]->is_active;
            return $isActive;
        }
    }
    /** Check if this module has unactive container */
    public function hasUnactiveContainer()
    {
        $parent = $this->parent_uid;
        if (preg_match_all('|\{(\d+)\}|', $parent, $matches)) {
            if (!empty($matches[1])) {
                foreach($matches[1] as $nextDbId) {
                    if (!static::isActive($nextDbId)) return true;
                }
            }
        }
        return false;
    }

    /** Correct parent_uid chain if module-container change it's own name or container */
    public function correctParents($oldSubmodulesParentUid, $newSubmodulesParentUid)
    {
        if ($oldSubmodulesParentUid === $newSubmodulesParentUid) return;

        $query = static::find()->where(['like', 'parent_uid', "{$oldSubmodulesParentUid}%", false]);
        $list = $query->all();
        foreach ($list as $item) {
            if (0 === strpos($item->parent_uid, $oldSubmodulesParentUid)) {
                $item->parent_uid = $newSubmodulesParentUid
                    . mb_substr($item->parent_uid, mb_strlen($oldSubmodulesParentUid));
                $item->save();
            }
        }
    }

}
