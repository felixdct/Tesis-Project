<?php
require_once('Credential.php');

class CredentialPDO 
{	
	private $connection;

	public function __construct($connection)
	{
		$this->connection = $connection;
	}
	
	public function save(\Credential $credential)
	{
		$stmt =  $this->connection->prepare('INSERT INTO credentials
			values(:c_userId, :c_passwd, :token, :c_fingerprint, :c_qrhash)');

		$userId = $credential->getUserId();
		$passwd = $credential->getPasswd();
		$token  = $credential->getToken();
		$fingerPrint = $credential->getFingerPrint();
		$qrHash = $credential->getQrHash();

		$stmt->bindValue(':c_userId', $userId, PDO::PARAM_INT);
                $passwordEncrypted = $this->hashing($passwd);
		$stmt->bindValue(':c_passwd', $passwordEncrypted, PDO::PARAM_STR);
		$stmt->bindValue(':token', $token, PDO::PARAM_STR);
		$stmt->bindValue(':c_fingerprint', $fingerPrint, PDO::PARAM_LOB);
		$stmt->bindValue(':c_qrhash', $qrHash);

		return $stmt->execute();

	}
        
        private function hashing($password){
            	$pw_hash = password_hash($password, PASSWORD_DEFAULT);
            	$full_hash = 'T01'.$pw_hash;
		return $full_hash;
	}

	public function getCredentialByUserId($userId) 
	{
		$stmt = $this->connection->prepare('
			SELECT * FROM credentials where credentials_userId = :userId
                	');
		$stmt->bindParam(':userId', $userId);
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_FUNC, "Credential::buildFromPDO");
		if ($result != false) {
			return $result[0];
		}
		return $result;
	}

	
	public function updatePasswd($userId, $passwd) 
	{
		$stmt = $this->connection->prepare('UPDATE credentials 
			SET credentials_passwd = :passwd 
			WHERE credentials_userId = :userid'
			);

		$passwdEncrypted = $this->hashing($passwd); 
		$stmt->bindParam(':passwd', $passwdEncrypted);
		$stmt->bindParam(':userid', $userId);

		return $stmt->execute();
	}

	public function updateFingerPrint($userId, $fingerPrint)
	{
		$stmt = $this->connection->prepare('UPDATE credentials 
			SET credentials_fingerprint = :fingerprint 
			WHERE credentials_userId = :userid'
			);

		$stmt->bindValue(':fingerprint', $fingerPrint, PDO::PARAM_LOB);
		$stmt->bindParam(':userid', $userId);

		return $stmt->execute();

	}

	public function updateQRHash($userId, $QRHash) {
		$stmt = $this->connection->prepare('UPDATE credentials 
			SET credentials_qrhash = :qrhash 
			WHERE credentials_userId = :userid'
			);

		$stmt->bindValue(':qrhash', $QRHash, PDO::PARAM_STR);
		$stmt->bindParam(':userid', $userId);

		return $stmt->execute();

	}

	public function updateToken($userId, $token)
	{
		$stmt = $this->connection->prepare('UPDATE credentials 
			SET credentials_token = :token 
			WHERE credentials_userId = :userid'
			);

		$stmt->bindValue(':token', $token, PDO::PARAM_STR);
		$stmt->bindParam(':userid', $userId);

		return $stmt->execute();

	}
}
?>
