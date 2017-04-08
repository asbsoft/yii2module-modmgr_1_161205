<?php
//ru
return [
    'Modules manager'          => 'Менеджер модулей',
    
    'Installed modules'        => 'Установленные модули',
    'Install module'           => 'Установить модуль',
    'Select module class file' => 'Файл класса модуля',
    'Enter module ID (alias)'  => 'Введите (псевдоним) модуля',
    'Select module container'  => 'Выберите модуль-контейнер',
    'Install new module'       => 'Установить новый модуль',
    'Install'                  => 'Установить',
    'Update installed module'  => 'Редактировать установленный модуль',
    'Module'                   => 'Модуль',
    "Module '{name}' (#{id})"  => "Модуль '{name}' (#{id})",
    'Name of module'           => 'Название модуля',
    'To list'                  => 'К списку',
    'Update'                   => 'Редактировать',
    'Delete'                   => 'Удалить',
    'Add submodule'            => 'Добавить подмодуль',
    'Actions'                  => 'Действия',

    'Unactive'                 => 'Неактивный',
    'Active but locked by parent'
                               => 'Активный, но заблокированный предком',
    'Active'                   => 'Активный',
    'Change to active'         => 'Сделать активным',
    'Change to unactive'       => 'Сделать неактивным',

    "Are you sure you want to {action} this item #{id}?"
                               => "Ви уверены, что хотите {action} этот элемент #{id}?",
    'activate'                 => 'активировать',
    'deactivate'               => 'деактивировать',

    'Additional module config' => 'Дополнительный конфиг модуля',
    'Added here parameters will overrite default collected config'
                               => 'Введенные здесь параметры перепишут параметры по умолчанию',
    '(full config collected from config files of parents)'
                               => '(полный конфиг, собранный из всех конфигов предков)',
    'Press to rebuild collected config (after change config files)'
                               => 'Нажмите, чтобы пересобрать полный конфиг (после изменения конфиг-файлов)',

    'Update'                   => 'Изменить',
    'Remove'                   => 'Удалить',
    'Deinstall module?'        => 'Удалить модуль?',
    'Deinstall module {module}?'
                               => 'Удалить модуль {module}?',

    'application'              => 'приложение',

// models
    'Module ID (alias)' => 'Ид (псевдоним) модуля',
    'Module-container'  => 'Модуль-контейнер',
    'Name'              => 'Название',
    'Active?'           => 'Активен?',
    'Module class'      => 'Класс модуля',
    "Bootstrap class or input '+' for use uniqueId as modules bootstrap"
                        => "Bootstrap-класс или введите '+' чтобы использовать uniqueId как bootstrap модуля",
    'Default config'    => 'Конфигурация по умолчанию',
    'Config'            => 'Конфигурация',
    'Create at'         => 'Создано',
    'Update at'         => 'Модифицировано',
    'Module unique ID: contaiter UID ...'
                        => 'Уникальный Iд: UID контейнера ...',
    '+ module ID'       => '+ Iд модуля',

// messages
    "Module's activity has been changed"
                                 => 'Активноcть модуля изменена',
    'Module has been deleted'    => 'Модуль успешно удален',
    "Can't delete active module" => 'Нельзя удалить активный модуль',

// errors
    'Only latin letters, digits, hyphen, underline and point'
                               => 'Только латинские буквы, цифры, дефис, подчеркивание и точка',
    'Press to rebuild collected config (after change config files)'
                               => '',
    'module class "{value}" already installed'
                               => 'модуль с классои "{value}" уже установлен',
    'such module ID (alias) already installed in this module-container'
                               => 'модуль с таким ИД (псевдонимом) уже установлен в этом модуле-контейнере',
    "can't set itself as module-container"
                               => "нельзя использовать модуль в качестве своего же контейнера-предка",
    'already exists static module with same ID: '
                               => 'уже есть статический модуль с таким же Iд: ',
    'already exists module with same uniqueId: '
                               => 'уже есть модуль с таким же уникальным Iд: ',
// instruction
    '[INSTRUCTION]'            => ''
    . 'Перед добавлением нового модуля в систему необходимо<br />'
    . '- установить модуль в нужное место файловой системы вместе со всеми зависимостями<br />'
    . '- добиться правильной работы автозагрузки (autoload) классов модуля<br />'
    . '- выполнить необходимые миграции.<br />'
    . 'Менеджер модулей поможет без редактирования конфигурационных файлов'
    . ' (де)активировать, изменить параметры модуля и префиксы роутов.<br />'
];
