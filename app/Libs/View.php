<?php

namespace App\Libs;

use App\Libs\CrawlLogger;

class View {

    protected $version;
    protected $url;
    protected $logger;
    protected $hasError = FALSE;
    protected $errorMessage = NULL;

    public function __construct($url, $version, $logger) {
        $this->version = $version;
        $this->logger  = $logger;
        $this->url     = $url;
    }

    /**
     *
     */
    public function printJSON() {
        $result = array();
        $tests  = array();

        /* Scan results */
        $tests["domain"] = $this->url;
        $tests["urls"] = $this->logger->crawlResult;

        /* Scanner details - overall */
        $result["name"] = "SIWECOS-CRAWLER";
        $result["version"] = $this->version;
        $result["hasError"] = $this->hasError;
        $result["errorMessage"] = $this->errorMessage;

        $result["result"] = $tests;

        return $result;
    }

    /**
     * Something went wrong. Print error message according specifications.
     *
     * Possible types:
     * REQUEST_ERROR, TRANSFER_ERROR, CONNECT_ERROR, CLIENT_ERROR,
     * SERVER_ERROR, TOOMANYREDIRECTS_ERROR, BADRESPONSE_ERROR
     *
     * @return array
     */
    public function printError($errorMessage, $type) {
        $type = strtoupper(str_replace("Exception", "", explode("\\", $type)[2])) . "_ERROR";

        $this->hasError = TRUE;
        $this->errorMessage["placeholder"] = $type;
        $this->errorMessage["values"]["description"] = $errorMessage;

        $result = array();
        $tests  = array();

        /* Scan results */
        $tests["domain"] = $this->url;
        $tests["urls"] = $this->logger->crawlResult;

        /* Scanner details - overall */
        $result["name"] = "SIWECOS-CRAWLER";
        $result["version"] = $this->version;
        $result["hasError"] = $this->hasError;
        $result["errorMessage"] = $this->errorMessage;

        $result["result"] = $tests;

        return $result;
    }
}

?>