<?php
require_once('UserPDO.php');
require_once('CredentialPDO.php');
require_once('SystemTrackPDO.php');
require_once('AuthenticatePDO.php');
require_once('../libs/phpqrcode.php');
require_once('../resources/config/GlobalVariables.php');
require_once('../utils/EncryptionRSA.php');


class UserDAO {
	private $connection;
	private $userPDO;
	private $credentialPDO;
	private $systemTrackPDO;
	private $owncloudAccountDAO;

	public function __construct() 
	{
		$this->connection = new AuthenticatePDO();
		$this->connection->setAttribute(
			PDO::ATTR_ERRMODE,
			PDO::ERRMODE_EXCEPTION
			);

		$this->userPDO = new UserPDO($this->connection);
		$this->credentialPDO  = new CredentialPDO($this->connection);
		$this->systemTrackPDO = new SystemTrackPDO($this->connection);
		$this->owncloudAccountDAO = new Owncloud_AccountsDAO($this->connection);
	}

	/** 
	 * User operations done with for every user.
	 * ----------------------------------------------- 
	 */	

	/**
	 * Create a new user on the database 
	 */
	public function saveUser(\User $user, \Credential $credential)
	{ 

	       	$date = date('Y/m/d h:i:s');
		/* Initialize system track : userid, 0, 0, 0 */
		$systemTrack = new SystemTrack($user->getUserId(), SystemTrack::ADD_OPERATION,
			SystemTrack::IN_PROGRESS_STATE_OPERATION, SystemTrack::NONE_ERROR, $date);
		/* Start transaction to save user, his/her credentials, and his/her systemtrack,
		 * in case something goes wrong, rollback txn
		 */
		try {
			$this->connection->beginTransaction();
			$this->userPDO->save($user);
		} catch(PDOException $exception) {
			$this->connection->rollback();
			error_log("Err : userSave {".$exception->getMessage()."}\r\n", 3, GlobalVariables::$fileLog);
			$resp = ErrorSystem::USER_ERROR_ISSUE_SAVING_USER;
			return $resp;
		}

		try {
			$this->credentialPDO->save($credential);
		} catch(PDOException $exception) {
			$this->connection->rollback();
			error_log("Err : credential {".$exception->getMessage()."}\r\n", 3, GlobalVariables::$fileLog);
			$resp = ErrorSystem::USER_ERROR_ISSUE_SAVING_USER; 
			return $resp;
		}

		try {
			$this->systemTrackPDO->save($systemTrack);
			$this->connection->commit();
			$resp = SystemTrack::NONE_ERROR;
		} catch(PDOException $exception) {
			$this->connection->rollback();
			error_log("Err : systemTrackSave {".$exception->getMessage()."}\r\n", 3, GlobalVariables::$fileLog);
			$resp = ErrorSystem::USER_ERROR_ISSUE_SAVING_USER; 
			return $resp;
		}


		return $resp;

	}


	public function getUser($user_nickName) {
		return $this->userPDO->getUser($user_nickName);
	}

	public function getCredential($userId)
	{
		return $this->credentialPDO->getCredentialByUserId($userId);
	}

	public function deleteUser($user_nickName, $op) {
		/* Validate that we are doing the same operation */
		$resp =  $this->validateSystemOperation($user_nickName, $op);
		if ($resp == ErrorSystem::SUCCESS) {
			try {
				$deleteUserResult = $this->userPDO->deleteUser($user_nickName);
				if ($deleteUserResult == True) {
					$resp = ErrorSystem::SUCCESS;
				} else {
					error_log("Err : deleting user\r\n", 3, GlobalVariables::$fileLog);
					$resp = ErrorSystem::USER_ERROR_DELETING;
				}
			}catch(PDOException $exception) {
				error_log("Err : deleting user {".$exception->getMessage()."}\r\n", 3, GlobalVariables::$fileLog);
				$resp = ErrorSystem::USER_ERROR_DELETING;
			}
		}
		return $resp;
	}
	
	/**
	 * Validate user and password
	 */
	public function validateUser($user_nickName, $passwd, $op)
	{
		/* Validate the we are doing the same operation */
		$resp = $this->validateSystemOperation($user_nickName, $op);
		if ($resp == ErrorSystem::SUCCESS) {
			/* Get user id from the nickname */
			$user = $this->userPDO->getUser($user_nickName);
			if ($user != False) {
				/* Get credentials for user id */
				$credential = $this->credentialPDO->getCredentialByUserId($user->getUserId());
				if ($credential != False) {
					/* Compare passwd specified with 
				         * passwd saved in database for user id */
                                        $prefix=3;
                                        $password = substr($credential->getPasswd(), $prefix);
                                        $verify=password_verify($passwd, $password);
					if($verify != false){
						$resp = ErrorSystem::SUCCESS;
					} else {
						$resp = ErrorSystem::USER_ERROR_NOT_MATCH_PASSWD;
					}
				} else {
					$resp = ErrorSystem::CREDENTIAL_ERROR_NOT_EXISTS;
				}
			}else {
				$resp = ErrorSystem::USER_ERROR_NOT_EXISTS;
			}
		}

		return $resp;
	}	

	/**
	 * Update the active state of a user
	 */
	public function updateUserActive($user_nickName, $op, $newActiveFlag) {
		/* Validate the we are doing the same operation */
		$resp = $this->validateSystemOperation($user_nickName, $op);
		if ($resp == ErrorSystem::SUCCESS) {
			/* Get user id from the nickname */
			$user = $this->userPDO->getUser($user_nickName);
			if ($user != False) {
				/* change active of a user */
				$updateActiveResp = $this->userPDO->updateActive($user->getNickName(), $newActiveFlag);
				if ($updateActiveResp == True) {
					$resp = ErrorSystem::SUCCESS;
				} else {
					$resp = ErrorSystem::USER_ERROR_UPDATING_ACTIVE;
				}
			}else {
				$resp = ErrorSystem::USER_ERROR_NOT_EXISTS;
			}
		}

		return $resp;

	}

	/** 
	 * Credential operations done for every user.
	 * ----------------------------------------------
	 */

	/**
	 * Resetting user password 
	 */
	public function resetUserPasswd($user_nickName, $op, $newPasswd) {
		/* Validate that we are doing the same operation */
		$resp = $this->validateSystemOperation($user_nickName, $op);
		if ($resp == ErrorSystem::SUCCESS) {
			/* Get user id from the nickname */
			$user = $this->userPDO->getUser($user_nickName);
			if ($user != False) {
				/* Reset user password */
				$respUpdate = $this->credentialPDO->updatePasswd($user->getUserId(), $newPasswd);
				if ($respUpdate == True) {
					$resp = ErrorSystem::SUCCESS;
				}else {
					$resp = ErrorSystem::CREDENTIAL_ERROR_NOT_UDPATING_PASSWD; 
				}
			}else {
				$resp = ErrorSystem::USER_ERROR_NOT_EXISTS;
			}

		}
		return $resp;
	} 

	/**
	 * Update fingerPrint user
	 */
	public function updateUserFingerPrint($user_nickName, $op, $newFingerPrint)
	{
		/* Validate that we are doing the same operation */
		$resp = $this->validateSystemOperation($user_nickName, $op);
		if ($resp == ErrorSystem::SUCCESS) {
			/* Get user id from the nickname */
			$user = $this->userPDO->getUser($user_nickName);
			if ($user != False) {
				/* Reset user fingerprint */
				$respUpdate = $this->credentialPDO->updateFingerPrint($user->getUserId(), $newFingerPrint);
				if ($respUpdate == True) {
					$resp = ErrorSystem::SUCCESS;
				}else {
					$resp = ErrorSystem::CREDENTIAL_ERROR_NOT_UDPATING_FINGERPRINT; 
				}
			}else {
				$resp = ErrorSystem::USER_ERROR_NOT_EXISTS;
			}

		}
		return $resp;

	}

	public function updateUserQRHash($user_nickName, $newPasswd, $op, $token) {
		/* Validate that we are doing the same operation */
		$resp = $this->validateSystemOperation($user_nickName, $op);
		if ($resp == ErrorSystem::SUCCESS) {
			/* Get user id from the nickname */
			if ($op == SystemTrack::ADD_OPERATION) {
				$active = User::DISABLE;
				$user = $this->userPDO->getUser($user_nickName, $active);
			} else {
				$user = $this->userPDO->getUser($user_nickName);
			}
			if ($user != False) {
				$credential = $this->credentialPDO->getCredentialByUserId($user->getUserId());
				if ($credential != false) {
					if ($op == SystemTrack::LOGIN_OPERATION || (strcmp($token, '') != 0 && strcmp($credential->getToken(), $token) == 0)) {
						/* Set QR text without encrypt */
						if (!is_null($newPasswd) && !empty($newPasswd)) {
							$newQRHash = $user_nickName.' '.$op.' '.$newPasswd.' '.time();
						}else {
							$newQRHash = $user_nickName.' '.$op.' '.time();
						}
						$newQRHash = utf8_encode($newQRHash);

						/* Create public and private keys if they do not exist */
						if (EncryptionRSA::privateKeyFileExists() != True) {
							EncryptionRSA::generateKeys();
						}

						/* Get public key */
						$publicKey  = openssl_pkey_get_public(file_get_contents(GlobalVariables::$publicKeyURI));
						/* Encrypt QR text */
						$newQRHashEncrypted = EncryptionRSA::encode($newQRHash, $publicKey);
						$newQRHashEncrypted = base64_encode($newQRHashEncrypted);

						if ($newQRHashEncrypted != False) {
							$conn = 1;

							$credentialInDb = $this->credentialPDO->getCredentialByUserId($user->getUserId());

							if ($credentialInDb != False) {
								$qrHashInDb = $credentialInDb->getQrHash();
								$qrHashEncryptedArray;
								/* If there is not a previous qrHashInDb */
								if ($qrHashInDb == null || $qrHashInDb == '') {
									$newArrayQr = array(strval($conn) => rawurlencode($newQRHashEncrypted));
									$qrHashEncryptedArray = array('qrHash' => $newArrayQr);
								} else {
									$qrHashEncryptedArray = json_decode($qrHashInDb, true);
									$arrayQr = $qrHashEncryptedArray['qrHash'];
									$conn = count($arrayQr) + 1;
									$arrayQr[strval($conn)] = /*utf8_encode*/rawurlencode($newQRHashEncrypted);
									$qrHashEncryptedArray['qrHash'] = $arrayQr;
								}
								/* Sets QR image */
								$errorCorrectionLevel = 'H';
								$matrixPointSize = 4;
								$fileName = $user_nickName.$op.$conn.'.png';
								$fileNamePath = GlobalVariables::$tmpQRDirectory."/".$fileName;

								/* Creates QR png */
								QRcode::png($newQRHashEncrypted, $fileNamePath, $errorCorrectionLevel, $matrixPointSize, 2);

								$hashEncryptedJSON = json_encode($qrHashEncryptedArray, JSON_UNESCAPED_SLASHES | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

								/* update qr hash */
								$respUpdate = $this->credentialPDO->updateQRHash($user->getUserId(), 
												$hashEncryptedJSON/*$newQRHashEncrypted*/);
								if ($respUpdate == True) {
									$resp = array();
									$resp['errCode'] = 'imagesqr/'.$fileName;
								        $resp['conn']  = strval($conn);
								}else {
									$resp = ErrorSystem::CREDENTIAL_ERROR_NOT_UDPATING_QRHASH; 
								}
								
							} else {
								$resp = ErrorSystem::CREDENTIAL_ERROR_NOT_EXISTS;
							}
						} else {
							$resp = ErrorSystem::ENCRYPTION_ERROR_FAIL_ENCRYPT;
						}
					} else {
						$resp = ErrorSystem::CREDENTIAL_ERROR_TOKEN_INVALID;
					}
				}else {
					$resp = ErrorSystem::CREDENTIAL_ERROR_NOT_EXISTS;
				}
			}else {
				$resp = ErrorSystem::USER_ERROR_NOT_EXISTS;
			}
		}
		return $resp;

	}

	public function updateToken($user_nickName, $op, $token) {
		$resp = $this->validateSystemOperation($user_nickName, $op);
		if ($resp == ErrorSystem::SUCCESS) {
			$user = $this->userPDO->getUser($user_nickName);
			if ($user != False) {
				$respUpdate = $this->credentialPDO->updateToken($user->getUserId(), $token);
				if ($respUpdate == True) {
					$resp = ErrorSystem::SUCCESS;
				}else {
					$resp = ErrorSystem::CREDENTIAL_ERROR_NOT_UPDATING_TOKEN;
				}
			}else {
				$resp =  ErrorSystem::USER_ERROR_NOT_EXISTS;
			}
		}
		return $resp;
	}

	/**
	 * Validate if $QRHash match with a QRHash saved in the database for a specific
	 * user
	 */
	public function validateUserQR($user_nickName, $op, $qrHash, $tries){
		/* Validate that we are doing the same operation */
		$resp = $this->validateSystemOperation($user_nickName, $op);
		if ($resp == ErrorSystem::SUCCESS) {
			/* Get user id from the nickname */
			if ($op == SystemTrack::ADD_OPERATION){
				$user = $this->userPDO->getUser($user_nickName, User::DISABLE);
			}else {
				$user = $this->userPDO->getUser($user_nickName);
			}
			if ($user != False) {
				/* Reset user fingerprint */
				$credential = $this->credentialPDO->getCredentialByUserId($user->getUserId());
				if ($credential != False) {
					$qrHashJSON = $credential->getQrHash();
					if (!is_null($qrHashJSON) && !empty($qrHashJSON)) {
						$jsonArray = json_decode($qrHashJSON, true);
						$qrHashArray = $jsonArray['qrHash'];
						$resp = -1;
						foreach($qrHashArray as $key => $value) {
							if (strcmp($value,$qrHash)==0) {
								$resp = ErrorSystem::SUCCESS;
								break;
							}
						}

						if ($resp != ErrorSystem::SUCCESS) {
							$resp = ErrorSystem::CREDENTIAL_ERROR_NOT_MATCH_QRHASH;
						}
						/*
						if ($resp != ErrorSystem::SUCCESS) {*/
							/* If number of tries is more thna maximumTries config */
							/*if ($tries >= GlobalVariables::$maximumTries) {*/
								/* Get SystemTrack */
							/*	$systemTrack = $this->systemTrackPDO->getSystemTrackByUserId($user->getUserId());
								if ($systemTrack != False) {*/
									/* If current opertion is an add operation, then remove
									 * the register on the database, because the operation of add
									 * is incomplete, else change the state of the system operation
									 * to be current operation: NONE, state operation: NONE
									 */
								/*	if ($systemTrack->getOperation() == SystemTrack::ADD_OPERATION) {
										$this->deleteUser($user_nickName, $op);
									} else {
										$this->updateSystemOperation($user_nickName, SystemTrack::NONE_OPERATION);
									}
								} else {
									$resp = ErrorSystem::SYSTEMTRACK_ERROR_NOT_EXISTS;
								}
							}
							$resp = ErrorSystem::CREDENTIAL_ERROR_NOT_MATCH_QRHASH;
						}*/
					} else {
						$resp = ErrorSystem::CREDENTIAL_ERROR_NONE_QRHASH;
					}
				}else {
					$resp = ErrorSystem::CREDENTIAL_ERROR_NOT_EXISTS; 
				}
			}else {
				$resp = ErrorSystem::USER_ERROR_NOT_EXISTS;
			}
		}
		return $resp;
	}

        /** 
	 * SystemTrack operations done with for every user.
	 * ----------------------------------------------- 
	 */	
 
	/** 
	 * Update what is doing the system for an specific user. Updating column system_track_op in 
	 * systemtrack table.
	 */
	public function updateSystemOperation($user_nickName, $newOp) {
		/* Validate that we are doing the same operation */
		$resp = $this->validateNewSystemOperation($user_nickName, $newOp);
		if ($resp == ErrorSystem::SUCCESS) {
			/* Get user id from the nickname */
			$user = $this->userPDO->getUser($user_nickName);
			if ($user != False) {
				/* Update operation */
				$progress = SystemTrack::IN_PROGRESS_STATE_OPERATION;
				if ($newOp == SystemTrack::NONE_OPERATION) {
					$progress = SystemTrack::NONE_STATE_OPERATION;
				}
				$respUpdate = $this->systemTrackPDO->updateOperation($user->getUserId(), $newOp, $progress);
				if ($respUpdate == True) {
					$resp = ErrorSystem::SUCCESS;
				} else {
					$resp = ErrorSystem::SYSTEMTRACK_ERROR_UPDATING_OPERATION;
				}
			}else {
				$resp = ErrorSystem::USER_ERROR_NOT_EXISTS;
			}
		}

		return $resp;
	}

	/** 
	 * Update state of the current operation $op. Updating system_track_op_state in systemTrack
	 * table
	 */
	public function updateSystemOperationState($user_nickName, $op, $newOpState) {
		/* Validate the we are doing the same operation */
		$resp = $this->validateSystemOperation($user_nickName, $op);

		if ($resp == ErrorSystem::SUCCESS) {
			/* Get user id from the nickname */
			$user = $this->userPDO->getUser($user_nickName);
			if ($user != False) {
				/* Update new operation state */
				$respUpdate = $this->systemTrackPDO->updateOperationState($user->getUserId(), $newOpState);
				if ($respUpdate == True) {
					$resp = ErrorSystem::SUCCESS;
				} else {
					$resp = ErrorSystem::SYSTEMTRACK_ERROR_UPDATING_OPERATION_STATE;
				}
			}else {
				$resp = ErrorSystem::USER_ERROR_NOT_EXISTS;
			}
		}
		return $resp;
	}

	public function updateSystemError($user_nickName, $op, $newErrCode)
	{
		/* Validate the we are doing the same operation */
		$resp = $this->validateSystemOperation($user_nickName, $op);

		if ($resp == ErrorSystem::SUCCESS) {
			/* Get user id from the nickname */
			$user = $this->userPDO->getUser($user_nickName);
			if ($user != False) {
				/* Update new operation state */
				$respUpdate = $this->systemTrackPDO->updateErrCode($user->getUserId(), $newErrCode);
				if ($respUpdate == True) {
					$resp = ErrorSystem::SUCCESS;
				} else {
					$resp = ErrorSystem::SYSTEMTRACK_ERROR_UPDATING_ERROR_CODE;
				}
			}else {
				$resp = ErrorSystem::USER_ERROR_NOT_EXISTS;
			}
		}
		return $resp;

	}	

	public function cleanOperation($user_nickName, $op, $conn, &$token) {
		/* Validate the we are doing the same operation */
			$user = $this->userPDO->getUser2($user_nickName);
			try {
				if ($user != False) {
					$this->connection->beginTransaction();
					$resp = $this->systemTrackPDO->resetSystemTrack($user->getUserId());
					if ($resp != False) {
						if ($op == SystemTrack::LOGIN_OPERATION){
							$credential = $this->credentialPDO->getCredentialByUserId($user->getUserId());
							if ($credential != False) {
								$qrHashJSON = $credential->getQrHash();

								if ($qrHashJSON != null && $qrHashJSON != '') {
									$jsonArray = json_decode($qrHashJSON, true);
									$qrHashArray = $jsonArray['qrHash'];


									$newQrHashArray = array();
									foreach($qrHashArray as $key => $value) {
										if ($key != $conn) {
											$newQrHashArray[$key] = $value;
										}
									}

									if (count($newQrHashArray) == 0) {
										$respUpdate = $this->credentialPDO->updateQRHash($user->getUserId(), null);
										if ($respUpdate == True) {
											$token = bin2hex(OAuthProvider::generateToken(8));
											$respUpdate = $this->credentialPDO->updateToken($user->getUserId(), $token);
											if ($respUpdate == True) {
										   		$resp = ErrorSystem::SUCCESS;
												$this->connection->commit();
											}else {
												$resp = ErrorSystem::CREDENTIAL_ERROR_NOT_UPDATING_TOKEN;
											}
										} else {
											$resp = ErrorSystem::CREDENTIAL_ERROR_NOT_UDPATING_QRHASH;
											$this->connection->rollback();
										}
									} else {
										$newQrHashJSON = json_encode($newQrHashArray);
										$respUpdate = $this->credentialPDO->updateQRHash($user->getUserId(), $newQrHashJSON);
										if ($respUpdate == True) {
											$token = bin2hex(OAuthProvider::generateToken(8));
											$respUpdate = $this->credentialPDO->updateToken($user->getUserId(), $token);
											if ($respUpdate == True) {
												$resp = ErrorSystem::SUCCESS;
												$this->connection->commit();
											} else {
												$resp = ErrorSystem::CREDENTIAL_ERROR_NOT_UPDATING_TOKEN;
											}
										} else {
											$resp = ErrorSystem::CREDENTIAL_ERROR_NOT_UDPATING_QRHASH;
											$this->connection->rollback();
										}
									}
								} else {
									$token = bin2hex(OAuthProvider::generateToken(8));
									$respUpdate = $this->credentialPDO->updateToken($user->getUserId(), $token);
									if ($respUpdate == True) {
										$resp = ErrorSystem::SUCCESS;
										$this->connection->commit();
									} else{
										$resp = ErrorSystem::CREDENTIAL_ERROR_NOT_UPDATING_TOKEN;
									}
								}
							} else {
								$resp = ErrorSystem::CREDENTIAL_ERROR_NOT_EXISTS;
								$this->connection->rollback();
							}
							$fileName = GlobalVariables::$tmpQRDirectory."/".$user_nickName.$op.$conn.'.png';
						}else {
							$respUpdate = $this->credentialPDO->updateQRHash($user->getUserId(), null);
							if ($respUpdate == true) {
								$this->connection->commit();
								$resp = ErrorSystem::SUCCESS;
							}else {
								$this->connection->rollback();
								$resp = ErrorSystem::CREDENTIAL_ERROR_NOT_UDPATING_QRHASH;
							}

							$fileName = GlobalVariables::$tmpQRDirectory."/".$user_nickName.$op.'.png';
						}
						if (file_exists($fileName)){
							unlink($fileName);
						}
					}
				}else {
					$resp = ErrorSystem::USER_ERROR_NOT_EXISTS;
				}
			} catch(PDOException $exception) {
				$this->connection->rollback();
				error_log($exception->getMessage(), 3, GlobalVariables::$fileLog);
				$resp = ErrorSystem::CREDENTIAL_SYSTEM_TRACK_CLEANING_ERROR;
			}

		return $resp;
	}

	public function anyOperationInProgressForUser($user_nickName) 
	{
		/* Get user id from the nickname */
		$user = $this->userPDO->getUser2($user_nickName);
		if ($user != False) {
			$systemTrack = $this->systemTrackPDO->getSystemTrackByUserId($user->getUserId());
			if ($systemTrack != False) {
				if ($systemTrack->getOperation() != SystemTrack::NONE_OPERATION && $systemTrack->getOperationState() == SystemTrack::IN_PROGRESS_STATE_OPERATION) {
					$resp = ErrorSystem::SUCCESS;
				}else {
					$resp = ErrorSystem::SYSTEMTRACK_NOT_OPERATION_IN_PROGRESS;
				}
			}else {
				$resp = ErrorSystem::SYSTEMTRACK_ERROR_NOT_EXISTS;
			}
		}else {
			$resp = ErrorSystem::USER_ERROR_NOT_EXISTS;
		}

		return $resp;
	}

	/**
	 * Validates that the new operation does not have conflicts with the operation saved
	 * in the database.
	 * NOTE: We only support concurrent Login, the other operations can not be possible
	 * concurrent
	 */
	public function validateNewSystemOperation($user_nickName, $newOp) 
	{
		$resp;

		/* If $newOp is NONE_OPERATION return true because NONE_OPERATION does not cause
		 * conflict with other kind of operation
		 */
		if ($newOp == SystemTrack::NONE_OPERATION) {
			return True;
		}

		$user = $this->userPDO->getUser($user_nickName);

		if ($user != False) {
			$systemTrackInSystem = $this->systemTrackPDO->getSystemTrackByUserId($user->getUserId());
	
			/* Validate that there is a systemTrack row with the user specified */
			if ($systemTrackInSystem != False) {
				/* In CASE CURRENT $newOp is LOGIN: We validate that the operation saved in
				 * the database is the same. This is an special case, we support multiple 
				 * login operations. Other operations can not be concurrent.
				 */
				if ($newOp == SystemTrack::LOGIN_OPERATION) {	
					if ($systemTrackInSystem->getOperation() != SystemTrack::NONE_OPERATION && 
					    $systemTrackInSystem->getOperation() != $newOp) {
						$resp = ErrorSystem::SYSTEMTRACK_ERROR_OP_CONFLICT;
					} else {
						$resp = ErrorSystem::SUCCESS;
					}
				} 
				/* IN CASE CURRENT $newOp IS NO LOGIN: We validate that there is no other
				 * concurrent operation
				 */
				else if ($newOp != SystemTrack::NONE_OPERATION){
					if ($systemTrackInSystem->getOperation() != SystemTrack::NONE_OPERATION) {
						$resp = ErrorSystem::SYSTEMTRACK_ERROR_OP_CONFLICT;
					} else {
						$resp = ErrorSystem::SUCCESS;
					}
				}
				/* SUCCESS! */
			       	else {
					$resp = ErrorSystem::SUCCESS;
				}
			} else {
				$resp = ErrorSystem::SYSTEMTRACK_ERROR_NOT_EXISTS;
			}
		} else {
			$resp = ErrorSystem::USER_ERROR_NOT_EXISTS;
		}

		return $resp;
	}

	/**
	 * Validates that the current operation is the same registered on the database
	 */
	public function validateSystemOperation($user_nickName, $op) 
	{
		$resp;

		if ($op == SystemTrack::ADD_OPERATION) {
			$user = $this->userPDO->getUser($user_nickName, User::DISABLE);
		}else {
			$user = $this->userPDO->getUser($user_nickName); 
		}

		if ($user != False) {
			$systemTrackInSystem = $this->systemTrackPDO->getSystemTrackByUserId($user->getUserId());
	
			/* Validate that there is a systemTrack row with the user specified */
			if ($systemTrackInSystem != False) {
				/* SUCCESS! */
				if ($op == $systemTrackInSystem->getOperation() || $systemTrackInSystem->getOperation() == 0){
					$resp = ErrorSystem::SUCCESS;
				}
			       	else {
					$resp = ErrorSystem::SYSTEMTRACK_ERROR_DIFERENT_OP_DETECTED;
				}
			} else {
				$resp = ErrorSystem::SYSTEMTRACK_ERROR_NOT_EXISTS;
			}
		} else {
			$resp = ErrorSystem::USER_ERROR_NOT_EXISTS;
		}

		return $resp;
	}

	public function getLastUser() {
		$user = $this->userPDO->getLastUser();
		return $user;
	}

	public function getSystemTrack($user_nickName, $op) {
			$user = $this->userPDO->getUser2($user_nickName);
			if ($user != False) {
				return $this->systemTrackPDO->getSystemTrackByUserId($user->getUserId());

			} else {
				$resp = ErrorSystem::USER_ERROR_NOT_EXISTS;
			} 

		return $resp;
	} 

	public function completeOrCancelOp($user_nickName, $op, $err) {
		$user = $this->userPDO->getUser2($user_nickName);
		if ($user != False) {
			$opState = SystemTrack::SUCCESS_STATE_OPERATION;
			if ($err != SystemTrack::NONE_ERROR) {
				$opState = SystemTrack::FAIL_STATE_OPERATION;
			}

			try {
				$this->connection->beginTransaction();
				$respUpdate = $this->systemTrackPDO->updateErrCode($user->getUserId(), $err);
				if ($respUpdate == True) {
					$respUpdate = $this->systemTrackPDO->updateOperationState($user->getUserId(), $opState);
					if ($respUpdate == True) {
						$this->connection->commit();
						$resp = ErrorSystem::SUCCESS;
					}else {
						$this->connection->rollback();
						$resp = ErrorSystem::SYSTEMTRACK_ERROR_UPDATING_ERROR_CODE;
					}
				} else {
					$this->connection->rollback();
					$resp = ErrorSystem::SYSTEMTRACK_ERROR_UPDATING_OPERATION_STATE;
				}
			} catch(PDOException $exception) {
				$this->connection->rollback();
				$resp = ErrorSystem::SYSTEMTRACK_ERROR_COMPLETING_OR_CANCEL_OPERATION;
				error_log("Err : systemTrackError {".$exception->getMessage()."}\r\n", 3, GlobalVariables::$fileLog);
			}
		}else {
			$resp = ErrorSystem::USER_ERROR_NOT_EXISTS;
		}

		return $resp;
	} 

	public function savePublicFingerKey($nickName, $op, $fingerKey)
	{
		$resp =  $this->validateSystemOperation($nickName, $op);
		if ($resp == ErrorSystem::SUCCESS) {
			if ($op == SystemTrack::ADD_OPERATION) { 
				$user = $this->userPDO->getUser2($nickName);
			}else {
				$user = $this->userPDO->getUser($nickName);
			}
			if ($user != False) {
				$respUpdate = $this->credentialPDO->updateFingerPrint($user->getUserId(), $fingerKey);
				if ($respUpdate == True) {
					$resp = ErrorSystem::SUCCESS;
				} else {
					$resp = ErrorSystem::CREDENTIAL_ERROR_NOT_UDPATING_FINGERPRINT;
				}
			}else {
				$resp = ErrorSystem::USER_ERROR_NOT_EXISTS;
			}
		}

		return $resp;
	}

	public function validateFingerData($nickName, $op, $newPasswd, $fingerData, $fingerSignature, $err, $tries)
	{
		error_log("newpasswd: ".$newPasswd, 3, GlobalVariables::$fileLog);
		$resp = $this->validateSystemOperation($nickName, $op);
		if ($resp == ErrorSystem::SUCCESS) {
			if ($op == SystemTrack::ADD_OPERATION) { 
				$user = $this->userPDO->getUser2($nickName);
			}else {
				$user = $this->userPDO->getUser($nickName);
			}

			if ($user != False) {
				if ($err != SystemTrack::FINGERPRINT_ERROR_NOT_MATCH) {
					$credential = $this->credentialPDO->getCredentialByUserId($user->getUserId());
					/* Get public key in pem format */
					$publicKeyPEM = $credential->getFingerPrint();

					/* Decode message */
					$msgTextPlain = base64_decode($fingerData);

					/* Decode signature */
					$signatureStr = str_replace('-----BEGIN SIGNATURE-----', '', $fingerSignature);
					$signatureStr = str_replace('-----END SIGNATURE-----', '', $signatureStr);
					$signatureStr = base64_decode($signatureStr);

					/* Verify signature */
					$validate = openssl_verify($msgTextPlain, $signatureStr, $publicKeyPEM, OPENSSL_ALGO_SHA256);
					$respUpdate = true;

					/* Make the respective operation: LOGIN, ADD_USER, DELETE_USER, CHANGE_PASSWORD */
					if ($validate == 1) {
						try {
							$this->connection->beginTransaction();
							switch ($op) {
								case SystemTrack::LOGIN_OPERATION :
								{
									/* Login only needs to say, Yes all is ok, and finish complete without error */
									break;
								}
								case SystemTrack::ADD_OPERATION :
								{
									/* Add User needs to active the user on the user table, and then says: Yes all is ok, 
									 * and finish complete without error */
									$respUpdate = $this->userPDO->updateActive($nickName, User::ACTIVE);
									break;
								}
								case SystemTrack::REMOVE_OPERATION :
								{
									/* Remove User needs to remove the user from the user table, and then says: Yes all is ok,
									 * and finish complete without error */
									$respUpdate = $this->userPDO->deleteUser($nickName);
									$this->owncloudAccountDAO->delete($nickName);
									break;
								}
								case SystemTrack::UPDATE_OPERATION :
								{
									/* toDO: We need to get the new password to do this */
									$respUpdate = $this->credentialPDO->updatePasswd($user->getUserId(), $newPasswd);
									break;
								}
							}

							if ($respUpdate == true) {
								$respUpdate = $this->systemTrackPDO->updateSystemStatusOperation($user->getUserId(), 
								 		     SystemTrack::SUCCESS_STATE_OPERATION, SystemTrack::NONE_ERROR);
								if ($respUpdate == true){
									$resp = ErrorSystem::SUCCESS;
									$this->connection->commit();
								} else {
									$resp = ErrorSystem::CREDENTIAL_ERROR_MAKING_OPERATION;
									error_log("fingerPrint(Making final operation failed): ".$exception->getMessage(), 3, GlobalVariables::$fileLog);
									$this->connection->rollback();
								}
							} else {
								$resp = ErrorSystem::CREDENTIAL_ERROR_MAKING_OPERATION;
								error_log("fingerPrint(Making final operation failed): ".$exception->getMessage(), 3, GlobalVariables::$fileLog);
								$this->connection->rollback();
							}
						} catch (PDOException $exception) {
							$resp = ErrorSystem::CREDENTIAL_ERROR_MAKING_OPERATION;
							error_log("fingerPrint(Making final operation failed): ".$exception->getMessage(), 3, GlobalVariables::$fileLog);
							$this->connection->rollback();
						}
					} 
					/* Can not verify fingerprint, so then, if the number of tries is more or equal than the maximum number of tries, then
					 * cancel operation with a fail operation and error FingerPrint not match
					 */
					else if ($validate == 0) { 
						if ((int)$tries >= GlobalVariables::$maximumTries) {
							$this->systemTrackPDO->updateSystemStatusOperation($user->getUserId(), 
							  				SystemTrack::FAIL_STATE_OPERATION, 
											SystemTrack::FINGERPRINT_ERROR_NOT_MATCH);
						}
						$resp = ErrorSystem::CREDENTIAL_FINGERPRINT_NOT_VALIDATED;
					}
					/* If there is a problem verifying the fingerprint, then cancel the operation with a fail
					*  operation and error fingerprint not match */
					else {
						$this->systemTrackPDO->updateSystemStatusOperation($user->getUserId(), 
							  				SystemTrack::FAIL_STATE_OPERATION, 
											SystemTrack::FINGERPRINT_ERROR_NOT_MATCH);

						$resp = ErrorSystem::CREDENTIAL_FINGERPRINT_ERROR_VERIFYING;
						error_log("oppenssl_verify: publickeypem: ".
							$publicKeyPEM. " msg: ".$msgTextPlain.
							" signature: ".$signatureStr, 3, GlobalVariables::$fileLog);
					}
				} else {
					/* FingerPrint was not validated */
					if ((int)$tries >= GlobalVariables::$maximumTries) {
						$this->systemTrackPDO->updateSystemStatusOperation($user->getUserId(), 
								  		SystemTrack::FAIL_STATE_OPERATION, 
										SystemTrack::FINGERPRINT_ERROR_NOT_MATCH);

						/* Delete the row that we add previously, 
						 * a user inactive.
						*/
						if ($op == SystemTrack::ADD_OPERATION) {
							$this->userPDO->deleteUser($nickName);
						}

					}
					$resp = ErrorSystem::CREDENTIAL_FINGERPRINT_NOT_VALIDATED;

				}
			}else {
				$resp = ErrorSystem::USER_ERROR_NOT_EXISTS;
			}
		}

		return $resp;
	}

}
?>
