<?php
/*** IMPORT MVC CLASSES AND PIMPLE CONTAINER ***/
require(dirname(__FILE__)."/../../lib/mvc/model.php");
require(dirname(__FILE__)."/../../lib/mvc/controller.php");
require(dirname(__FILE__)."/../../lib/mvc/validator.php");
require(dirname(__FILE__)."/../../lib/mvc/cache.php");
require(dirname(__FILE__)."/../../lib/Pimple/Container.php");

/*** CREATE CONTAINER FOR STORING PARAMETERS AND OBJECTS ***/
use Pimple\Container;
$app = new Container();

/*** LOAD CONFIGURATIONS,PARAMETERS,DATA AND LANGUAGE FILE ***/
if(!isset($_GET['lang'])) $_GET['lang']='en';
$app['config'] 	= require("../app/config.php");
$app['meta']	= require("../app/meta.php");
$app['valid']	= require("../app/validation.php");
$app['params'] 	= $_GET;
$app['data'] 	= $_POST;
$app['i18n'] 	= require("../app/language/".$app['params']['lang'].".php");

/*** ADD DATABASE CONNECTION CLOSURE FUNCTION TO CONTAINER ***/
$app['database'] = function($c) {
	// If PDO database object can not be created, then return PDOException
	try {
		$db = $c['config']['database'];
		return new PDO($db['dsn'],$db['username'],$db['password'],$db['options']);
	} catch(PDOException $e) { 
		return $e;
	}	
};

/*** ADD CACHE CLOSURE FUNCTION TO CONTAINER ***/
$app['cache'] = function($c) {
	return new Cache($c['config']['website']['cache'],$c['params']);
};

/*** ADD MODULE CLOSURE FUNCTION TO CONTAINER ***/
$app['model'] = function($c) {
	if(is_file('../app/model/'.$c['params']['module'].'.php')) {
		// if there is specific model created for current module then instantiate object from this class
		require_once('../app/model/'.$c['params']['module'].'.php');
		$class = ucwords($c['params']['module']).'Model';
		$model = new $class($c['params'],$c['data'],$c['meta']);
	} else {
		// if no specific model created for current module then instantiate object from Model class
		$model = new Model($c['params'],$c['data'],$c['meta']);
	}
	// model uses database by default and connection is created on calling this function
	// if no database needed then override hasDatabase() function in specific model to return false
	if($model->hasDatabase()) {
		$model->setDatabase($c['database']); 
	}
	return $model;
};

/*** ADD CONTROLLER CLOSURE FUNCTION TO CONTAINER ***/
$app['controller'] = function($c) {
	$module = $app['params']['module'];
	if(is_file('../app/controller/'.$module.'.php')) {
		// if there is specific controller created for current module then instantiate object from this class
		require_once('../app/controller/'.$module.'.php');
		$class = ucwords($module).'Controller';
		$controller = new $class();
	} else {
		// if no specific controller created for current module then instantiate object from Controller class
		$controller = new Controller();
	}
	// controller uses cache by default and cache object is created on calling this function
	// if no cache needed then override hasCache() function in specific controller to return false
	if($controller->hasCache()) {
		$controller->setCache($c['cache']);
	}
	// controller uses model by default and model object is created on calling this function
	// if no model needed then override hasModel() function in specific controller to return false
	if($controller->hasModel()) {
		$controller->setModel($c['model']);
	}	
	return $controller;
};

/*** AUTHENTICATION AND AUTHORIZATION HERE ***/
// TODO: Users in database and token in request header
// TODO: different URL routes, .htaccess files and controllers for different profiles - admin, member, public ...

/*** DATA VALIDATION ***/
// Query string parameters in GET are validated in .htaccess file, valid routes must be defined in mod_rewrite
$errors = [];
if($_SERVER['REQUEST_METHOD']=='POST') {
	$module = $app['params']['module'];
	$action = $app['params']['action'];
	$v = new Validator($app['data'],$app['meta'],$app['valid'][$module][$action]);
	$errors = $v->validate();
}

/*** ACTION ***/
if(count($errors)>0) {
	// if validation errors then pack params, data and errors in output array
	$output = ['params'=>$app['params'],'data'=>$app['data'],'report'=>['status'=>'ERROR','errors'=>$errors]];	
} else {
	// if no errors then instantiate controller object and call appropriate action for getting output array
	$ctrl = $app['controller'];
	$output = call_user_func([$ctrl,$app['params']['action']]);	
}

/*** RESPONSE ***/
if(isset($output['report']['errors']['info'])) {
	// translate and log errors if any
	foreach($output['report']['errors']['info'] as $k=>$v) {
		if(isset($app['i18n'][$v])) {
			$output['report']['errors']['info'][$k] = $app['i18n'][$v];
		}
		error_log('['.date('Y-m-d H:i:s').'] '.$output['report']['errors']['debug'][$k].PHP_EOL,3,$app['config']['website']['logs'].'/errors.txt');
	}
	// comment next line to debug
	unset($output['report']['errors']['debug']);
}

/*** CREATE JSON FROM OUTPUT ARRAY AND ECHO TO CLIENT ***/
echo json_encode($output,JSON_UNESCAPED_UNICODE);
?>