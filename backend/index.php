<?php
$userdata = "./userdata/";
$api = $_REQUEST["api"];
if($api == "add"){
	if(isset($_REQUEST["url"]) && isset($_REQUEST["msg"])){
		
		$url = htmlspecialchars($_REQUEST["url"]);
		if(empty($_COOKIE["privateid"])){
			die("You are not logged in");	
		}
		$parts = explode("_",$_COOKIE["privateid"]);
		$id = $parts[0];
		if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
    			die('Not a valid URL');
		}

		$msg = htmlspecialchars($_REQUEST["msg"]);
		if (preg_match('/[^A-Za-z0-9]/', $id)){
			die("Username only digits and letters");
		}
	
		if (preg_match('/[^A-Za-z0-9]/', $parts[1])){
			die("Corrupt hash");
		}

		$str =  json_encode(array("url" => "$url", "msg" => "$msg"));
		$privateid = $id."_".$parts[1];
		if (!file_exists($userdata.$privateid)) {
			die("Wrong credentials");
		}
		file_put_contents($userdata.$privateid,$str."\n", FILE_APPEND);
	}
}elseif($api == "get"){
	if(isset($_REQUEST["id"])){
		$id = $_REQUEST["id"];
		$path = $userdata.$id."_"; //risky!
		$path = glob("$path*");
		if($path == null){
			die("User doesn't exist");
		}
		$privatefile = file($path[0]);
		$count = count($privatefile);
		$c = 1;
	
		if(isset($_REQUEST["jsonp"]) && !empty($_REQUEST["jsonp"])){
			echo $_REQUEST["jsonp"].'({ "shares" : [';
			foreach($privatefile as $line) {
				echo $line;
				if($c < $count){	
					echo ",";
				}
				$c++;
			}
			echo "]})";
		}elseif($_REQUEST["format"] == "rss"){
			$xml = new SimpleXMLElement('<rss/>');
			$xml->addAttribute("version", "2.0");
			$channel = $xml->addChild("channel");
			$channel->addChild("title", "RSS feed of $id");
			$channel->addChild("description", "Created with ryfts");
			foreach ($privatefile as $line) {
    				$obj = json_decode($line);
				$item = $channel->addChild("item"); 
    				$item->addChild("title", $id.": ".$obj->{"msg"});
    				$item->addChild("link", $obj->{"url"});
    				$item->addChild("description", $id." recommends, ".$obj->{"url"});
				
			}
			header('Content-type: text/xml');
			echo $xml->asXML();
		}
	}
}elseif($api == "register" || $api == "login"){
	if(isset($_REQUEST["id"]) && isset($_REQUEST["pass"])){
		$id = $_REQUEST["id"];
		if (preg_match('/[^A-Za-z0-9]/', $id)){
 			die("Username only digits and letters");
		}	

		$pass = $_REQUEST["pass"];
		$privateid = $id."_".md5($id.$pass);

		if($api == "register"){
			if(glob("$userdata$id*") == null){
				echo "Registration successful!<br>";
				echo "Your public id is: $id (give this to your friends so they can follow you)<br>";
				file_put_contents($userdata.$privateid,"", FILE_APPEND);
				chmod($userdata.$privateid, 0777);		
			}else{
				echo "Username $id is already taken";
			}
		}else{
			if(file_exists($userdata.$privateid)){
				setcookie("privateid", $privateid, time()+60*60*24*30*100);
				echo "You are now logged in";
			}else{
				echo "Wrong credentials";
			}
		}

		
	}
}else{

	echo "Hello ".$_COOKIE["privateid"]."  (COOKIE)<br><br>";
	echo ". Make a request with ?api=register&id=NAME&pass=PASSWORD to register an account<br>";
	echo ". If you already have an account you can login with /?api=login&id=NAME&pass=PASSWORD<br>";
	echo ". You can get the rss of an user making a request with /?api=get&id=NAME&format=rss<br>";
	echo ". Alternatively you can get a jsonp response with /?api=get&id=NAME&jsonp=CALLBACK<br>"; 
	echo ". You can add new items to your storage with /?api=add&url=URL&msg=YOURMSG<br><br>";
	echo ". Chrome Extension: <a href='extension.crx' download='extension.crx'>download</a>";

}

?>

