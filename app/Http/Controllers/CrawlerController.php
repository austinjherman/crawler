<?php

namespace App\Http\Controllers;

use Goutte\Client as GoutteClient;

class CrawlerController extends Controller {

  public function __construct() {
    $this->allowedDomain = rtrim('tilleeyecareassociates.com', '/');
    $this->startUrl = rtrim('https://www.tilleeyecareassociates.com/', '/');
    $this->processedUrls = [];
    $this->unprocessedUrls = [
      $this->startUrl
    ];
    $this->client = new GoutteClient();
  }

  public function run() {
    $this->findLinks();
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

    // find all <a> tags in DOM object
    $crawler->filter('a')->each(function ($node, $i) {
      $href = $node->link()->getUri();
      if ($href[-1] === '/') {
        $href = rtrim($href, '/');
      }
      // if <a> tag is within allowed domain, has not been queued for process, and has not 
      // already been processed, then queue it for processing
      if (strpos($href, $this->allowedDomain) && !in_array($href, $this->unprocessedUrls) && !in_array($href, $this->processedUrls)) {
        $this->unprocessedUrls[] = $href;
      }
    });

    // remove current URL from unprocessed queue
    $key = array_search($urlToProcess, $this->unprocessedUrls);
    if ($key !== false) {
      unset($this->unprocessedUrls[$key]);
    }

    // get links on next url queued for processing
    foreach($this->unprocessedUrls as $unprocessedUrl) {
      $this->findLinks($unprocessedUrl);
    }

  }

}