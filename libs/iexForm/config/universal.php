<?
$params = [
    'multistep' => in_array($_POST['pformid'], ['multistep-popup', 'multistep-inline'], true),
    'exts' => [
        'AdditionalFields' => [],
        'Mail' => [
            'to' => 'rd@site-insight.ru',
        ],
    ],
];