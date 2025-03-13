<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Profile;
use App\Models\Disability;
use App\Models\Education;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Exception;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return response()->json(['error' => 'User tidak ditemukan'], 401);
            }

            $profile = Profile::where('user_id', $user->id)->first();
            if (!$profile) {
                return response()->json(['error' => 'Profile tidak ditemukan'], 404);
            }

            $supabaseUrl = env('SUPABASE_URL');
            $bucketName = env('SUPABASE_BUCKET', 'files');

            $imageUrl = $profile->image
                ? (str_starts_with($profile->image, 'http')
                    ? $profile->image
                    : "$supabaseUrl/storage/v1/object/public/$bucketName/" . $profile->image)
                : null;

            return response()->json([
                'message' => 'Profile berhasil diambil',
                'data' => [
                    'user' => $user->only(['id', 'username', 'name', 'email']),
                    'profile' => [
                        'phone' => $profile->phone,
                        'date_of_birth' => $profile->date_of_birth,
                        'address' => $profile->address,
                        'gender' => $profile->gender,
                        'education' => $profile->education->level ?? null,
                        'disability' => $profile->disability->type ?? null,
                        'image' => $imageUrl,
                    ],
                ],
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $user = auth('api')->user();
            $profile = $user->profile;

            if (!$profile) {
                return response()->json(['error' => 'Profile tidak ditemukan'], 404);
            }

            $validator = Validator::make($request->all(), [
                'username' => 'sometimes|required|unique:users,username,' . $user->id,
                'name' => 'sometimes|required',
                'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
                'password' => 'sometimes|min:6',
                'image' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
                'phone' => 'sometimes|required|numeric',
                'date_of_birth' => 'sometimes|date',
                'address' => 'sometimes|string',
                'gender' => 'sometimes|in:Laki-laki,Perempuan',
                'education_name' => 'sometimes|exists:educations,level',
                'disability_name' => 'sometimes|exists:disabilities,type',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            if ($request->has('password')) {
                $user->password = bcrypt($request->password);
            }

            if ($request->has('education_name')) {
                $education = Education::where('level', $request->education_name)->first();
                if ($education) {
                    $profile->education_id = $education->id;
                }
            }

            if ($request->has('disability_name')) {
                $disability = Disability::where('type', $request->disability_name)->first();
                if ($disability) {
                    $profile->disability_id = $disability->id;
                }
            }

            $imagePath = $profile->image;

            if ($request->hasFile('image')) {
                if ($profile->image && $profile->image !== 'default.png') {
                    $this->deleteFromSupabase($profile->image);
                }

                $imagePath = $this->uploadToSupabase($request->file('image'));
            }

            $user->update($request->only(['username', 'name', 'email']));
            $profile->update($request->only(['phone', 'date_of_birth', 'address', 'gender', 'education_id', 'disability_id']));
            $profile->image = $imagePath;
            $profile->save();

            return response()->json([
                'message' => 'Profile berhasil diperbarui',
                'data' => [
                    'user' => $user->only(['id', 'username', 'name', 'email']),
                    'profile' => [
                        'phone' => $profile->phone,
                        'date_of_birth' => $profile->date_of_birth,
                        'address' => $profile->address,
                        'gender' => $profile->gender,
                        'education' => $profile->education->level ?? null,
                        'disability' => $profile->disability->type ?? null,
                        'image' => $imagePath,
                    ],
                ],
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
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
            \Log::error('Upload Gagal: ' . $response->body());
            throw new \Exception('Failed to upload file to Supabase: ' . $response->body());
        }

        return "$supabaseUrl/storage/v1/object/public/$bucketName/$imagePath";
    }

    private function deleteFromSupabase($fileUrl)
    {
        $supabaseUrl = env('SUPABASE_URL');
        $supabaseKey = env('SUPABASE_KEY');
        $bucketName = env('SUPABASE_BUCKET', 'files');

        if (!$fileUrl || !str_contains($fileUrl, "$supabaseUrl/storage/v1/object/public/$bucketName/")) {
            return response()->json(['error' => 'File URL tidak valid'], 400);
        }

        $filePath = str_replace("$supabaseUrl/storage/v1/object/public/$bucketName/", '', $fileUrl);

        $response = Http::withHeaders([
            'apikey'        => $supabaseKey,
            'Authorization' => 'Bearer ' . $supabaseKey,
        ])->delete("$supabaseUrl/storage/v1/object/$bucketName/$filePath");

        if ($response->failed() && $response->status() !== 404) {
            throw new \Exception('Failed to delete file from Supabase: ' . $response->body());
        }

        return true;
    }
}
