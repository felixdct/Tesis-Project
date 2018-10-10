function handlerEventAfter(req, div, successMsg, urlRedirect)
{
	return function() {
		if (req.readyState == 4) {
			if (req.readyState == 4){
				if (req.status == 200) {
					json = req.responseText;
					jsonResp = JSON.parse(json);
					resp = jsonResp['errCode'];

					if (resp == _NONE_ERROR) {
						msg = successMsg;
					} else{
						msg = errOp[resp];
					} 

					token = jsonResp['token'];
					if (token != null){
						urlRedirect = urlRedirect.concat(token);
					}

					alert(msg);
					document.getElementById(div).innerHTML = msg;
					if (resp == _NONE_ERROR) {
						setTimeout(function(){redirect(urlRedirect)}, 2000);
					}else {
						setTimeout(function(){redirect('signin.html')}, 2000);

					}
				} else {
					document.getElementById(div).innerHTML = "Error, please try again!";
				}
			}
		}
	}
}
