<?php

namespace App\Http\Controllers\Api;

use App\Models\Policy;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PolicyResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Exception;
class PolicyController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['index', 'show']]);
        $this->middleware('admin')->only(['store', 'update', 'destroy']);
    }

    public function index()
    {
        try{
            $Policy = Policy::latest()->paginate(5);

            return new PolicyResource('List Data Policy', $Policy);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function store(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'location'     => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $Policy = Policy::create([
                'location'     => $request->location,
            ]);

            return new PolicyResource( 'Data Policy Berhasil Ditambahkan!', $Policy);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try{
            $Policy = Policy::find($id);

            return new PolicyResource('Detail Data Policy!', $Policy);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try{
            $validator = Validator::make($request->all(), [
                'location'     => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $Policy = Policy::find($id);

                $Policy->update([
                    'location'     => $request->location,
                ]);

            return new PolicyResource('Data Policy Berhasil Diubah!', $Policy);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try{
            $Policy = Policy::find($id);

            $Policy->delete();

            return new PolicyResource('Data Policy Berhasil Dihapus!', null);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
