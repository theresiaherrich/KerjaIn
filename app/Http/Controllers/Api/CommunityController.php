<?php

namespace App\Http\Controllers\Api;

use App\Models\Community;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommunityResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class CommunityController extends Controller
{
    /**
     * index
     *
     * @return void
     */
    public function index()
    {

        $Community = Community::latest()->paginate(5);

        return new CommunityResource( 'List Data Community', $Community);
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
            'name'     => 'required',
            'description'   => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $Community = Community::create([
            'name'     => $request->name,
            'description'   => $request->description,
        ]);

        return new CommunityResource( 'Data Community Berhasil Ditambahkan!', $Community);
    }

    public function show($id)
    {
        $Community = Community::find($id);

        return new CommunityResource( 'Detail Data Community!', $Community);
    }

    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'description'   => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $Community = Community::find($id);

            $Community->update([
                'name'     => $request->name,
                'description'   => $request->description,
            ]);


        return new CommunityResource( 'Data Community Berhasil Diubah!', $Community);
    }

    public function destroy($id)
    {

        $Community = Community::find($id);

        $Community->delete();

        return new CommunityResource( 'Data Community Berhasil Dihapus!', null);
    }
}
