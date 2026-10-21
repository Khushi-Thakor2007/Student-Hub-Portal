<?php

define('DB_HOST', getenv('EVENT_DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('EVENT_DB_NAME') ?: 'event_management');
define('DB_USER', getenv('EVENT_DB_USER') ?: 'root');
define('DB_PASS', getenv('EVENT_DB_PASS') ?: '');
define('MAX_POSTER_SIZE', 5 * 1024 * 1024);
define('POSTER_DIRECTORY', __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'posters');
define('POSTER_URL', 'uploads/posters/');
