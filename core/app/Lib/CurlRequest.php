<?php

namespace App\Lib;

class CurlRequest
{

    /**
    * GET request using curl
    *
    * @return mixed
    */
	public static function curlContent($url, $header = null, $forceIpv4 = false)
	{
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    if ($header) {
	    	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	    }
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($forceIpv4) {
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        }
	    $result = curl_exec($ch);
	    curl_close($ch);
	    return $result;
	}


    /**
    * POST request using curl
    *
    * @return mixed
    */
	public static function curlPostContent($url, $postData = null, $header = null, $forceIpv4 = false)
	{
	    if (is_array($postData)) {
	        $params = http_build_query($postData);
	    } else {
	        $params = $postData;
	    }
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    if ($header) {
	    	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	    }
	    curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($forceIpv4) {
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        }
	    $result = curl_exec($ch);
	    curl_close($ch);
	    return $result;
	}
}

