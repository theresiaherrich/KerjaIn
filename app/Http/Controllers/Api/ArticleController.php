<?php

namespace App\Http\Controllers\Api;

use App\Models\Article;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Exception;

class ArticleController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['index', 'show']]);
        $this->middleware('admin')->only(['store', 'update', 'destroy']);
    }

    public function index()
    {
        try{
            $Article = Article::latest()->paginate(4);

            return new ArticleResource( 'List Data Article', $Article);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function store(Request $request)
    {
        try{
        $validator = Validator::make($request->all(), [
            'image'     => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title'     => 'required',
            'content'   => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $imagePath = $this->uploadToSupabase($request->file('image'));


        $Article = Article::create([
            'image'     => $imagePath,
            'title'     => $request->title,
            'content'   => $request->content,
        ]);

        return new ArticleResource('Data Article Berhasil Ditambahkan!', $Article);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try{
            $Article = Article::find($id);

            return new ArticleResource('Detail Data Article!', $Article);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try{
        $validator = Validator::make($request->all(), [
            'title'     => 'required',
            'content'   => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $Article = Article::find($id);

        if ($request->hasFile('image')) {

            $imagePath = $this->uploadToSupabase($request->file('image'));
            $this->deleteFromSupabase($Article->image);

            $Article->update([
                'image'     => $imagePath,
                'title'     => $request->title,
                'content'   => $request->content,
            ]);
        } else {

            $Article->update([
                'title'     => $request->title,
                'content'   => $request->content,
            ]);
        }

        return new ArticleResource( 'Data Article Berhasil Diubah!', $Article);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {

        try{
        $Article = Article::find($id);

        $this->deleteFromSupabase($Article->image);

        $Article->delete();

        return new ArticleResource('Data Article Berhasil Dihapus!', null);
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
