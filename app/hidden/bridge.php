<?php

$username = $_GET['username'];
$link = "https://www.instagram.com/$username/";

 $oturum = curl_init();
    
$proxylist = file('app/hidden/proxylist.txt');

  $proxy  = $proxyList[array_rand($proxyList,1)];
  
    curl_setopt($oturum, CURLOPT_URL, $link);
    curl_setopt($oturum, CURLOPT_HTTPPROXYTUNNEL, 0);
	curl_setopt($oturum, $ch, CURLOPT_PROXY, $proxy);
	curl_setopt($oturum, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36');
    curl_setopt($oturum, CURLOPT_HEADER, 0);
    curl_setopt($oturum, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($oturum, CURLOPT_CONNECTTIMEOUT, 33);
    $cekilendatalar = curl_exec($oturum);
    curl_close($oturum);
    print_r($cekilendatalar);

?>