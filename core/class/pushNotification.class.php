<?php
	require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
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
		public function execute($_options = array()) {
			$uri=$this->getEqlogic()->getConfiguration('adress');
			switch($this->getEqlogic()->getConfiguration('type_mobile')){
				//Windows Store and Windows Phone 8.1 (non-Silverlight)
				case 'windows':
					$message = '
						<toast launch="app-defined-string">
						  <visual>
						    <binding template="ToastGeneric">
						      <text>'.$_options["title"]'.</text>
						      <text>'.$_options["message"]'.</text>
						      './/<image placement="AppLogoOverride" src="oneAlarm.png" />
						    '</binding>
						  </visual>
						  './/<actions>
						    //<action content="check" arguments="check" imageUri="check.png" />
						    //<action content="cancel" arguments="cancel" />
						  //</actions>
						  //<audio src="ms-winsoundevent:Notification.Reminder"/>
						'</toast>';
					$headers[] = 'X-WNS-Type: wns/toast';
				break;
				case 'ios':
					$message = '{"aps":{"alert":"'.$_options['message'].'"}}';
				break;
				case 'Android':
					$message = '{"data":{"message":"'.$_options['message'].'"}}';
				break;
			}
			$this->Send($uri, $headers, $message);
		}
		private function Send($uri, $headers, $message){
			$request = curl_init();
			curl_setopt($request, CURLOPT_HEADER, true);
			curl_setopt($request, CURLOPT_HTTPHEADER,$headers);
			curl_setopt($request, CURLOPT_POST, true);
			curl_setopt($request, CURLOPT_POSTFIELDS, $message);
			curl_setopt($request, CURLOPT_URL, $uri);
			curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
			$response = curl_exec($request);
			if($errorNumber = curl_errno($request)) {
				$errorMessage = curl_strerror($errorNumber);
				throw new PushException($errorMessage, $errorNumber);
			}
			curl_close($request);
			$result = array();
			foreach (explode("\n",$response) as $line) {
				$tab = explode(":", $line, 2);
				if(count($tab) == 2) {
					$result[$tab[0]] = trim($tab[1]);
				}
			}
			return $result;
		}
	}
?>
