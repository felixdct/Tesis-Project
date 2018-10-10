
<?php
class Oc_Accounts 
{
  	private $id;
	private $email;
	private $user_id;
	private $lower_user_id;
	private $display_name;
	private $quota;
	private $last_login;
	private $backend;
	private $home;
	private $state;

/* Constant for active column */
	const ENABLE    = 1; /*User is active*/
	const DISABLED  = 2; /*User is desabled*/
		
	public function __construct($id, $email, $user_id, $lower_user_id, $display_name, 
					$quota, $last_login, $backend, $home, $state)
	{
		$this->id              = $id;
		$this->email          	= $email;   
		$this->user_id		= $user_id;
		$this->lower_user_id   = $lower_user_id;
		$this->display_name    = $display_name;
		$this->quota		= $quota;
		$this->last_login	= $last_login; 
		$this->backend		= $backend; 
		$this->home		= $home;
		$this->state		= $state;
	}
	public static function buildFromPDO($id, $email, $user_id, $lower_user_id, $display_name, 
					$quota, $last_login, $backend, $home, $state)
	{
		$user = new self($id, $email, $user_id, $lower_user_id, $display_name, 
					$quota, $last_login, $backend, $home, $state);
		return $user;
	}

	function setOc_AccountId($id){
		$this->id = $id;
	}
	function getOc_AccountId(){
		return $this->id;
	}

	function setOc_AccountEmail($email){
		$this->email = $email;
	}
	function getOc_AccountEmail(){
		return $this->email;
	}

	function setOc_AccountUserId($user_id){
		$this->user_id = $user_id;
	}
	function getoc_Accountuserid(){
		return $this->user_id;
	}

	function setOc_AccountLowerUserId($lower_user_id){
		$this->lower_user_id = $lower_user_id;	
	}
	function getOc_AccountLowerUserId(){
		return $this->lower_user_id;
	}

	function setOc_AccountDisplayName($display_name){
		$this->display_name = $display_name;
	}
	function getOc_AccountDisplayName(){
		return $this->display_name;
	}

	function setOc_AccountQuota($quota){
		$this->quota = $quota;
	}
	function getOc_AccountQuota(){
		return $this->quota;
	}

	function setOc_AccountLastLogin($last_login){
		$this->last_login = $last_login;
	}
	function getOc_AccountLastLogin(){
		return $this->last_login;
	}

	function setOc_AccountBackEnd($backend){
		$this->backend = $backend;
	}
	function getOc_AccountBackEnd(){
		return $this->backend;
	}

	function setOc_AccountHome($home){
		$this->home = $home;
	}
	function getOc_AccountHome(){	
		return $this->home;
	}

	function setOc_AccountState($state){
		$this->state = $state;
	}
	function getOc_AccountState(){
		return $this->state;	
	}

}
?>
