<?php
class ErrorSystem {
	const SUCCESS  = 0;

	/* User errors */
	const USER_ERROR_NOT_EXISTS = 1;   /* User not exists */
	const USER_ERROR_ISSUE_SAVING_USER = 2; /* Some object: User, SystemTrack, Credential
		                                          * has not been possible to save, due maybe
							  * for some constraint
	 						  */
	const USER_ERROR_NOT_MATCH_PASSWD = 3; /* User does not match password */
	const USER_ERROR_UPDATING_ACTIVE = 4; /* A update issue while updating
							      * users_active column in
							      * users table
	 						      */
	const USER_ERROR_DELETING = 5;      /* A delete issue while deleting a row in the users table */

	/* Credential errors */
	const CREDENTIAL_ERROR_NOT_EXISTS = 21; /* Credential not exists for
	                                                        * a specific user
	 							*/	              
	const CREDENTIAL_ERROR_UPDATING_PASSWD = 22; /* A update issue while 
								     * updating credentials_passwd column
								     * in credentials table
	 							     */

	const CREDENTIAL_ERROR_UPDATING_FINGERPRINT = 23;  /* A update issue while 
								     * updating credentials_fingerprint column
								     * in credentials table
	 							     */
	const CREDENTIAL_ERROR_UPDATING_QRHASH = 24; /* A update issue while 
								     * updating credentials_qrhash column
								     * in credentials table
	 							     */
	const CREDENTIAL_ERROR_NONE_QRHASH = 25;      /* There is no QRHash */
	const CREDENTIAL_ERROR_NOT_MATCH_QRHASH  = 26; /* Not match QRHash */
	const CREDENTIAL_SYSTEM_TRACK_CLEANING_ERROR = 27; /* There was a error while cleaning columns in systemtrack and credential tables during cleaning operation */
	const CREDENTIAL_FINGERPRINT_NOT_VALIDATED = 28; /* Can not be validated the fingerprint */
	const CREDENTIAL_FINGERPRINT_ERROR_VERIFYING = 29; /* There was an error verifying fingerprint */
	const CREDENTIAL_ERROR_MAKING_OPERATION  = 30;  /* Error while making operation: login, add, delete, change */


	/* SystemTrack errors */
	const SYSTEMTRACK_ERROR_NOT_EXISTS = 31;  /* A systemtrack row was not found */
	const SYSTEMTRACK_ERROR_UPDATING_OPERATION = 32; /* A update issue while updating
								   * system_track_op column (operation column)
								   * in systemtrack table.
	 							   */
	const SYSTEMTRACK_ERROR_UPDATING_OPERATION_STATE = 33; /* A update issue while updating 
									 * system_track_op_state colun(operation state column)
									 * in systemtrack table.
	 								 */

	const SYSTEMTRACK_ERROR_UPDATING_ERROR_CODE = 34; /* A update issue while updating
								    * system_track_errCode column( error code column)
								    * in systemtrack table.
	 							    */
	const SYSTEMTRACK_ERROR_COMPLETING_OR_CANCEL_OPERATION = 35; /* A update issue while updating
	                                                            * system_track_errCode, and 
								    * system_track_opstate in 
								    * systemtrack table.
	 							    */
	const SYSTEMTRACK_ERROR_OP_CONFLICT = 36;   /* There is other operation in use that
							      * cause a conflict with the new operation
							      * that we are trying to do.
	 						    */
	const SYSTEMTRACK_ERROR_DIFERENT_OP_DETECTED = 37; /* There is other operation in use, while
							    * we are trying to do other operation 
	 						    */
	const SYSTEMTRACK_ERROR_OPERATION_NOT_DEFINE = 38; /* An operation system is not recognized */
	const SYSTEMTRACK_NOT_OPERATION_IN_PROGRESS = 39; /* There is not operation in progress */


	/**
	 * WEBSERVICE errors
	 * -----------------------------------------
	 */

	const WEBSERVICE_CONTROLLER_NOT_FOUND  = 41;   /* If a controller is not found */
	const WEBSERVICE_METHOD_NOT_FOUND = 42;      /* If a method was not found in a controller */
	const WEBSERVICE_METHOD_NOT_MATCH_SIGNATURE = 43; /* If a method is called and its parameters are not defined */

	/**
	 * Encryption errors
	 * -----------------------------------------
	 */

	const ENCRYPTION_ERROR_FAIL_ENCRYPT = 51;   /* There is a failure during encryption */
	const ENCRYPTION_ERROR_FAIL_DECRYPT_GET_PRIVATE_KEY = 52; /* There is a failure finding private key */
	const ENCRYPTION_ERROR_FAIL_DECRYPT = 53; /* There is an error decrypting */

	/**
	 * Email errors
	 * -----------------------------------------
	 */

	const EMAIL_ERROR_CAN_NOT_BE_SEND = 61; /* An email can not be send */

	const OWNCLOUD_USERS_ERROR_RESETPW = 70; /* Update password error in Owncloud */
	const OWNCLOUD_ERROR_SAVE_USER     = 71; /* Error saving user in Owncloud */
	const OWNCLOUD_ACCOUNT_ERROR_SAVE  = 72; /* Error saving user in account Owncloud */
	const OWNCLOUD_USERS_ERROR_DELETE  = 73; /* Error deleting user in owncloud */
	const OWNCLOUD_ACCOUNT_ERROR_DELETE = 74; /* Errro deleting user in account Owncloud */
	const CREDENTIAL_ERROR_TOKEN_INVALID = 81; /* Invalid token */
	const CREDENTIAL_ERROR_NOT_UPDATING_TOKEN = 82; /* Error updating Token */

}
?>
