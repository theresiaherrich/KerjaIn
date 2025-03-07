<?php

namespace App\Http\Controllers\Api;

use App\Models\Article;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class ArticleController extends Controller
{
    /**
     * index
     *
     * @return void
     */
    public function index()
    {

        $Article = Article::latest()->paginate(5);

        return new ArticleResource( 'List Data Article', $Article);
    }

    /**
     * store
     *
     * @param  mixed $request
     * @return void
     */
    public function store(Request $request)
    {
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
    }

    public function show($id)
    {
        $Article = Article::find($id);

        return new ArticleResource('Detail Data Article!', $Article);
    }

    public function update(Request $request, $id)
    {

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
    }

    public function destroy($id)
    {

        $Article = Article::find($id);

        $this->deleteFromSupabase($Article->image);

        $Article->delete();

        return new ArticleResource('Data Article Berhasil Dihapus!', null);
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
