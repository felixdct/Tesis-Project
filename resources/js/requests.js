/* Redirect to other htmlPage */
function redirect(htmlPage) 
{
	window.location=htmlPage;
}

function makeSyncRequest(method, page, params) {
	var req = new XMLHttpRequest();
	resp = '';
	try {
		if (req != null) {
			if (method == 'POST') {
				req.open(method, page, false);
				req.setRequestHeader("Content-type", "application/x-www-form-urlencoded"); 
				req.send(params);
			}else {
				req.open(method, page+"?"+params, false);
				req.send(null);
			}

			var jsonResp = new Array();

			if (req.status === 200) {
				json = req.responseText;
				jsonResp = JSON.parse(json);
			}else {
				jsonResp['errCode'] = -1;
			}

			resp = jsonResp['errCode'];
		}
	} catch(e) {	
		alert(e.toString());
	}

	return resp;
}

function makeASyncRequest(method, page, params, event_func, divMsg, successMsg, urlRedirect) {
	var req = new XMLHttpRequest();
	try {
		if (req != null) {
			req.onreadystatechange = event_func(req, divMsg, successMsg, urlRedirect);
			if (method == 'POST') {
				req.open(method, page, true);
				req.setRequestHeader("Content-type", "application/x-www-form-urlencoded"); 
				req.send(params);
			}else {
				req.open(method, page+"?"+params, true);
				req.send(null);
			}
		}
	} catch(e) {	
		alert(e.toString());
	}

	return resp;
}
