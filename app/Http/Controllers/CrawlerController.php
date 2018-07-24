<?php

namespace App\Http\Controllers;

use Goutte\Client as GoutteClient;

class CrawlerController extends Controller {

  public function __construct() {
    $this->allowedDomain = 'example.com';
    $this->startUrl = 'http://example.com';
    $this->urls = [];
    $this->client = new GoutteClient();
  }

  public function run() {
    $this->enqueueUrls();
    //var_dump($this->urls);
  }

  public function enqueueUrls() {
    $crawler = $this->client->request('GET', $this->startUrl);
    $crawler->filter('a')->each(function ($node) {
      var_dump($node);
      $href = $node->attr('href');
      if (strpos($href, $this->allowedDomain) && !in_array($href, $this->urls)) {
        $this->urls[] = $href;
      }
    });
  }

}