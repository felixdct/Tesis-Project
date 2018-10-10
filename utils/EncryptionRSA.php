<?php
require_once('../models/ErrorSystem.php');
require_once('../resources/config/GlobalVariables.php');

class EncryptionRSA {
	private static $privateKeyURI = '../resources/keys/private_key/private.key';

	public static function generateKeys() {
		$privateKey = openssl_pkey_new(array(
			'private_key_bits' => 2048, /* Key of size */ 
			'private_key_type' => OPENSSL_KEYTYPE_RSA)
			);

		/* Save private key in private.key. Never share this file */
		openssl_pkey_export_to_file($privateKey, self::$privateKeyURI); 
		/* Generate public key for private key */
		$publicKey = openssl_pkey_get_details($privateKey);
		/* Save public key */
		file_put_contents(GlobalVariables::$publicKeyURI, $publicKey['key']);
		/* Free private key */
		openssl_free_key($privateKey);
	}

	public static function privateKeyFileExists() {
		return file_exists(self::$privateKeyURI);
	}

	public static function encode($plainText, $publicKey){
		if ($publicKey == null || $publicKey == '') {
			return False;
		}

		if (file_exists(self::$privateKeyURI) != True) {
			generateKeys();
			/* Get public key */
			$publicKey = openssl_pkey_get_public(file_get_contents(GlobalVariables::$publicKeyURI));
		}

		if ($publicKey == False) {
			error_log(ErrorSystem::ENCRYPTION_ERROR_FAIL_ENCRYPT, 3, $fileLog);
			return False;
		}

		$resp = ''; 

		/* Encrypt $plainText with $publicKey */
		if (!openssl_public_encrypt($plainText, $encrypted, $publicKey)) {
			/* Write to log file : authenticate.log */
			error_log(ErrorSystem::ENCRYPTION_ERROR_FAIL_ENCRYPT, 3, $fileLog);
		}

		/* Free public key and return text encrypted */
		$resp .= $encrypted;
		openssl_free_key($publicKey);
		return $resp;
	}

	public static function decode($encryptedText) {
		if (file_exists(self::$privateKeyURI) != True) {
			return False;
		}

		/* Get private key */
		$privateKey = openssl_pkey_get_private(self::$privateKeyURI);

		/* Write to log file: authenticate.log */
		if (!$privateKey) {
			/* Write to Log file: authenticate.log */ 
			error_log(ErrorSystem::ENCRYPTION_ERROR_FAIL_DECRYPT_GET_PRIVATE_KEY, 3, $fileLog);
		}
		if (!openssl_private_decrypt($encryptedText, $decrypted, $privateKey)) {
			/* Write to Log file: authenticate.log */ 
			error_log(ErrorSystem::ENCRYPTION_ERROR_FAIL_DECRYPT, 3, $fileLog);
		}
		$resp .= $decrypted;
		openssl_free_key($privateKey);
		return $resp;

	}
}
?>
