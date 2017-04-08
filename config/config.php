<?php

return [
    //'params' => include(__DIR__ . '/params.php'),

    'baseTransCategory' => basename(dirname(__DIR__)),

    //'layoutPath' => '@asb/yii2/cms_3_170211/modules/sys/views/layouts',
    //'layoutPath' => '@project/views/layouts',
    'layouts' => [
        'backend'  => 'layout_admin',
    ],

    'routesConfig' => [ // default: type => prefix|[config]
      //'main'  => 'news',
        'admin' => 'admin/modmgr', // Yii2-base config
      //'admin' => 'modmgr',       // Yii2-advanced config
      //'rest'  => ['urlPrefix'  => 'restapi', 'sublink' => 'modmgr'],
    ],

    // shared models
    'models' => [ // alias => class name or object array
        //'Modmgr' => 'asb\yii2\modules\modmgr_1_161205\models\Modmgr',
        'ModulesManager' => 'asb\yii2\modules\modmgr_1_161205\models\ModulesManager',
    ],
];
