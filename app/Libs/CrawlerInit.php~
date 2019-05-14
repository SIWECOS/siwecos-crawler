<?php
/**
 *   SIWECOS CRAWLER
 *
 *   Copyright (C) 2019 Ruhr University Bochum
 *
 *   @author Yakup Ates <Yakup.Ates@rub.de>
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

use GuzzleHttp\Psr7\Uri;
use Spatie\Crawler\Crawler;
use GuzzleHttp\RequestOptions;
use Spatie\Crawler\CrawlProfile;
use Psr\Http\Message\UriInterface;
use Spatie\Browsershot\Browsershot;
use Spatie\Crawler\CrawlSubdomains;
use Spatie\Crawler\CrawlInternalUrls;
use Spatie\Crawler\CrawlAllUrls;
use Spatie\Crawler\Exception\InvalidCrawlRequestHandler;

require __DIR__ . '/vendor/autoload.php';
include __DIR__ . '/CrawlLogger.php';

set_time_limit(0);

class Model{
    private $controller;
    private $crawlLogger;

    public function __construct($controller, $reqProfile) {
        $this->controller = $controller;

        $this->crawlLogger = new CrawlLogger();

        $this->initCrawling($reqProfile);
    }

    public function getLogger() {
        return $this->crawlLogger;
    }

    private function initCrawling($reqProfile) {
        $baseURL = $this->controller->getURL();
        $mDepth = $this->controller->getMaxDepth();
        $mCount = $this->controller->getMaxCount();
        $userAgent = $this->controller->getUserAgent();
        $profile = new CrawlInternalUrls($baseURL);

        if ($reqProfile === "all") {
            $profile = new CrawlAllUrls($baseURL);
        } else if($reqProfile === "subdomains") {
            $profile = new CrawlSubdomains($baseURL);
        } else if ($reqProfile === "internal") {
            $profile = new CrawlInternalUrls($baseURL);
        }

        /* validate $userAgent */
        if (empty($userAgent)) {
            // will set default user agent. But normally we will never get here.
            $this->controller->setUserAgent();
        }

        /*
         * check if crawler is limited through depth/count
         * if so, create limited crawler. Else crawl until we got everything.
         */
        if ($mCount === 0) {
            if ($mDepth === 0) {
                // no limit
                Crawler::create([RequestOptions::HEADERS
                                 => ['User-Agent' => (string)$userAgent]])
                    ->setCrawlObserver($this->crawlLogger)
                    ->setCrawlProfile($profile)
                    ->startCrawling($baseURL);
            } else {
                // limited by depth
                Crawler::create([RequestOptions::HEADERS
                                 => ['User-Agent' => (string)$userAgent]])
                    ->setCrawlObserver($this->crawlLogger)
                    ->setMaximumDepth((int)$mDepth)
                    ->setCrawlProfile($profile)
                    ->startCrawling($baseURL);
            }
        } else {
            if ($mDepth === 0) {
                // limited by count
                Crawler::create([RequestOptions::HEADERS
                                 => ['User-Agent' => (string)$userAgent]])
                    ->setMaximumCrawlCount((int)$mCount)
                    ->setCrawlObserver($this->crawlLogger)
                    ->setCrawlProfile($profile)
                    ->startCrawling($baseURL);
            } else {
                // limited by count and depth
                Crawler::create([RequestOptions::HEADERS
                                 => ['User-Agent' => (string)$userAgent]])
                    ->setCrawlObserver($this->crawlLogger)
                    ->setMaximumDepth((int)$mDepth)
                    ->setMaximumCrawlCount((int)$mCount)
                    ->setCrawlProfile($profile)
                    ->startCrawling($baseURL);
            }
        }
    }
}

?>