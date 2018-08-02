<?php

namespace App\Http\Controllers;

use DOMDocument;
use DOMXPath;
use Goutte\Client as GoutteClient;
use App\Crawler\Client as AppClient;

class CrawlerController extends Controller {

  public function __construct() {
    $this->allowedDomain = rtrim('tilleeyecareassociates.com', '/');
    $this->startUrl = rtrim('http://tilleeyecareassociates.com/', '/');
    $this->processedUrls = [];
    $this->unprocessedUrls = [
      $this->startUrl
    ];
    $this->client = new GoutteClient();
  }

  public function run() {
    ini_set('max_execution_time', 300); //300 seconds = 5 minutes
    $start = microtime(true);
    //$this->findLinksV3();
    $this->findLinksV3($this->startUrl);
    $time_elapsed_secs = microtime(true) - $start;
    echo '<p>Execution Time: ' . $time_elapsed_secs . '</p>';
    //echo '<pre>';
    //var_dump($this->processedUrls);
    //echo '</pre>';
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


  public function findLinksV3($urlToProcess) {
    $client = new AppClient();
    $dom = $client->request('GET', $urlToProcess);
    //$dom = new DOMXPath($dom);
    //$nodes = $dom->query('//body//a');
    $nodes = $dom->getElementsByTagName('a');
    $count = 0;
    foreach($nodes as $node) {
      $url = $this->rel2abs($node->getAttribute('href'), $this->startUrl);
      if (strpos($url, $this->allowedDomain) !== false) {
        $count++;
        echo $url . '<br>';
      } 
    }
    echo 'Count: ' . $count;
  }


  //https://stackoverflow.com/questions/4444475/transfrom-relative-path-into-absolute-url-using-php
  public function rel2abs($rel, $base) {
    /* return if already absolute URL */
    if (parse_url($rel, PHP_URL_SCHEME) != '') return $rel;

    /* queries and anchors */
    if ($rel[0]=='#' || $rel[0]=='?') return $base.$rel;

    /* parse base URL and convert to local variables:
       $scheme, $host, $path */
    //extract(parse_url($base));
    $scheme = parse_url($base, PHP_URL_SCHEME);
    $host = parse_url($base, PHP_URL_HOST);
    $path = parse_url($base, PHP_URL_PATH);

    /* remove non-directory element from path */
    $path = preg_replace('#/[^/]*$#', '', $path);

    /* destroy path if relative url points to root */
    if ($rel[0] == '/') $path = '';

    /* dirty absolute URL */
    $abs = "$host$path/$rel";

    /* replace '//' or '/./' or '/foo/../' with '/' */
    $re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
    for($n=1; $n>0; $abs=preg_replace($re, '/', $abs, -1, $n)) {}

    /* absolute URL is ready! */
    return $scheme.'://'.$abs;
  }



}