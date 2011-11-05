var ZFDebugLoad = window.onload;
window.onload = function(){
	if (ZFDebugLoad) {
		ZFDebugLoad();
	}
	var debugHeight = ZFDebugReadCookie('ZFDebugHeight');
	if (debugHeight) {
		debugHeight = parseInt(debugHeight, 0);
	}

	window.zfdebugHeight = debugHeight > 0 ? debugHeight : 240;

	var collapsed = ZFDebugReadCookie('ZFDebugCollapsed');
	if (collapsed) {
		ZFDebugPanel(collapsed);
	}

	document.onmousemove = function(e) {
		var event = e || window.event;
		window.zfdebugMouse = Math.max(40, Math.min(window.innerHeight, -1*(event.clientY-window.innerHeight-32)));
	}

	var ZFDebugResizeTimer = null;
	document.getElementById("ZFDebugResize").onmousedown=function(e){
		ZFDebugResize();
		ZFDebugResizeTimer = setInterval("ZFDebugResize()",50);
		return false;
	}
	document.onmouseup=function(e){
		clearTimeout(ZFDebugResizeTimer);
	}
};

function ZFDebugResize()
{
	window.zfdebugHeight = window.zfdebugMouse;
	document.cookie = "ZFDebugHeight="+window.zfdebugHeight+";expires=;path=/";
	document.getElementById("ZFDebug").style.height = window.zfdebugHeight+"px";
	document.getElementById("ZFDebug_offset").style.height = window.zfdebugHeight+"px";

	var panels = document.getElementById("ZFDebug").children;
	for (var i=0; i < document.getElementById("ZFDebug").childElementCount; i++) {
		if (panels[i].className.indexOf("ZFDebug_panel") == -1)
		continue;

		panels[i].style.height = window.zfdebugHeight-50+"px";
	}
}

var ZFDebugCurrent = null;

function ZFDebugPanel(name) {
	if (ZFDebugCurrent == name) {
		document.getElementById("ZFDebug").style.height = "32px";
		document.getElementById("ZFDebug_offset").style.height = "32px";
		ZFDebugCurrent = null;
		document.cookie = "ZFDebugCollapsed=;expires=;path=/";
	} else {
		document.getElementById("ZFDebug").style.height = window.zfdebugHeight+"px";
		document.getElementById("ZFDebug_offset").style.height = window.zfdebugHeight+"px";
		ZFDebugCurrent = name;
		document.cookie = "ZFDebugCollapsed="+name+";expires=;path=/";
	}

	var panels = document.getElementById("ZFDebug").children;
	for (var i=0; i < document.getElementById("ZFDebug").childElementCount; i++) {
		if (panels[i].className.indexOf("ZFDebug_panel") == -1)
		continue;

		if (ZFDebugCurrent && panels[i].id == name) {
			document.getElementById("ZFDebugInfo_"+name.substring(8)).className += " ZFDebug_active";
			panels[i].style.display = "block";
			panels[i].style.height = (window.zfdebugHeight-50)+"px";
		} else {
			var element = document.getElementById("ZFDebugInfo_"+panels[i].id.substring(8));
			element.className = element.className.replace("ZFDebug_active", "");
			panels[i].style.display = "none";
		}
	}
}

function ZFDebugReadCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0)
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}
