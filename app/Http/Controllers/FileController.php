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

    public function uploadResource(Request $request)
    {
        $file = $request->file('files');
        $FILE_NAME = Storage::disk('aws')->putFile('', $file, 'public');
        $resource = $this->resourceServices->saveResource($FILE_NAME, $file->getSize());
        return response()->json(['resource' => $resource]);
    }

    public function deleteResouce($path)
    {
        if (!$resource = $this->resourceServices->getResourceByPath($path))
            return response()->json(['response' => 'No resource found'], 400);
        $resource->delete();
        Storage::disk('aws')->delete($path);

        return response()->json(['resource_id' => $resource->resource_id]);
    }
}
