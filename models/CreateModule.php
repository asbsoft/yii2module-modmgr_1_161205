<?php

namespace asb\yii2\modules\modmgr_1_161205\models;

use Yii;
use yii\base\Model;

class CreateModule extends Model
{
    // attributes
    public $moduleClassFile;
    public $moduleId;
    public $parentUid;

    public $tc;

    public function rules()
    {
        return [
            [['parentUid', 'moduleId'], 'string', 'max' => 255],
            [['moduleId', 'moduleClassFile'], 'required'],
            [['moduleClassFile'], 'file',
                'extensions' => 'php',
                'checkExtensionByMimeType' => false,
            ],
            ['moduleId', 'match',
                //'pattern' => '|^[^/]+$|',
                //'message' => Yii::t($this->tc, "you can't use '/' in module alias"),
                'pattern' => '/^[a-z0-9_\-\.]+$/i',
                'message' => Yii::t($this->tc, 'Only latin letters, digits, hyphen, underline and point')
            ],
        ];
    }

    public function loadDefaultValues()
    {
        $this->moduleClassFile = '';
        $this->moduleId = '';
        $this->parentUid = null;
    }

}
