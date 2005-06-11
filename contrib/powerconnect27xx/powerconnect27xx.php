<?php
$user = "admin";
$pass = "";
$session = "";

$login = curl_post("http://dell2708/", "", "cookiejar");
preg_match('/Session" value="(\S+?)"/', $login, $matches);
$session = $matches[1];
$logincgi = curl_post("http://dell2708/tgi/login.tgi", "Username=$user&Password=".md5($user.$pass.$session)."&Session=$session", "cookiejar");
$portstats = curl_post("http://dell2708/portStats.htm?PortNo=8", "", "cookiejar");
echo $portstats;

function curl_post($url, $post="", $cookiejar="")
{
	$retstr = "";
	
	// output buffer b/c curl goes straight to screen
	ob_start();
	
	// create a new curl resource
	$ch = curl_init();
	
	// set URL and other appropriate options
	curl_setopt($ch, CURLOPT_URL, $url);
	
	// post if applicable
	if (!empty($post))
	{
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	} // end if post
	
	// handle cookies
	if (!empty($cookiejar))
	{
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiejar);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiejar);
	} // end if cookiejar
	
	// grab URL and pass it to the browser
	curl_exec($ch);
	
	// close curl resource, and free up system resources
	curl_close($ch);
	
	$retstr = ob_get_clean();
	return $retstr;
} // end curl_post();
?>
