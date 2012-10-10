<?php
// Version
define('VERSION', '1.5.2.1');

// DIR
define('DIR_APPLICATION', ($appRoot = realpath(__DIR__ . '/../')) . '/admin/');
define('DIR_SYSTEM', $appRoot . '/system/');
define('DIR_DATABASE', $appRoot . '/system/database/');
define('DIR_LANGUAGE', $appRoot . '/admin/language/');
define('DIR_TEMPLATE', $appRoot . '/admin/view/template/');
define('DIR_CONFIG', $appRoot . '/system/config/');
define('DIR_IMAGE', $appRoot . '/image/');
define('DIR_CACHE', $appRoot . '/system/cache/');
define('DIR_DOWNLOAD', $appRoot . '/download/');
define('DIR_LOGS', $appRoot . '/system/logs/');
define('DIR_CATALOG', $appRoot . '/catalog/');

// Startup
/** @var \Nette\DI\Container|\SystemContainer $container */
$container = require_once(DIR_SYSTEM . 'startup.php');
/** @var \Nette\Http\Request $httpRequest */
$httpRequest = $container->httpRequest;

// HTTP
define('HTTP_SERVER', ($host = $httpRequest->getUrl()->getHostUrl()) . '/admin/');
define('HTTP_IMAGE', $host . '/image/');
define('HTTP_CATALOG', $host . '/');

// HTTPS
define('HTTPS_SERVER', $host . '/admin/');
define('HTTPS_IMAGE', $host . '/image/');

// DB
define('DB_PREFIX', '');
define('DB_DRIVER', '');
define('DB_HOSTNAME', '');
define('DB_USERNAME', '');
define('DB_PASSWORD', '');
define('DB_DATABASE', '');

// Application Classes
require_once(DIR_SYSTEM . 'library/currency.php');
require_once(DIR_SYSTEM . 'library/user.php');
require_once(DIR_SYSTEM . 'library/weight.php');
require_once(DIR_SYSTEM . 'library/length.php');

// Registry
$registry = new Registry();
$registry->set('nette', $container);

// Loader
$loader = new Loader($registry);
$registry->set('load', $loader);

// Config
$config = new Config();
$registry->set('config', $config);

// Database
$db = new DB($database = $container->database);
$registry->set('db', $db);
$registry->set('database', $database);

// Settings
$query = $db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '0'");

foreach ($query->rows as $setting) {
	if (!$setting['serialized']) {
		$config->set($setting['key'], $setting['value']);
	} else {
		$config->set($setting['key'], unserialize($setting['value']));
	}
}

// Url
$url = new Url(HTTP_SERVER, $config->get('config_use_ssl') ? HTTPS_SERVER : HTTP_SERVER);
$registry->set('url', $url);

// Log
$log = new Log($config->get('config_error_filename'));
$registry->set('log', $log);

// Request
$request = new Request();
$registry->set('request', $request);

// Response
$response = new Response();
$response->addHeader('Content-Type: text/html; charset=utf-8');
$registry->set('response', $response);

// Cache
$cache = new Cache();
$registry->set('cache', $cache);

// Session
$session = new Session();
$registry->set('session', $session);

// Language
$languages = array();

$query = $db->query("SELECT * FROM " . DB_PREFIX . "language");

foreach ($query->rows as $result) {
	$languages[$result['code']] = $result;
}

$config->set('config_language_id', $languages[$config->get('config_admin_language')]['language_id']);

// Language
$language = new Language($languages[$config->get('config_admin_language')]['directory']);
$language->load($languages[$config->get('config_admin_language')]['filename']);
$registry->set('language', $language);

// Document
$registry->set('document', new Document());

// Currency
$registry->set('currency', new Currency($registry));

// Weight
$registry->set('weight', new Weight($registry));

// Length
$registry->set('length', new Length($registry));

// User
$registry->set('user', new User($registry));

// Front Controller
$controller = new Front($registry);

// Login
$controller->addPreAction(new Action('common/home/login'));

// Permission
$controller->addPreAction(new Action('common/home/permission'));

// Router
if (isset($request->get['route'])) {
	$action = new Action($request->get['route']);
} else {
	$action = new Action('common/home');
}

// Dispatch
$controller->dispatch($action, new Action('error/not_found'));

// Output
$response->output();
