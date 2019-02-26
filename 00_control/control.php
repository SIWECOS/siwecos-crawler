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
     * Function to check if the error message.
     */
    public function getErrorMessage() {
        return $this->errorMessage;
    }



    /**
     * Function to check if the scanner had an error.
     */
    public function getHasError() {
        return $this->hasError;
    }
}

?>
