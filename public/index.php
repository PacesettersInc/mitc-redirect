<?php
$debug = isset($_GET['debug']);

function debug($text) {
    echo "<pre>$text</pre>";
}

function isCharterIP($ip){
    $name = gethostbyaddr($ip);
    debug("Name: $name");
    if( strpos($name,'charter') !== FALSE )
        return true;
    return false;
}

function isCharterName($ip){
    $name = gethostbyaddr(gethostbyname($ip));
    debug("Name: $name");
    if( strpos($name,'charter') !== FALSE )
        return true;
    return false;
}


$dyndns = Array('mitc1.pacesetterstn.com','mitc2.pacesetterstn.com');

$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
$isUserCharter = isCharterIP($ip);

debug("IP: $ip");
debug("isUserCharter: " . ($isUserCharter ? 'Yes' : 'No'));

$charter_dyndns = 1;
$frontier_dyndns = 0;

$isCharterAlive = false;
$isFrontierAlive = false;

$goto = $frontier_dyndns;

#-=-=- See if the connects are Alive -=-=-#
// create both cURL resources
$ch1 = curl_init(); //Charter
$ch2 = curl_init(); //Frontier

// set URL and other appropriate options
curl_setopt($ch1, CURLOPT_URL, 'http://'.$dyndns[$charter_dyndns].'/Default.ASP');
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch1, CURLOPT_CONNECTTIMEOUT,2);

curl_setopt($ch2, CURLOPT_URL, 'http://'.$dyndns[$frontier_dyndns].'/Default.ASP');
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch2, CURLOPT_CONNECTTIMEOUT,2);

$mh = curl_multi_init();

curl_multi_add_handle($mh,$ch1);
curl_multi_add_handle($mh,$ch2);

$active = null;
//execute the handles
do {
    $mrc = curl_multi_exec($mh, $active);
} while ($mrc == CURLM_CALL_MULTI_PERFORM);

while ($active && $mrc == CURLM_OK) {
    if (curl_multi_select($mh) != -1) {
    do {
        $mrc = curl_multi_exec($mh, $active);
    } while ($mrc == CURLM_CALL_MULTI_PERFORM);
    }
}

//do stuff

$httpCodeCharter  = curl_getinfo($ch1, CURLINFO_HTTP_CODE);
$httpCodeFrontier = curl_getinfo($ch2, CURLINFO_HTTP_CODE);

//close the handles
curl_multi_remove_handle($mh, $ch1);
curl_multi_remove_handle($mh, $ch2);
curl_multi_close($mh);

#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#


// if( isCharterName($dyndns[$frontier_dyndns]) ){
//     debug('Charter!')
//     $temp = $charter_dyndns;
//     $charter_dyndns = $frontier_dyndns;
//     $frontier_dyndns = $temp;
// }

debug("Charter HTTP: $httpCodeCharter");
debug("Other HTTP: $httpCodeFrontier");

if( $httpCodeCharter == 200 )
    $isCharterAlive = true;
    
if( $httpCodeFrontier == 200 )
    $isFrontierAlive = true;

if ( $isCharterAlive && $isUserCharter ) 
    $goto = $charter_dyndns;

$gotoUrl = 'http://'.$dyndns[$goto].'/mymitc';

debug("GoTo: <a href='$gotoUrl'>$gotoUrl</a>");

if ($debug) die('Debug is enabled!');

header('Location: http://'.$dyndns[$goto].'/mymitc');
exit();
?>
