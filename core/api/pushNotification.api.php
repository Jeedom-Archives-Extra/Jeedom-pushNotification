//()
<?php
header('Content-Type: application/json');

require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
global $jsonrpc;
if (!is_object($jsonrpc)) {
	throw new Exception(__('JSONRPC object not defined', __FILE__), -32699);
}

$params = $jsonrpc->getParams();
log::add('pushNotification','debug',json_encode($params));
if ($jsonrpc->getMethod() == 'Iq') {
	$platform = $params['platform'];
	$uri = $params['query'];
	$user = user::byHash($params['apikey']);
        if (isset($params['id']) && $params['id']!='')
		$Equipement = eqLogic::byId($params['id']);
	if (!isset($Equipement) && !is_object($Equipement)){
		log::add('pushNotification','debug','Création d\'un nouvelle equipement');
		$Equipement = new pushNotification();
		$Equipement->setName($platform.'-'.config::genKey(3));
		$Equipement->setEqType_name('pushNotification');
		$Equipement->setObject_id(null);
		$Equipement->setIsEnable(1);
		$Equipement->setIsVisible(1);
	}
	$Equipement->setConfiguration('type_mobile',$platform);
	$Equipement->setConfiguration('adress',$uri);
	$Equipement->setConfiguration('affect_user',$user->getId());
	$Equipement->save();
	log::add('pushNotification','debug','Mise a jours de l\'Uri channel');
	$jsonrpc->makeSuccess($Equipement->getLogicalId());	
}


throw new Exception(__('Aucune demande', __FILE__));
?>
