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
		public function execute($_options = array()) {
			$uri=$this->getEqlogic()->getConfiguration('adress');
			switch($this->getEqlogic()->getConfiguration('type_mobile')){
				//Windows Store and Windows Phone 8.1 (non-Silverlight)
				case 'windows':
					
				break;
				case 'ios':
				break;
				case 'Android':
				break;
			}
		}
	}
class Notification {
	public $format;
	public $payload;
	# array with keynames for headers
	# Note: Some headers are mandatory: Windows: X-WNS-Type, WindowsPhone: X-NotificationType
	# Note: For Apple you can set Expiry with header: ServiceBusNotification-ApnsExpiry in W3C DTF, YYYY-MM-DDThh:mmTZD (for example, 1997-07-16T19:20+01:00).
	public $headers;
	function __construct($format, $payload) {
		if (!in_array($format, ["template", "apple", "windows", "gcm", "windowsphone"])) {
			throw new Exception('Invalid format: ' . $format);
		}
		$this->format = $format;
		$this->payload = $payload;
	}
}
class NotificationHub {
	const API_VERSION = "?api-version=2013-10";
	private $endpoint;
	private $hubPath;
	private $sasKeyName;
	private $sasKeyValue;
	function __construct($connectionString, $hubPath) {
		$this->hubPath = $hubPath;
		$this->parseConnectionString($connectionString);
	}
	private function parseConnectionString($connectionString) {
		$parts = explode(";", $connectionString);
		if (sizeof($parts) != 3) {
			throw new Exception("Error parsing connection string: " . $connectionString);
		}
		foreach ($parts as $part) {
			if (strpos($part, "Endpoint") === 0) {
				$this->endpoint = "https" . substr($part, 11);
			} else if (strpos($part, "SharedAccessKeyName") === 0) {
				$this->sasKeyName = substr($part, 20);
			} else if (strpos($part, "SharedAccessKey") === 0) {
				$this->sasKeyValue = substr($part, 16);
			}
		}
	}
	private function generateSasToken($uri) {
		$targetUri = strtolower(rawurlencode(strtolower($uri)));
		$expires = time();
		$expiresInMins = 60;
		$expires = $expires + $expiresInMins * 60;
		$toSign = $targetUri . "\n" . $expires;
		$signature = rawurlencode(base64_encode(hash_hmac('sha256', $toSign, $this->sasKeyValue, TRUE)));
		$token = "SharedAccessSignature sr=" . $targetUri . "&sig="
					. $signature . "&se=" . $expires . "&skn=" . $this->sasKeyName;
		return $token;
	}
	public function broadcastNotification($notification) {
		$this->sendNotification($notification, "");
	}
	public function sendNotification($notification, $tagsOrTagExpression) {
		//echo $tagsOrTagExpression."<p>";
		if (is_array($tagsOrTagExpression)) {
			$tagExpression = implode(" || ", $tagsOrTagExpression);
		} else {
			$tagExpression = $tagsOrTagExpression;
		}
		# build uri
		$uri = $this->endpoint . $this->hubPath . "/messages" . NotificationHub::API_VERSION;
		//echo $uri."<p>";
		$ch = curl_init($uri);
		if (in_array($notification->format, ["template", "apple", "gcm"])) {
			$contentType = "application/json";
		} else {
			$contentType = "application/xml";
		}
		$token = $this->generateSasToken($uri);
		$headers = [
		    'Authorization: '.$token,
		    'Content-Type: '.$contentType,
		    'ServiceBusNotification-Format: '.$notification->format
		];
		if ("" !== $tagExpression) {
			$headers[] = 'ServiceBusNotification-Tags: '.$tagExpression;
		}
		# add headers for other platforms
		if (is_array($notification->headers)) {
			$headers = array_merge($headers, $notification->headers);
		}
		
		curl_setopt_array($ch, array(
		    CURLOPT_POST => TRUE,
		    CURLOPT_RETURNTRANSFER => TRUE,
		    CURLOPT_SSL_VERIFYPEER => FALSE,
		    CURLOPT_HTTPHEADER => $headers,
		    CURLOPT_POSTFIELDS => $notification->payload
		));
		$response = curl_exec($ch);
		if($response === FALSE){
		    throw new Exception(curl_error($ch));
		}
		$info = curl_getinfo($ch);
		if ($info['http_code'] <> 201) {
			throw new Exception('Error sending notificaiton: '. $info['http_code'] . ' msg: ' . $response);
		}
	} 
}
?>
