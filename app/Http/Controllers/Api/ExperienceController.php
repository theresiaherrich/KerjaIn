<?php

namespace App\Http\Controllers\Api;

use App\Models\Experience;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ExperienceResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ExperienceController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('admin')->only(['store', 'update', 'destroy', 'index']);
    }

    public function index()
    {

        $Experience = Experience::latest()->paginate(5);

        return new ExperienceResource( 'List Data Experience', $Experience);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'duration'     => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $Experience = Experience::create([
            'duration'     => $request->duration,
        ]);

        return new ExperienceResource( 'Data Experience Berhasil Ditambahkan!', $Experience);
    }

    public function show($id)
    {
        $Experience = Experience::find($id);

        return new ExperienceResource('Detail Data Experience!', $Experience);
    }

    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'duration'     => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $Experience = Experience::find($id);

            $Experience->update([
                'duration'     => $request->duration,
            ]);


        return new ExperienceResource('Data Experience Berhasil Diubah!', $Experience);
    }

    public function destroy($id)
    {

        $Experience = Experience::find($id);

        $Experience->delete();

        return new ExperienceResource( 'Data Experience Berhasil Dihapus!', null);
    }
}
