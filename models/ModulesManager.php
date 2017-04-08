<?php

namespace asb\yii2\modules\modmgr_1_161205\models;

use asb\yii2\common_2_170212\base\ModulesManagerInterface;
use asb\yii2\common_2_170212\web\RoutesBuilder;

use Yii;
use yii\helpers\ArrayHelper;

class ModulesManager extends Modmgr implements ModulesManagerInterface
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->getDynModulesCache();
    }

    /** Modules info cache */
    protected static $_dynModulesCache = [];
    protected static $_dynModulesIdsTrans = [];
    protected static $_dynModulesTransToIds = [];
    /**
     * Get module's uniqueId from it's id in database table.
     * @param integer $id
     * @return string
     */
    public static function uniqueIdFromDbId($id)
    {
        //if (empty(static::$_dynModulesCache)) Yii::createObject(static::className());//var_dump((static::$_dynModulesCache));exit;
        foreach (static::$_dynModulesCache as $muid => $info) {
            if ($id == $info['id']) {
                return $muid;
            }
        }
        return ''; // means application
    }
    /**
     * Get module's numbered uniqueId from it's id in database table.
     * @param integer $id
     * @return string in format '.../{NNN}/...'
     */
    public static function numberedIdFromDbId($id)
    {//echo __METHOD__."($id)";
        foreach (static::$_dynModulesCache as $moduleInfo) {
            if ($moduleInfo['id'] == $id) {
                return $moduleInfo['module_uid_expanded'];
            }
        }
        return ''; // means application
    }
    
    /**
     * Convert module's uniqueId to form used in Modules manager, for example 'sys/user/{NNN}'.
     * @param string $mid
     * @return string
     */
    public static function tonumberModuleId($mid)
    {
        if (!empty(static::$_dynModulesTransToIds[$mid])) {
            $mid = static::$_dynModulesTransToIds[$mid];
        }
        return $mid;
    }
    /**
     * Convert module's uniqueId to form used in Modules manager, for example 'sys/user/{NNN}'.
     * @param string $muid
     * @return string
     */
    public static function tonumberModuleUniqueId($muid)
    {
        if (!empty(static::$_dynModulesCache[$muid]['module_uid'])) {//echo __METHOD__."($uid)";var_dump(static::$_dynModulesCache[$uid]);exit;
            $muid = static::$_dynModulesCache[$muid]['module_uid'];
        }
        return $muid;
    }
    /**
     * Convert module's uniqueId from form used in Modules manager,
     * for example 'sys/user/{NNN}' => 'sys/user/group'.
     * @param string $muid
     * @return string
     */
    public static function fromnumberModuleUniqueId($muid)
    {
        $muid = strtr($muid, static::$_dynModulesIdsTrans);
        return $muid;
    }
    /**
     * Get dynamicly attached modules info with translation field parent_uid.
     * Field parent_uid contain substrings such as '{ID}' for dynamicly attached modules,
     * for example 'sys/user/{NNN}' will translate to 'sys/user/group'
     * where 'group' is moduleId for dynamicly attached module with id = NNN.
     * @return array [module's unique id => modules manager data]
     */
    protected function getDynModulesCache()
    {
        if (empty(static::$_dynModulesCache)) {
            $list = $this::find()->select(['id', 'module_id', 'parent_uid', 'is_active'])->asArray()->all();//var_dump($list);exit;
            foreach ($list as $item) {
                static::$_dynModulesIdsTrans['{' . $item['id'] . '}'] = $item['module_id'];
                static::$_dynModulesTransToIds[$item['module_id']] = '{' . $item['id'] . '}';
            }//var_dump(static::$_dynModulesIdsTrans);var_dump(static::$_dynModulesTransToIds);exit;
            reset($list);
            $cache = [];
            foreach ($list as $item) {
                $moduleUid = static::$_dynModulesTransToIds[$item['module_id']];
                $moduleUid = (empty($item['parent_uid']) ? '' : ($item['parent_uid'] . '/') ) . $moduleUid;
                $item['module_uid'] = $moduleUid;

                $item['parentUniqueId'] = $item['parent_uid'];
/*
                $numParentUid = $item['parent_uid'];
                $numParentUid = static::expandNumberMuid($numParentUid);//??
                $parentUniqueId = static::fromnumberModuleUniqueId($numParentUid);
                $item['parentUniqueId'] = $parentUniqueId;
*/
                $moduleUniqueId = (empty($parentUniqueId) ? '' : ($parentUniqueId . '/') ) . $item['module_id'];
                $item['moduleUniqueId'] = $moduleUniqueId;

                //$cache[$moduleUniqueId] = $item;
                $cache['{' . $item['id'] . '}'] = $item;
            }//echo __METHOD__;var_dump(array_keys(static::$_dynModulesCache));//var_dump(static::$_dynModulesCache);exit;
            static::$_dynModulesCache = static::fixDynModulesCache($cache);
        }
        return static::$_dynModulesCache;
    }

    /** Expand one-number module's uniqueId into chain */
    protected static function fixDynModulesCache($cache)
    {//echo __METHOD__;var_dump($cache);
        $result = [];
        foreach ($cache as $numMid => $info) {//echo"({$info['id']}){$numMid} => parent='{$info['parent_uid']}'<br>";
            $expParentUid = $parentNumber = $info['parent_uid'];
            while (!empty($parentNumber)) {
                //if (preg_match('/\{\d+\}/', $parentNumber, $matches) == 0) break;//var_dump($matches);exit;
                //$idx = $matches[0];
                //if (empty($cache[$idx]['parent_uid'])) break;
                //else $parentNumber = $cache[$idx]['parent_uid'];

                if (empty($cache[$parentNumber]['parent_uid'])) break;
                else $parentNumber = $cache[$parentNumber]['parent_uid'];
                $expParentUid = $parentNumber . '/' . $expParentUid;
            }//echo"parent'{$info['parent_uid']}' expanded to '{$expParentUid}'<br>";

            $cache[$numMid]['parent_uid_expanded'] = $expParentUid;
            $cache[$numMid]['module_uid_expanded'] = (empty($expParentUid) ? '' : ($expParentUid . '/')) . $numMid;
            $idx = static::fromnumberModuleUniqueId($cache[$numMid]['module_uid_expanded']);
            $result[$idx] = $cache[$numMid];
        }//var_dump($result);exit;
        return $result;
    }

    /** Get name of registered module or false if not refistered */
    public static function registeredModuleName($moduleUid)
    {
        if (!empty(static::$_dynModulesCache[$moduleUid]['name'])) {
            $name = static::$_dynModulesCache[$moduleUid]['name'];//echo __METHOD__."($moduleUid) => {$name}<br>";
            return $name;
        }
        return false;
    }

    /**
     * Get submodules configs for module from modules manager
     * addition to static submodules configs defined in module's $config['modules'].
     * @param \yii\base\Module $module
     * @param boolean $onlyActivated if true show only activated in Modules manager
     * @return array of submodules configs
     */
    public function getSubmodules($module, $onlyActivated = true)
    {//echo __METHOD__."('{$module->uniqueId}','{$onlyActivated}')<br>";
        $result = [];

        $allDynModules = $this->getDynModulesCache();//var_dump($allDynModules);exit;

        if (!isset($module->uniqueId)) { // $module->uniqueId may be ''
            return $result;
        }
        $uniqueIdNumeric = empty($allDynModules[$module->uniqueId])
            ? $module->uniqueId
            : $allDynModules[$module->uniqueId]['module_uid']
            ;//var_dump($uniqueIdNumeric);
        $where = [];
        if ($onlyActivated) {
            $where['is_active'] = true;
        }
        if (empty($uniqueIdNumeric)) {
            $where['parent_uid'] = '';
        } else {
            $where['parent_uid'] = $uniqueIdNumeric;
        }
        $query = $this::find()->where($where);//list($sql, $sqlParams) = Yii::$app->db->getQueryBuilder()->build($query);var_dump($sql);var_dump($sqlParams);
        $list = $query->all();
        
        foreach ($list as $item) {//var_dump($item->attributes);
            $config = [];
            $config = unserialize($item->config_add);//var_dump($item->config_add);var_dump($config);echo'<hr>';
            $config['class'] = $item->module_class;

            $result[$item->module_id] = $config;
        }//var_dump(array_keys($result));//exit;
        return $result;
    }

    /** Correct URL prefixes */
    protected function fixUrlPrefixes($moduleUid, $routesConfig)
    {//echo __METHOD__.'<br>';
        $result = [];
        $module = Yii::$app->getModule($moduleUid);
        foreach ($routesConfig as $type => $config) {
            $urlPrefix = RoutesBuilder::correctUrlPrefix('', $module, $type);
            if (!empty($urlPrefix)) {
                if (is_string($config)) {
                    //$config = $urlPrefix . '/' . $config;
                    $config = $urlPrefix;
                } elseif (is_array($config)) {
                    //$config['urlPrefix'] = empty($config['urlPrefix']) ? $urlPrefix : $urlPrefix . '/' . $config['urlPrefix'];
                    $config['urlPrefix'] = $urlPrefix;
                }
            }
            $result[$type] = $config;
        }//echo __METHOD__;var_dump($result);
        return $result;
    }

    /**
     * @inheritdoc
     * @return array in format [bootstrap class or module's uniqueId => config]
     */
    public function getBootstrapList($parentModuleUid = '')
    {//echo __METHOD__."($parentModuleUid)<br>";
        $where = ['is_active' => true];
        if (!empty($parentModuleUid)) {
            $parentModuleUid = static::tonumberModuleUniqueId($parentModuleUid);
            $where['parent_uid'] = $parentModuleUid;
        }
        $query = $this::find()->where($where);//list($sql, $sqlParams) = Yii::$app->db->getQueryBuilder()->build($query);var_dump($sql);var_dump($sqlParams);
        $list = $query->all();

        $result = [];
        foreach ($list as $item) {//var_dump($item->attributes);
            if (!empty($item->bootstrap) && is_string($item->bootstrap)) {
                $config = [];

                $configAdd = unserialize($item->config_add);
                //$configDefault = @eval("return {$item->config_default}");
                //$configAdd = ArrayHelper::merge($configDefault, $configAdd);

                if (!empty($configAdd['params'])) $config['params'] = $configAdd['params'];

                $config['moduleUid'] = (empty($item->parent_uid) ? '' : ($item->parent_uid . '/')) . $item->module_id;
                $config['moduleUid'] = static::fromnumberModuleUniqueId($config['moduleUid']);
                $routesConfig = empty($configAdd['routesConfig']) ? [] : $configAdd['routesConfig'];
                $config['routesConfig'] = $this->fixUrlPrefixes($config['moduleUid'], $routesConfig);

                if ($item->bootstrap == '+') {
                    $boot = static::fromnumberModuleUniqueId($config['moduleUid']);
                } else {
                    $boot = $item->bootstrap;
                }
                $result[$boot] = $config;
            }
        }//echo __METHOD__;var_dump($result);//exit;
        return $result;
    }

}
