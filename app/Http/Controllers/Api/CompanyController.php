<?php

namespace App\Http\Controllers\Api;

use App\Models\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class CompanyController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('admin')->only(['store', 'update', 'destroy', 'index']);
    }

    public function index()
    {

        $Company = Company::latest()->paginate(5);

        return new CompanyResource('List Data Company', $Company);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'logo'     => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'name'     => 'required',
            'location'     => 'required',
            'description'   => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $imagePath = $this->uploadToSupabase($request->file('logo'));


        $Company = Company::create([
            'logo'     => $imagePath,
            'name'     => $request->name,
            'location'     => $request->location,
            'description'   => $request->description,
        ]);

        return new CompanyResource( 'Data Company Berhasil Ditambahkan!', $Company);
    }

    public function show($id)
    {
        $Company = Company::find($id);

        return new CompanyResource( 'Detail Data Company!', $Company);
    }

    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'location'     => 'required',
            'description'   => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $Company = Company::find($id);

        if ($request->hasFile('logo')) {

            $imagePath = $this->uploadToSupabase($request->file('logo'));
            $this->deleteFromSupabase($Company->logo);

            $Company->update([
                'logo'     => $imagePath,
                'name'     => $request->name,
                'location'     => $request->location,
                'description'   => $request->description,
            ]);
        } else {

            $Company->update([
                'name'     => $request->name,
                'location'     => $request->location,
                'description'   => $request->description,
            ]);
        }

        return new CompanyResource( 'Data Company Berhasil Diubah!', $Company);
    }

    public function destroy($id)
    {

        $Company = Company::find($id);

        $this->deleteFromSupabase($Company->logo);

        $Company->delete();

        return new CompanyResource(true, 'Data Company Berhasil Dihapus!', null);
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
