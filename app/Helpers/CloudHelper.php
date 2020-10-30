<?php


namespace App\Helpers;


// Singleton to connect db.
use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\Storage;

class CloudHelper
{

    public static function getStorage()
    {
        return $storage = new StorageClient(
            [
                'keyFilePath' => base_path(env('GOOGLE_CLOUD_KEY_FILE', null))
            ]
        );
    }

    public static function createInitialCounters()
    {
        $bucket = CloudHelper::getStorage()->bucket(env('GOOGLE_CLOUD_STORAGE_BUCKET'));
        $objects = $bucket->objects([
            'fields' => 'items/name,nextPageToken'
        ]);
        $total_images = 0;
        foreach ($objects as $object) {
            $total_images++;
        }
        $arr1 = array('total_images' => $total_images);
        file_put_contents(base_path("json_google/indicators.json"), json_encode($arr1));
        return $total_images;
    }

    public static function updateImagesCount($count)
    {
        $arr1 = array('total_images' => $count);
        file_put_contents(base_path("json_google/indicators.json"), json_encode($arr1));
    }

    public static function getImagesCount()
    {

        $arr = json_decode(file_get_contents(base_path("json_google/indicators.json")), true);
        if (is_null($arr["total_images"]))
            $total_images = self::createInitialCounters();
        else $total_images = $arr["total_images"];
        return $total_images;
    }

    public static function deleteObjectSlow($objectName)
    {
        $disk = Storage::disk('gcs');
        return $disk->exists($objectName) ? $disk->delete($objectName) : false;

    }

    public static function deleteObject($objectName)
    {
        try {
            $storage = CloudHelper::getStorage();
            $bucket = $storage->bucket(env('GOOGLE_CLOUD_STORAGE_BUCKET'));
            $object = $bucket->object($objectName);
            $object->delete();
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @param $objectName
     * @return string
     * @throws \Exception
     */
    public static function getSignedUrl($objectName)
    {
        try {
            $storage = CloudHelper::getStorage();
            $bucket = $storage->bucket(env('GOOGLE_CLOUD_STORAGE_BUCKET'));
            $object = $bucket->object($objectName);
            $url = $object->signedUrl(
            # This URL is valid for 15 minutes
                new \DateTime('15 min'),
                [
                    'version' => 'v4',
                ]
            );
            return $url;
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }
}