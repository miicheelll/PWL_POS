<?php

namespace App\Http\Controllers;

use App\Models\KategoriModel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class KategoriController extends Controller
{
    public function index()
    {
        $breadcrumb = (object) [
            'title' => 'Data Kategori',
            'list' => ['Home', 'kategori']
        ];
        $page = (object) [
            'title' => 'Daftar kategori user yang terdaftar dalam sistem'
        ];
        $activeMenu = 'kategori'; 
        return view('kategori.index', ['breadcrumb' => $breadcrumb, 'page' => $page, 'activeMenu' => $activeMenu]);
    }

    public function list(Request $request)
    {
        $kategoris = KategoriModel::select('kategori_id', 'kategori_kode', 'kategori_nama');
        if ($request->kategori_id) {
            $kategoris->where('kategori_id', $request->kategori_id);
        }

        return DataTables::of($kategoris)
            ->addIndexColumn()
            ->addColumn('aksi', function ($kategori) { 
                /*$btn  = '<a href="' . url('/kategori/' . $kategori->kategori_id) . '" class="btn btn-info btn
sm">Detail</a> ';
                $btn .= '<a href="' . url('/kategori/' . $kategori->kategori_id . '/edit') . '" class="btn btn-warning btn-sm">Edit</a> ';
                $btn .= '<form class="d-inline-block" method="POST" action="' .
                    url('/kategori/' . $kategori->kategori_id) . '">'
                    . csrf_field() . method_field('DELETE') .
                    '<button type="submit" class="btn btn-danger btn-sm" onclick="return 
confirm(\'Apakah Anda yakit menghapus data ini?\');">Hapus</button></form>';*/
                $btn  = '<button onclick="modalAction(\''.url('/kategori/' . $kategori->kategori_id . 
                '/show_ajax').'\')" class="btn btn-info btn-sm">Detail</button> '; 
                $btn .= '<button onclick="modalAction(\''.url('/kategori/' . $kategori->kategori_id . 
                '/edit_ajax').'\')" class="btn btn-warning btn-sm">Edit</button> '; 
                $btn .= '<button onclick="modalAction(\''.url('/kategori/' . $kategori->kategori_id . 
                '/delete_ajax').'\')"  class="btn btn-danger btn-sm">Hapus</button> '; 
                return $btn;
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }
    
    public function create()
    {
        $breadcrumb = (object) [
            'title' => 'Tambah Kategori',
            'list' => [
                'Home',
                'Kategori',
                'Tambah'
            ]
        ];
        $page = (object) [
            'title' => 'Tambah kategori baru'
        ];
        $activeMenu = 'kategori';
        $kategori = KategoriModel::all();
        return view('kategori.create', ['breadcrumb' => $breadcrumb, 'page' => $page, 'activeMenu' => $activeMenu, 'kategori' => $kategori]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kategori_kode' => 'required|string|max:10|unique:m_kategori,kategori_kode',
            'kategori_nama' => 'required|string|max:100'
        ]);
        KategoriModel::create([
            'kategori_kode' => $request->kategori_kode,
            'kategori_nama' => $request->kategori_nama
        ]);
        return redirect('/kategori')->with('success', 'Data kategori berhasil disimpan');
    }

    public function show(string $id)
    {
        $kategori = KategoriModel::find($id);
        $breadcrumb = (object) [
            'title' => 'Detail Kategori',
            'list' => ['Home', 'Kategori', 'Detail']
        ];
        $page =
            (object) [
                'title' => 'Detail kategori'
            ];
        $activeMenu = 'kategori';
        return view('kategori.show', ['breadcrumb' => $breadcrumb, 'page' => $page, 'kategori' => $kategori, 'activeMenu' => $activeMenu]);
    }

    public function edit(string $id)
    {
        $kategori = KategoriModel::find($id);

        $breadcrumb = (object) [
            'title' => 'Edit kategori',
            'list' => ['Home', 'kategori', 'Edit']
        ];
        $page = (object) [
            'title' => 'Edit kategori'
        ];
        $activeMenu = 'kategori'; 
        return view('kategori.edit', ['breadcrumb' => $breadcrumb, 'page' => $page, 'kategori' => $kategori, 'activeMenu' => $activeMenu]);
    }
    public function update(Request $request, string $id)
    {
        $request->validate([
            'kategori_kode' => 'required|string|max:10',
            'kategori_nama' => 'required|string|max:100'
        ]);

        KategoriModel::find($id)->update([
            'kategori_kode' => $request->kategori_kode,
            'kategori_nama' => $request->kategori_nama
        ]);
        return redirect('/kategori')->with('success', 'Data kategori berhasil diubah');
    }

    public function destroy(string $id)
    {
        $check = KategoriModel::find($id);
        if (!$check) {
            return redirect('/kategori')->with('error', 'Data kategori tidak ditemukan ');
        }
        try {
            KategoriModel::destroy($id);
            return redirect('/kategori')->with('success', 'Data kategori berhasil dihapus');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect('/kategori')->with('error', 'Data kategori gagal dihapus karena masih terdapat tabel lain yang terkait dengan data ini');
        }
    }

    public function create_ajax() 
    {
        return view('kategori.create_ajax');
    }

    public function store_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'kategori_kode' => 'required|string|max:10|unique:m_kategori,kategori_kode',
                'kategori_nama' => 'required|string|max:100'
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'msgField'=> $validator->errors(),
                ]);
            }

            KategoriModel::create($request->all());
            return response()->json([
                'status'=> true,
                'message'=> 'Data kategori berhasil disimpan'
            ]);
        }
        redirect('/');
    }

    public function edit_ajax(string $id)
    {
        $kategori = KategoriModel::find($id);

        return view('kategori.edit_ajax',['kategori' => $kategori]);
    }

    public function update_ajax(Request $request, $id)
    { 
        if ($request->ajax() || $request->wantsJson()) { 
            $rules = [ 
                'kategori_kode' => 'required|string|max:10|unique:m_kategori,kategori_kode',
                'kategori_nama' => 'required|string|max:100' 
            ]; 

            $validator = Validator::make($request->all(), $rules); 
 
            if ($validator->fails()) { 
                return response()->json([ 
                    'status'   => false,   
                    'message'  => 'Validasi gagal.', 
                    'msgField' => $validator->errors() 
                ]); 
            } 
     
            $check = KategoriModel::find($id); 
            if ($check) { 
                $check->update($request->all()); 
                return response()->json([ 
                    'status'  => true, 
                    'message' => 'Data berhasil diupdate' 
                ]); 
            } else{ 
                return response()->json([ 
                    'status'  => false, 
                    'message' => 'Data tidak ditemukan' 
                ]); 
            } 
        } 
        return redirect('/'); 
    } 

    public function confirm_ajax(string $id) 
    {
        $kategori = KategoriModel::find($id);

        return view('kategori.confirm_ajax',['kategori'=> $kategori]);
    }

    public function delete_ajax(Request $request, $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $kategori = KategoriModel::find($id);
            if ($kategori) {
                try {
                    $kategori->delete();
                return response()->json([
                    'status'=> true,
                    'message'=> 'Data berhasil dihapus'
                ]);
                } catch (\Illuminate\Database\QueryException $e) {
                return response()->json([
                    'status'=> false,
                    'message'=> 'Data kategori gagal dihapus karena masih terdapat tabel lain yang terkait dengan data ini'
                ]);
                } 
            } else {
                return response()->json([
                    'status'=> false,
                    'message'=> 'Data tidak ditemukan'
                ]);
            }
        }
        return redirect('/');
    }
}
