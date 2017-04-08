<?php

use asb\yii2\modules\modmgr_1_161205\models\Modmgr;


return [
    'label'   => 'Modules manager',
    'version' => '1.161205',

    'pageSizeAdmin' => 15, // admin list page size

    Modmgr::className() => [
        'tableName' => '{{%modmgr}}',
    ],
];
