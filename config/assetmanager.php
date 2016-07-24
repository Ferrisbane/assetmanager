<?php

return [
	'external' => [
		'catch' => true,
		'catch_minutes' => 1440, // 10 days
        'add_header' => true
	],

	'version_overrides' => [
    	'/css/style.css' => '2.0.1',
    	'/css/style.*' => '2.1.1',
    ],

    // Requires {fileHash}
    'route' => '/asset/{fileHash}'
];