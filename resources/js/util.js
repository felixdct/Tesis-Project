function utils_show_image_in_div(src, width, height, alt, div) {
	var img = document.createElement('img');
	img.src = src;
	img.width = width;
	img.height = height;
	img.alt = alt;

	document.getElementById(div).appendChild(img);
}

function utils_show_message_in_div(div, msg) {
	document.getElementById(div).innerHTML = msg;
}

function disable_object(id)
{
	document.getElementById(id).disable = true;
}
