function validateUserGenQR(nickName, passwd, op)
{
	var params = 'controller=User&format=json&method=validateUserGenQR&nickname='+nickName+'&passwd='+passwd+'&op='+op;
	resp = makeSyncRequest('POST', 'controller.php', params);
	return resp;
}

function validateUserGenQRAndNewPasswd(nickName, passwd, newPasswd, op)
{
	var params = 'controller=User&format=json&method=validateUserGenQR&nickname='+nickName+'&passwd='+passwd+'&newpasswd='+newPasswd+'&op='+op;
	resp = makeSyncRequest('POST', 'controller.php', params);
	return resp;
}


function waitForFinishOp(nickName, op, conn, event_func, divMsg, successMsg, urlRedirect) 
{
	var params = 'controller=User&format=json&method=verifyOperation&nickname='+nickName+'&conn='+conn+'&op='+op;
	resp = makeASyncRequest('POST', 'controller.php', params, event_func, divMsg, successMsg, urlRedirect);
	return resp;
}

/* WS that set up the current operation on the system:
 * login, add user, remove user, change password 
 */
function updateSystemStateOpWS(nickName, op) 
{
	var params = 'controller=User&format=json&method=setOperation&nickname='+nickName+'&op='+op;
	resp = makeSyncRequest('POST', 'controller.php', params);
	return resp;
}

/**
 * WS that validate the user and passwd
 */
function validateUserWS(nickName, passwd, op)
{
	var params = 'controller=User&format=json&method=validateUser&nickname='+nickName+'&passwd='+passwd+'&op='+op;
	resp = makeSyncRequest('GET', 'controller.php', params);
	return resp;
}

/**
 * WS generates a QR code encrypted in base of the user and the operation 
 * that the system is being done, and save the qr code in the database
 */ 
function genQRWS(nickName, newPasswd, op, token) 
{
	var params = 'controller=User&format=json&method=generateQR&nickname='+nickName+'&newpasswd='+newPasswd+'&op='+op+'&token='+token;
	resp = makeSyncRequest('GET', 'controller.php', params);
	return resp;
}

/**
 * WS that validates that the QR code that is begin read match with the QR code in the database.
 * Compare two QR codes: QR code read, QR code in database.
 */
function validateQRWS(nickName, qrHash, tries, op)
{
	var params = 'controller=User&format=json&method=validateQR&nickname='+nickName+'&op='+op+'&qrhash='+qrHash+"&tries="+tries;
	resp = makeSyncRequest('GET', 'controller.php', params);
	return resp;

}


function registerUserWS(nickName, userName, passwd, lastName, email, op) {
	var params = 'controller=User&format=json&method=registerUser&nickname='+ nickName + '&username='+ userName + 
		'&passwd=' + passwd + '&lastname='+lastName+'&email='+email+'&op='+op;
	resp = makeSyncRequest('POST', 'controller.php', params);
	return resp;
}

function anyOperationInProgressForUser(nickName)
{
	var params = 'controller=User&format=json&method=anyOperationInProgressForUser&nickname='+ nickName;
	resp = makeSyncRequest('POST', 'controller.php', params);
	return resp;
}

function completeOrCancelOperationInProgressWS(nickName, op, err) {
	var params = 'controller=User&format=json&method=completeOrCancelOperation&nickname='+ nickName+'&op='+op+'&err='+err;
	resp = makeSyncRequest('POST', 'controller.php', params);
	return resp;
}
function saveOwncloudData(email,user_id, userName, lastName, password){
	var display_name = userName;
	display_name += lastName; //full name
	var lower_user_id = user_id.toLowerCase();	
	var params = 'controller=User&format=json&method=saveOwncloudData&email='+email+'&user_id='+user_id+'&lower_user_id='+lower_user_id+'&display_name='+display_name+'&password='+password;
	
	resp = makeSyncRequest('POST', 'controller.php', params);
	return resp;	
}


function operationsUserOwncloud(uid, password,op){
	var params = 'controller=User&format=json&method=operationsUserOwncloud&uid='+uid+'&password='+password+'&op='+op;
	resp = makeSyncRequest('POST', 'controller.php', params);
	return resp;
}

function loginInOwncloud(uid, passwd, token)
{
	var params = 'controller=User&format=json&method=loginInOwncloud&uid='+uid+'&passwd='+passwd+'&token='+$token;
	makeSyncRequest('POST', 'controller.php', params);
}
