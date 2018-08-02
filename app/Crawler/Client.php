<?php 

namespace App\Crawler;

use DOMXPath;
use DOMDocument;

class Client {

	public function __construct(String $allowedDomain=null) {
		$this->allowedDomain = $allowedDomain;
	}

	/**
	 * Make a request to the provided URL and returns a 
	 * traversable DOMDocument. 
	 *
	 * @param String $requestMethod | String $url
	 *
	 * @return DOMDocument
	 */
	public function request(String $requestMethod, String $url) {
		$ch = curl_init();
    	curl_setopt_array($ch, array(
      		CURLOPT_RETURNTRANSFER => 1,
      		CURLOPT_URL => $url,
      		CURLOPT_USERAGENT => 'Crawler Testing',
      		CURLOPT_FOLLOWLOCATION => true
    	));
    	$resp = curl_exec($ch);
    	curl_close($ch);
    	$dom = new DOMDocument();
    	@$dom->loadHTML($resp);
    	return $dom;
	}

}