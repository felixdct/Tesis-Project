/* Validates that fields specified in args are not null or not empty
 * @param args:  start with index 0 and has next structure: 
 *  arg_n  --> contains field to show error message
 *  arg_m  --> contains value to be evaluate
 * where n = odd
 *       m = even
 */
function validateNotNull(...args)
{
	var argsSize = args.length;
	var resp = true;
	if (argsSize % 2 == 0) {
		for (var i = 1; i < argsSize; i+=2) {
			if (args[i] == null || args[i] == "") {
				document.getElementById(args[i-1]).innerHTML = "* campo vacio";
				resp = false;
				break;
		        }
		}
	} else {
		alert('revisa que siempre haya pares de <elementoerror, valor>');
        }
	return resp;
}

/* Clear div fields
 * @param args: contains div ids to be clean up
 */
function clearDivFields(...args) {
	var argsSize = args.length;
	for (var i= 0; i < argsSize; i++) {
		document.getElementById(args[i]).innerHTML = "";
	}
}

function validateEmail(email) 
{
    var emailCaract = new RegExp(/^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/);
    if (emailCaract.test(email) == false){ 
        alert("email incorrecto"); 
        return false;
    }
    else{
        return true;
    }		
}

function validatePassword(password) 
{
    var passwordValidate = password.length;
    if(passwordValidate < 6){
	alert("la contraseña debe tener al menos 6 caracteres");
	return false;
    }
    else{
	var passwordCaract = new RegExp(/^[a-zA-Z0-9]+[!\@#"$%&\=?¡]$/);
    	if (passwordCaract.test(password) == false){ 
        	alert("la contraseña debe tener letras, números y caracteres especiales"); 
        	return false;
    	}
    	else{
        	return true;
    	}	
    }	
    		
}

function validateEqualPasswords(passwd_value, passwd2_value){
	
	if(passwd_value == passwd2_value){
           return true;
        }
        else{
             alert("las contraseñas son diferentes");
             return false;
	     
        }
}

function cancelOpInProgress(nickName, op, err) 
{
	var cancelOpResult = true;
	resp = anyOperationInProgressForUser(nickName);
	if (resp == _SUCCESS) {
		cancelOp = confirm('Hay una operacion en progreso, deseas cancelar la operacion?');
		if (cancelOp) {
		 	resp  = completeOrCancelOperationInProgressWS(nickName, op, err);
			if (resp == _SUCCESS) {
				cancelOpResult = true;
			} else {
				cancelOpResult = false;
			}
		}else {
			cancelOpResult = false;
		}
	}

	return cancelOpResult;
}
