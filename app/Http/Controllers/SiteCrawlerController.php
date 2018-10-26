<?php

namespace App\Http\Controllers;

use DOMXPath;
use DOMDocument;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Goutte\Client as GoutteClient;
use Illuminate\Support\Facades\Cache;

class SiteCrawlerController extends Controller {

    /**
     * Run the crawler
     *
     * @return void
    */
    public function run(Request $request) {

        // set a max execution time
        ini_set('max_execution_time', 1200); //300 seconds = 5 minutes

        // start timer
        $start = microtime(true);

        $domain = $request->query('d');
        $nocache = $request->query('nocache');
        $nocache = $nocache !== null ? true : false;

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

}