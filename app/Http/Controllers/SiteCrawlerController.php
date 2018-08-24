<?php

namespace App\Http\Controllers;

use DOMXPath;
use DOMDocument;
use Illuminate\Http\Response;
use Goutte\Client as GoutteClient;
use Illuminate\Support\Facades\Cache;

class SiteCrawlerController extends Controller {

  /**
   * Add schemes here that you don't want the 
   * crawler to crawl if encountered.
   *
   */
  const DISALLOWED_SCHEMES = [
    'mailto',
    'tel',
    'sms',
    'file'
  ];

  /**
   * Add extensions here that you don't want 
   * the crawler to crawl if encountered.
   *
   */
  const DISALLOWED_EXTENSIONS = [
    'gif', 
    'jpg',
    'jpeg',
    'png',
    'pdf'
  ];

  /**
   * Set to true if you want to ignore
   * URL fragments (e.g. example.com#fragment)
   *
   * Setting to true will prevent crawling the
   * same page twice.
   *
   */
  const IGNORE_FRAGMENTS = true;

  /**
   * Set the cache time in minutes
   *
   */
  const CACHE_TIME = 60*24*7; // 1 week


  /**
   * Class constructor
   *
   * @return void
   */
  public function __construct() {
    $this->allowedDomain = rtrim('tilleeyecareassociates.com', '/');
    $this->startUrl = rtrim('http://tilleeyecareassociates.com/', '/');
    $this->client = new GoutteClient();
  }


  /**
   * Run the crawler
   *
   * @return json response
   */
  public function run() {
    // run the crawler
  }

  public function makeRequest($url) {
    $this->client->request('GET', $url);
  }

  public function handleResponse(GoutteClient $client) {
    if ($this->client->getResponse()->getStatus() === 200) {
      $crawler = $this->client->getCrawler();
      $uri = $crawler->getUri();
      // Cache successful url
      // Cache Crawler
    }
    else {
      // Cache unsuccessful url
    }
  }

}