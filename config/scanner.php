<?php

return [

    /*
    |--------------------------------------------------------------------------
    | User Agent
    |--------------------------------------------------------------------------
    |
    | The given User-Agent will be used for crawling.
    |
    */    
    'user_agent' => env('USER_AGENT', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36'),


    /*
    |--------------------------------------------------------------------------
    | Max Depth
    |--------------------------------------------------------------------------
    |
    | This defines the depth of the crawler. Determining how deep into
    | the structure of the website the crawler crawler should go.
    | 
    | DEFINITION FOR RESULT
    |
    */      
    'maxDepth'   => env('MAX_DEPTH', 1),

    /*
    |--------------------------------------------------------------------------
    | Max Count
    |--------------------------------------------------------------------------
    |
    | Count of URL's that is going to be looked for.
    |
    | DEFINITION FOR RESULT
    | 
    */          
    'maxCount'   => env('MAX_COUNT', 10),

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    |
    | Defines the crawl Strategie that is going to be used.
    | Possible profiles:
    |    all             -   crawl for everything
    |    subdomains      -   crawl only within subdomains
    |    internal        -   crawl only within internal domains
    */              
    'profile'   => env('PROFILE', 'internal'),


    /*
    |--------------------------------------------------------------------------
    | Prio Strings
    |--------------------------------------------------------------------------
    |
    | Paths containing specific strings could be more interesting than
    | others.
    | These strings are defined here.
    | 
    */                  
    'prio_strings'   => env('PRIO_STRINGS', [
        "app",
        "admin",
        "blog",
        "cms",
        "drupal",
        "impressum",
        "joomla",
        "typo3",
        "wordpress",
        "wp-content",
        "wiki",
        "tools",
        "jobs",
        "forum",
        "download",
    ]),


    /*
    |--------------------------------------------------------------------------
    | Max Count (INTERNAL)
    |--------------------------------------------------------------------------
    |
    | Count of URL's that is going to be looked for.
    |
    | USED INTERNALLY
    | 
    */          
    'i_maxCount'   => env('I_MAX_COUNT', 40),

    /*
    |--------------------------------------------------------------------------
    | Max Depth (INTERNAL)
    |--------------------------------------------------------------------------
    |
    | This defines the depth of the crawler. Determining how deep into
    | the structure of the website the crawler crawler should go.
    |
    | USED INTERNALLY
    |     
    */      
    'i_maxDepth'   => env('I_MAX_DEPTH', 1),        
    
];
