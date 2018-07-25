<?php

namespace App\Http\Controllers;

use Goutte\Client as GoutteClient;

class CrawlerController extends Controller {

  public function __construct() {
    $this->allowedDomain = 'tilleeyecareassociates.com';
    $this->startUrl = 'https://www.tilleeyecareassociates.com';
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

    // set the current URL as processed so we don't crawl forever
    $this->processedUrls[] = $urlToProcess;

    // find all <a> tags in DOM object
    $crawler->filter('a')->each(function ($node, $i) {
      $href = $node->attr('href');
      // remove trailing slash
      if ($href[-1] === '/') {
        $href = rtrim($href, '/');
      }
      // 
      if (strpos($href, $this->allowedDomain) && !in_array($href, $this->unprocessedUrls) && !in_array($href, $this->processedUrls)) {
        $this->unprocessedUrls[] = $href;
      }
    });

    // remove the processed url so we don't crawl forever
    $key = array_search($urlToProcess, $this->unprocessedUrls);
    if ($key !== false) {
      unset($this->unprocessedUrls[$key]);
    }

    foreach($this->unprocessedUrls as $unprocessedUrl) {
      $this->findLinks($unprocessedUrl);
    }

  }

}