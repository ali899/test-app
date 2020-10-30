<?php

namespace App\Jobs;

use App\Helpers\CloudHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class getImagesCount implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $bucket = CloudHelper::getInstance()->getStorage()->bucket(env('GOOGLE_CLOUD_STORAGE_BUCKET'));
        $objects = $bucket->objects([
            'fields' => 'items/name,nextPageToken'
        ]);
        $total_images = 0;
        foreach ($objects as $object) {
            $total_images++;
        }
        $arr1 = array('total_images' => $total_images);
        file_put_contents(base_path("json_google/indicators.json"), json_encode($arr1));
    }
}
