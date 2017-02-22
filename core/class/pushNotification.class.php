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
					$message = '<toast>
						<visual>
							<binding template="ToastText01">
								<text id="1">'.$_options['title'].'</text>
								<text id="2">'.$_options['message'].'</text>
							</binding>
						</visual>
					</toast>';
					$headers[] = 'X-WNS-Type: wns/toast';
				break;
				case 'iOS':
					$message = '{"aps":{"alert":"'.$_options['message'].'"}}';
				break;
				case 'Android':
					$message = '{"data":{"message":"'.$_options['message'].'"}}';
				break;
				case 'WindowsPhone':
					//Windows Phone 8.0 and 8.1 Silverlight
					$message = '<?xml version="1.0" encoding="utf-8"?>' .
						'<wp:Notification xmlns:wp="WPNotification">' .
							'<wp:Toast>' .
								'<wp:Text1>'.$_options['title'].'</wp:Text1>' .
								'<wp:Text2>'.$_options['message'].'</wp:Text2>' .
							'</wp:Toast> ' .
						'</wp:Notification>';
					$headers[] = 'X-WindowsPhone-Target : toast';
					$headers[] = 'X-NotificationClass : 2';
				break;
				case 'KindleFire':
					//Kindle Fire
					$message = '{"data":{"msg":"'.$_options['message'].'!"}}';
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
