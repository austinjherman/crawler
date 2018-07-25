<?php

namespace App\Http\Controllers;

use Goutte\Client as GoutteClient;

class CrawlerController extends Controller {

  public function __construct() {
    $this->allowedDomain = rtrim('sociusmarketing.com', '/');
    $this->startUrl = rtrim('https://www.sociusmarketing.com/', '/');
    $this->processedUrls = [];
    $this->unprocessedUrls = [
      $this->startUrl
    ];
    $this->client = new GoutteClient();
  }

  public function run() {
    $start = microtime(true);
    $this->findLinksV2();
    $time_elapsed_secs = microtime(true) - $start;
    echo '<p>Execution Time: ' . $time_elapsed_secs . '</p>';
    echo '<pre>';
    var_dump($this->processedUrls);
    echo '</pre>';
  }

  public function findLinks($urlToProcess=null) {

    // base case
    if (empty($this->unprocessedUrls)) {
      return;
    }

    // if no url is provided, process the starting url
    if (!$urlToProcess) {
      $crawler = $this->client->request('GET', $this->startUrl);
      $urlToProcess = $this->startUrl;
      $this->unprocessedUrls = [];
    }

    // otherwise process the url provided
    else {
      $crawler = $this->client->request('GET', $urlToProcess);
    }

    // set the current URL as processed
    $this->processedUrls[] = $urlToProcess;

    // remove current URL from unprocessed queue
    $key = array_search($urlToProcess, $this->unprocessedUrls);
    if ($key !== false) {
      unset($this->unprocessedUrls[$key]);
    }

    // find all <a> tags in DOM object
    $crawler->filter('a')->each(function ($node, $i) {
      $shortUri = rtrim($node->attr('href'), '/');
      $fullUri = rtrim($node->link()->getUri(), '/');
      // if <a> tag is within allowed domain, has not been queued for process, and has not 
      // already been processed, then queue it for processing
      if (strpos($fullUri, $this->allowedDomain)) {
        if (!in_array($fullUri, $this->unprocessedUrls) && !in_array($fullUri, $this->processedUrls)) {
          $this->unprocessedUrls[] = $fullUri;
        }
      }
    });

    // get links on next url queued for processing
    foreach($this->unprocessedUrls as $unprocessedUrl) {
      $this->findLinks($unprocessedUrl);
    }

  }

  public function findLinksV2($urlToProcess=null) {

    // if no url is provided, process the starting url
    if (!$urlToProcess) {
      $urlToProcess = $this->startUrl;  
    }
    
    $crawler = $this->client->request('GET', $urlToProcess);
    $this->processedUrls[] = $urlToProcess;

    $crawler->filter('a')->each(function ($node, $i) {
      
      //$shortUri = rtrim($node->attr('href'), '/');
      $fullUri = rtrim($node->link()->getUri(), '/');

      if (strpos($fullUri, $this->allowedDomain) && !in_array($fullUri, $this->processedUrls)) {
        $this->findLinksV2($fullUri);
      }

    });

    return;

  }

}