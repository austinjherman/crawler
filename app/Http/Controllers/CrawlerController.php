<?php

namespace App\Http\Controllers;

use Goutte\Client as GoutteClient;

class CrawlerController extends Controller {

  public function __construct() {
    $this->allowedDomain = 'tilleeyecareassociates.com';
    $this->startUrl = 'https://www.tilleeyecareassociates.com';
    $this->finalUrls = [];
    $this->unprocessedUrls = [
      $this->startUrl
    ];
    $this->client = new GoutteClient();
  }

  public function run() {
    $this->findLinks();
    // echo '<pre>';
    // var_dump($this->finalUrls);
    // echo '</pre>';
  }

  public function findLinks($urlToProcess=null) {

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

    // find all <a> tags in DOM object
    $crawler->filter('a')->each(function ($node, $i) {
      $href = $node->attr('href');
      if ($href[-1] === '/') {
        $href = rtrim($href, '/');
      }
      if (strpos($href, $this->allowedDomain) && !in_array($href, $this->finalUrls)) {
        $this->finalUrls[] = $href;
      }
      if (strpos($href, $this->allowedDomain) && !in_array($href, $this->unprocessedUrls)) {
        $this->unprocessedUrls[] = $href;
      }
    });

    // remove the processed url so we don't crawl forever
    $key = array_search($urlToProcess, $this->unprocessedUrls);
    if ($key !== false) {
      unset($this->unprocessedUrls[$key]);
    }

    echo '<pre>';
    var_dump($this->unprocessedUrls);
    echo '</pre>';

    //foreach($this->unprocessedUrls as $unprocessedUrl) {
      //$this->findLinks($unprocessedUrl);
    //}

  }

}