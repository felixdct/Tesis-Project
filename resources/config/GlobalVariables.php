<?php
class GlobalVariables {
	public static $fileLog = __DIR__.'/../logs/authenticate.log';
	public static $tmpQRDirectory = __DIR__.'/../tempqr';
	public static $publicKeyURI = __DIR__.'/../keys/public_key/public.key';
	public static $maximumTries = 3;

	/* Email variables */
	public static $mailHost = 'smtp.gmail.com';
	public static $fromEmail = 'felixtoledo95@gmail.com';
	public static $passEmail = 'Toledodct9594!';
	public static $userEmail = 'My CloudAuth System';
	public static $smtpSecure = 'ssl';
	public static $portEmail = 465;
	public static $smtpAuth = True;


	public static $signup_subject = 'Confirma tu cuenta en MyCloud';
	public static $delete_subject = 'Baja de MyCloud';
	public static $update_subject = 'Actualizacion de contraseña';

	public static $base_url = 'https://192.168.15.111/authenticate2';
	public static $owncloud_base_url = 'https://192.168.15.111/owncloud/index.php';
	public static $owncloud_path_base = '/home/datosowncloud/'; 
}
?>
