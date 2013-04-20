<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/
function isCharterIP($ip){
	$name = gethostbyaddr($ip);
	if( strpos($name,'charter') !== FALSE )
		return true;
	return false;
}

function isCharterName($ip){
	$name = gethostbyaddr(gethostbyname($ip));
	if( strpos($name,'charter') !== FALSE )
		return true;
	return false;
}

Route::get('/', function()
{

	$dyndns = Array('mitc1.pacesetterstn.com','mitc2.pacesetterstn.com');
	
	$ip = $_SERVER['REMOTE_ADDR'];
	$isUserCharter = isCharterIP($ip);
	
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
	
	
	if( isCharterName($dyndns[$frontier_dyndns]) ){
		$temp = $charter_dyndns;
		$charter_dyndns = $frontier_dyndns;
		$frontier_dyndns = $temp;
	}
	
	if( $httpCodeCharter == 200 )
		$isCharterAlive = true;
		
	if( $httpCodeFrontier == 200 )
		$isFrontierAlive = true;
	
	if ( $isCharterAlive && $isUserCharter ) 
		$goto = $charter_dyndns;

	return Redirect::to('http://'.$dyndns[$goto].'/mymitc');
});
