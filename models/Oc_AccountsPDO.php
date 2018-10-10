<?php
require_once('Oc_Accounts.php');
class Oc_AccountsPDO{
	private $connection;

	public function __construct($connection)
	{
		$this->connection = $connection;
	}

	public function save(\Oc_Accounts $Oc_Accounts) {
		$stmt = $this->connection->prepare('INSERT INTO oc_accounts (id, email, user_id, lower_user_id, display_name, quota, last_login, backend, home, state)
			values(:id, :email, :user_id, :lower_user_id, :display_name, :quota, :last_login, :backend, :home, :state)');
		$id              = $Oc_Accounts->getOc_AccountId();
		$email           = $Oc_Accounts->getOc_AccountEmail();  
		$user_id         = $Oc_Accounts->getOc_AccountUserId(); 
		$lower_user_id   = $Oc_Accounts->getOc_AccountLowerUserId();
		$display_name    = $Oc_Accounts->getOc_AccountDisplayName();
		$quota           = $Oc_Accounts->getOc_AccountQuota();
		$last_login      = $Oc_Accounts->getOc_AccountLastLogin();
		$backend         = $Oc_Accounts->getOc_AccountBackEnd();
		$home            = $Oc_Accounts->getOc_AccountHome();
		$state           = Oc_Accounts::ENABLE;

		$stmt->bindParam(':id', $id);
		$stmt->bindParam(':email', $email); 
		$stmt->bindParam(':user_id', $user_id);
		$stmt->bindParam(':lower_user_id', $lower_user_id);
		$stmt->bindParam(':display_name', $display_name);
		$stmt->bindParam(':quota', $quota);
		$stmt->bindParam(':last_login', $last_login);
		$stmt->bindParam(':backend', $backend);
		$stmt->bindParam(':home', $home);
		$stmt->bindParam(':state', $state);

		return $stmt->execute();
	}

	public function delete($uid) {
		$stmt = $this->connection->prepare('delete from oc_accounts where user_id = :uid');
		$stmt->bindParam(':uid', $uid);
		return $stmt->execute();
	}

	public function getLastUser()
	{
		$stmt = $this->connection->prepare('SELECT * FROM oc_accounts ORDER BY id DESC LIMIT 1');
		$stmt->execute();

		$result = $stmt->fetchAll(PDO::FETCH_FUNC, "Oc_Accounts::buildFromPDO");
		if ($result != False) {
			return $result[0];
		} 
		return $result;
	}

	public function getUser($nickName){
		$stmt = $this->connection->prepare('SELECT * FROm oc_accounts WHERE user_id = :userId');
		$stmt->bindParam(':userId', $nickName);
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_FUNC, "Oc_Accounts::buildFromPDO");

		if ($result != False) {
			return $result[0];
		}
		return $result;
	}

} 
?>
