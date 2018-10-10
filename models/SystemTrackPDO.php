<?php
class SystemTrackPDO 
{	
	private $connection;
	public function __construct($connection)
	{
		$this->connection = $connection;
		date_default_timezone_set('America/Mexico_City');
	}

	public function save(\SystemTrack $systemTrack)
	{
		$stmt = $this->connection->prepare('
				INSERT INTO systemTrack 
                                VALUES (:userid, :op, :opstate, :errcode, :lastUpdated)');
		$userId = $systemTrack->getUserId();
		$operation = $systemTrack->getOperation();
		$operationState = $systemTrack->getOperationState();
		$errCode = $systemTrack->getErrCode();
		$date = date('Y/m/d h:i:s');

		$stmt->bindValue(':userid', $userId, PDO::PARAM_INT);
		$stmt->bindValue(':op', $operation, PDO::PARAM_INT);
		$stmt->bindValue(':opstate', $operationState, PDO::PARAM_INT);
		$stmt->bindValue(':errcode', $errCode, PDO::PARAM_INT);
		$stmt->bindValue(':lastUpdated', $date);

		return $stmt->execute();
	}

	public function getSystemTrackByUserId($userId) {
		$stmt = $this->connection->prepare('
				SELECT * FROM systemTrack 
				WHERE system_track_userId = :userid
				');
		$stmt->bindParam(':userid', $userId);
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_FUNC, "SystemTrack::buildFromPDO"); 
		if ($result != false) {
			return $result[0];
		}
		return $result;
	}

	public function updateOperation($userId, $op, $progress)
	{
		$stmt = $this->connection->prepare('UPDATE systemTrack 
			SET system_track_op = :op, 
			system_track_op_state = :progress, 
			system_track_last_updated = :lastUpdated
			WHERE system_track_userId = :userid');

		$date = date('Y/m/d h:i:s'); 
		$stmt->bindParam(':op', $op);
		$stmt->bindParam(':progress', $progress);
		$stmt->bindParam(':userid', $userId);
		$stmt->bindParam(':lastUpdated', $date);
		return $stmt->execute();
	}

	public function updateOperationState($userId, $opState)
	{
		$stmt = $this->connection->prepare('UPDATE systemTrack 
			SET system_track_op_state = :opstate, 
			system_track_last_updated = :lastUpdated
			WHERE system_track_userId = :userid');

		$date = date('Y/m/d h:i:s'); 
		$stmt->bindParam(':opstate', $opState);
		$stmt->bindParam(':userid', $userId);
		$stmt->bindParam(':lastUpdated', $date);
		return $stmt->execute();
	}

	public function updateErrCode($userId, $errCode)
	{
		$stmt = $this->connection->prepare('UPDATE systemTrack 
			SET system_track_errCode = :errCode, 
			system_track_last_updated = :lastUpdated
			WHERE system_track_userId = :userid');

		$date = date('Y/m/d h:i:s'); 
		$stmt->bindParam(':errCode', $errCode);
		$stmt->bindParam(':userid', $userId);
		$stmt->bindParam(':lastUpdated', $date);
		return $stmt->execute();
	}

	/**
	 * Reset to clean state systemtrack for a specifi user 
	 */
	public function resetSystemTrack($userId) {
		$stmt = $this->connection->prepare('UPDATE systemTrack
			SET system_track_op = :op,
			system_track_op_state = :state,
			system_track_errCode = :errCode,
			system_track_last_updated = :lastUpdated
                        WHERE system_track_userId = :userId');

			$op = SystemTrack::NONE_OPERATION;
			$stateOp = SystemTrack::NONE_STATE_OPERATION;
			$errCode = SystemTrack::NONE_ERROR;
			$date = date('Y/m/d h:i:s'); 

			$stmt->bindParam(':op', $op);
			$stmt->bindParam(':state', $stateOp);
			$stmt->bindParam(':errCode', $errCode);
			$stmt->bindParam(':userId', $userId);
			$stmt->bindParam(':lastUpdated', $date);

			return $stmt->execute();
	}

	public function updateSystemStatusOperation($userId, $operationState, $errCode) 
	{
		$stmt = $this->connection->prepare('UPDATE systemTrack
			SET system_track_op_state = :state,
			system_track_errCode = :errCode
			WHERE system_track_userId = :userId');

		$stmt->bindParam(':state', $operationState);
		$stmt->bindParam(':errCode', $errCode);
		$stmt->bindParam(':userId', $userId);

		return $stmt->execute();
	}
}
?>
