<?php
class SystemTrackDAO 
{
	private PDO $connection;
	private $systemTrackPDO;

	public function __construct($connection = NULL)
	{
		if (is_null($connection)) {
			$this->connection = new AuthenticatePDO();
			$this->connection->setAttribute(
				PDO::ATTR_ERRMODE,
				PDO::ERRMODE_EXCEPTIOn
				);
		} else {
			$this->connection = $connection;
		}

		$this->systemTrackPDO = new SystemTrackPDO($this->connection);
	}

	/**
	 * Update what is doing the system. Updating column system_track_op in systemtrack table
	 */
	public function updateOperation(\SystemTrack $systemTrack) {
		$resp = validateOperation($systemTrack);
		if (resp == Error::$SUCCESS) {
			$respUpdate = $this->systemTrackPDO->updateOperation(
				                $systemTrack->getUserId(),
						$systemTrack->getOperation()
						);

			if ($respUpdate == True) {
				$resp == ERROR::$SUCCESS;
			}else {
				$resp == ERROR::$SYSTEMTRACK_ERROR_UPDATING_OPERATION;
			}
		}

		return $resp;
	}

	/**
	 * Validates that the operation can be done.
	 * NOTE: We only support concurrent Login, the other operations can not be possible
	 * concurrent
	 */
	public function validateOperation(\SystemTrack $systemTrack) 
	{
		$resp;
		$op = $systemTrack->getOperation();
		$systemTrackInSystem = $this->systemTrackPDO->getSystemTrackByUserId($systemTrack->getUserId());
	
		/* Validate that there is a systemTrack row with the user specified */
		if ($systemTrackInSystem != False) {
			/* In CASE CURRENT $op is LOGIN: We validate that the operation saved in
			 * the database is the same. This is an special case, we support multiple 
			 * login operations. Other operations can not be concurrent.
			 */
			if ($op == SystemTrack::$LOGIN_OPERATION) {	
			    if ($systemTrackInSystem->getUserId() != $op) {
				$resp = Error::$SYSTEMTRACK_ERROR_OP_IN_USE;
			    }
			} 
			/* IN CASE CURRENT $op IS NO LOGIN: We validate that there is no other
			 * concurrent operation
			 */
			else if ($op != SystemTrack::$NONE_OPERATION){
				$resp = Error::$SYSTEMTRACK_ERROR_OP_IN_USE;
			}
			/* SUCCESS! */
		       	else {
				$resp = Error::$SUCCESS;
			}
		} else {
			$resp = Error::$SYSTEMTRACK_ERROR_NOT_EXISTS;
		}

		return $resp;
	}

	/**
	 * Update the state of the operation that the system is already done. Updating 
	 * column system_track_op_state in systemtrack table
	 */
	public function updateOperationState(\SystemTrack systemTrack) 
	{
		$resp = validateOperation($systemTrack);
		if ($resp == Error::$SUCCESS) {

		}
		return $resp;
	}
}
?>
