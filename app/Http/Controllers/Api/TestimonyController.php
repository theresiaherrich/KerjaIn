<?php

namespace App\Http\Controllers\Api;

use App\Models\Testimony;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\TestimonyResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Exception;
class TestimonyController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['index', 'show']]);
        $this->middleware('admin')->only(['store', 'update', 'destroy']);
    }

    public function index()
    {
        try{
            $Testimony = Testimony::latest()->paginate(4);

            return new TestimonyResource( 'List Data Testimony', $Testimony);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'name'     => 'required',
                'description'   => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $Testimony = Testimony::create([
                'name'     => $request->name,
                'description'   => $request->description,
            ]);

            return new TestimonyResource('Data Testimony Berhasil Ditambahkan!', $Testimony);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try{
            $Testimony = Testimony::find($id);

            return new TestimonyResource( 'Detail Data Testimony!', $Testimony);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try{
            $validator = Validator::make($request->all(), [
                'name'     => 'required',
                'description'   => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $Testimony = Testimony::find($id);

                $Testimony->update([
                    'name'     => $request->name,
                    'description'   => $request->description,
                ]);

            return new TestimonyResource( 'Data Testimony Berhasil Diubah!', $Testimony);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try{
            $Testimony = Testimony::find($id);

            $Testimony->delete();

            return new TestimonyResource('Data Testimony Berhasil Dihapus!', null);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
