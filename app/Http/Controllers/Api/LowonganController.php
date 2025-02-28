<?php

namespace App\Http\Controllers\Api;

use App\Models\Lowongan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\LowonganResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class LowonganController extends Controller
{
    /**
     * index
     *
     * @return void
     */
    public function index()
    {

        $Lowongans = Lowongan::latest()->paginate(5);

        return new LowonganResource(true, 'List Data Lowongans', $Lowongans);
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
        $image->storeAs('public/Lowongans', $image->hashName());

        $Lowongan = Lowongan::create([
            'image'     => $image->hashName(),
            'nama'     => $request->nama,
            'content'   => $request->content,
        ]);

        return new LowonganResource(true, 'Data Lowongan Berhasil Ditambahkan!', $Lowongan);
    }

    public function show($id)
    {
        $Lowongan = Lowongan::find($id);

        return new LowonganResource(true, 'Detail Data Lowongan!', $Lowongan);
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

        $Lowongan = Lowongan::find($id);

        if ($request->hasFile('image')) {

            $image = $request->file('image');
            $image->storeAs('public/Lowongans', $image->hashName());

            Storage::delete('public/Lowongans/' . basename($Lowongan->image));

            $Lowongan->update([
                'image'     => $image->hashName(),
                'nama'     => $request->nama,
                'content'   => $request->content,
            ]);
        } else {

            $Lowongan->update([
                'nama'     => $request->nama,
                'content'   => $request->content,
            ]);
        }

        return new LowonganResource(true, 'Data Lowongan Berhasil Diubah!', $Lowongan);
    }

    public function destroy($id)
    {

        $Lowongan = Lowongan::find($id);

        Storage::delete('public/Lowongans/'.basename($Lowongan->image));

        $Lowongan->delete();

        return new LowonganResource(true, 'Data Lowongan Berhasil Dihapus!', null);
    }
}
