<?php

return [
    [
        'name' => 'Cron:PaypalImporter.Import',
        'entity' => 'Job',
        'params' => [
            'version' => 3,
            'name' => 'PayPal Import',
            'description' => 'Import transactions from PayPal',
            'run_frequency' => 'Hourly',
            'api_entity' => 'PaypalImporter',
            'api_action' => 'Import',
            'parameters' => '',
        ],
    ],
];
