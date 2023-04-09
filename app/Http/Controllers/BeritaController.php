<?php

namespace App\Http\Controllers;

use App\Models\Berita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class BeritaController extends Controller
{
    public function index()
    {
        $beritas = Berita::orderBy('created_at', 'DESC')->get();

        return response()->json([
            'status' => 'success',
            'data' => $beritas
        ]);
    }

    public function show($id)
    {
        $berita = Berita::find($id);

        if (!$berita) {
            return response()->json([
                'status' => 'error',
                'message' => 'Berita not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $berita
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'isi' => 'required',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        $berita = new Berita();
        $berita->judul = $request->input('judul');
        $berita->isi = $request->input('isi');

        if ($request->hasFile('gambar')) {
            $image = $request->file('gambar');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $path = public_path('uploads/' . $filename);
            Image::make($image->getRealPath())->resize(300, 300)->save($path);
            $berita->gambar = url('uploads/' . $filename);
            $request->gambar->move(public_path('berita'), $filename);
        }

        $berita->save();

        return response()->json([
            'status' => 'success',
            'data' => $berita
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $berita = Berita::find($id);

        if (!$berita) {
            return response()->json([
                'status' => 'error',
                'message' => 'Berita not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'isi' => 'required',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        $berita->judul = $request->input('judul');
        $berita->isi = $request->input('isi');

        if ($request->hasFile('gambar')) {
            $image = $request->file('gambar');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $path = public_path('uploads/' . $filename);
            Image::make($image->getRealPath())->resize(300, 300)->save($path);
            $berita->gambar = $filename;
        }

        $berita->save();

        return response()->json([
            'status' => 'success',
            'data' => $berita
        ], 200);
    }

    public function destroy($id)
    {
        $berita = Berita::find($id);

        if (!$berita) {
            return response()->json([
                'status' => 'error',
                'message' => 'Berita not found'
            ], 404);
        }

        $berita->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Berita deleted successfully'
        ], 200);
    }

    public function search(Request $request)
    {
        $keyword = $request->input('keyword');

        $berita = Berita::where('judul', 'LIKE', "%$keyword%")
            ->orWhere('isi', 'LIKE', "%$keyword%")
            ->orderBy('created_at', 'DESC')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $berita
        ], 200);
    }
}
