<?php

namespace App\Http\Controllers\Api;

use App\Models\Story;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\StoryResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class StoryController extends Controller
{
    /**
     * index
     *
     * @return void
     */
    public function index()
    {

        $Story = Story::latest()->paginate(5);

        return new StoryResource( 'List Data Story', $Story);
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

        $Story = Story::create([
            'name'     => $request->name,
            'description'   => $request->description,
        ]);

        return new StoryResource( 'Data Story Berhasil Ditambahkan!', $Story);
    }

    public function show($id)
    {
        $Story = Story::find($id);

        return new StoryResource('Detail Data Story!', $Story);
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

        $Story = Story::find($id);

            $Story->update([
                'name'     => $request->name,
                'description'   => $request->description,
            ]);


        return new StoryResource( 'Data Story Berhasil Diubah!', $Story);
    }

    public function destroy($id)
    {

        $Story = Story::find($id);

        $Story->delete();

        return new StoryResource('Data Story Berhasil Dihapus!', null);
    }
}
