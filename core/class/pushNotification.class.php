<?php
	require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
	include_file('core', 'WindowsNotification', 'class', 'pushNotification');
	class pushNotification extends eqLogic {
		public function postSave() {
			$this->AddCmd("Notification push","push");
		}
		public function AddCmd($Name,$_logicalId) 	{
			$Commande = $this->getCmd(null,$_logicalId);
			if (!is_object($Commande)){
				$Commande = new pushNotificationCmd();
				$Commande->setId(null);
				$Commande->setName($Name);
				$Commande->setLogicalId($_logicalId);
				$Commande->setEqLogic_id($this->getId());
				$Commande->setIsVisible(1);
				$Commande->setType('action');
				$Commande->setSubType('message');
				$Commande->save();
			}
			return $Commande;
		}
	}
	class pushNotificationCmd extends cmd {
		private function GetToken() {
			$cache = cache::byKey('pushNotification::token');
			$token = json_decode($cache->getValue(null),true);
			//if($token !== null){     //init the WindowsNotification Class     
				$Notifier = new WindowsNotification\WindowsNotificationClass();     
				$Auth = $Notifier->AuthenticateService();     
				if($Auth->response_status == 200){     
					cache::set('pushNotification::token', json_encode($Auth), 0);
				}
			//}
			log::add('pushNotification','debug',json_encode($token));
			return $token;
		}
		public function execute($_options = array()) {
			if ($_options === null) {
				throw new Exception(__('[Mail] Les options de la fonction ne peuvent etre null', __FILE__));
			}

			if ($_options['message'] == '' && $_options['title'] == '') {
				throw new Exception(__('[Mail] Le message et le sujet ne peuvent être vide', __FILE__));
				return false;
			}

			if ($_options['title'] == '') {
				$_options['title'] = __('[Jeedom] - Notification', __FILE__);
			}

			$uri=$this->getEqlogic()->getConfiguration('adress');
			switch($this->getEqlogic()->getConfiguration('type_mobile')){
				//Windows Store and Windows Phone 8.1 (non-Silverlight)
				case 'windows':
             			   	log::add('pushNotification','debug','Récupération du token');
					$token = $this->GetToken();
					$Options = new WindowsNotification\WNSNotificationOptions();
					$Options->SetAuthorization(new WindowsNotification\OAuthObject($token));
					$Options->SetX_WNS_REQUESTFORSTATUS(WindowsNotification\X_WNS_RequestForStatus::Request);
					log::add('pushNotification','debug','Demande de l\'autorisation d\'émetre une notification');
					$Notifier = new WindowsNotification\WindowsNotificationClass($Options);	
					$toast=WindowsNotification\TemplateToast::ToastText02($_options['titre'],$_options['message']);
					if (isset($_options['files']) && is_array($_options['files'])) {
						foreach ($_options['files'] as $file) {
							$toast=WindowsNotification\TemplateToast::ToastImageAndText02($_options['titre'],$_options['message'],$file);
						}
					}
					$result=$Notifier->Send($uri,$toast);
					log::add('pushNotification','debug',json_encode($result));
				break;
				case 'ios':
				break;
				case 'Android':
				break;
			}
		}
	}

?>
