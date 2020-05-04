<?php


namespace App\Http\Controllers;

use App\Services\UserServices;
use App\Services\FileServices;
use App\Services\ResourcesServices;
use App\Services\Utils;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    protected $fileServices;
    protected $userServices;
    protected $resourceServices;

    function __construct(FileServices $fileService, UserServices $userServices, ResourcesServices $resourceServices)
    {
        $this->fileServices = $fileService;
        $this->userServices = $userServices;
        $this->resourceServices = $resourceServices;
    }

    public function uploadCV(Request $request, $congressId, $userId)
    {

        if (!$user = $this->userServices->getUserById($userId))
            return response()->json(['response' => 'User not found'], 400);

        $file = $request->file("cv-file");
        $chemin = config('media.user-cv');
        $path = $file->store($chemin);
        if (!$user = $this->userServices->updateUserPathCV($path, $user))
            return response()->json(['response' => 'Path not found'], 400);

        return response()->json(['path' => $path]);
    }


    public function getUserCV($path)
    {
        if (!$path)
            return response()->json(['response' => 'No CV Found'], 400);

        $chemin = config('media.user-cv');
        return response()->download(storage_path('app/' . $chemin . "/" . $path));
    }

    public function deleteUserCV($path, $userId)
    {
        if (!$user = $this->userServices->getUserById($userId))
            return response()->json(['response' => 'user not found'], 400);

        $chemin = config('media.user-cv');
        $path = $chemin . '/' . $path;
        Storage::delete($path);
        $this->userServices->makeUserPathCvNull($user);
        return response()->json(['response' => 'user cv deleted', 'media' => $path], 201);
    }

    public function deleteLogoCongress($path)
    {

        $chemin = config('media.congress-logo');
        $path = $chemin . '/' . $path;
        Storage::delete($path);

        return response()->json(['response' => 'congress logo deleted', 'media' => $path], 201);
    }

    public function uploadLogo(Request $request)
    {
        $file = $request->file('logo-file');
        if (!Utils::verifyImg($file->getClientOriginalExtension())) {
            return response()->json(['response' => 'file must be of type image'], 400);
        }

        $chemin = config('media.congress-logo');

        $path = $file->store($chemin);

        return response()->json(['path' => $path]);
    }

    public function getLogoCongress($path)
    {
        $chemin = config('media.congress-logo');
        return response()->download(storage_path('app/' . $chemin . "/" . $path));
    }

    public function deleteBannerCongress($path)
    {

        $chemin = config('media.congress-banner');
        $path = $chemin . '/' . $path;
        Storage::delete($path);

        return response()->json(['response' => 'congress banner deleted', 'media' => $path], 201);
    }


    public function uploadBanner(Request $request)
    {
        $file = $request->file('banner-file');

        if (!Utils::verifyImg($file->getClientOriginalExtension())) {
            return response()->json(['response' => 'file must be of type image'], 400);
        }

        $chemin = config('media.congress-banner');

        $path = $file->store($chemin);

        return response()->json(['path' => $path]);
    }

    public function getBannerCongress($path)
    {
        $chemin = config('media.congress-banner');
        return response()->download(storage_path('app/' . $chemin . "/" . $path));
    }

    public function uploadResource(Request $request)
    {
        $file = $request->file('files');

        $chemin = config('media.resource');
        $path = $file->store($chemin);
        $savedPath = str_replace('resource/', '', $path);
        $resource = $this->resourceServices->saveResource($savedPath, $file->getSize());
        return response()->json(['resource' => $resource]);
    }
    public function getResouce($path)
    {
        $chemin = config('media.resource');
        return response()->download(storage_path('app/' . $chemin . "/" . $path));
    }

    public function deleteResouce($path)
    {
        if (!$resource = $this->resourceServices->getResourceByPath($path))
            return response()->json(['response' => 'No resource found'], 400);
        $resource->delete();
        $chemin = config('media.resource');
        $path = $chemin . '/' . $path;
        Storage::delete($path);

        return response()->json(['resource_id' => $resource->resource_id]);
    }
}
