
chrome.tabs.query({'active': true, 'windowId': chrome.windows.WINDOW_ID_CURRENT},
   function(tabs){
	url = tabs[0].url;
});

document.addEventListener('DOMContentLoaded', function () {
	document.getElementById('share').onclick = function(){
		var backend = "here backend adress";
		var msg = document.getElementById("msg").value;
		var apiurl = backend + "/?api=add&url=" + encodeURIComponent(url) + "&msg="+msg;
        	var xhReq = new XMLHttpRequest();
        	xhReq.open("POST",apiurl.split("?")[0], false);
        	xhReq.setRequestHeader("Content-type","application/x-www-form-urlencoded");
        	xhReq.send(apiurl.substr(apiurl.indexOf("?")+1));
       		var serverResponse = xhReq.responseText;
		window.close();
	}
});



