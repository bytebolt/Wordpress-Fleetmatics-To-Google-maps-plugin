<?php
add_action( "wp_ajax_nopriv_refreshMap", "refreshMap" );
add_action( "wp_ajax_refreshMap", "refreshMap" );

add_action( "wp_ajax_nopriv_lastSeen", "lastSeen" );
add_action( "wp_ajax_lastSeen", "lastSeen" );


$encodedUsernamepassword = '';
$FIM_app_id = 'fleetmatics-p-eu-xxxxx';
$consumerkey = 'secret key provided after app creation';

function refreshMap(){
if(time()-filemtime(plugin_dir_path( __FILE__ )."token")>20*60){
			$curl = curl_init();

			curl_setopt_array($curl, array(
			  CURLOPT_URL => 'https://fim.api.eu.fleetmatics.com/token',
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => '',
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => 'GET',
			  CURLOPT_HTTPHEADER => array(
			    'Authorization: Basic '.$encodedUsernamepassword
			  ),
			));

			$response = curl_exec($curl);

			curl_close($curl);
			file_put_contents(plugin_dir_path( __FILE__ )."token",$response);
		}


		

		$token = file_get_contents(plugin_dir_path( __FILE__ )."token");
		$vehicles = array();

		foreach (json_decode(file_get_contents(plugin_dir_path( __FILE__ )."vehicles.json")) as $key => $value) {
			array.array_push($vehicles, $key["VehicleNumber"]); # code...
		}

		$auth_endpoint = 'https://fim.api.eu.fleetmatics.com/token';
		$where_endpoint = 'https://fim.api.eu.fleetmatics.com/rad/v1/vehicles/%s/location';

// Call method and set variable to location string   
		function get_vehicle_location($vehicle_number, $endpoint, $app_id, $token)
		{
      //Inserts vehicle_number into '%s" space in endpoint     
			$url = sprintf($endpoint, $vehicle_number);  

      //Get necessary headers for REST call     	  
			$headers = get_where_call_headers($app_id, $token);

   			$session = curl_init($url);                                 //Initialize transfer with URL   
   			curl_setopt($session, CURLOPT_HEADER, false);               //Exclude header info in response      
   			curl_setopt($session, CURLOPT_RETURNTRANSFER, true);        //Return transfer as a string of the return value of curl_exec()      
   			curl_setopt($session, CURLOPT_HTTPHEADER, $headers);        //Pass in headers      

   //Execute transfer of $session     	  
		   $response = curl_exec($session); 

		    //Get http code outcome of the #session transfer	     
		   $http_code = curl_getinfo($session, CURLINFO_HTTP_CODE);

   //Measure false response/error     
   if($response === false)
   {
   	echo 'Error: '. curl_error($session);
   }

   //ALWAYS close transfer connection      
   curl_close($session);

   //Evaluate variable for non 200(OK) http code     
   if($http_code !== 200)
   {
   	echo 'Error: Http Status Code returned '.$http_code;
   }

   return $response;    
}

function get_where_call_headers($app_id, $token)
{
      //Inserts app_id and token into respective '%s' spaces in the auth header       
	$auth_header = sprintf('Authorization: Atmosphere atmosphere_app_id=%s, Bearer %s', $app_id, $token);

      //Create necessary headers for REST call      
	$headers = array();
	$headers[] = $auth_header;  
    $headers[] = 'Accept: application/json';                      //alternatively 'Accept: application/xml'     

    return $headers;
}

$out = "{";
foreach ($vehicles as $key => $vehicle) {
   $location = (get_vehicle_location($vehicle, $where_endpoint, $FIM_app_id, $token));
   $out.= '"'.$vehicle.'":'.$location.",";

}
echo rtrim($out, ","). "}";
file_put_contents(plugin_dir_path( __FILE__ )."last.json", $out);
die();
}

function lastSeen(){
	// echo time();
	// echo filemtime("last.json");
	// die();
	echo file_get_contents(plugin_dir_path( __FILE__ )."last.json");
	echo '"lastSeen":'.(1000*filemtime(plugin_dir_path( __FILE__ )."last.json")).'}';
	die();
}

// echo time();
// echo filemtime(plugin_dir_path( __FILE__ )."vehicles.json");


if(time()-filemtime(plugin_dir_path( __FILE__ )."vehicles.json")>60*60*24){
	if(time()-filemtime(plugin_dir_path( __FILE__ )."token")>20*60)
	{
			$curl = curl_init();

			curl_setopt_array($curl, array(
			  CURLOPT_URL => 'https://fim.api.eu.fleetmatics.com/token',
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => '',
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => 'GET',
			  CURLOPT_HTTPHEADER => array(
			    'Authorization: Basic '.$encodedUsernamepassword
			  ),
			));

			$response = curl_exec($curl);

			curl_close($curl);
			file_put_contents(plugin_dir_path( __FILE__ )."token",$response);
	}

	$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://fim.api.eu.fleetmatics.com:443/cmd/v1/vehicles?atmosphere_app_id='.$FIM_app_id.'&atmosphere_consumer_key='.$consumerkey.'&startdatetimeutc=2020-11-16T18:32:17Z&enddatetimeutc=2020-11-16T18:32:17Z',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Bearer '.file_get_contents(plugin_dir_path( __FILE__ ).'token')
  ),
));

$response = curl_exec($curl);

curl_close($curl);
file_put_contents(plugin_dir_path( __FILE__ ).'vehicles.json', $response);

}

?>