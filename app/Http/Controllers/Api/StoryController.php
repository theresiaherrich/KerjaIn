<?php

namespace App\Http\Controllers\Api;

use App\Models\Story;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\StoryResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Exception;
class StoryController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['index', 'show']]);
        $this->middleware('admin')->only(['store', 'update', 'destroy']);
    }

    public function index()
    {
        try{
            $Story = Story::latest()->paginate(5);

            return new StoryResource( 'List Data Story', $Story);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try{
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
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try{
            $Story = Story::find($id);

            return new StoryResource('Detail Data Story!', $Story);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try{
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
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try{
            $Story = Story::find($id);

            $Story->delete();

            return new StoryResource('Data Story Berhasil Dihapus!', null);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
