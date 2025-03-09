<?php

namespace App\Http\Controllers\Api;

use App\Models\Program;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProgramResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class ProgramController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['index', 'show']]);
        $this->middleware('admin')->only(['store', 'update', 'destroy']);
    }

    public function index()
    {

        $Program = Program::latest()->paginate(5);

        return new ProgramResource( 'List Data Program', $Program);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image'     => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'name'     => 'required',
            'date'   => 'required',
            'price'   => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $imagePath = $this->uploadToSupabase($request->file('image'));


        $Program = Program::create([
            'image'     => $imagePath,
            'name'     => $request->name,
            'date'   => $request->date,
            'price'   => $request->price,
        ]);

        return new ProgramResource( 'Data Program Berhasil Ditambahkan!', $Program);
    }

    public function show($id)
    {
        $Program = Program::find($id);

        return new ProgramResource( 'Detail Data Program!', $Program);
    }

    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'date'   => 'required',
            'price'   => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $Program = Program::find($id);

        if ($request->hasFile('image')) {

            $imagePath = $this->uploadToSupabase($request->file('image'));
            $this->deleteFromSupabase($Program->image);

            $Program->update([
                'image'     => $imagePath,
                'name'     => $request->name,
                'date'   => $request->date,
                'price'   => $request->price,
            ]);

        } else {

            $Program->update([
               'name'     => $request->name,
                'date'   => $request->date,
                'price'   => $request->price,
            ]);
        }

        return new ProgramResource( 'Data Program Berhasil Diubah!', $Program);
    }

    public function destroy($id)
    {

        $Program = Program::find($id);

        $this->deleteFromSupabase($Program->image);

        $Program->delete();

        return new ProgramResource( 'Data Program Berhasil Dihapus!', null);
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
