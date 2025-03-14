<?php

namespace App\Http\Controllers\Api;

use App\Models\Disability;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\DisabilityResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Exception;

class DisabilityController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['index', 'show']]);
        $this->middleware('admin')->only(['store', 'update', 'destroy', 'index']);
    }

    public function index()
    {
        try{
            $Disability = Disability::latest()->paginate(5);

            return new DisabilityResource( 'List Data Disability', $Disability);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function store(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'type'     => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $Disability = Disability::create([
                'type'     => $request->type,
            ]);

            return new DisabilityResource( 'Data Disability Berhasil Ditambahkan!', $Disability);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try{
            $Disability = Disability::find($id);

            return new DisabilityResource( 'Detail Data Disability!', $Disability);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try{
            $validator = Validator::make($request->all(), [
                'type'     => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $Disability = Disability::find($id);

                $Disability->update([
                    'type'     => $request->type,
                ]);

            return new DisabilityResource('Data Disability Berhasil Diubah!', $Disability);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try{
            $Disability = Disability::find($id);

            $Disability->delete();

            return new DisabilityResource( 'Data Disability Berhasil Dihapus!', null);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
