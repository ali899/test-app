<?php

namespace App\Http\Controllers\Api;

use App\Bhads;
use App\BhadsStatus;
use App\Exports\StatusExport;
use App\Helpers\CloudHelper;
use App\Helpers\JsonResponse;
use App\Helpers\ResponseStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
class AdsController extends Controller
{
    private $storage;

    public function __construct()
    {

    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @desc return total images in the bucket
     * @author ali.shaaban
     */
    public function totalImages()
    {
        try {
            $total_images = CloudHelper::getImagesCount();
            if (is_null($total_images))
                CloudHelper::createInitialCounters();
            $total_images = CloudHelper::getImagesCount();
            return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, ["totalImages" => $total_images]);
        } catch (\Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @desc get number of remaining images (annotator hasn’t seen yet)
     * @author ali.shaaban
     */
    public function remainingImages()
    {
        try {
            $total_images = CloudHelper::getImagesCount();
            $seen_images = BhadsStatus::count();
            $remaining = $total_images - $seen_images;
            return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, ["remaining" => $remaining]);
        } catch (\Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @desc get two counters represents number of total ads “bh_ads” and number of total images status records
     * @author ali.shaaban
     */
    public function adsCounters()
    {
        try {
            $total_ads = Bhads::count();
            $total_status = BhadsStatus::count();
            return JsonResponse::respondSuccess(
                JsonResponse::MSG_SUCCESS,
                ["total_ads" => $total_ads, "total_status" => $total_status]);
        } catch (\Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @desc get three counter values represent (total images annotated) and (accepted images) and (rejected images)
     * @author ali.shaaban
     */
    public function imagesStatusCounters()
    {
        try {
            // to do here
            $total_status = BhadsStatus::count();
            $accepted = BhadsStatus::where('imageStatus', true)
                ->count();
            $rejected = BhadsStatus::where('imageStatus', false)
                ->count();
            return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS,
                ["total_status" => $total_status, "accepted" => $accepted, "rejected" => $rejected]);
        } catch (\Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @desc Set image status (status is true when “accepted” or false when “rejected”)
     * @author ali.shaaban
     */
    public function setImageStatus(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),
                [
                    'adCode' => 'required',
                    'imgIndex' => 'required',
                    'imgUrl' => 'required',
                    'imageStatus' => 'required|boolean',
                ]);
            if ($validator->fails()) {
                return JsonResponse::respondError($validator->errors());
            }
            $status = new BhadsStatus();
            $status->adCode = (int)$request->input("adCode");
            $status->imgIndex = (int)$request->input("imgIndex");
            $status->imgUrl = $request->input("imgUrl");
            $status->imageStatus = (bool)$request->input("imageStatus");
            $status->annotatedBy = auth('api')->user()->username;
            $status->save();
            return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, "Image status added successfully");
        } catch (\Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @desc  Remove image (removes only from the bucket), its name is provided as a parameter
     * @author ali.shaaban
     */
    public function removeImage(Request $request, $imageName)
    {
        try {
            $disk = Storage::disk('gcs');
            if ($disk->exists($imageName)) {
                $totalImages = CloudHelper::getImagesCount();
                $totalImages--;
                CloudHelper::updateImagesCount($totalImages);
            }
            $deleted = CloudHelper::deleteObject($imageName);
            if ($deleted)
                return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, "deleted successfully");
            else
                return JsonResponse::respondError("delete error or image not found");

        } catch (\Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @desc Remove ad (removes ad record with all its related images in the bucket) its id is provided as a parameter
     * @author ali.shaaban
     */
    public function removeAds(Request $request, $id)
    {
        try {
            $ad = Bhads::find($id);
            if (!$ad) {
                return JsonResponse::respondError("resource not found", ResponseStatus::NOT_FOUND);
            }
            $counter = 0;
            if ($ad) {
                $images = BhadsStatus::where('adCode', $ad->adCode)->get();
                foreach ($images as $image) {
                    $ids = array_push($image->id);
                    $imageName = $image->adCode . $image->imageIndex . ".jpg";
                    $disk = Storage::disk('gcs');
                    if ($disk->exists($imageName)) {
                        CloudHelper::deleteObject($imageName);
                        $counter++;
                    }
                }
                $totalImages = CloudHelper::getImagesCount();
                $totalImages = $totalImages - $counter;
                CloudHelper::updateImagesCount($totalImages);
                // remove bulk of ids
                BhadsStatus::whereIn('id', $images->pluck('id'))->delete();
                $ad->delete();
            }

            return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, null);
        } catch (\Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     * @desc export imageStatus as type
     * @author ali.shaaban
     */
    public function export(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),
                [
                    'type' => 'required',
                    Rule::in(['json', 'xml', 'csv']),
                ]);
            if ($validator->fails()) {
                return JsonResponse::respondError($validator->errors());
            }
            switch ($request->input("type")) {
                case "json":
                    $images = BhadsStatus::where('imageStatus', false)->get();
                    return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $images);
                    break;
                case "xml":
                    $images = BhadsStatus::where('imageStatus', false)->get();
                    return JsonResponse::responseXml($images);
                    break;
                case "csv":
                    return Excel::download(new StatusExport, 'status.csv', \Maatwebsite\Excel\Excel::CSV, ['Content-Type' => 'text/csv']);
                    break;
                default:
                    return JsonResponse::respondError("unknown type");
            }
        } catch (\Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @param $imagePath
     * @return string
     * @throws \Exception
     */
    public function getSignedImageUrl(Request $request, $imagePath)
    {
        return CloudHelper::getSignedUrl($imagePath);
    }
}
