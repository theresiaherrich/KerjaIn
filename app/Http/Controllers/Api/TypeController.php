<?php

namespace App\Http\Controllers\Api;

use App\Models\Type;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\TypeResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Exception;
class TypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['index', 'show']]);
        $this->middleware('admin')->only(['store', 'update', 'destroy']);
    }

    public function index()
    {
        try{
            $Type = Type::latest()->paginate(5);

            return new TypeResource('List Data Type', $Type);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'duration'     => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $Type = Type::create([
                'duration'     => $request->duration,
            ]);

            return new TypeResource( 'Data Type Berhasil Ditambahkan!', $Type);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try{
            $Type = Type::find($id);

            return new TypeResource( 'Detail Data Type!', $Type);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try{
            $validator = Validator::make($request->all(), [
                'duration'     => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $Type = Type::find($id);

                $Type->update([
                    'duration'     => $request->duration,
                ]);

            return new TypeResource( 'Data Type Berhasil Diubah!', $Type);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try{
            $Type = Type::find($id);

            $Type->delete();

            return new TypeResource( 'Data Type Berhasil Dihapus!', null);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
