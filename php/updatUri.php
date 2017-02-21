<?php
require_once dirname(__FILE__) . "/../../../core/php/core.inc.php";
$pushNotification = eqLogic::byId(init('id'));
if (!is_object($pushNotification)) {
	die();
}
if ($pushNotification->getEqType_name() != 'pushNotification') {
	die();
}
if (config::byKey('api') != init('apikey')) {
	die();
}
$pushNotification->setConfiguration('adress',init('uri'));
$pushNotification->save()
exit;
?>