<?php

namespace App\Jobs;

use App\CrawlerScan;
use App\Http\Requests\ScanStartRequest;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class CrawlerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $request;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request)
    {
        $this->request = new ScanStartRequest($request);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Starting Scan Job for ' . $this->request->get('url'));
        Log::info('Queue jobs remaining ' . Queue::size($this->queue));

        // NOTE(ya): Default parameters out of config
        $agent  = \Config::get('scanner.user_agent');
        $depth  = \Config::get('scanner.maxDepth');
        $count  = \Config::get('scanner.maxCount');
        $profile  = \Config::get('scanner.profile');

        $scan = new CrawlerScan(
            $this->request->get('url'),
            0,
            $this->request->get('callbackurls', []),
            $this->request->get('userAgent', $agent),
            $this->request->get('maxDepth', $depth),
            $this->request->get('maxCount', $count),
            $this->request->get('profile', $profile)
        );

        $scan->scan();
    }

    /**
     * The job failed to process.
     * This will never be called - for now
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function failed(\Exception $exception)
    {
        foreach ($this->request->get('callbackurls', []) as $url) {
            Log::info(
                'Job failed: ' . $url . ', error code: ' . json_encode($exception->getMessage())
            );
            try {
                $client = new Client;
                $client->post(
                    $url,
                    [
                        'http_errors' => false,
                        'timeout' => 60,
                        'json' => [
                            'name'         => 'CRAWLER',
                            'version'      => file_get_contents(base_path('VERSION')),
                            'hasError'     => true,
                            'errorMessage' => $exception->getMessage(),
                            'score'        => 0
                        ],
                    ]
                );
            } catch (\Exception $e) {
                Log::warning('Could not send the failed report to the following callback url: ' . $url);
            }
        }
    }
}
