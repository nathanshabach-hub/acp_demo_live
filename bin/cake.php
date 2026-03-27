#!/usr/bin/env php
<?php
declare(strict_types=1);

use App\Application;
use Cake\Console\CommandRunner;

require dirname(__DIR__) . '/config/bootstrap.php';

$runner = new CommandRunner(new Application(CONFIG), 'cake');
exit($runner->run($argv));
