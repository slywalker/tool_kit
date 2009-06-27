<?php
/*
This php class allows an address to be converted into Geographic Coordinates, 
Latitude and Longitude through the use of Google's Geocoding Service. This code
is in no way related nor affiliated with Google.  
Copyright (C) 2006 Mahmoud Lababidi

This software is licensed under the MIT License:

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.

Requirements:

1. A GoogleMaps API Key, which can be received from
		http://www.google.com/apis/maps/signup.html

2. CURL, Which should be included on most PHP installations.

Usage:

require_once('google_geo.php');
$google = new GoogleGeo('1600 Pennsylvania Ave NW, Washington, DC 2005');
$geo = $google->geo();

OR

require_once('google_geo.php');
$address = array(	'street'=>'1600 Pennsylvania Ave NW', 
					'city'=>'Washington', 
					'state'=>'DC', 
					'zip'=>'2005');
$google = new GoogleGeo($address);
$geo = $google->geo();


*/

class GoogleGeo {

//your GoogleMaps Api Key
var $api_key = 
	"ABQIAAAAnfs7bKE82qgb3Zc2YyS-oBT2yXp_ZAY8_ufC3CFXhHIE1NvwkxSySz_REpPq-4WZA27OwgbtyR3VcA"; 

function GoogleGeo($address=null){
	$this->address_set = false;
	if($address!=null)
		$this->setAddress($address);
	}

function setAddress($address){
	if(is_array($address)){
		$this->address_array = $address;
		$this->address_set = true;
		}
	else if(is_string($address)){
		$this->address_string = $address;
		$this->address_set = true;
		}
	}



function geo($address = null){
	$base = "http://maps.google.com/maps/geo?q=";
	if($address!=null)
		$this->setAddress($address);
	if($this->address_set){
		$url = $base;
		if(isset($this->address_string)){
			$url.=urlencode($this->address_string);
			}
		else if (isset($this->address_array)){
			foreach($this->address_array as $a)
				$url.=urlencode($a.' ');
			}
		$url.="&output=csv&key=".$this->api_key;
		//var_dump($url);
		$csv = get_content($url);
		$result = explode(',',$csv);
		if($result[0] == '200'){ //200 stands for HTTP status OK, let's go!
			$geo = array('latitude'=>$result[2],'longitude'=>$result[3]);
			return $geo;
			}
		}
	if($result[0]=='602') {
			$url = $base;
			if(isset($this->address_string)){
				$url.=urlencode($this->address_string);
				}
			else if (isset($this->address_array)){
				unset($this->address_array['zip']);
				foreach($this->address_array as $a)
					$url.=urlencode($a.' ');
				}
			$url.="&output=csv&key=".$this->api_key;
			//var_dump($url);
			$csv = get_content($url);
			$result = explode(',',$csv);
		if($result[0] == '200'){ //200 stands for HTTP status OK, let's go!
			$geo = array('latitude'=>$result[2],'longitude'=>$result[3]);
			return $geo;
			}
		}	 
		return null;
		
	}
}



function get_content($url)
{
   $ch = curl_init();
   curl_setopt ($ch, CURLOPT_URL, $url);
   curl_setopt ($ch, CURLOPT_HEADER, 0);
   ob_start();
   curl_exec ($ch);
   curl_close ($ch);
   $string = ob_get_contents();
   ob_end_clean();
   return $string;   
  }

