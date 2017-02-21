<?php
header('Content-Type: application/json');

require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
global $jsonrpc;
if (!is_object($jsonrpc)) {
	throw new Exception(__('JSONRPC object not defined', __FILE__), -32699);
}

$params = $jsonrpc->getParams();
if ($jsonrpc->getMethod() == 'Iq') {
	$platform = $params['platform'];
	$uri = $params['query'];
	$user = user::byHash($params['apikey']);
	$mobile = new eqLogic;
	$mobile->setEqType_name('pushNotification');
	$mobile->setName($platform.'-'.config::genKey(3));
	$mobile->setConfiguration('type_mobile',$platform);
	$mobile->setConfiguration('adress',$uri);
	$mobile->setConfiguration('affect_user',$user->getId());
	$mobile->setIsEnable(1);
	$mobile->AddCmd("Notification push","push");
	$mobile->save();
	$jsonrpc->makeSuccess($mobile->getLogicalId());	
}


throw new Exception(__('Aucune demande', __FILE__));
?>
