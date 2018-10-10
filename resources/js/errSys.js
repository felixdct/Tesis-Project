const _SUCCESS = 0;
const _USER_ERROR_NOT_EXISTS = 1;
const _USER_ERROR_ISSUE_SAVING_USER = 2;
const _USER_ERROR_NOT_MATCH_PASSWD = 3;
const _USER_ERROR_UPDATING_ACTIVE = 4;
const _USER_ERROR_DELETING = 5;

const _CREDENTIAL_ERROR_NOT_EXISTS = 21;
const _CREDENTIAL_ERROR_UPDATING_PASSWD = 22;
const _CREDENTIAL_ERROR_UPDATING_FINGERPRINT = 23;
const _CREDENTIAL_ERROR_UPDATING_QRHASH = 24;
const _CREDENTIAL_ERROR_NONE_QRHASH = 25;
const _CREDENTIAL_ERROR_NOT_MATCH_QRHASH = 26;
const _CREDENTIAL_SYSTEM_TRACK_CLEANING_ERROR = 27;
const _CREDENTIAL_FINGERPRINT_NOT_VALIDATED = 28;
const _CREDENTIAL_FINGERPRINT_ERROR_VERIFYING = 29;
const _CREDENTIAL_ERROR_MAKING_OPERATION = 30; 

const _SYSTEMTRACK_ERROR_NOT_EXISTS = 31;
const _SYSTEMTRACK_ERROR_UPDATING_OPERATION = 32;
const _SYSTEMTRACK_ERROR_UPDATING_OPERATION_STATE = 33;
const _SYSTEMTRACK_ERROR_UPDATING_ERROR_CODE = 34;
const _SYSTEMTRACK_ERROR_COMPLETING_OR_CANCEL_OPERATION = 35;
const _SYSTEMTRACK_ERROR_OP_CONFLICT = 36;
const _SYSTEMTRACK_ERROR_DIFERENT_OP_DETECTED = 37;
const _SYSTEMTRACK_ERROR_OPERATION_NOT_DEFINE = 38;
const _SYSTEMTRACK_NOT_OPERATION_IN_PROGRESS = 39;

const _WEBSERVICE_CONTROLLER_NOT_FOUND = 41;
const _WEBSERVICE_METHOD_NOT_FOUND = 42;
const _WEBSERVICE_METHOD_NOT_MATCH_SIGNATURE = 43;

const _ENCRYPTION_ERROR_FAIL_ENCRYPT = 51;
const _ENCRYPTION_ERROR_FAIL_DECRYPT_GET_PRIVATE_KEY = 52;
const _ENCRYPTION_ERROR_FAIL_DECRYPT = 53;

const _EMAIL_ERROR_CAN_NOT_BE_SEND = 61;

const _OWNCLOUD_USERS_ERROR_RESETPW = 70;
const _OWNCLOUD_ERROR_SAVE_USER     = 71;
const _OWNCLOUD_ACCOUNT_ERROR_SAVE  = 72;
const _OWNCLOUD_USERS_ERROR_DELETE  = 73;
const _OWNCLOUD_ACCOUNT_ERROR_DELETE = 74;

const _CREDENTIAL_ERROR_TOKEN_INVALID = 81;
const _CREDENTIAL_ERROR_NOT_UPDATING_TOKEN = 82;

var errSystemOwn = {};
errSystemOwn[_SUCCESS] = "";
errSystemOwn[_USER_ERROR_NOT_EXISTS] = "Usuario no existe";
errSystemOwn[_USER_ERROR_ISSUE_SAVING_USER] = "El usuario ya existe";
errSystemOwn[_USER_ERROR_NOT_MATCH_PASSWD] = "Password incorrecto";
errSystemOwn[_USER_ERROR_UPDATING_ACTIVE] = "Error mientras se actualizaba el usuario";
errSystemOwn[_USER_ERROR_DELETING] = "Error mientras se eliminando usuario";
errSystemOwn[_CREDENTIAL_ERROR_NOT_EXISTS] = "Credenciales de autenticacion no existe";
errSystemOwn[_CREDENTIAL_ERROR_UPDATING_PASSWD] = "Error mientras se actualizaba el password";
errSystemOwn[_CREDENTIAL_ERROR_UPDATING_FINGERPRINT] = "Error mientras se actualizaba huella digital";
errSystemOwn[_CREDENTIAL_ERROR_UPDATING_QRHASH] = "Error mientras se actualiza el codigo QR";
errSystemOwn[_CREDENTIAL_ERROR_NONE_QRHASH] = "No hay un codigo QR registrado";
errSystemOwn[_CREDENTIAL_ERROR_NOT_MATCH_QRHASH] = "Codigo QR es diferente";
errSystemOwn[_CREDENTIAL_SYSTEM_TRACK_CLEANING_ERROR] = "Error mientras se estaba limpiando la operacion actual";
errSystemOwn[_CREDENTIAL_FINGERPRINT_NOT_VALIDATED] = "No se ha podido verificar su huella dactilar";
errSystemOwn[_CREDENTIAL_FINGERPRINT_ERROR_VERIFYING] = "Error mientras se estaba verificando su huella dactilar";
errSystemOwn[_CREDENTIAL_ERROR_MAKING_OPERATION] = "Error mientras se esta realizando la operacion, por favor contacte a soporte";
errSystemOwn[_SYSTEMTRACK_ERROR_NOT_EXISTS] = "Estado del sistema no existe";
errSystemOwn[_SYSTEMTRACK_ERROR_UPDATING_OPERATION] = "Error mientras se estaba actualizando lo que esta realizando el sistema";
errSystemOwn[_SYSTEMTRACK_ERROR_UPDATING_OPERATION_STATE] = "Error mientras se esta actualizando el estado de lo que esta realizando el sistema";
errSystemOwn[_SYSTEMTRACK_ERROR_UPDATING_ERROR_CODE] = "Error mientras se estaba registrando el error detectado";
errSystemOwn[_SYSTEMTRACK_ERROR_COMPLETING_OR_CANCEL_OPERATION] = "Error mientras se estaba completando o cancelando la operacion";
errSystemOwn[_SYSTEMTRACK_ERROR_OP_CONFLICT] = "Hay operaciones en conflicto";
errSystemOwn[_SYSTEMTRACK_ERROR_DIFERENT_OP_DETECTED] = "Hay una operacion previa detectada";
errSystemOwn[_SYSTEMTRACK_ERROR_OPERATION_NOT_DEFINE] = "No se reconoce la operacion a realizar";
errSystemOwn[_SYSTEMTRACK_NOT_OPERATION_IN_PROGRESS] = "No hay operacion en progreso";
errSystemOwn[_WEBSERVICE_CONTROLLER_NOT_FOUND] = "Controlador del webservice no detectado";
errSystemOwn[_WEBSERVICE_METHOD_NOT_FOUND] = "No se encontro el webservice";
errSystemOwn[_WEBSERVICE_METHOD_NOT_MATCH_SIGNATURE] = "Los parametros no coinciden con los que el webservice requiere";
errSystemOwn[_ENCRYPTION_ERROR_FAIL_ENCRYPT] = "Error detectado durante la encriptacion";
errSystemOwn[_ENCRYPTION_ERROR_FAIL_DECRYPT_GET_PRIVATE_KEY] = "No se pudo encontrar la llave privada";
errSystemOwn[_ENCRYPTION_ERROR_FAIL_DECRYPT] = "Error detectado durante la descriptacion";
errSystemOwn[_EMAIL_ERROR_CAN_NOT_BE_SEND] = "No se pudo enviar el correo";
errSystemOwn[_OWNCLOUD_USERS_ERROR_RESETPW] = "Hubo un error cuando se intentaba resetear el password en owncloud";
errSystemOwn[_OWNCLOUD_ERROR_SAVE_USER] = "Hubo un error cuando se intentaba guardar el usuario en owncloud";
errSystemOwn[_OWNCLOUD_ACCOUNT_ERROR_SAVE] = "Hubo un error cuando se intentaba guardar el usuario en account owncloud";
errSystemOwn[_OWNCLOUD_USERS_ERROR_DELETE] = "Hubo un error cuando se intentaba eliminar el usuario en owncloud";
errSystemOwn[_OWNCLOUD_ACCOUNT_ERROR_DELETE] = "Hubo un error cuando se intentaba eliminar el usuario en account owncloud";
errSystemOwn[_CREDENTIAL_ERROR_TOKEN_INVALID] = "Token invalido";
errSystemOwn[_CREDENTIAL_ERROR_NOT_UPDATING_TOKEN] = "Token no se ha podido actualizar";



function getErrMessage(resp)
{
	return errSystemOwn[resp];
}


const _REG_SEND_EMAIL_REGISTER = "Se ha enviado un email con instrucciones para su registro";
const _REG_SEND_EMAIL_DELETE   = "Se ha enviado un email con instrucciones para baja de su usuario";
const _REG_SEND_EMAIL_UPDATE   = "Se ha enviado un email con instrucciones para cambiar su contraseña";



/**
 * Response codes for how the system operations ends 
 */
const _NONE_ERROR = 0;   /* There was not an error. SUCCESS! */
const _QR_ERROR_NOT_MATCH = 1; /* There was an error when validating qr code after maximum number of tries */
const _FINGERPRINT_ERROR_NOT_MATCH = 2; /* There was an error when validating fingerprint after maximum number of tries */
const _CANCEL_ERROR = 3; /* There was a cancel operation: User decides to do other operation, User go out of the system, so on. */
const _CANCEL_ERROR_TIMEOUT = 4; /* There was a timeout operation, after 24 hours by default. This happens when the user stay on the page, but he/she does not continue the process to complete the operation */



var errOp = {};
errOp[_NONE_ERROR] = "";
errOp[_QR_ERROR_NOT_MATCH] = "El codigo QR no se pudo validar despues de los intentos permitidos";
errOp[_FINGERPRINT_ERROR_NOT_MATCH] = "La huella digital no se pudo validar despues de los intentos permitidos";
errOp[_CANCEL_ERROR] = "Usted ha decidido cancelar la operacion actual";
errOp[_CANCEL_ERROR_TIMEOUT] = "Despues de un tiempo se ha detectado que la actual operacion no ha sido validada";

const _OP_LOGIN_SUCCESS = "Se ha validado el usuario";
const _OP_DELETE_SUCCESS = "Se ha eliminado su usuario correctamente";
const _OP_UPDATE_SUCCESS = "Se ha actualizado la contraseña correctamente";
const _OP_ADD_SUCCESS = "Se ha registrado su usuario correctamente";

const _WAITING_FOR_LOGIN = 'Esperando por validacion de su usuario ...';
const _WAITING_FOR_REGISTER = 'Esperando por validacion de registro de usuario ...';
const _WAITING_FOR_DELETE = 'Esperando por validacion de eliminacion de usuario ...';
const _WAITING_FOR_UPDATE = 'Esperando por validacion de cambio de contraseña ...';

