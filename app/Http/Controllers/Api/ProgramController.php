<?php

namespace App\Http\Controllers\Api;

use App\Models\Program;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProgramResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Exception;
class ProgramController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['index', 'show']]);
        $this->middleware('admin')->only(['store', 'update', 'destroy']);
    }

    public function index(Request $request)
    {
        try{
            $search = $request->input('search');

            $programs = Program::search($search)
                ->orderBy('created_at', 'desc')
                ->paginate(8);

            return response()->json([
                'message' => 'List Data Program',
                'data' => $programs
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function store(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'image'     => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'name'     => 'required',
                'date'   => 'required',
                'price'   => 'required',
                'description'   => 'required',
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
                'description'   => $request->description,
            ]);

            return new ProgramResource( 'Data Program Berhasil Ditambahkan!', $Program);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try{
            $Program = Program::find($id);

            return new ProgramResource( 'Detail Data Program!', $Program);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try{
        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'date'   => 'required',
            'price'   => 'required',
            'description'   => 'required',
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
                'description'   => $request->description,
            ]);

        } else {

            $Program->update([
               'name'     => $request->name,
                'date'   => $request->date,
                'price'   => $request->price,
                'description'   => $request->description,
            ]);
        }

        return new ProgramResource( 'Data Program Berhasil Diubah!', $Program);
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
    }

    public function destroy($id)
    {
        try{
            $Program = Program::find($id);

            $this->deleteFromSupabase($Program->image);

            $Program->delete();

            return new ProgramResource( 'Data Program Berhasil Dihapus!', null);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
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
