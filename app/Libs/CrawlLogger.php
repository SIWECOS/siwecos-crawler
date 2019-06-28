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
        //print_r($this->crawlResult);

        $this->crawlStatus = "Finished";
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

        sort($filtered_mergedURLs);
        return $filtered_mergedURLs;
    }
}

?>
