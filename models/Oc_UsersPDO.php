<?php
require_once('Oc_Users.php');
class Oc_UsersPDO{
	private $connection;

	public function __construct($connection)
	{
		$this->connection = $connection;
	}

	public function save(\Oc_Users $Oc_Users) {
		$stmt = $this->connection->prepare('INSERT INTO oc_users
			values(:uid, :display_name, :password)');
		$uid              = $Oc_Users->getOc_UsersUid();
		$display_name     = $Oc_Users->getOc_UsersDisplayName();
		$password         = $Oc_Users->getOc_UsersPassword();

		$stmt->bindParam(':uid', $uid);
		$stmt->bindParam(':display_name', $display_name);
		$passwordEncrypted = $this->hashing($password);
		$stmt->bindParam(':password', $passwordEncrypted);

		return $stmt->execute();
	}
	
	private function hashing($password){
		$number=1;
                $options = [
                	'cost' => 10,
                ];
            	$pw_hash = password_hash($password, PASSWORD_DEFAULT, $options);
            	$full_hash = $number.'|'.$pw_hash;
		return $full_hash;
	}

	public function updatePasswd($uid, $password) 
	{
		$stmt = $this->connection->prepare('UPDATE oc_users 
			SET password = :password
			WHERE uid = :uid'
			);

		$encryptedPassword = $this->hashing($password);
		$stmt->bindParam(':password', $encryptedPassword);
		$stmt->bindParam(':uid', $uid);

		return $stmt->execute();
	}

	public function delete($uid) {
		$stmt = $this->connection->prepare('delete from oc_users where uid = :uid');
		$stmt->bindParam(':uid', $uid);
		return $stmt->execute();
	}

	public function getUser($uid) {
		$stmt = $this->connection->prepare('SELECT * FROM oc_users where uid = :uid');
		$stmt->bindParam(':uid', $uid);
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_FUNC, "Oc_Users::buildFromPDO");

		if ($result != False) {
			return $result[0];
		}
		return $result;
	}
} 
?>
