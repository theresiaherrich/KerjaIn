<?php

namespace App\Http\Controllers\Api;

use App\Models\Ads;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdsResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Exception;

class AdsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['index', 'show']]);
        $this->middleware('admin')->only(['store', 'update', 'destroy']);
    }

    public function index()
    {
        try{
            $ads = Ads::latest()->paginate(5);
            return new AdsResource('List Data Ads', $ads);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try{
        $validator = Validator::make($request->all(), [
            'image'       => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'name'        => 'required',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        try {
            $imagePath = $this->uploadToSupabase($request->file('image'));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        $ads = Ads::create([
            'image'       => $imagePath,
            'name'        => $request->name,
            'description' => $request->description,
        ]);

        return new AdsResource('Data Ads Berhasil Ditambahkan!', $ads);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try{
            $ads = Ads::find($id);
            if (!$ads) {
                return response()->json(['error' => 'Data tidak ditemukan!'], 404);
            }
            return new AdsResource('Detail Data Ads!', $ads);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try{
        $validator = Validator::make($request->all(), [
            'name'        => 'required',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $ads = Ads::find($id);
        if (!$ads) {
            return response()->json(['error' => 'Data tidak ditemukan!'], 404);
        }

        try {
            if ($request->hasFile('image')) {
                $imagePath = $this->uploadToSupabase($request->file('image'));
                $this->deleteFromSupabase($ads->image);
                $ads->update(['image' => $imagePath]);
            }
            $ads->update($request->only(['name', 'description']));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return new AdsResource('Data Ads Berhasil Diubah!', $ads);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try{
            $ads = Ads::find($id);
            if (!$ads) {
                return response()->json(['error' => 'Data tidak ditemukan!'], 404);
            }

            try {
                $this->deleteFromSupabase($ads->image);
                $ads->delete();
            } catch (Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

            return new AdsResource('Data Ads Berhasil Dihapus!', null);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function uploadToSupabase($file)
    {
        $supabaseUrl = env('SUPABASE_URL');
        $supabaseKey = env('SUPABASE_KEY');
        $bucketName  = env('SUPABASE_BUCKET', 'files');

        if (!$supabaseUrl || !$supabaseKey) {
            throw new Exception('Konfigurasi Supabase tidak ditemukan.');
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
            throw new Exception('Gagal mengunggah file ke Supabase.');
        }

        return "$supabaseUrl/storage/v1/object/public/$bucketName/$imagePath";
    }

    private function deleteFromSupabase($fileUrl)
    {
        if (!$fileUrl) {
            throw new Exception('URL file tidak valid.');
        }

        $supabaseUrl = env('SUPABASE_URL');
        $supabaseKey = env('SUPABASE_KEY');
        $bucketName  = env('SUPABASE_BUCKET', 'files');

        if (!$supabaseUrl || !$supabaseKey) {
            throw new Exception('Konfigurasi Supabase tidak ditemukan.');
        }

        $filePath = str_replace("$supabaseUrl/storage/v1/object/public/$bucketName/", '', $fileUrl);

        $response = Http::withHeaders([
            'apikey'        => $supabaseKey,
            'Authorization' => 'Bearer ' . $supabaseKey,
        ])->delete("$supabaseUrl/storage/v1/object/$bucketName/$filePath");

        if ($response->failed()) {
            throw new Exception('Gagal menghapus file dari Supabase.');
        }
    }
}
