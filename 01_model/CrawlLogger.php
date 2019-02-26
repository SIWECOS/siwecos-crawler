<?php
/**
 *   SIWECOS CRAWLER
 *
 *   Copyright (C) 2019 Ruhr University Bochum
 *
 *   @author Yakup Ates <Yakup.Ates@rub.de
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

use Spatie\Crawler\CrawlObserver;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\Url;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

require __DIR__ . '/vendor/autoload.php';

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

