include __DIR__ . '/../01_model/messages.php';
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
