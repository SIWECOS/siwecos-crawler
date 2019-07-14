<?php

namespace App;

use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Client;
use Spatie\Crawler\Crawler;
use GuzzleHttp\RequestOptions;
use Spatie\Crawler\CrawlProfile;
use Psr\Http\Message\UriInterface;
use Spatie\Browsershot\Browsershot;
use Spatie\Crawler\CrawlSubdomains;
use Spatie\Crawler\CrawlInternalUrls;
use Spatie\Crawler\CrawlAllUrls;
use Spatie\Crawler\Exception\InvalidCrawlRequestHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Libs\CrawlLogger;
use App\Libs\View;

/**
 * All possible scoreTypes.
 */
abstract class Profile {
    const all = "all";
    const subdomains = "subdomains";
    const internal = "internal";
}

class CrawlerScan {

    protected $verbose = FALSE;
    protected $version;
    protected $url;
    protected $dangerlevel;
    protected $callbackurls;
    protected $useragent;
    protected $maxdepth;
    protected $maxcount;
    protected $profile;
    protected $client;
    protected $crawlLogger;
    protected $client_config;

    protected $result;


    public function __construct(string $url, int $dangerlevel,
                                array $callbackurls, string $useragent,
                                int $maxdepth, int $maxcount, string $profile) {
        $this->version = file_get_contents(base_path('VERSION'));
        $this->crawlLogger = new CrawlLogger();

        $this->client_config = [RequestOptions::HEADERS => ['User-Agent' => (string)$this->useragent],
                                ['defaults' => [ 'exceptions' => false ]],
                                RequestOptions::TIMEOUT => 30,
                                RequestOptions::CONNECT_TIMEOUT => 30,
                                RequestOptions::HTTP_ERRORS => false];

        $this->url = (string) $url;
        $this->dangerlevel = (int) $dangerlevel;
        $this->callbackurls = $callbackurls;
        $this->useragent = (string) $useragent;
        $this->maxdepth = (int) $maxdepth;
        $this->maxcount = (int) $maxcount;

        if (($profile !== Profile::all) &&
            ($profile !== Profile::subdomains) &&
            ($profile !== Profile::internal)) {
            $this->profile = Profile::internal;  // NOTE(ya): Default profile, if invalid profile is set
        } else {
            $this->profile = $profile;
        }



        $this->client = new Client($this->client_config);
    }

    public function __destruct() {
        unset($this->client);
        unset($this->verbose);
        unset($this->version);
        unset($this->url);
        unset($this->dangerlevel);
        unset($this->callbackurls);
        unset($this->useragent);
        unset($this->maxdepth);
        unset($this->maxcount);
        unset($this->profile);
        unset($this->client);
        unset($this->crawlLogger);
        unset($this->client_config);
        unset($this->result);
    }

    public function scan() {
        $view = new View($this->url,
                         file_get_contents(base_path('VERSION')),
                         $this->crawlLogger);

        try {
            // NOTE(ya): Try to establish a connection first and catch exceptions.
            $status_code = $this->client->get($this->url)->getStatusCode();

            // NOTE(ya): This can also throw exceptions. But probably won't if the
            //           first attempt to establish a connection was successfull
            $this->initCrawling();
        } catch (\Exception $e) {
            \Log::warning('Could not connect to: ' . $this->url);

            if ($this->verbose)
                \Log::warning('Guzzle error: ' . $e);

            $view = $view->printError($e->getMessage(), get_class($e));

            $this->result = json_encode($view,
                                        JSON_PRETTY_PRINT |
                                        JSON_UNESCAPED_UNICODE |
                                        JSON_UNESCAPED_SLASHES);

            if (count($this->callbackurls)) {
                $this->notifyCallbacks();
            }

            \Log::warning('Error reporting done: ' . $this->url);

            return $this->result;
        }


        $view = $view->printJSON();

        $this->result = json_encode($view,
                                    JSON_PRETTY_PRINT |
                                    JSON_UNESCAPED_UNICODE |
                                    JSON_UNESCAPED_SLASHES);

        if (count($this->callbackurls)) {
            $this->notifyCallbacks();
        }

        \Log::info('JOB DONE: ' . $this->url);

        unset($view);
        return $this->result;
    }

    private function initCrawling() {
        $url = $this->punycodeUrl($this->addHTTP($this->url));

        if ($this->profile === Profile::all) {
            $profile = new CrawlAllUrls($url);
        } else if($this->profile === Profile::subdomains) {
            $profile = new CrawlSubdomains($url);
        } else if ($this->profile === Profile::internal) {
            $profile = new CrawlInternalUrls($url);
        } else {
            $profile = new CrawlInternalUrls($url);
        }

        /*
         * check if crawler is limited through depth/count
         * if so, create limited crawler. Else crawl until we got everything.
         */
        if ($this->maxcount === 0) {
            if ($this->maxdepth === 0) {
                // no limit
                Crawler::create($this->client_config)
                    ->setCrawlObserver($this->crawlLogger)
                    ->setCrawlProfile($profile)
                    ->startCrawling($url);
            } else {
                // limited by depth
                Crawler::create($client_config)
                    ->setCrawlObserver($this->crawlLogger)
                    ->setMaximumDepth((int)$this->maxdepth)
                    ->setCrawlProfile($profile)
                    ->startCrawling($url);
            }
        } else {
            if ($this->maxdepth === 0) {
                // limited by count
                Crawler::create($this->client_config)
                    ->setMaximumCrawlCount((int)$this->maxcount)
                    ->setCrawlObserver($this->crawlLogger)
                    ->setCrawlProfile($profile)
                    ->startCrawling($url);
            } else {
                // limited by count and depth
                Crawler::create($this->client_config)
                    ->setCrawlObserver($this->crawlLogger)
                    ->setMaximumDepth((int)$this->maxdepth)
                    ->setMaximumCrawlCount((int)$this->maxcount)
                    ->setCrawlProfile($profile)
                    ->startCrawling($url);
            }
        }

        return 0;
    }

    /**
     * @short: Add HTTP scheme to the URL.
     * @var url: The URL which will get the scheme added
     * @algorithm: Is the scheme specified? If not add it, else leave it as it
     * * is.
     * @return string
     */
    private function addHTTP($url, $scheme = 'http://') {
        return parse_url($url, PHP_URL_SCHEME) === null ? $scheme . $url : $url;
    }

    /**
     * Returns the Punycode encoded URL for a given URL.
     *
     * @param string $url URL to encode
     *
     * @return string Punycode-Encoded URL.
     * @author https://github.com/Lednerb
     */
    private function punycodeUrl($url) {
        $parsed_url = parse_url($url);
        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'].'://' : '';
        $host = isset($parsed_url['host']) ? idn_to_ascii($parsed_url['host'],
                                                          IDNA_NONTRANSITIONAL_TO_ASCII,
                                                          INTL_IDNA_VARIANT_UTS46) : '';
        $port = isset($parsed_url['port']) ? ':'.$parsed_url['port'] : '';
        $user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass = isset($parsed_url['pass']) ? ':'.$parsed_url['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query = isset($parsed_url['query']) ? '?'.$parsed_url['query'] : '';

        return "$scheme$user$pass$host$port$path$query";
    }

    protected function notifyCallbacks(): void
    {
        foreach ($this->callbackurls as $url) {
            Log::info('Callback to: ' . $url);

            try {
                $this->client->post($url, [
                    'http_errors' => false,
                    'timeout'     => 60,
                    'json'        => json_decode($this->result),
                ]);
            } catch (\Exception $e) {
                Log::warning('Callback error (url): ' . $url);
            }

            Log::info('Finished callback for ' . $url);
        }
    }
}
?>
