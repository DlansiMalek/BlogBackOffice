<?php


namespace App\Http\Controllers;

use App\Services\UserServices;
use App\Services\CongressServices;
use App\Services\FileServices;
use App\Services\ResourcesServices;
use App\Services\Utils;
use App\Models\Resource;
use App\Models\Congress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    protected $fileServices;
    protected $userServices;
    protected $resourceServices;
    protected $congressServices;

    function __construct(FileServices $fileService, UserServices $userServices, ResourcesServices $resourceServices, CongressServices $congressServices)
    {
        $this->fileServices = $fileService;
        $this->userServices = $userServices;
        $this->resourceServices = $resourceServices;
        $this->congressServices = $congressServices;
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

    public function uploadResource(Request $request)
    {
        $file = $request->file('files'); 
        $FILE_NAME = Storage::disk('digitalocean')->putFile('', $file, 'public');
        $resource = $this->resourceServices->saveResource($FILE_NAME, $file->getSize());
        return response()->json(['resource' => $resource]);
    }

    public function deleteResouce($path)
    {
        if (!$resource = $this->resourceServices->getResourceByPath($path))
            return response()->json(['response' => 'No resource found'], 400);
        $resource->delete();
        Storage::disk('digitalocean')->delete($path);

        return response()->json(['resource_id' => $resource->resource_id]);
    }

    public function deleteLogoCongress($path)
    {

        $chemin = config('media.congress-logo');
        $path = $chemin . '/' . $path;
        Storage::delete($path);

        return response()->json(['response' => 'congress logo deleted', 'media' => $path], 201);
    }

    public function deleteBannerCongress($path)
    {

        $chemin = config('media.congress-banner');
        $path = $chemin . '/' . $path;
        Storage::delete($path);

        return response()->json(['response' => 'congress banner deleted', 'media' => $path], 201);
    }
}
