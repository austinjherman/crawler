<?php

namespace App\Http\Controllers;

use Goutte\Client as GoutteClient;

class CrawlerController extends Controller {

  public function __construct() {
    $this->allowedDomain = 'http://tilleeyecareassociates.com/';
  }

  public function run() {
    $client = new GoutteClient();
    $crawler = $client->request('GET', $this->allowedDomain);
    $r = $crawler->filter('a')->each(function ($node) {
      if (strpos($node->attr('href'), $this->allowedDomain)) {
        return $node->text();
      }
    });
    var_dump($r);
  }

}