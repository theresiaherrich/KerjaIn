<?php

namespace App\Http\Controllers\Api;

use App\Models\Application;
use App\Models\Disability;
use App\Models\jobsIn;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApplicationResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\ApplicationSubmitted;

class ApplicationController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('admin')->only(['index']);
    }

    public function index()
    {

        $Application = Application::latest()->paginate(5);

        return new ApplicationResource( 'List Data Application', $Application);
    }


    public function store(Request $request)
    {
        $user = auth('api')->user();

        $validator = Validator::make($request->all(), [
            'jobs_name' => 'required | exists:jobs_ins,name',
            'phone'   => 'required',
            'date_of_birth' => 'required',
            'gender' => 'required',
            'disability_type' => 'required | exists:disabilities,type',
            'cv' => 'required|file|mimes:jpg,png,pdf|max:2048',
            'cover_letter' => 'required|file|mimes:jpg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $jobsIn = jobsIn::where('name', $request->jobs_name)->first();
        $disability = Disability::where('type', $request->disability_type)->first();
        $cvPath = $this->uploadToSupabase($request->file('cv'));
        $coverLetterPath = $this->uploadToSupabase($request->file('cover_letter'));

        $Application = Application::create([
            'user_id' => $user->id,
            'jobs_id' => $jobsIn->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'disability_id' => $disability->id,
            'cv' => $cvPath,
            'cover_letter' => $coverLetterPath,
        ]);

        Mail::to($user->email)->send(new ApplicationSubmitted($Application));

        return new ApplicationResource( 'Data Application Berhasil Ditambahkan!', $Application);
    }

    public function show($id)
    {
        $Application = Application::find($id);


        return new ApplicationResource( 'Detail Data Application!', $Application);
    }

    public function update(Request $request, $id)
    {


        $validator = Validator::make($request->all(), [
           'jobs_name' => 'required | exists:jobs_ins,name',
            'name'     => 'required',
            'email'   => 'required',
            'phone'   => 'required',
            'date_of_birth' => 'required',
            'gender' => 'required',
            'disability_type' => 'required | exists:disabilities,type',
            'cv' => 'required|file|mimes:jpg,png,pdf|max:2048',
            'cover_letter' => 'required|file|mimes:jpg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $application = Application::findOrFail($id);
        $jobsIn = jobsIn::where('name', $request->jobs_name)->first();
        $disability = Disability::where('type', $request->disability_type)->first();

        if ($request->hasFile('cv')) {
            $this->deleteFromSupabase($application->cv);
            $application->cv = $this->uploadToSupabase($request->file('cv'));
        }
        if ($request->hasFile('cover_letter')) {
            $this->deleteFromSupabase($application->cover_letter);
            $application->cover_letter = $this->uploadToSupabase($request->file('cover_letter'));
        }

        $application->update([
            'jobs_id' => $jobsIn->id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'disability_id' => $disability->id,
        ]);

        return new ApplicationResource( 'Data Application Berhasil Diubah!', $application);
    }

    public function destroy($id)
    {

        $Application = Application::find($id);

        $this->deleteFromSupabase($Application->cv);
        $this->deleteFromSupabase($Application->cover_letter);

        $Application->delete();

        return new ApplicationResource( 'Data Application Berhasil Dihapus!', null);
    }

    private function uploadToSupabase($file)
{
    $supabaseUrl = env('SUPABASE_URL');
    $supabaseKey = env('SUPABASE_KEY');
    $bucketName = env('SUPABASE_BUCKET', 'files');

    if (!$supabaseUrl || !$supabaseKey) {
        throw new \Exception('Supabase URL atau Key tidak ditemukan di .env');
    }

    $fileName = time() . '_' . $file->getClientOriginalName();
    $filePath = "files/{$fileName}";

    $fileContent = file_get_contents($file->getRealPath());

    $response = Http::withHeaders([
        'apikey'        => $supabaseKey,
        'Authorization' => 'Bearer ' . $supabaseKey,
        'Content-Type'  => $file->getMimeType(),
    ])->withBody($fileContent, $file->getMimeType())
      ->put("$supabaseUrl/storage/v1/object/$bucketName/$filePath");

    if ($response->failed()) {
        throw new \Exception('Failed to upload file to Supabase: ' . $response->body());
    }

    return "$supabaseUrl/storage/v1/object/public/$bucketName/$filePath";
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

    public function userApplications()
{
    $user = auth('api')->user();

    $applications = Application::where('user_id', $user->id)
        ->with('jobsIn')
        ->latest()
        ->get();

    return response()->json([
        'message' => 'Riwayat Lamaran',
        'data' => $applications
    ]);
}




}
