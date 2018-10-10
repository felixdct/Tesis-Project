<?php
class Oc_Users 
{
  	private $uid;
	private $displayname;
	private $password;

/* Constant for active column */
	const ENABLE    = 1; /*User is active*/
	const DISABLED  = 2; /*User is desabled*/
		
	public function __construct($uid, $displayname, $password)
	{
		$this->uid            = $uid;
		$this->displayname    = $displayname;
		$this->password       = $password;
	}

	public static function buildFromPDO($uid, $displayname, $password)
	{
		$user = new self($uid, $displayname, $password);
		return $user;
	}

	function setOc_UsersUid($uid){
		$this->uid = $uid;
	}
	function getOc_UsersUid(){
		return $this->uid;
	}

	function setOc_UsersDisplayName($displayname){
		$this->displayname = $displayname;
	}
	function getOc_UsersDisplayName(){
		return $this->displayname;
	}


	function setOc_UsersPassword($password){
		$this->password = $password;
	}
	function getOc_UsersPassword(){
		return $this->password;
	}
}
?>
