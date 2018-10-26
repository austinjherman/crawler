<?php

namespace App\Http\Controllers;

use DOMXPath;
use DOMDocument;
use Illuminate\Http\Response;
use Goutte\Client as GoutteClient;
use Illuminate\Support\Facades\Cache;

class UrlCrawlerController extends Controller {

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
    $this->allowedDomain = rtrim('example.com', '/');
    $this->startUrl = rtrim('http://example.com', '/');
    $this->processedUrls = [];
    $this->unprocessedUrls = [];
    $this->urlCount = 0;
    $this->client = new GoutteClient();
  }


  /**
   * Run the crawler
   *
   * @return json response
   */
  public function run() {

    // start timer
    $start = microtime(true);
    
    // set a max execution time
    ini_set('max_execution_time', 1200); //300 seconds = 5 minutes

    // check cache 
    $urls = false;
    if (isset($_GET['nocache'])) {
      Cache::forget($this->allowedDomain);
    }
    else {
      // unserialize(null) will return null
      $urls = unserialize(Cache::get($this->allowedDomain));
    }

    if (!$urls) {
      $this->findLinks($this->startUrl);
      $urls = new \stdClass();
      $urls->processedUrls = $this->processedUrls;
      $urls->unprocessedUrls = $this->unprocessedUrls;
      Cache::add($this->allowedDomain, serialize($urls), self::CACHE_TIME);
    }

    // end timer
    $time_elapsed_secs = microtime(true) - $start;

    $countProcessed = count($this->processedUrls);
    $countUnprocessed = count($this->unprocessedUrls);

    return response()->json([
      'allowed_domain' => $this->allowedDomain,
      'execution_time' => $time_elapsed_secs,
      'urls_found' => $this->urlCount,
      'urls_crawled' => [
        'count' => $countProcessed,
        'urls' => $urls->processedUrls
      ],
      'urls_not_crawled' => [
        'count' => $countUnprocessed,
        'urls' => $urls->unprocessedUrls
      ]
    ]);

  }


  /**
   * Recursive function that finds the <a> tags
   * in a DOM document.
   *
   * @return void
   */
  public function findLinks($urlToProcess) {

    $this->urlCount++;
    
    // get crawler object
    $crawler = $this->client->request('GET', $urlToProcess);

    // build and cache page object
    $page = $this->buildPageObject($urlToProcess, $crawler);
    $this->cachePageObject($page);

    // mark url as processed 
    $this->processedUrls[] = $urlToProcess;

    // for each link
    $crawler->filter('a')->each(function ($node, $i) {

      $fullUrl = $node->link()->getUri();
      $crawlUrl = $this->parseUrl($fullUrl);

      if ($crawlUrl) {
        $this->findLinks($crawlUrl);
      }

    });

    return;

  }

  /**
   * Builds a page object for caching
   *
   * @return Page
   */
  public function buildPageObject($url, $crawler) {
    $page = new \stdClass();
    $page->url = $url;
    $page->status = $this->client->getResponse()->getStatus();
    $page->headers = $this->client->getResponse()->getHeaders();
    $page->html = $crawler->html();
    return $page;
  }


  /**
   * Cache a page object for access later
   *
   * @return void
   */
  public function cachePageObject($page) {
    // the only reason this function would run is if
    // there wasn't a cache found or the user specified
    // not to return cached resources
    if (Cache::get($page->url)) {
      Cache::forget($page->url);
    }
    Cache::add($page->url, serialize($page), self::CACHE_TIME);
  }


  /**
   * Determine if URL should be crawled
   * by the crawler.
   *
   * @return String $updatedUrl or false
   */
  public function parseUrl($url) {
    
    // get URL pieces
    $parsed_url = parse_url($url);
    $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : ''; 
    $host     = isset($parsed_url['host']) ? $parsed_url['host'] : ''; 
    $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : ''; 
    $user     = isset($parsed_url['user']) ? $parsed_url['user'] : ''; 
    $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : ''; 
    $pass     = ($user || $pass) ? "$pass@" : ''; 
    $path     = isset($parsed_url['path']) ? $parsed_url['path'] : ''; 
    $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
    $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';

    // sometimes queries aren't encoded right
    $query = urlencode($query);

    // remove trailing "/" from path
    $path = rtrim($path, '/');

    if (self::IGNORE_FRAGMENTS) {
      $fragment = '';
    }

    $updatedUrl = "$scheme$user$pass$host$port$path$query$fragment";

    // don't crawl disallowed schemes
    $schemeWithoutDotSlash = rtrim($scheme, '://');
    if (in_array($schemeWithoutDotSlash, self::DISALLOWED_SCHEMES)) {
      $this->unprocessedUrls[$url] = [
        "reason_not_crawled" => "scheme $schemeWithoutDotSlash is disallowed"
      ];
      return false;
    }

    // don't crawl disallowed extensions
    $ext = strrchr($path, '.');
    if ($ext !== false) {
      $ext = ltrim($ext, '.');
      if (in_array($ext, self::DISALLOWED_EXTENSIONS)) {
        $this->unprocessedUrls[$url] = [
          "reason_not_crawled" => "extension $ext is disallowed"
        ];
        return false;
      }
    }

    // if updated url is not in allowed domain
    if (!strpos($updatedUrl, $this->allowedDomain)) {
      $this->unprocessedUrls[$url] = [
        'reason_not_crawled' => 'url not in allowed domain'
      ];
      return false;
    }

    // if the updated url has already been proccessed
    if (in_array($updatedUrl, $this->processedUrls)) {
      $this->unprocessedUrls[$url] = [
        'reason_not_crawled' => 'url already processed'
      ];
      return false;
    }

    return $updatedUrl;

  }

}