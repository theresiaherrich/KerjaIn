<?php

namespace App\Http\Controllers\Api;

use App\Models\Ads;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdsResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class AdsController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['index', 'show']]);
        $this->middleware('admin')->only(['store', 'update', 'destroy']);
    }


    public function index()
    {

        $Ads = Ads::latest()->paginate(5);

        return new AdsResource( 'List Data Ads', $Ads);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image'     => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'name'     => 'required',
            'description'   => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $imagePath = $this->uploadToSupabase($request->file('image'));


        $Ads = Ads::create([
            'image'     => $imagePath,
            'name'     => $request->name,
            'description'   => $request->description,
        ]);

        return new AdsResource( 'Data Ads Berhasil Ditambahkan!', $Ads);
    }

    public function show($id)
    {
        $Ads = Ads::find($id);

        return new AdsResource( 'Detail Data Ads!', $Ads);
    }

    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'description'   => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $Ads = Ads::find($id);

        if ($request->hasFile('image')) {

            $imagePath = $this->uploadToSupabase($request->file('image'));
            $this->deleteFromSupabase($Ads->image);

            $Ads->update([
                'image'     => $imagePath,
                'name'     => $request->name,
                'description'   => $request->description,
            ]);

        } else {

            $Ads->update([
               'name'     => $request->name,
                'description'   => $request->description,
            ]);
        }

        return new AdsResource( 'Data Ads Berhasil Diubah!', $Ads);
    }

    public function destroy($id)
    {

        $Ads = Ads::find($id);

        $this->deleteFromSupabase($Ads->image);

        $Ads->delete();

        return new AdsResource( 'Data Ads Berhasil Dihapus!', null);
    }

    private function uploadToSupabase($file)
    {
        $supabaseUrl = env('SUPABASE_URL');
        $supabaseKey = env('SUPABASE_KEY');
        $bucketName = env('SUPABASE_BUCKET', 'files');

        if (!$supabaseUrl || !$supabaseKey) {
            throw new \Exception('Supabase URL atau Key tidak ditemukan di .env');
        }

        $imageName = time() . '_' . $file->getClientOriginalName();
        $imagePath = "files/{$imageName}";

        $fileContent = file_get_contents($file->getRealPath());

        $response = Http::withHeaders([
            'apikey'        => $supabaseKey,
            'Authorization' => 'Bearer ' . $supabaseKey,
            'Content-Type'  => $file->getMimeType(),
        ])->withBody($fileContent, $file->getMimeType())
          ->put("$supabaseUrl/storage/v1/object/$bucketName/$imagePath");

        if ($response->failed()) {
            throw new \Exception('Failed to upload file to Supabase: ' . $response->body());
        }

        return "$supabaseUrl/storage/v1/object/public/$bucketName/$imagePath";
    }

    private function deleteFromSupabase($fileUrl)
{
    $supabaseUrl = env('SUPABASE_URL');
    $supabaseKey = env('SUPABASE_KEY');
    $bucketName = env('SUPABASE_BUCKET', 'files');

    if (!$supabaseUrl || !$supabaseKey) {
        throw new \Exception('Supabase URL atau Key tidak ditemukan di .env');
    }

    // Ambil path file dari URL Supabase
    $filePath = str_replace("$supabaseUrl/storage/v1/object/public/$bucketName/", '', $fileUrl);

    $response = Http::withHeaders([
        'apikey'        => $supabaseKey,
        'Authorization' => 'Bearer ' . $supabaseKey,
    ])->delete("$supabaseUrl/storage/v1/object/$bucketName/$filePath");

    if ($response->failed()) {
        throw new \Exception('Failed to delete file from Supabase: ' . $response->body());
    }
}

}
