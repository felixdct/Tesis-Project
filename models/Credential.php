<?php
class Credential implements JsonSerializable 
{
	private $userId;
	private $passwd;
	private $token;
	private $fingerPrint;
	private $qrHash;

	public function __construct($userId, $passwd, $token) 
	{
		$this->userId      = $userId;
		$this->passwd      = $passwd;
		$this->token       = $token;
		$this->fingerPrint = "";
		$this->qrHash      = "";
	}

	public static function buildFromPDO($userId, $passwd, $token, $fingerPrint, $qrHash) {
		$credential = new self($userId, $passwd, $token);
		$credential->fingerPrint = $fingerPrint;
		$credential->qrHash = $qrHash;
		return $credential;
	}

	function setUserId($userId) 
	{
		$this->userId = $userId;
	}

	function getUserId()
	{
		return $this->userId;
	}

	function setPasswd($passwd) {
		$this->passwd = $passwd;
	}

	function getPasswd() 
	{
		return $this->passwd;
	}

	function setToken($token)
	{
		$this->token = $token;
	}

	function getToken()
	{
		return $this->token;
	}

	function setFingerPrint($fingerPrint) {
		$this->fingerPrint = $fingerPrint;
	}

	function getFingerPrint()
	{
		return $this->fingerPrint;
	}

	function setQrHash($qrHash) 
	{
		$this->qrHash = $qrHash;
	}

	function getQrHash() 
	{
		return $this->qrHash;
	}
	
	function jsonSerialize() 
	{
		return get_object_vars($this);
	}


}
?>
