<?php

namespace asb\yii2\modules\modmgr_1_161205;

use asb\yii2\common_2_170212\base\ModulesManager;
use asb\yii2\common_2_170212\base\UniModule;
use asb\yii2\common_2_170212\helpers\ConfigsBuilder;

use Yii;
use yii\base\Module as YiiBaseModule;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\UnknownPropertyException;
use yii\helpers\ArrayHelper;

class Bootstrap implements BootstrapInterface
{
    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        $app->getModule('modmgr');

        // Add to application modules registered in modules-manager
        $modules = $app->modules;
        $activeModules = ModulesManager::submodules($app, true, $app);
        $app->modules = ArrayHelper::merge($modules, $activeModules);

        // Run additional modules bootstraps here.
        // Unfortunately not all modules will bootstrap here
        // because in modules-containers chain may be dynamicly added modules
        $bootList = ModulesManager::bootstrapList('', $app);
        foreach ($bootList as $bootObj => $bootConf) {
            if (strpos($bootObj, '\\') !== false) {
                $boot = Yii::createObject($bootObj);
            } else {
                $boot = $app->getModule($bootObj);
                if ($boot instanceof YiiBaseModule) {
                    foreach ($bootConf as $property => $data) {
                        if (property_exists($boot, $property)) {
                            $boot->$property = ArrayHelper::merge($boot->$property, $data);
                        }
                    }
                }
            }
            if (method_exists($boot, 'bootstrap')) {
                // need to send module's uniqueId to bootstrap
                if (property_exists($boot, 'moduleUid') && empty($boot->moduleUid)) {
                    $boot->moduleUid = $bootConf['moduleUid']; 
                }
                $boot->bootstrap($app);
            }
        }

        // Add to any standard yii\base\Module dynamic submodules from Modules manager and bootstrap them.
        $app->on(Application::EVENT_BEFORE_REQUEST, function($event) use($app) {
            foreach($app->loadedModules as $class => $module) {
                if (! $module instanceof UniModule && ! $module instanceof IWithoutUniSubmodules
                 && ! ModulesManager::alreadyAddSubmodulesFor($module, $app)
                ){
                    $submodules = ModulesManager::submodules($module);
                    foreach ($submodules as $submoduleId => $config) {
                        if (!array_key_exists ($submoduleId, $module->modules)) {
                            $module->setModule($submoduleId, $config); // add dynamic submodule

                            // Bootstrap additional submodules here if their parent-container is dynamicly added module.
                            //!!no: $module->getModule($submoduleId); //?? auth problem
                            $bootList = ModulesManager::bootstrapList($module->uniqueId);
                            foreach ($bootList as $bootObj => $bootConf) {
                                if (strpos($bootObj, '\\') !== false) {
                                    $boot = Yii::createObject($bootObj);
                                } else {
                                    $boot = $app->getModule($bootObj);
                                }
                                if (method_exists($boot, 'bootstrap')) {
                                    if (property_exists ($boot, 'moduleUid') && empty($boot->moduleUid)) {
                                        $boot->moduleUid = $bootConf['moduleUid']; 
                                    }
                                    $boot->bootstrap($app);
                                }
                            }
                        }
                    }
                    ModulesManager::setAlreadyAddSubmodulesFor($module, $app);
                }
            }

            ConfigsBuilder::cacheAllConfigsFile($app);

        }, null, false); // run this handler first
    }

}
