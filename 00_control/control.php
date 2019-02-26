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

include __DIR__ . '/../01_model/messages.php';

class Control{
    public $url;
    private $punycode_url; /* punycode converted URL */

    private $messages;
    private $hasError = FALSE;
    private $errorMessage = NULL;

    private $dangerLevel;  /* not used */
    private $mDepth;
    private $mCount;
    private $userAgent;
    private $callbackurls = array();

    public function __construct($url, $ua, $limit_defaults) {
        $this->messages = new Messages();
        $this->setUserAgent($ua);

        $this->mDepth = $limit_defaults["mDepth"];
        $this->mCount = $limit_defaults["mCount"];

        $this->url = $url;
        $this->punycode_url = $this->punycodeUrl($url);
        $this->punycode_url = $this->checkURL($this->punycode_url);
    }


    public function getMaxDepth() {
        return $this->mDepth;
    }

    public function getMaxCount() {
        return $this->mCount;
    }

    public function setMaxDepth($depth) {
        if ($depth !== NULL) {
            $this->mDepth = $depth;
        }
    }

    public function setMaxCount($count) {
        if ($count !== NULL) {
            $this->mCount = $count;
        }
    }

    /**
     * Function to set dangerLevel
     * NOTE: dangerLevel is not used for now.
     */
    public function setDangerLevel($dangerlevel) {
        if (is_int($dangerlevel)) {
            $this->dangerLevel = $dangerlevel;
        }
    }

    /**
     * Function to set callbackurls
     */
    public function setCallbackurls($callbackurls) {
        $this->callbackurls = $callbackurls;
    }

    /**
     * Function to access dangerLevel
     * NOTE: dangerLevel is not used for now.
     */
    public function getDangerLevel() {
        return $this->dangerLevel;
    }

    /**
     * Function to access callbackurls
     */
    public function getCallbackurls() {
        return $this->callbackurls;
    }

    /**
     * Function to access the private variable $url
     */
    public function getURL() {
        return $this->punycode_url;
    }

    /**
     * Function to access the private variable $userAgent
     */
    public function getUserAgent() {
        return $this->userAgent;
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
     * Set the user agent individually
     * Default: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36
     */
    public function setUserAgent($agent) {
        if (!empty($agent)) {
            $this->userAgent = $agent;
        } else {
            /**
             * Default user agent
             */
            $agent  = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) ";
            $agent .= "AppleWebKit/537.36 (KHTML, like Gecko) ";
            $agent .= "Chrome/60.0.3112.113 Safari/537.36";

            $this->userAgent = $agent;
        }
    }

    /**
     * Returns the Punycode encoded URL for a given URL.
     *
     * @param string $url URL to encode
     *
     * @return string Punycode-Encoded URL.
     * @author https://github.com/Lednerb
     */
    public function punycodeUrl($url) {
        $parsed_url = parse_url($url);
        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'].'://' : '';
        $host = isset($parsed_url['host']) ? idn_to_ascii($parsed_url['host'], IDNA_NONTRANSITIONAL_TO_ASCII,INTL_IDNA_VARIANT_UTS46) : '';
        $port = isset($parsed_url['port']) ? ':'.$parsed_url['port'] : '';
        $user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass = isset($parsed_url['pass']) ? ':'.$parsed_url['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query = isset($parsed_url['query']) ? '?'.$parsed_url['query'] : '';

        return "$scheme$user$pass$host$port$path$query";
    }

    /**
     * Function to set the error message.
     */
    public function setErrorMessage($id, $values) {
        if (is_int($id)) {
            $placeholder = $this->messages->getNameById($id);

            $this->errorMessage = array("placeholder" => (string)$placeholder[0],
                                        "values" => $values);
        }
    }

    /**
     * Function to check if the error message.
     */
    public function getErrorMessage() {
        return $this->errorMessage;
    }


    /**
     * Function to indicate that there was an error.
     */
    public function setHasError($hasError=FALSE) {
        if (is_bool($hasError)) {
            $this->hasError = $hasError;
        }
    }

    /**
     * Function to check if the scanner had an error.
     */
    public function getHasError() {
        return $this->hasError;
    }
}

?>
