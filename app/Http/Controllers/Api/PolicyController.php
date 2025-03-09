<?php

namespace App\Http\Controllers\Api;

use App\Models\Policy;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PolicyResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class PolicyController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('admin')->only(['store', 'update', 'destroy']);
    }

    public function index()
    {

        $Policy = Policy::latest()->paginate(5);

        return new PolicyResource('List Data Policy', $Policy);
    }


    public function store(Request $request)
    {
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
    }

    public function show($id)
    {
        $Policy = Policy::find($id);

        return new PolicyResource('Detail Data Policy!', $Policy);
    }

    public function update(Request $request, $id)
    {

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
    }

    public function destroy($id)
    {

        $Policy = Policy::find($id);

        $Policy->delete();

        return new PolicyResource('Data Policy Berhasil Dihapus!', null);
    }
}
