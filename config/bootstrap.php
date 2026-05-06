<?php
declare(strict_types=1);

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Datasource\ConnectionManager;
use Cake\Log\Log;
use Cake\Mailer\Email;
use Cake\Mailer\TransportFactory;
use Cake\Utility\Security;
use Cake\Http\ServerRequest;
use Detection\MobileDetect;

require __DIR__ . '/paths.php';
require ROOT . DS . 'vendor' . DS . 'autoload.php';

$coreFunctions = [
    ROOT . DS . 'vendor' . DS . 'cakephp' . DS . 'cakephp' . DS . 'src' . DS . 'Core' . DS . 'functions.php',
    CORE_PATH . 'src' . DS . 'Core' . DS . 'functions.php',
    ROOT . DS . 'vendors' . DS . 'cakephp' . DS . 'cakephp' . DS . 'src' . DS . 'Core' . DS . 'functions.php',
];
foreach ($coreFunctions as $coreFunctionFile) {
    if (file_exists($coreFunctionFile)) {
        require_once $coreFunctionFile;
        break;
    }
}

$coreGlobalFunctions = [
    ROOT . DS . 'vendor' . DS . 'cakephp' . DS . 'cakephp' . DS . 'src' . DS . 'Core' . DS . 'functions_global.php',
    CORE_PATH . 'src' . DS . 'Core' . DS . 'functions_global.php',
    ROOT . DS . 'vendors' . DS . 'cakephp' . DS . 'cakephp' . DS . 'src' . DS . 'Core' . DS . 'functions_global.php',
];
foreach ($coreGlobalFunctions as $coreGlobalFunctionsFile) {
    if (file_exists($coreGlobalFunctionsFile)) {
        require_once $coreGlobalFunctionsFile;
        break;
    }
}

$i18nFunctions = [
    ROOT . DS . 'vendor' . DS . 'cakephp' . DS . 'cakephp' . DS . 'src' . DS . 'I18n' . DS . 'functions.php',
    CORE_PATH . 'src' . DS . 'I18n' . DS . 'functions.php',
    ROOT . DS . 'vendors' . DS . 'cakephp' . DS . 'cakephp' . DS . 'src' . DS . 'I18n' . DS . 'functions.php',
];
foreach ($i18nFunctions as $i18nFunctionsFile) {
    if (file_exists($i18nFunctionsFile)) {
        require_once $i18nFunctionsFile;
        break;
    }
}

$i18nGlobalFunctions = [
    ROOT . DS . 'vendor' . DS . 'cakephp' . DS . 'cakephp' . DS . 'src' . DS . 'I18n' . DS . 'functions_global.php',
    CORE_PATH . 'src' . DS . 'I18n' . DS . 'functions_global.php',
    ROOT . DS . 'vendors' . DS . 'cakephp' . DS . 'cakephp' . DS . 'src' . DS . 'I18n' . DS . 'functions_global.php',
];
foreach ($i18nGlobalFunctions as $i18nGlobalFunctionsFile) {
    if (file_exists($i18nGlobalFunctionsFile)) {
        require_once $i18nGlobalFunctionsFile;
        break;
    }
}

require CORE_PATH . 'config' . DS . 'bootstrap.php';

try {
    Configure::config('default', new PhpConfig());
    Configure::load('app', 'default', false);
    if (file_exists(CONFIG . 'app_local.php')) {
        Configure::load('app_local', 'default');
    }
} catch (Exception $exception) {
    exit($exception->getMessage() . "\n");
}

if (!Configure::read('debug')) {
    Configure::write('Cache._cake_model_.duration', '+1 years');
    Configure::write('Cache._cake_core_.duration', '+1 years');
}

date_default_timezone_set('Australia/Brisbane');
mb_internal_encoding(Configure::read('App.encoding'));
ini_set('intl.default_locale', Configure::read('App.defaultLocale'));

$errorConfig = (array)Configure::read('Error');

$isCli = PHP_SAPI === 'cli';

if ($isCli) {
    (new \Cake\Console\ConsoleErrorHandler($errorConfig))->register();
} else {
    (new \Cake\Error\ErrorHandler($errorConfig))->register();
}

if ($isCli) {
    require __DIR__ . '/bootstrap_cli.php';
}

Cache::setConfig((array)Configure::consume('Cache'));
ConnectionManager::setConfig((array)Configure::consume('Datasources'));
$transports = (array)Configure::consume('EmailTransport');
if ($transports) {
    TransportFactory::setConfig($transports);
}
$mailers = (array)Configure::consume('Email');
if ($mailers) {
    Email::setConfig($mailers);
}
Log::setConfig((array)Configure::consume('Log'));
Security::setSalt((string)Configure::consume('Security.salt'));

ServerRequest::addDetector('mobile', function (ServerRequest $request) {
    $detector = new MobileDetect();
    return $detector->isMobile();
});
ServerRequest::addDetector('tablet', function (ServerRequest $request) {
    $detector = new MobileDetect();
    return $detector->isTablet();
});
