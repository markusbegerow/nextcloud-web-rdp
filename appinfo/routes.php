<?php

return [
    'routes' => [
        ['name' => 'page#index',              'url' => '/',                    'verb' => 'GET'],
        ['name' => 'connection#index',        'url' => '/api/connections',     'verb' => 'GET'],
        ['name' => 'connection#create',       'url' => '/api/connections',     'verb' => 'POST'],
        ['name' => 'connection#update',       'url' => '/api/connections/{id}','verb' => 'PUT'],
        ['name' => 'connection#destroy',      'url' => '/api/connections/{id}','verb' => 'DELETE'],
        ['name' => 'tunnel#getToken',         'url' => '/api/tunnel/{id}',     'verb' => 'POST'],
        ['name' => 'settings#getSettings',      'url' => '/api/settings',        'verb' => 'GET'],
        ['name' => 'settings#saveSettings',    'url' => '/api/settings',        'verb' => 'POST'],
        ['name' => 'settings#getUserSettings', 'url' => '/api/user-settings',   'verb' => 'GET'],
        ['name' => 'settings#saveUserSettings','url' => '/api/user-settings',   'verb' => 'POST'],
    ],
];
