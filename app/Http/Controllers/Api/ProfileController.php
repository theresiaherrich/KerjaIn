<?php

namespace App\Http\Controllers\Api;

use App\Models\Profile;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProfileResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * index
     *
     * @return void
     */
    public function index()
    {

        $Profile = Profile::latest()->paginate(5);

        return new ProfileResource(true, 'List Data Profile', $Profile);
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
            'image'     => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'nama'     => 'required',
            'content'   => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $image = $request->file('image');
        $image->storeAs('public/Profile', $image->hashName());

        $Profile = Profile::create([
            'image'     => $image->hashName(),
            'nama'     => $request->nama,
            'content'   => $request->content,
        ]);

        return new ProfileResource(true, 'Data Profile Berhasil Ditambahkan!', $Profile);
    }

    public function show($id)
    {
        $Profile = Profile::find($id);

        return new ProfileResource(true, 'Detail Data Profile!', $Profile);
    }

    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'nama'     => 'required',
            'content'   => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $Profile = Profile::find($id);

        if ($request->hasFile('image')) {

            $image = $request->file('image');
            $image->storeAs('public/Profile', $image->hashName());

            Storage::delete('public/Profile/' . basename($Profile->image));

            $Profile->update([
                'image'     => $image->hashName(),
                'nama'     => $request->nama,
                'content'   => $request->content,
            ]);
        } else {

            $Profile->update([
                'nama'     => $request->nama,
                'content'   => $request->content,
            ]);
        }

        return new ProfileResource(true, 'Data Profile Berhasil Diubah!', $Profile);
    }

    public function destroy($id)
    {

        $Profile = Profile::find($id);

        Storage::delete('public/Profile/'.basename($Profile->image));

        $Profile->delete();

        return new ProfileResource(true, 'Data Profile Berhasil Dihapus!', null);
    }
}
