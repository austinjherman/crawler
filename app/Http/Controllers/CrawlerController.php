<?php

namespace App\Http\Controllers;

use DOMDocument;
use DOMXPath;
use Goutte\Client as GoutteClient;

class CrawlerController extends Controller {

  public function __construct() {
    $this->allowedDomain = rtrim('sociusmarketing.com', '/');
    $this->startUrl = rtrim('https://www.sociusmarketing.com', '/');
    $this->processedUrls = [];
    $this->client = new GoutteClient();
  }

  public function run() {
    ini_set('max_execution_time', 600); //300 seconds = 5 minutes
    $start = microtime(true);
    $this->findLinks($this->startUrl);
    $time_elapsed_secs = microtime(true) - $start;
    echo '<p>Execution Time: ' . $time_elapsed_secs . ' seconds </p>';
    echo '<pre>';
    var_dump($this->processedUrls);
    echo '</pre>';
  }

  public function findLinks($urlToProcess) {
    
    $crawler = $this->client->request('GET', $urlToProcess);
    $this->processedUrls[] = $urlToProcess;

    $crawler->filter('a')->each(function ($node, $i) {
      
      //$shortUri = rtrim($node->attr('href'), '/');
      $urlParts = parse_url($node->link()->getUri());
      $scheme   = isset($urlParts['scheme']) ? $urlParts['scheme'] . '://' : ''; 
      $host     = isset($urlParts['host']) ? $urlParts['host'] : ''; 
      $port     = isset($urlParts['port']) ? ':' . $urlParts['port'] : ''; 
      $user     = isset($urlParts['user']) ? $urlParts['user'] : ''; 
      $pass     = isset($urlParts['pass']) ? ':' . $urlParts['pass']  : ''; 
      $pass     = ($user || $pass) ? "$pass@" : ''; 
      $path     = isset($urlParts['path']) ? $urlParts['path'] : ''; 
      $query    = isset($urlParts['query']) ? '?' . urlencode($urlParts['query']) : ''; 
      $fragment = ''; 

      $fullUri = "$scheme$user$pass$host$port$path$query$fragment";
      $fullUri = rtrim($fullUri, '/');

      if (strpos($fullUri, $this->allowedDomain) && !in_array($fullUri, $this->processedUrls)) {
        $ext = strrchr($fullUri, '.');
        if ($ext !== '.pdf' && $ext !== '.jpg' && $ext !== '.png' && $ext !== '.gif') {
          $this->findLinks($fullUri);
        }
      }

    });

    return;

  }

}