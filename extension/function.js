document.addEventListener('DOMContentLoaded', function () {
	chrome.tabs.getSelected(null,function(tab) { // null defaults to current window
  		var title = tab.title;
		document.getElementById("msg").value = title;
	});


	document.getElementById('share').onclick = function(){
		var api = "api here";
		var msg = document.getElementById("msg").value;
		chrome.tabs.query({'active': true, 'windowId': chrome.windows.WINDOW_ID_CURRENT},
   			function(tabs){
				url = tabs[0].url;	
				post(api,url,msg);
			});
	}
});

function post(api,url, msg){
	var req = new XMLHttpRequest();
	req.open("POST",api, true);
	req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	req.onload = function(){
			if(this.response.length > 1){
				alert(this.response);
			}
			window.close();
		};
	req.send("api=add&url=" + encodeURIComponent(url) + "&msg="+encodeURIComponent(msg));
}
