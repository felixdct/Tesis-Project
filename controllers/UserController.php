<?php
require_once('../models/UserDAO.php');
require_once('../models/ErrorSystem.php');
require_once('../models/SystemTrack.php');
require_once('../resources/config/GlobalVariables.php');
require_once('../../PHPMailer/PHPMailerAutoload.php');
require_once('../models/Owncloud_AccountsDAO.php');
//require_once('../../owncloud/index.php');

class UserController {
	private $httpMethod;
	private $parameters;
	private $userDAO;
	private $Owncloud_AccountsDAO;

	public function __construct($httpMethod, $parameters) {
		$this->httpMethod = $httpMethod;
		$this->parameters = $this->parseParameters($parameters);
		$this->userDAO = new UserDAO();
		$this->Owncloud_AccountsDAO = new Owncloud_AccountsDAO();
	}

	/**
	 * Validate user, generate and save QR code.
	 * For Login operation. Show the QR code.
	 * For remove operation/change password. Send to email url to generate QR code.
	 */
	public function validateUserGenQRAction() 
	{
		$nickName  = $this->getParameterValue('nickname');
		$passwd    = $this->getParameterValue('passwd');
		$newpasswd = $this->getParameterValue('newpasswd');
		$op        = $this->getParameterValue('op');

		/* If variables are defined */
		if (isset($nickName) && isset($passwd) && isset($op)) {
			/* 1. Validate user */
			if($op != SystemTrack::UPDATE_OPERATION){
				$resp['errCode'] = $this->userDAO->validateUser($nickName,
			            	                             $passwd, $op);
			}else{
				$resp['errCode'] = ErrorSystem::SUCCESS;
			}

			if ($resp['errCode'] == ErrorSystem::SUCCESS) {
				/* 2. Set current operation on the database */
				$resp['errCode'] = $this->userDAO->updateSystemOperation($nickName, $op);
				if ($resp['errCode'] == ErrorSystem::SUCCESS) {
					/* 3. Generates QR code for LOGIN operation or send email with url to generate QR
					 *    code for remove user or change password.
					 */
					switch ($op) {
						case SystemTrack::LOGIN_OPERATION :
						{
							$resp['errCode'] =  $this->userDAO->updateUserQRHash($nickName,'', $op, '');
							break;
						}
						case SystemTrack::ADD_OPERATION : 
						{
							break;
						}
						case SystemTrack::REMOVE_OPERATION :
						{
							$user = $this->userDAO->getUser($nickName);
							$token = bin2hex(OAuthProvider::generateToken(8));
							$respUpdate = $this->userDAO->updateToken($nickName, $op, $token);
							if ($respUpdate == ErrorSystem::SUCCESS) {
								$msg  = 'Lamentamos que hayas decidido irte. Para completar la baja de tu usuario en MyCloud por favor da click en el siguiente enlace:
								'.GlobalVariables::$base_url.'/genQR.html?nickname='.$nickName.'&op=delete&token='.$token;
								$resp['errCode'] = $this->sendEmail($user->getEmail(), GlobalVariables::$delete_subject, $msg);
							} else {
								$resp['errCode'] = $respUpdate;
							}

							break;
						}
						case SystemTrack::UPDATE_OPERATION :
						{
							if (isset($newpasswd)) {
								$user = $this->userDAO->getUser($nickName);
								$token = bin2hex(OAuthProvider::generateToken(8));
								$respUpdate = $this->userDAO->updateToken($nickName, $op, $token);
								if ($respUpdate == ErrorSystem::SUCCESS) {
									$msg  = 'Para cambiar tu contraseña, por favor da click en el siguiente enlace:
									'.GlobalVariables::$base_url.'/genQR.html?nickname='.$nickName.'&newpasswd='.$newpasswd.'&op=changepasswd&token='.$token;
									$resp['errCode'] = $this->sendEmail($user->getEmail(), GlobalVariables::$update_subject, $msg);
								} else {
									$resp['errCode'] = $respUpdate;
								}
							} else {
								$resp['errCode'] = ErrorSystem::WEBSERVICE_METHOD_NOT_MATCH_SIGNATURE;
							}
							break;
						}
						default:
						{
							$resp['errCode'] = SystemTrack::SYSTEMTRACK_ERROR_OPERATION_NOT_DEFINE;
						}
					}
				}

			}

		}else {
			$resp['errCode'] = ErrorSystem::WEBSERVICE_METHOD_NOT_MATCH_SIGNATURE;
		}

		return $resp;
	}


	/**
	 * Loop over systemTrack table until system_track_op_state change from
	 * IN_PROGRESS_STATE_OPERATION to SUCCESS_STATE_OPERATION, or any FAILURE:
	 *  - QR_ERROR_NOT_MATCH
	 *  - FINGERPRINT_ERROR_NOT_MATCH
	 *  - CANCEL_ERROR
	 *  - CANCEL_ERROR_TIMEOUT
	 */
	public function verifyOperationAction() {
		$nickName = $this->getParameterValue('nickname');
		$op       = $this->getParameterValue('op');
		$conn     = $this->getParameterValue('conn'); /* Optional: Only used in login operation for now */

		$resp['errCode'] = -1;

		/* Validate must parameters */
		if (isset($nickName) && isset($op)) {
			/* Infinitive loop until the current operation has been done.
			 * for SUCCESS or any FAILURE. Not in PROGRESS state.
			 * If the operation has been inactive 24 hours, the system
			 * detect it and then CANCEL the operation with a TIMEOUT error,
			 * and this loop will be out. So then, 24 hours is the maximum
			 * time executing this infinitive loop
			 */
			do {
				$systemTrack = $this->userDAO->getSystemTrack($nickName, $op);
				/* If there is an error trying to get a system track for the
				 * nickName specified, then $systemTrack will be a number
				 */
				if (is_numeric($systemTrack)) {
					$resp['errCode'] = $systemTrack;
					break;
				}
				sleep(5);
			}while($systemTrack->getOperationState() == SystemTrack::IN_PROGRESS_STATE_OPERATION);

			/* If the loop go out because the operation has been SUCCESS or FAILURE
			 * then, return the errcode and clean the state related for the 
			 * current operation: op = 0, state op = 0, errCode = 0;
			 */

			if ($resp['errCode'] == -1) {
				$lastErr = $systemTrack->getErrCode();
				$resp = $this->cleanOperationAction();
				$resp['errCode'] = $lastErr;
			} else {
				if($op == SystemTrack::REMOVE_OPERATION) {
					$resp['errCode'] = SystemTrack::NONE_ERROR;
				}
			}

		}else {
			$resp['errCode'] = ErrorSystem::WEBSERVICE_METHOD_NOT_MATCH_SIGNATURE;
		}

		return $resp;
	}

	public function cleanOperationAction() {
		$nickName = $this->getParameterValue('nickname');
		$op       = $this->getParameterValue('op');
		$conn     = $this->getParameterValue('conn'); /* Optional: Only used in login operation for now */
		$token    = $this->getParameterValue('token'); /* Optional: Only used in login operation for now */


		if (isset($nickName)) {
			$token = '';
			$resp['errCode'] = $this->userDAO->cleanOperation($nickName, $op, $conn, $token);
			if ($resp['errCode'] == ErrorSystem::SUCCESS) {
				//error_log("token added: ".$token."\r\n", 3, GlobalVariables::$fileLog);
				$resp['token'] = $token;
			}
		} else {
			$resp['errCode'] = ErrorSystem::WEBSERVICE_METHOD_NOT_MATCH_SIGNATURE;
		}

		return $resp;

	}


	/**
	 * POST web service that change the state of the system, which operation is
	 * doing?: login, sing up, delete, change password
	 * @param  nickName: user nickname that is doing the operation
	 * @param  op: New operation to be set.
	 * @return 0 Operation was set
	 *         Errorcode There was an error.
	 */
	public function setOperationAction() {
		$nickName = $this->getParameterValue('nickname');
		$op       = $this->getParameterValue('op');
		$resp;

		if (isset($nickName) && isset($op)) {
			$resp['errCode'] = $this->userDAO->updateSystemOperation($nickName, $op);
		}else {
			$resp['errCode'] = ErrorSystem::WEBSERVICE_METHOD_NOT_MATCH_SIGNATURE;
		}
		return $resp;
	}

	/**
	 * Web service that validates a user.
	 * @param nickName: user nickname to validate the user and password;
	 * @param passwd: Password to be validated
	 * @param op: Current state of the system:  Which operation is be done?
	 */
	public function validateUserAction() {
		$nickName = $this->getParameterValue('nickname');
		$passwd   = $this->getParameterValue('passwd');
		$op       = $this->getParameterValue('op');

		if (isset($nickName) && isset($passwd) && isset($op)) {
			$resp['errCode'] = $this->userDAO->validateUser($nickName, $passwd, $op);
		}else {
			$resp['errCode'] = ErrorSystem::WEBSERVICE_METHOD_NOT_MATCH_SIGNATURE;
		}
		return $resp;
	}

	public function generateQRAction() {
		$nickName  = $this->getParameterValue('nickname');
		$newPasswd = $this->getParameterValue('newpasswd'); /* Optional, used in change password */
		$op        = $this->getParameterValue('op');
		$token     = $this->getParameterValue('token');


		if (isset($nickName) && isset($op) && isset($token)) {
			$resp['errCode'] = $this->userDAO->updateUserQRHash($nickName, $newPasswd, $op, $token);
		}else {
			$resp['errCode'] = ErrorSystem::WEBSERVICE_METHOD_NOT_MATCH_SIGNATURE;
		}
		return $resp;
	}

	public function validateQRAction() {
		$nickName = $this->getParameterValue('nickname');
		$op       = $this->getParameterValue('op');
		$qrHash   = rawurlencode($this->getParameterValue('qrhash'));
		$tries    = $this->getParameterValue('tries');

		/*print_r($nickName."\r\n");
		print_r($op."\r\n");
		print_r($qrHash."\r\n");
		print_r($tries."\r\n");*/

		if (isset($nickName) && isset($op) && isset($qrHash) && isset($tries)) {
			$resp['errCode'] = $this->userDAO->validateUserQR($nickName, $op, $qrHash, $tries);
		} else {
			$resp['errCode'] = ErrorSystem::WEBSERVICE_METHOD_NOT_MATCH_SIGNATURE;
		}

		return $resp;
	}

	/** 
	 * This function sign up an inactive user. 
	 */
	public function registerUserAction() {
		$nickName = $this->getParameterValue('nickname');
		$passwd   = $this->getParameterValue('passwd');
		$userName = $this->getParameterValue('username');
		$lastName = $this->getParameterValue('lastname');
		$email = $this->getParameterValue('email');
		$op    = $this->getParameterValue('op');

		if (isset($nickName) && isset($passwd) && 
		    isset($userName) && isset($lastName) && 
		    isset($email) && isset($op)) {

		    $userId = 1;
		    $lastUser = $this->userDAO->getLastUser();
		    if ($lastUser != False) {
			    $userId = $lastUser->getUserId()+1;
		    }

		    $user = new User($userId, $nickName, $userName, $lastName, $email, 0);
		    $token = $this->generateNewToken();
		    $credential =  new Credential($userId, $passwd, $token);
		    $resp['errCode'] = $this->userDAO->saveUser($user, $credential);
		    /* If we save the user, then we send email */
		    if ($resp['errCode'] == ErrorSystem::SUCCESS) {
				$message = 'Hola '.$nickName.'. Haz solicitado registrarte en MyCloud, por favor haz click en el siguiente link para confirmar:  
				    '. GlobalVariables::$base_url.'/genQR.html?nickname='.$nickName.'&op=add&token='.$token;
			    	$resp['errCode'] = $this->sendEmail($email, GlobalVariables::$signup_subject, $message);
		    }
		} else {
			$resp['errCode'] = ErrorSystem::WEBSERVICE_METHOD_NOT_MATCH_SIGNATURE; 
		}
                return $resp;
	}

	public function anyOperationInProgressForUserAction() {
		$nickName = $this->getParameterValue('nickname'); 
		if (isset($nickName)) {
			$resp['errCode'] = $this->userDAO->anyOperationInProgressForUser($nickName); 
		} else {
			$resp['errCode'] = ErrorSystem::WEBSERVICE_METHOD_NOT_MATCH_SIGNATURE;
		}
		return $resp;
	}

	public function completeOrCancelOperationAction() {
		$nickName = $this->getParameterValue('nickname'); 
		$op       = $this->getParameterValue('op');
		$err      = $this->getParameterValue('err');

		if (isset($nickName) && isset($op) && isset($err)) {
			$resp['errCode'] = $this->userDAO->completeOrCancelOp($nickName, $op, $err); 
		} else {
			$resp['errCode'] = ErrorSystem::WEBSERVICE_METHOD_NOT_MATCH_SIGNATURE;
		}
		return $resp;

	}

	public function savePublicFingerPrintKeyAction() {
		$nickName  = $this->getParameterValue('nickname');
		$op        = $this->getParameterValue('op');
		/* Decode URLEncoder.encode, get base64 encode */
		$fingerKey = rawurldecode($this->getParameterValue('fingerprintkey'));

		if (isset($nickName) && isset($op) && isset($fingerKey)) {
			$resp['errCode'] = $this->userDAO->savePublicFingerKey($nickName, $op, $fingerKey);
		}else {
			$resp['errCode'] = ErrorSystem::WEBSERVICE_METHOD_NOT_MATCH_SIGNATURE;
		}
		
		return $resp;
	}

	public function validateFingerprintDataAction()
	{
		$userName   = $this->getParameterValue('nickname');
		$op         = $this->getParameterValue('op');
		/* Decode URLEncoder.encode, get base64 encode */ 
		$fingerData = rawurldecode($this->getParameterValue('fingerprintdata'));
		$fingerSignature = rawurldecode($this->getParameterValue('fingerprintsignature'));
		$err             = $this->getParameterValue('err');	
		$numTries        = $this->getParameterValue('tries');
		$newPasswdEncrypted = $this->getParameterValue('newpasswd');
		$newPasswdDecrypt   = $this->decryptAES($newPasswdEncrypted);

		if (isset($userName) && isset($op) && isset($fingerData) 
			&& isset($fingerSignature) && isset($err) &&
			isset($numTries)) {
			$resp['errCode'] = $this->userDAO->validateFingerData($userName, $op, $newPasswdDecrypt, $fingerData, $fingerSignature, $err, $numTries);
		}else {
			$resp['errCode'] = ErrorSystem::WEBSERVICE_METHOD_NOT_MATCH_SIGNATURE; 
		}

		return $resp;
	}


	private function parseParameters($parameters) {
		$classParameters = array();

		foreach ($parameters as $key=>$value) {
			switch($key) {
				case "nickname" : 
				{
					$classParameters['nickname'] = $value;
					break;
				}
				case "username":
				{
					$classParameters['username'] = $value;
					break;
				}
				case "lastname":
				{
					$classParameters['lastname'] = $value;
					break;
				}	
				case "email":
				{
					$classParameters['email'] = $value;
					break;
				}
				case "tries":
				{
					$classParameters['tries'] = $value;
					break;
				}
				case "op":
				{
					if (strcmp($value, 'login') == 0) {
						$classParameters['op'] = SystemTrack::LOGIN_OPERATION;
					}else if (strcmp($value, 'add') == 0) {
						$classParameters['op'] = SystemTrack::ADD_OPERATION;
					}else if (strcmp($value, 'delete') == 0) {
						$classParameters['op'] = SystemTrack::REMOVE_OPERATION;
					}else if (strcmp($value, 'changepasswd') == 0) {
						$classParameters['op'] = SystemTrack::UPDATE_OPERATION;
					}
					break;
				}
				case "token":
				{
					$classParameters['token'] = $value;
					break;
				}
				case "conn":
				{
					$classParameters['conn'] = $value;
					break;
				}
				case "passwd":
				{
					$classParameters['passwd'] = $this->decryptAES($value);
					break;
				}
				case "newpasswd":
				{
					$classParameters['newpasswd'] = $value;
					break;
				}
				case "qrhash" :
				{
					$classParameters['qrhash'] = $value;
					break;
				}
				case "fingerprintkey" :
				{
					$classParameters['fingerprintkey'] = $value;
					break;
				}
				case "user_id" : 
				{
					$classParameters['user_id'] = $value;
					break;
				}
				case "lower_user_id":
				{
					$classParameters['lower_user_id'] = $value;
					break;
				}
				case "display_name":
				{
					$classParameters['display_name'] = $value;
					break;
				}
				case "password":
				{
					$classParameters['password'] = $this->decryptAES($value);
					break;
				}
				case "uid":
				{
					$classParameters['uid'] = $value;
					break;
				}
				case "fingerprintdata" :
				{
					$classParameters['fingerprintdata'] = $value;
					break;
				}
				case "fingerprintsignature":
				{
					$classParameters['fingerprintsignature'] = $value;
					break;
				}
				case "err" :
				{
					if (strcmp($value, 'success') == 0) {
						$classParameters['err'] = SystemTrack::NONE_ERROR;
					}else if(strcmp($value, 'qrnotmatch') == 0) {
						$classParameters['err'] = SystemTrack::QR_ERROR_NOT_MATCH;
					} else if(strcmp($value, 'fingerprintnotmatch') == 0) {
						$classParameters['err'] = SystemTrack::FINGERPRINT_ERROR_NOT_MATCH;
					} else if (strcmp($value, "cancel") == 0) {
						$classParameters['err'] = SystemTrack::CANCEL_ERROR;
					} else if (strcmp($value, "canceltimeout") == 0) {
						$classParameters['err'] = SystemTrack::CANCEL_ERROR_TIMEOUT;
					}
					break;
				}
				case "opcloud":
				{
					$classParameters['opcloud'] = $value;
					break;
				}

				default:
				{
					break;
				}
			}
		}

		return $classParameters;
	}

	private function getParameterValue($parameter) {
		$value = NULL;
		if (isset($this->parameters[$parameter])) {
			//if ($this->parameters[$parameter] != '') {
				$value = $this->parameters[$parameter];
			//}
		}
		return $value;
	}	

	private function sendEmail($sendTo, $subject, $message) {
		$mail = new PHPMailer(true); 
		$mail->IsSMTP(); 
		$mail->Host = GlobalVariables::$mailHost;
		$mail->SMTPAuth = True;
		$mail->Username = GlobalVariables::$fromEmail;
		$mail->Password = GlobalVariables::$passEmail;
		$mail->SMTPSecure = GlobalVariables::$smtpSecure;
		$mail->Port = GlobalVariables::$portEmail;
		$mail->SetFrom(GlobalVariables::$fromEmail, GlobalVariables::$userEmail);
		$mail->AddAddress($sendTo);
		$mail->Subject = $subject;
		$mail->Body = $message;

		if ($mail->Send()) {
			$resp = ErrorSystem::SUCCESS;
		}else {
			$resp = ErrorSystem::EMAIL_ERROR_CAN_NOT_BE_SEND;
		}

		return $resp;
	}

	private function redirectOwnCloud($nickName, $passwd) {
		$user =  $this->Owncloud_AccountsDAO->getUser($nickName);
		
		
		if ($user != False){
			$ch   = curl_init(GlobalVariables::$owncloud_base_url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			//curl_setopt($ch, CURLOPT_URL, GlobalVariables::$owncloud_base_url2);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                        curl_setopt($ch, CURLOPT_USERPWD, $nickName.":".$passwd);
			curl_setopt($ch, CURLOPT_POST,true);
                        
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                        
			//curl_setopt($ch, CURLOPT_COOKIEJAR, dirname(__FILE__)."my_cookies.txt"); 
			//curl_setopt($ch, CURLOPT_COOKIEFILE, dirname(__FILE__)."my_cookies.txt" );
			$response =curl_exec($ch);
			echo $response;
			curl_close($ch);
			exit;
		}
	}

	public function saveOwncloudDataAction(){
		$email         = $this->getParameterValue('email');
		$user_id       = $this->getParameterValue('user_id');
		$lower_user_id    = $this->getParameterValue('lower_user_id');
		$display_name  = $this->getParameterValue('display_name');
		$password      = $this->getParameterValue('password');


		/*print_r($email."\r\n");
		print_r($user_id."\r\n");
		print_r($lower_user_id."\r\n");
		print_r($display_name."\r\n");
		print_r($password."\r\n");*/

		if(isset($email) && isset($user_id) && isset($lower_user_id) && isset($display_name)){
			$home = GlobalVariables::$owncloud_path_base.$user_id;
			$id   = 1;
		    	$lastUser = $this->Owncloud_AccountsDAO->getLastUser();
		    	if ($lastUser != False) {
			    $id = $lastUser->getOc_AccountId()+1;
		    	}
			$Oc_Accounts  = new Oc_Accounts($id, $email, $user_id, $lower_user_id, $display_name, NULL, 0, "OC\User\Database",$home, 1);
			$Oc_Users     = new Oc_Users($user_id,NULL, $password); 
                        $resp['errCode'] = $this->Owncloud_AccountsDAO->saveUser($Oc_Accounts, $Oc_Users);
		}else{
			$resp['errCode'] = ErrorSystem::WEBSERVICE_METHOD_NOT_MATCH_SIGNATURE; 
		} 
                return $resp;
	}

	public function operationsUserOwncloudAction() {
		$uid = $this->getParameterValue('uid');
		$password   = $this->getParameterValue('password');
		$op       = $this->getParameterValue('op');


		/*print_r($uid."\r\n");
		print_r($password."\r\n");
		print_r($op."\r\n");*/

		if (isset($uid) && isset($password) && isset($op)) {
			/*
			**  LOGIN USER=1, RESET PASSWORD == 2, DELETE USER ==3.
 			*/
			switch($op){
				case SystemTrack::LOGIN_OPERATION:
					$resp['errCode'] = ErrorSystem::SUCCESS;
					break;
				case SystemTrack::UPDATE_OPERATION:
					$resp['errCode'] = $this->Owncloud_AccountsDAO->resetOwncloud_AccountsPassword($uid, $password);
					break;
				case SystemTrack::REMOVE_OPERATION:
					$resp['errCode'] = $this->Owncloud_AccountsDAO->delete($uid);
					break;
				default:
					$resp['errCode'] = ErrorSystem::OWNCLOUD_ERROR_OPERATION_NOT_DEFINE; 
					break;
			}
		}else {
			$resp['errCode'] = ErrorSystem::WEBSERVICE_METHOD_NOT_MATCH_SIGNATURE;
		}
		return $resp;
	}

	public function loginInOwncloudAction() 
	{
		$nickName = $this->getParameterValue('uid');
		$passwd   = $this->getParameterValue('passwd');
		$token    = $this->getParameterValue('token');

		$resp['errCode'] = ErrorSystem::SUCCESS;
		//error_log("Bienvenido ".$uid."\n", 3, GlobalVariables::$fileLog);
		$user = $this->userDAO->getUser($nickName);
		if ($user) {
			$credential = $this->userDAO->getCredential($user->getUserId());
			if ($credential) {
				$passwdEncrypted = $credential->getPasswd();
				$passwdEncrypted = substr($passwdEncrypted, 3);
				if (password_verify($passwd, $passwdEncrypted)) {
					//error_log("se ha verificado el password\n", 3, GlobalVariables::$fileLog);
					if (!is_null($token) && strcmp($token, '') != 0 
						&& strcmp($credential->getToken(), $token) == 0) {
						$this->redirectOwnCloud($nickName, $passwd);
					}else {
						$resp['errCode'] = ErrorSystem::CREDENTIAL_ERROR_TOKEN_INVALID;
					}
				}else {
					$resp['errCode'] = ErrorSystem::USER_ERROR_NOT_MATCH_PASSWD;
				}
			}else {
				$resp['errCode'] = ErrorSystem::CREDENTIAL_ERROR_NOT_EXISTS;
			}
		}else {
			$resp['errCode'] = ErrorSystem::USER_ERROR_NOT_EXISTS;
		}
		return $resp;
	}

	private function decryptAES($password) 
	{
		$key = hex2bin("bcb04b7e103a0cd8b54763051cef08bc55abe029fdebae5e1d417e2ffb2a00a3");
		$iv  = hex2bin("101112131415161718191a1b1c1d1e1f");
		$cypherDec = base64_decode($password);
		$decryptPasswd = openssl_decrypt($cypherDec, 'AES-256-CBC', $key, false, $iv);
		return $decryptPasswd;
	}

	private function generateNewToken()
	{
		$token = OAuthProvider::generateToken(8);
		return bin2hex($token);
	}

}
?>
