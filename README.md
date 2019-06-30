# siwecos-crawler
Website crawler written in PHP.

Using [spatie/crawler](https://github.com/spatie/crawler) and
[guzzle](https://github.com/guzzle/guzzle).


# Docker
First build the image:

	  docker build -t siwecos/crawler

Then start the container:

	 docker run --rm --name siwecos-crawler -d -p 8080:80 siwecos/crawler


# Usage
Assuming the crawler is running with a container like shown above, one can run
the crawler either by using a GET or a POST request.


## Running the crawler
### GET
An example for a crawl request using all available parameters would look like
the following:

	http://localhost:8080/?url=https://URL_TO_CRAWL.COM&maxCount=100&maxDepth=2&profile=internal

The `url` parameter is required, thus a minimal request looks like:

	http://localhost:8080/?url=https://URL_TO_CRAWL.COM


### POST
An example for a crawl request using all available parameters would look like
the following:

```
{
   "url":"https://URL_TO_CRAWL.COM",
   "maxDepth":3,
   "maxCount":100,
   "profile":"internal",
   "dangerLevel":0,
   "callbackurls":[
      "localhost:8080/test_callback.php"
   ],
   "userAgent":"Example User Agent"
}
```


## Parameters
The `url` parameter is always required.

| Parameter    | Description                                               | Default              | Required |
|--------------|-----------------------------------------------------------|----------------------|----------|
| url          | The url to crawl                                          | none                 | yes      |
| maxDepth     | How deep should the crawler go                            | 1                    | no       |
| maxCount     | How many crawled results do you want to be returned       | 10                   | no       |
| profile      | Set the crawler profile ("internal", "all", "subdomains") | internal             | no       |
| dangerLevel  | - no affect -                                             | 0                    | no       |
| callbackurls | Define where the result should be sent to                 | none                 | yes      |
| userAgent    | Set the user agent, the crawler should use while crawling | [...] Chrome 60 [..] | no       |


## Output

```
{
   "name":"SIWECOS-CRAWLER",
   "version":"0.9.8",
   "hasError":false,
   "errorMessage":null,
   "result":{
      "domain":"https://URL_TO_CRAWL.COM",
      "urls":[
         "https://URL_TO_CRAWL.COM/a/test1.php",
         "https://URL_TO_CRAWL.COM/b/test1.php",
         "https://URL_TO_CRAWL.COM/c/test1.php",
         "https://URL_TO_CRAWL.COM/a/test2.php",
         "https://URL_TO_CRAWL.COM/b/test2.php",
         "https://URL_TO_CRAWL.COM/c/test2.php",
         ... ... ...
      ]
   }
}
```
