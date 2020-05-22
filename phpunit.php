<?php

include __DIR__ . '/vendor/autoload.php';
date_default_timezone_set('UTC');

// Para os testes detectarem se estão rodando no windows
define("IS_WINDOWS", strtolower(substr(PHP_OS, 0, 3)) === 'win');
