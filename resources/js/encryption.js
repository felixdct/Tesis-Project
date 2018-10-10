function encryptAES(password) {
	var key = CryptoJS.enc.Hex.parse('bcb04b7e103a0cd8b54763051cef08bc55abe029fdebae5e1d417e2ffb2a00a3');
	var iv  = CryptoJS.enc.Hex.parse('101112131415161718191a1b1c1d1e1f');
	var encrypted = CryptoJS.AES.encrypt(password, key, {iv: iv, mode: CryptoJS.mode.CBC});
	var passwdBase64 = btoa(encrypted);
	return passwdBase64;
}
