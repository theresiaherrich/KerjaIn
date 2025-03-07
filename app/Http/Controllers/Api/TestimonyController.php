<?php

namespace App\Http\Controllers\Api;

use App\Models\Testimony;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\TestimonyResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class TestimonyController extends Controller
{
    /**
     * index
     *
     * @return void
     */
    public function index()
    {

        $Testimony = Testimony::latest()->paginate(5);

        return new TestimonyResource( 'List Data Testimony', $Testimony);
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

        $Testimony = Testimony::create([
            'name'     => $request->name,
            'description'   => $request->description,
        ]);

        return new TestimonyResource('Data Testimony Berhasil Ditambahkan!', $Testimony);
    }

    public function show($id)
    {
        $Testimony = Testimony::find($id);

        return new TestimonyResource( 'Detail Data Testimony!', $Testimony);
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

        $Testimony = Testimony::find($id);

            $Testimony->update([
                'name'     => $request->name,
                'description'   => $request->description,
            ]);


        return new TestimonyResource( 'Data Testimony Berhasil Diubah!', $Testimony);
    }

    public function destroy($id)
    {

        $Testimony = Testimony::find($id);

        $Testimony->delete();

        return new TestimonyResource('Data Testimony Berhasil Dihapus!', null);
    }
}
