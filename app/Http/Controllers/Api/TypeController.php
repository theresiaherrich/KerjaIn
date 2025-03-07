<?php

namespace App\Http\Controllers\Api;

use App\Models\Type;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\TypeResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class TypeController extends Controller
{
    /**
     * index
     *
     * @return void
     */
    public function index()
    {

        $Type = Type::latest()->paginate(5);

        return new TypeResource('List Data Type', $Type);
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
            'duration'     => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $Type = Type::create([
            'duration'     => $request->duration,
        ]);

        return new TypeResource( 'Data Type Berhasil Ditambahkan!', $Type);
    }

    public function show($id)
    {
        $Type = Type::find($id);

        return new TypeResource( 'Detail Data Type!', $Type);
    }

    public function update(Request $request, $id)
    {

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
    }

    public function destroy($id)
    {

        $Type = Type::find($id);

        $Type->delete();

        return new TypeResource( 'Data Type Berhasil Dihapus!', null);
    }
}
