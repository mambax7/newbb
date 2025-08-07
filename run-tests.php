<?php declare(strict_types=1);

require_once 'vendor/autoload.php';

$command = new PHPUnit\TextUI\Command;

$command->run(['', 'tests']);
