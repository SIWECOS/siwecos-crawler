<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ScanStartRequest;
use App\CrawlerScan;
use App\Jobs\CrawlerJob;

class CrawlController extends Controller
{
    public function start(ScanStartRequest $request) {
        if ($request->get('callbackurls')) {
            CrawlerJob::dispatch($request->validated());

            return "OK";
        }

        // NOTE(ya): Default parameters out of config
        $agent  = config('crawler.user_agent');
        if (config('crawler.i_maxDepth') < config('crawler.maxDepth')) {
            $depth  = config('crawler.maxDepth');
        } else {
            $depth  = config('crawler.i_maxDepth');
        }

        if (config('crawler.i_maxCount') < config('crawler.maxCount')) {
            $count  = config('crawler.maxCount');
        } else {
            $count  = config('crawler.i_maxCount');
        }

        $profile  = config('crawler.profile');

        $scan = new CrawlerScan(
            $request->get('url'),
            0,
            $request->get('callbackurls', []),
            $request->get('userAgent', $agent),
            $request->get('maxDepth', $depth),
            $request->get('maxCount', $count),
            $request->get('profile', $profile)
        );

        return response($scan->scan(), 200)->header('Content-Type', 'application/json; charset=utf-8');
    }
}
