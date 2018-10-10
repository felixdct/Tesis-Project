<?php
require_once('User.php');

class UserPDO 
{	
	private $connection;

	public function __construct($connection)
	{
		$this->connection = $connection;
	}

	public function save(\User $user) {
		$stmt = $this->connection->prepare('INSERT INTO users
			values(:userid, :nickname, :username, :lastname, :email, :active)');

		$userId   = $user->getUserId();
		$nickName = $user->getNickName();
		$userName = $user->getUserName();
		$lastName = $user->getLastName();
		$email    = $user->getEmail();
		$disable = User::DISABLE;

		$stmt->bindParam(':userid', $userId);
		$stmt->bindParam(':nickname', $nickName); 
		$stmt->bindParam(':username', $userName);
		$stmt->bindParam(':lastname', $lastName);
		$stmt->bindParam(':email', $email);
		$stmt->bindParam(':active', $disable);

		return $stmt->execute();
	}

	public function deleteUser($nickName) {
		$stmt = $this->connection->prepare('delete from users where users_nickName = :nickName');
		$stmt->bindParam(':nickName', $nickName);
		return $stmt->execute();
	}

	public function getUser($nickName, $active=User::ACTIVE) {
		$stmt = $this->connection->prepare('
				SELECT * FROM users WHERE users_nickName = :nickName
				and users_active = :active
				');
		$stmt->bindParam(':nickName', $nickName);
		$stmt->bindParam(':active', $active);
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_FUNC, "User::buildFromPDO");
		if ($result != False) {
			return $result[0];
		} 
		return $result;
	}

	public function getUser2($nickName) {
		$stmt = $this->connection->prepare('
				SELECT * FROM users WHERE users_nickName = :nickName');
		$stmt->bindParam(':nickName', $nickName);
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_FUNC, "User::buildFromPDO");
		if ($result != False) {
			return $result[0];
		} 
		return $result;
	}

	public function updateActive($nickName, $active)
	{
		$stmt = $this->connection->prepare('UPDATE users 
			SET users_active = :active
			WHERE users_nickName = :nickname');
		$stmt->bindParam(':active', $active);
		$stmt->bindParam(':nickname', $nickName);
		return $stmt->execute();
	}

	public function getLastUser()
	{
		$stmt = $this->connection->prepare('SELECT * FROM users ORDER BY users_userId DESC LIMIT 1');
		$stmt->execute();

		$result = $stmt->fetchAll(PDO::FETCH_FUNC, "User::buildFromPDO");
		if ($result != False) {
			return $result[0];
		} 
		return $result;
	}
}
?>
