<?php
class User implements JsonSerializable 
{
	private $userId;
	private $nickName;
	private $userName;
	private $lastName;
	private $email;
	private $active;

        /* Constant for active column */
	const ACTIVE = 1; /* User is actived */
	const DISABLE = 0; /* User is not actived */
	
	public function __construct($userId, $nickName, $userName, $lastName, $email, $active)
	{
		$this->userId      = $userId;
		$this->nickName    = $nickName;
		$this->userName    = $userName;
		$this->lastName    = $lastName;
		$this->email       = $email;
		$this->active      = $active;
	}

	public static function buildFromPDO($userId, $nickName, $userName, $lastName, $email, $active)
	{
		$user = new self($userId, $nickName, $userName, $lastName, $email, $active);
		return $user;
	}


	function setUserId($userId) 
	{
		$this->userId = $userId;
	} 

	function getUserId() 
	{
		return $this->userId;
	} 
		
	function setNickName($nickName) 
	{
		$this->nickName = $nickName;
	}

	function getNickName() 
	{
		return $this->nickName;
	}

	function setUserName($userName)
	{
		$this->userName = $userName;
	}

	function getUserName()
	{
		return $this->userName;
	}

	function setLastName($lastName)
	{
		$this->lastName = $lastName;
	}

	function getLastName()
	{
		return $this->lastName;
	}
	
	function setEmail($email) 
	{
		$this->email = $email;
	}

	function getEmail() 
	{
		return $this->email;
	}

	function setActive($active) 
	{
		$this->active = $active;
	}

	function getActive()
	{
		return $this->active;
	}

	function jsonSerialize() 
	{
		return get_object_vars($this);
	}
}
?>
