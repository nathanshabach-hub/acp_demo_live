<?php
declare(strict_types=1);

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Datasource\ConnectionManager;
use Cake\Error\ErrorTrap;
use Cake\Error\ExceptionTrap;
use Cake\Log\Log;
use Cake\Mailer\Mailer;
use Cake\Mailer\TransportFactory;
use Cake\Utility\Security;
use Cake\Http\ServerRequest;
use Detection\MobileDetect;

require __DIR__ . '/paths.php';
require ROOT . DS . 'vendor' . DS . 'autoload.php';
require ROOT . DS . 'vendor' . DS . 'cakephp' . DS . 'cakephp' . DS . 'src' . DS . 'Core' . DS . 'functions.php';
require ROOT . DS . 'vendor' . DS . 'cakephp' . DS . 'cakephp' . DS . 'src' . DS . 'Core' . DS . 'functions_global.php';
require_once ROOT . DS . 'vendor' . DS . 'cakephp' . DS . 'cakephp' . DS . 'src' . DS . 'I18n' . DS . 'functions.php';
require_once ROOT . DS . 'vendor' . DS . 'cakephp' . DS . 'cakephp' . DS . 'src' . DS . 'I18n' . DS . 'functions_global.php';
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
(new ErrorTrap($errorConfig))->register();
(new ExceptionTrap($errorConfig))->register();

$isCli = PHP_SAPI === 'cli';

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
    Mailer::setConfig($mailers);
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
