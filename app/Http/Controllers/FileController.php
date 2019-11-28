<?php


namespace App\Http\Controllers;


use App\Services\FileServices;
use App\Services\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    protected $fileServices;

    function __construct(FileServices $fileService)
    {
        $this->fileServices = $fileService;
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


}