<?php
require_once('Oc_AccountsPDO.php');
require_once('OwncloudPDO.php');
require_once('Oc_UsersPDO.php');

class Owncloud_AccountsDAO{
	private $connection;
	private $Oc_AccountsPDO;
	public function __construct() 
	{
		$this->connection = new OwncloudPDO();
		$this->connection->setAttribute(
			PDO::ATTR_ERRMODE,
			PDO::ERRMODE_EXCEPTION
			);

		$this->Oc_AccountsPDO = new Oc_AccountsPDO($this->connection);
		$this->Oc_UsersPDO    = new Oc_UsersPDO($this->connection);
	}

	public function saveUser(\Oc_Accounts $account, \Oc_Users $user)
	{
		try {
			$this->connection->beginTransaction();
			$respSave = $this->Oc_AccountsPDO->save($account);
			if($respSave != false){
                        	$respSave = $this->Oc_UsersPDO->save($user);
				if($respSave != false){
                        		$this->connection->commit();
					$resp = ErrorSystem::SUCCESS;
				}else{
					$this->connection->rollback();
                                        $resp = ErrorSystem::OWNCLOUD_ACCOUNT_ERROR_SAVE;
				}
			}
		} catch(PDOException $exception) {
			$this->connection->rollback();
			error_log("Err : userSave {".$exception->getMessage()."}\r\n", 3, GlobalVariables::$fileLog);
			$resp = ErrorSystem::OWNCLOUD_ERROR_SAVE_USER ;
		}
		return $resp;		 
	}
	
	public function resetOwncloud_AccountsPassword($uid, $password){
		$resp;
		try{
			$this->connection->beginTransaction();
			$respUpdate = $this->Oc_UsersPDO->updatePasswd($uid, $password);
			if($respUpdate != false){
				$this->connection->commit();
				$resp = ErrorSystem::SUCCESS;
			}else {
				$this->connection->rollback();
                                $resp = ErrorSystem::OWNCLOUD_USERS_ERROR_RESETPW;
                        }
		}catch(PDOException $exception){
			error_log("Err : resetpw Owncloud {".$exception->getMessage()."}\r\n", 3, GlobalVariables::$fileLog);
                        $this->connection->rollback();
			$resp = ErrorSystem::OWNCLOUD_USERS_ERROR_RESETPW;			
		}
                return $resp;
	}	
	
	public function delete($uid) {
		$resp;

                try {
			$this->connection->beginTransaction();
			$respDelete = $this->Oc_AccountsPDO->delete($uid);
                        if ($respDelete != false) {
                        	$respDelete = $this->Oc_UsersPDO->delete($uid);
				
				if ($respDelete != false){
					$this->connection->commit();
                        		$resp = ErrorSystem::SUCCESS;
				} else {
					$this->connection->rollback();
                                        $resp = ErrorSystem::OWNCLOUD_USERS_ERROR_DELETE;
                                }
                        } else {
				$this->connection->rollback();
                                $resp = ErrorSystem::OWNCLOUD_ACCOUNT_ERROR_DELETE;
                        }
                }catch(PDOException $exception){
			error_log("Err : deleting Owncloud user {".$exception->getMessage()."}\r\n", 3, GlobalVariables::$fileLog);
                        $this->connection->rollback();
			$resp = ErrorSystem::OWNCLOUD_USERS_ERROR_DELETE_USER; 
			
		}
                return $resp;
        }

	public function getLastUser() {
		$user = $this->Oc_AccountsPDO->getLastUser();
		return $user;
	}

	public function getUser($nickName) {
		$user = $this->Oc_UsersPDO->getUser($nickName);
		return $user;
	}
}
?>
