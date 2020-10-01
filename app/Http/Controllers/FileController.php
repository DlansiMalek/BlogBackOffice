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

    public function uploadAbstractBook(Request $request , $congressId)
    {
        
        $file = $request->file("abstract-book-file");
        $chemin = config('media.user-abstract-book');
        $path = $file->store($chemin);
        $savedPath = str_replace('user-abstract-book/', '', $path);
        $congress = $this->congressServices->getById($congressId);
        $congress->path_abstract_book = $savedPath;
        $congress->update();
         return response()->json(['path' => $savedPath]); 
    }

    public function downloadBook($path)
    {
        if (!$path)
            return response()->json(['response' => 'No Book Found'], 400);

        $chemin = config('media.user-abstract-book');
        return response()->download(storage_path('app/' . $chemin . "/" . $path));
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

    public function uploadResource(Request $request)
    {
        $file = $request->file('files'); 
        $chemin = config('media.resource');
        $path = $file->storeAs($chemin,$file->getClientOriginalName());
        $savedPath = str_replace('resource/', '', $path);
        $resource = $this->resourceServices->saveResource($savedPath, $file->getSize(),$savedPath);
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

    public function deleteLogoCongress($path)
    {

        $chemin = config('media.congress-logo');
        $path = $chemin . '/' . $path;
        Storage::delete($path);

        return response()->json(['response' => 'congress logo deleted', 'media' => $path], 201);
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

    public function getBannerCongress($path)
    {
        $chemin = config('media.congress-banner');
        return response()->download(storage_path('app/' . $chemin . "/" . $path));
    }
}
