<?php

if (!is_file($autoloadFile = __DIR__ . '/../vendor/autoload.php')) {
    throw new \LogicException('Could not find autoload.php in vendor/. Did you run "composer install --dev"?');
}

stream_context_set_default([
    'http' => [
        'follow_location' => 1,
        'protocol_version' => '1.1',
        'max_redirects' => 20,
        'header' => []
    ]
]);

require $autoloadFile;
