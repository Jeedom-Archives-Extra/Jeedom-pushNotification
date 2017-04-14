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
			$token = json_decode($cache->getValue(null));
			if($token !== null){     //init the WindowsNotification Class     
				$Notifier = new WindowsNotification\WindowsNotificationClass();     
				$Auth = $Notifier->AuthenticateService();     
				if($Auth->response_status == 200){     
					cache::set('pushNotification::token', json_encode($Auth), 0);
				}
			}
			log::add('pushNotification','debug',json_encode($token));
			return $token;
		}
		public function execute($_options = array()) {
			$uri=$this->getEqlogic()->getConfiguration('adress');
			switch($this->getEqlogic()->getConfiguration('type_mobile')){
				//Windows Store and Windows Phone 8.1 (non-Silverlight)
				case 'windows':
					$token = $this->GetToken();
					$Options = new WindowsNotification\WNSNotificationOptions();
					$Options->SetAuthorization(new WindowsNotification\OAuthObject($token));
					$Options->SetX_WNS_REQUESTFORSTATUS(WindowsNotification\X_WNS_RequestForStatus::Request);
					$Notifier = new WindowsNotification\WindowsNotificationClass($Options);
					//Send a ToastText02 with custom sounds
					//$Notifier->Send($uri,WindowsNotification\TemplateToast::ToastText02("HELLO!","I'm the message!!!!",WindowsNotification\TemplateToast::NotificationMail));
					//Send a ToastText01 to another channel
					log::add('pushNotification','debug','Message a envoyé: '.$_options['message']);
					$result=$Notifier->Send($uri,WindowsNotification\TemplateToast::ToastText01($_options['message']));
					log::add('pushNotification','debug',json_encode($result));
					//Send a ToastText01 to another channel with local sound
					//$Notifier->Send($uri,WindowsNotification\TemplateToast::ToastText01("HELLO!"),WindowsNotification\TemplateToast::CustomSound("<my local url>");
				break;
				case 'ios':
				break;
				case 'Android':
				break;
			}
		}
	}

?>
