<?php

return [
    [
        'name' => 'Cron:PaypalDataImport.Process',
        'entity' => 'Job',
        'params' => [
            'version' => 3,
            'name' => 'Call PaypalDataImport.Process API',
            'description' => 'Call PaypalDataImport.Process API',
            'run_frequency' => 'Hourly',
            'api_entity' => 'PaypalDataImport',
            'api_action' => 'Process',
            'parameters' => '',
        ],
    ],
];
