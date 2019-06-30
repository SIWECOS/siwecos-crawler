<?php

namespace App\Libs;

use Spatie\Crawler\CrawlObserver;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\Url;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

class CrawlLogger extends CrawlObserver
{
    /** @var string */
    protected $observerId;

    public $crawlStatus = "INIT";
    private $crawledURL = array();
    private $willCrawlURL = array();
    public $crawlFailedURL = array();

    public $crawlResult = array();

    public function __construct(string $observerId = '') {
        if ($observerId !== '') {
            $observerId .= ' - ';
        }

        $this->observerId = $observerId;
        $this->crawlStatus = "Crawling";
    }

    /**
     * Called when the crawler will crawl the url.
     *
     * @param Spatie\Crawler\Url   $url
     */
    public function willCrawl(UriInterface $url) {
        array_push($this->willCrawlURL, $url);
    }

    /**
     * Called when the crawler has crawled the given url.
     *
     * @param \Psr\Http\Message\UriInterface $url
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param \Psr\Http\Message\UriInterface|null $foundOnUrl
     */
    public function crawled(
        UriInterface $url,
        ResponseInterface $response,
        ?UriInterface $foundOnUrl = null
    ) {
        array_push($this->crawledURL, $url);
    }

    /**
     * Called when the crawler failed to crawl the url.
     */
    public function crawlFailed(
        UriInterface $url,
        RequestException $requestException,
        ?UriInterface $foundOnUrl = null
    ) {
        $tmp = array("URI" => $url, "EXCEPTION" => $requestException,
                     "FOUNDON" => $foundOnUrl);

        array_push($this->crawlFailedURL, $tmp);

        throw $requestException;
    }

    /**
     * For testing purposes.
     */
    protected function logCrawl(UriInterface $url, ?UriInterface $foundOnUrl) {
        $logText = "{$this->observerId}hasBeenCrawled: {$url}\r\n";

        if($foundOnUrl) {
            $logText .= "[+] --- found on {$foundOnUrl}\r\n";
        }

        echo $logText;
    }

    public function UriInterfaceToURL(UriInterface $uri) {
        return $uri->__toString();
    }

    public function convertUriArrayToURL($uriArray) {
        $result = array();

        foreach($uriArray as $i) {
            array_push($result, $this->UriInterfaceToURL($i));
        }

        return $result;
    }

    /**
     * Called when the crawl has ended.
     */
    public function finishedCrawling() {
        $this->crawlResult = $this->generateResultList();

        $this->crawlStatus = "Finished";
    }

    /**
     * Prioritize a given array of URLs.
     */
    private function prioritizeList($list) {
        $prioList = [];
        $prioList_tmp = [];
        $paths = [];

        // Transform list into tuple: (URL, PATH)
        $i = 0;
        foreach ($list as $url) {
            $parsed_path = parse_url($url, PHP_URL_PATH);

            // remove main domain
            if ($parsed_path === "/") {
                unset($list[$i]);
            }

            $paths[] = [$url, $parsed_path];

            $i++;
        }

        // Determine path depth and prioritize
        foreach ($paths as $tuple) {
            // remove trailing slash
            if (substr($tuple[1], -1) === "/") {
                $tuple[1] = rtrim($tuple[1], "/");
            }
            $path_split = explode("/", $tuple[1]);

            if (count($path_split) === 2) {
                if (strlen($path_split[1]) > 0){
                    array_push($prioList_tmp, $tuple[0]);
                }
            }
        }

        /**
         * Insert URLss containing a priority string into $prioList
         */
        $prio_strings = config("scanner.prio_strings");
        $prioList = $this->pushElementsContainingString($prio_strings, $prioList_tmp, $prioList);

        /**
         * Insert URLs pointing to an unique path that are not added yet
         */
        $prioList = $this->pushElementsNotInList($prioList_tmp, $prioList);
        $prioList_tmp = $paths = 0;

        /**
         * Insert all other URLs (lower priority) into list
         */
        $prioList = $this->pushElementsNotInList($list, $prioList);

        /**
         * Shorten result to env('MAX_COUNT', 10)
         */
        echo count($prioList);
        if (count($prioList) > config("scanner.maxCount")) {
            $prioList = array_slice($prioList, 0, config("scanner.maxCount"), true);
        }

        return array_unique($prioList);
    }


    /**
     * Pushes entries of $elements into $list if it is not contained already
     */
    private function pushElementsNotInList($elements, $list) {
        foreach ($elements as $e) {
            if (!in_array($e, $list)) {
                array_push($list, $e);
            }
        }

        return $list;
    }

    /**
     * Pushes entries of $list_all containing specific strings ($elements) into $list_specific
     */
    private function pushElementsContainingString($elements, $list_all, $list_specific) {
        foreach ($list_all as $url) {
            foreach ($elements as $prio) {
                if (strpos($url, $prio)) {
                    array_push($list_specific, $url);
                }
            }
        }

        return $list_specific;
    }

    private function generateResultList() {
        $mergedURIs = array_merge($this->crawledURL, $this->willCrawlURL);
        $filtered_mergedURIs = array_unique($mergedURIs, SORT_STRING);

        // transform URI objects to URL strings
        $filtered_mergedURLs = $this->convertUriArrayToURL($filtered_mergedURIs);

        // transform URI objects to URL strings
        $failedURIs = array();
        foreach($this->crawlFailedURL as $failedURL) {
            array_push($failedURIs, $failedURL["URI"]);
        }
        $failedURLs = $this->convertUriArrayToURL($failedURIs);

        /**
         * check urls that failed to crawl are in the resulting list
         */
        $intersect = array_intersect($failedURLs, $filtered_mergedURLs);

        // delete failed urls in result list
        foreach($intersect as $toDelete) {
            if (($key = array_search($toDelete, $filtered_mergedURLs)) !== false) {
                unset($filtered_mergedURLs[$key]);
            }
        }

        return $this->prioritizeList($filtered_mergedURLs);
    }
}

?>
