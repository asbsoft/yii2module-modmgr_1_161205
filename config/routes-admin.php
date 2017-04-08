<?php
// route without prefix => controller/action without current (and parent) module(s) IDs
return [
    '<action:(view|update|delete|change-active|rebuild-default-config)>/<id:\d+>'
                                     => 'admin/<action>',
    '<action:(create)>/<parent:\d+>' => 'admin/<action>',
    '<action:(index)>/<page:\d+>'    => 'admin/<action>',
    '<action:(index|create)>'        => 'admin/<action>',
    '?'                              => 'admin/index', // without URL-normalizer
];
