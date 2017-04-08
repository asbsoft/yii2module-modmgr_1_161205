<?php

namespace asb\yii2\modules\modmgr_1_161205;

use asb\yii2\common_2_170212\base\ModulesManager;
use asb\yii2\common_2_170212\base\UniModule;

use Yii;
use yii\base\Module as YiiBaseModule;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\UnknownPropertyException;
use yii\helpers\ArrayHelper;

class Bootstrap implements BootstrapInterface
{
    // !! here are not all routes - only for backend parts of modules - registered from config
    // frontend routes will appent in sitetree module according to site structure from db
    public function bootstrap($app)
    {//echo __METHOD__.'<br>';//var_dump($app->modules);exit;
        $app->getModule('modmgr');//var_dump(array_keys(Yii::$app->loadedModules));exit;//var_dump(Yii::$app->loadedModules);exit;

        // add to application modules registered in this module
        $modules = $app->modules;//var_dump(array_keys($modules));//exit;
        $activeModules = ModulesManager::submodules($app);//var_dump(array_keys($activeModules));
        $app->modules = ArrayHelper::merge($modules, $activeModules);//var_dump(array_keys($app->modules));

        // Run additionalmodules bootstraps here.
        // (!!) Unfortunately not all modules will bootstrap here
        // because in modules-containers chain may be dynamicly addedmodules
        $bootList = ModulesManager::bootstrapList();//var_dump($bootList);
        foreach ($bootList as $bootObj => $bootConf) {//echo $bootObj;var_dump($bootConf);
            if (strpos($bootObj, '\\') !== false) {
                $boot = Yii::createObject($bootObj);
            } else {//echo"next:'$bootObj'";var_dump($bootConf);
                $boot = $app->getModule($bootObj);//echo'booted module:';if(!$boot)var_dump($boot);else{echo"{$boot->uniqueId}:submodules:";var_dump(array_keys($boot->modules));}
                //?? better add dynamic submodules here
                //!! but module-container without bootstrap will not appear here

                if ($boot instanceof YiiBaseModule) {//var_dump($boot::className());var_dump($bootConf);
/*
                    $configAdd = [];
                    foreach ($bootConf as $property => $data) {
                        if (property_exists($boot, $property)) {
                            $configAdd[$property] = $data; 
                        }
                    }//var_dump($configAdd);
                    Yii::configure($boot, $configAdd); //!! no: lost old values of properties
*/
                    foreach ($bootConf as $property => $data) {
                        if (property_exists($boot, $property)) {
                            $boot->$property = ArrayHelper::merge($boot->$property, $data);
                        }
                    }
                }
            }//

            if (method_exists($boot, 'bootstrap')) {//echo"bootstrap:'".$boot::className()."'<br>";
                // need to send module's uniqueId to bootstrap
                if (property_exists($boot, 'moduleUid') && empty($boot->moduleUid)) {
                    $boot->moduleUid = $bootConf['moduleUid']; 
                }
                $boot->bootstrap($app);
            }
        }//var_dump(array_keys($app->modules));

        // Add to any standard yii\base\Module dynamic submodules from Modules manager and bootstrap them.
        $app->on(Application::EVENT_BEFORE_REQUEST, function($event) use($app) {//echo __METHOD__;var_dump($event->name);
            //echo'submodules of app:';var_dump(array_keys($app->loadedModules));var_dump(array_keys($app->modules));exit;
            foreach($app->loadedModules as $class => $module) {
                if (! $module instanceof UniModule && ! $module instanceof IWithoutUniSubmodules
                 && ! ModulesManager::alreadyAddSubmodules($module)
                ){//echo"$class:({$module->uniqueId}):now:";var_dump(array_keys($module->modules));
                    $submodules = ModulesManager::submodules($module);//echo"add:";var_dump(array_keys($submodules));
                    foreach ($submodules as $submoduleId => $config) {
                        if (!array_key_exists ($submoduleId, $module->modules)) {//var_dump($config);
                            $module->setModule($submoduleId, $config); // add dynamic submodule

                            // Bootstrap additional submodules here if their parent-container is dynamicly added module.
                            //!!no: $module->getModule($submoduleId); //?? auth problem
                            $bootList = ModulesManager::bootstrapList($module->uniqueId);
                            foreach ($bootList as $bootObj => $bootConf) {//echo $bootObj;var_dump($bootConf);
                                if (strpos($bootObj, '\\') !== false) {
                                    $boot = Yii::createObject($bootObj);
                                } else {//var_dump($bootObj);var_dump($bootConf);
                                    $boot = $app->getModule($bootObj);//echo'boot module:';if($boot)var_dump($boot->uniqueId); else var_dump($boot);
                                }
                                if (method_exists($boot, 'bootstrap')) {
                                    if (property_exists ($boot, 'moduleUid') && empty($boot->moduleUid)) {
                                        $boot->moduleUid = $bootConf['moduleUid']; 
                                    }
                                    $boot->bootstrap($app);
                                }
                            }
                        }
                    }//echo"$class:({$module->uniqueId}):AFTER:";var_dump(array_keys($module->modules));
                    ModulesManager::setAlreadyAddSubmodules($module);
                }
            }
        }, null, false); // run this handler first
    }

}
