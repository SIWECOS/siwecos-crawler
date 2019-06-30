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
        $agent  = config('scanner.user_agent');
        if (config('scanner.i_maxDepth') < config('scanner.maxDepth')) {
            $depth  = config('scanner.maxDepth');
        } else {
            $depth  = config('scanner.i_maxDepth');
        }

        if (config('scanner.i_maxCount') < config('scanner.maxCount')) {
            $count  = config('scanner.maxCount');
        } else {
            $count  = config('scanner.i_maxCount');
        }

        $profile  = config('scanner.profile');

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
