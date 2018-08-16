<?php

namespace App\Http\Controllers;

use DOMXPath;
use DOMDocument;
use Illuminate\Http\Response;
use Goutte\Client as GoutteClient;
use Illuminate\Support\Facades\Cache;

class UrlCrawlerController extends Controller {

  public function __construct() {
    $this->allowedDomain = rtrim('tilleeyecareassociates.com', '/');
    $this->startUrl = rtrim('https://www.tilleeyecareassociates.com', '/');
    $this->processedUrls = [];
    $this->client = new GoutteClient();
  }

  public function run() {

    // start timer
    $start = microtime(true);
    
    // set a max execution time
    ini_set('max_execution_time', 600); //300 seconds = 5 minutes

    // check cache 
    $urls = Cache::get($this->allowedDomain);
    if (!$urls) {
      $this->findLinks($this->startUrl);
      Cache::add($this->allowedDomain, $this->processedUrls, 2);
      $urls = $this->processedUrls;
    }

    // end timer
    $time_elapsed_secs = microtime(true) - $start;

    return response()->json([
      'allowed_domain' => $this->allowedDomain,
      'execution_time' => $time_elapsed_secs,
      'urls_found' => count($urls),
      'urls' => $urls
    ]);

  }

  public function findLinks($urlToProcess) {
    
    // get crawler object
    $crawler = $this->client->request('GET', $urlToProcess);

    // mark url as processed and save html for later
    //$this->processedUrls[$urlToProcess] = $dom->html();
    $this->processedUrls[$urlToProcess] = [
      'response' => $this->client->getResponse()->getStatus(),
      'content_type' => $this->client->getResponse()->getHeader('Content-Type'),
      'headers' => $this->client->getResponse()->getHeaders(),
      'crawler_object' => serialize($crawler),
      'html' => $crawler->html()
    ];

    // for each link
    $crawler->filter('a')->each(function ($node, $i) {

      // get full url without trailing /
      $fullUrl = rtrim($node->link()->getUri(), '/');

      // if the allowed domain is in the full url and full url has not been processed
      if (strpos($fullUrl, $this->allowedDomain) && !array_key_exists($fullUrl, $this->processedUrls)) {
        // get elements of string after and including the last "."
        $ext = strrchr($fullUrl, '.');
        if ($ext !== '.pdf' && $ext !== '.jpg' && $ext !== '.png' && $ext !== '.gif') {
          $this->findLinks($fullUrl);
        }
      }

    });

    return;

  }

}