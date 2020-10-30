<?php


namespace App\Helpers;

use App\Bhads;
use App\BhadsStatus;
use Spatie\ArrayToXml\ArrayToXml;

class JsonResponse
{
    const MSG_ADDED_SUCCESSFULLY = 'added successfully';
    const MSG_UPDATED_SUCCESSFULLY = "updated successfully";
    const MSG_DELETED_SUCCESSFULLY = "deleted successfully";

    const MSG_NOT_ALLOWED = "responses.msg_not_allowed";
    const MSG_NOT_AUTHENTICATED = "not authenticated";
    const MSG_NOT_FOUND = "not found";
    const MSG_USER_NOT_FOUND = "user not found";
    const MSG_WRONG_PASSWORD = "responses.wrong password";
    const MSG_SUCCESS = "success";
    const MSG_FAILED = "failed";
    const MSG_LOGIN_SUCCESSFULLY = "login successfully";
    const MSG_LOGIN_FAILED = "login failed";


    /**
     * @param $message
     * @param null $content
     * @param int $status
     * @param string $conventionType
     * @return \Illuminate\Http\JsonResponse
     */
    public static function respondSuccess($message, $content = null, $status = 200)
    {

        return response()->json([
            'result' => 'success',
            'content' => $content,
            'message' => $message,
            'status' => $status
        ]);
    }

    /**
     * @param $message
     * @return \Illuminate\Http\JsonResponse
     */
    public static function respondError($message, $status = 500)
    {
        return response()->json([
            'result' => 'failed',
            'content' => null,
            'message' => $message,
            'status' => $status
        ]);
    }

    /**
     * @param $url
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @extra function to download file from local storage
     */
    public static function downloadFile($url)
    {
        return response()->download(public_path('storage/' . $url));
    }

    /**
     * @param $zipName
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     *  * @extra function to download file from local storage as zip file type
     */
    public static function downloadProject($zipName)
    {
        $headers = ['Content-Type: application/zip'];
        return response()->download($zipName, '', $headers);
    }

    /**
     * @param $items
     * @return mixed
     * @desc convert array to an xml object
     */
    public static function responseXml($items)
    {
        // we need to convert items keys to underscore numbers or words
        foreach ($items as $key => $image) {
            $data['item' . $key]["_id"] = $image->id;
            $data['item' . $key]["adCode"] = $image->adCode;
            $data['item' . $key]["imageStatus"] = $image->imageStatus;
            $data['item' . $key]["imageUrl"] = $image->imageUrl;
        }
        return ArrayToXml::convert($data, [], true, 'UTF-8', '1.1', [], true);
    }
}
