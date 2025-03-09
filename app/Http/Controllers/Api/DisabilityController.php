<?php

namespace App\Http\Controllers\Api;

use App\Models\Disability;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\DisabilityResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class DisabilityController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('admin')->only(['store', 'update', 'destroy', 'index']);
    }

    public function index()
    {

        $Disability = Disability::latest()->paginate(5);

        return new DisabilityResource( 'List Data Disability', $Disability);
    }


    public function store(Request $request)
    {
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
    }

    public function show($id)
    {
        $Disability = Disability::find($id);

        return new DisabilityResource( 'Detail Data Disability!', $Disability);
    }

    public function update(Request $request, $id)
    {

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
    }

    public function destroy($id)
    {

        $Disability = Disability::find($id);


        $Disability->delete();

        return new DisabilityResource( 'Data Disability Berhasil Dihapus!', null);
    }
}
