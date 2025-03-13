<?php

namespace App\Http\Controllers\Api;

use App\Models\Education;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\EducationResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Exception;
class EducationController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('admin')->only(['store', 'update', 'destroy', 'index']);
    }


    public function index()
    {
        try{
            $Education = Education::latest()->paginate(5);

            return new EducationResource( 'List Data Education', $Education);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function store(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'level'     => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $Education = Education::create([
                'level'     => $request->level,
            ]);

            return new EducationResource( 'Data Education Berhasil Ditambahkan!', $Education);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try{
            $Education = Education::find($id);

            return new EducationResource( 'Detail Data Education!', $Education);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try{
            $validator = Validator::make($request->all(), [
                'level'     => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $Education = Education::find($id);

                $Education->update([
                    'level'     => $request->level,
                ]);

            return new EducationResource('Data Education Berhasil Diubah!', $Education);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try{
            $Education = Education::find($id);

            $Education->delete();

            return new EducationResource( 'Data Education Berhasil Dihapus!', null);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
