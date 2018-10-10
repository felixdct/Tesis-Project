<?php
class SystemTrack implements JsonSerializable
{
	private $userId;
	private $operation;
	private $operationState;
	private $errCode;
	private $lastUpdated;

	/* Constants for $operation column */
	const NONE_OPERATION   = 0; /* There is no operation registered on the system for a 
					      * specific user.
	 				      */
	const LOGIN_OPERATION  = 1; /* There is a login operation registered on the system
					      * for a specific user.
	 				      */  
	const ADD_OPERATION    = 2; /* There is a register operation registered
						    * on the system for a specific user 
	 					    */
	const REMOVE_OPERATION = 3; /* There is a remove operation registerd
					            * on the system for a specific user 
	 					    */
	const UPDATE_OPERATION = 4; /* There is an update operation registered
	                                            * on the system for a specific user

	/* Constants for $operation state columns */
	const NONE_STATE_OPERATION          = 0; /* The current operation does not have a state assigned */
	const IN_PROGRESS_STATE_OPERATION   = 1; /* The current operation is in progress */
	const SUCCESS_STATE_OPERATION       = 2; /* The current operation has been end SUCCESFULLY */
	const FAIL_STATE_OPERATION          = 3; /* The current operation has been end FAILING */

	/* Constants for $err code columns */
	const NONE_ERROR    = 0;  /* There is no error reported on the system */
	const QR_ERROR_NOT_MATCH = 1; /* QR code has not been validated, does not match */
	const FINGERPRINT_ERROR_NOT_MATCH = 2; /* Fingerprint has not been validated, does not match */
	const CANCEL_ERROR = 3;  /* The operation has been cancel */
	const CANCEL_ERROR_TIMEOUT = 4; /* The operation has not been validated in 24 hours */

	public function __construct($userId, $operation, $operationState, $errCode, $lastUpdated)
	{
		$this->userId    = $userId;
		$this->operation = $operation;
		$this->operationState = $operationState;
		$this->errCode   = $errCode;
		$this->lastUpdated = $lastUpdated;
	}

	public static function buildFromPDO($userId, $operation, $operationState, $errCode, $lastUpdated) 
	{
		$systemTrack = new self($userId, $operation, $operationState, $errCode, $lastUpdated);
		return $systemTrack;
	}

	function setUserId($userId)
	{
		$this->userId = $userId;
	}

	function getUserId()
	{
		return $this->userId;
	}

	function setOperation($operation) 
	{
		$this->operation = $operation;
	}

	function getOperation() 
	{
		return $this->operation;
	}

	function setOperationState($operationState) 
	{
		$this->operationState = $operationState;
	}

	function getOperationState() 
	{
		return $this->operationState;
	}

	function setErrCode($errCode)
	{
		$this->errCode = $errCode;
	}

	function getErrCode()
	{
		return $this->errCode;
	}

	function setLastUpdated($lastUpdated)
	{
		$this->lastUpdated = $lastUpdated;
	}

	function getLastUpdated()
	{
		return $this->lastUpdated;
	}

	function jsonSerialize() 
	{
		return get_object_vars($this);
	}
}
?>
