<?php

namespace App\Http\Controllers\Api;

//import model Lowongan
use App\Models\Lowongan;

use Illuminate\Http\Request;

//import resource LowonganResource
use App\Http\Controllers\Controller;

//import Http request
use App\Http\Resources\LowonganResource;

//import facade Validator
use Illuminate\Support\Facades\Validator;

//import facade Storage
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
        //get all Lowongans
        $Lowongans = Lowongan::latest()->paginate(5);

        //return collection of Lowongans as a resource
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
        //define validation rules
        $validator = Validator::make($request->all(), [
            'image'     => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'nama'     => 'required',
            'content'   => 'required',
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //upload image
        $image = $request->file('image');
        $image->storeAs('public/Lowongans', $image->hashName());

        //create Lowongan
        $Lowongan = Lowongan::create([
            'image'     => $image->hashName(),
            'nama'     => $request->nama,
            'content'   => $request->content,
        ]);

        //return response
        return new LowonganResource(true, 'Data Lowongan Berhasil Ditambahkan!', $Lowongan);
    }

    /**
     * show
     *
     * @param  mixed $id
     * @return void
     */
    public function show($id)
    {
        //find Lowongan by ID
        $Lowongan = Lowongan::find($id);

        //return single Lowongan as a resource
        return new LowonganResource(true, 'Detail Data Lowongan!', $Lowongan);
    }

    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $id
     * @return void
     */
    public function update(Request $request, $id)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'nama'     => 'required',
            'content'   => 'required',
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //find Lowongan by ID
        $Lowongan = Lowongan::find($id);

        //check if image is not empty
        if ($request->hasFile('image')) {

            //upload image
            $image = $request->file('image');
            $image->storeAs('public/Lowongans', $image->hashName());

            //delete old image
            Storage::delete('public/Lowongans/' . basename($Lowongan->image));

            //update Lowongan with new image
            $Lowongan->update([
                'image'     => $image->hashName(),
                'nama'     => $request->nama,
                'content'   => $request->content,
            ]);
        } else {

            //update Lowongan without image
            $Lowongan->update([
                'nama'     => $request->nama,
                'content'   => $request->content,
            ]);
        }

        //return response
        return new LowonganResource(true, 'Data Lowongan Berhasil Diubah!', $Lowongan);
    }

    /**
     * destroy
     *
     * @param  mixed $id
     * @return void
     */
    public function destroy($id)
    {

        //find Lowongan by ID
        $Lowongan = Lowongan::find($id);

        //delete image
        Storage::delete('public/Lowongans/'.basename($Lowongan->image));

        //delete Lowongan
        $Lowongan->delete();

        //return response
        return new LowonganResource(true, 'Data Lowongan Berhasil Dihapus!', null);
    }
}
