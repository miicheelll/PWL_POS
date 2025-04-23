<?php
namespace App\Http\Controllers;

use App\Models\BarangModel;
use App\Models\PenjualanModel;
use App\Models\PenjualanDetailModel;
use App\Models\StokModel;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
class PenjualanController extends Controller
{
    public function index (){

        $activeMenu = 'penjualan';
        $breadcrumb = (object) [
            'title' => 'Data Penjualan',
            'list' => ['Home', 'Penjualan']
        ];
        $user = UserModel::select('user_id', 'username')->get();
        return view('penjualan.index', [
            'activeMenu' => $activeMenu,
            'breadcrumb' => $breadcrumb,
            'user' => $user
        ]);
    }

    public function show_ajax($id)
    {
        $penjualan = PenjualanModel::with('penjualan_detail.barang')->find($id);
        $breadcrumb = (object) [
            'title' => 'Detail Penjualan',
            'list' => ['Home', 'Penjualan', 'Detail']
        ];
        $page =
            (object) [
                'title' => 'Detail Penjualan'
            ];
        $penjualanDetail = PenjualanDetailModel::where('penjualan_id', $id)
            ->with('penjualan')
            ->get();
        $activeMenu = 'penjualan'; // set menu yang sedang aktif
        return view('penjualan.show', ['breadcrumb' => $breadcrumb, 'page' => $page, 'penjualan' => $penjualan, 'penjualanDetail' => $penjualanDetail, 'activeMenu' => $activeMenu]);
    }


    public function list(Request $request)
    {
        $penjualan = PenjualanModel::select('penjualan_id', 'user_id', 'pembeli', 'penjualan_kode', 'penjualan_tanggal')->with('user');
        $user_id = $request->input('filter_user');
        if(!empty($user_id)){
            $penjualan->where('user_id', $user_id);
        }
        return DataTables::of($penjualan)
            ->addIndexColumn()
            ->addColumn('aksi', function ($penjualan) { // menambahkan kolom aksi
                /*$btn = '<a href="'.url('/penjualan/' . $penjualan->penjualan_id).'" class="btn btn-info btn-sm">Detail</a> ';
                $btn .= '<a href="'.url('/penjualan/' . $penjualan->penjualan_id . '/edit').'"class="btn btn-warning btn-sm">Edit</a> ';
                $btn .= '<form class="d-inline-block" method="POST" action="'.
                url('/penjualan/'.$penjualan->penjualan_id).'">'
                . csrf_field() . method_field('DELETE') .
                '<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Apakah Kita yakit menghapus data ini?\');">Hapus</button></form>';*/
                $btn = '<button onclick="modalAction(\'' . url('/penjualan/' . $penjualan->penjualan_id .
                    '/show_ajax') . '\')" class="btn btn-info btn-sm">Detail</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/penjualan/' . $penjualan->penjualan_id .
                    '/edit_ajax') . '\')" class="btn btn-warning btn-sm">Edit</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/penjualan/' . $penjualan->penjualan_id .
                    '/delete_ajax') . '\')" class="btn btn-danger btn-sm">Hapus</button> ';
                return $btn;
            })
            ->rawColumns(['aksi']) // ada teks html
            ->make(true);
    }

    public function create_ajax()
    {
        $user = UserModel::select('user_id', 'username')->get();
        $barang = BarangModel::select('barang_id', 'barang_nama')->get();
        return view('penjualan.create_ajax')->with('user', $user)->with('barang', $barang);
    }

    public function store_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            // Validasi data penjualan utama
            $rules = [
                'user_id' => ['required', 'integer', 'exists:m_user,user_id'],
                'pembeli' => ['required', 'string', 'max:50'],
                'penjualan_kode' => ['required', 'max:20', 'unique:t_penjualan,penjualan_kode'],
                'penjualan_tanggal' => ['required', 'date']
            ];
    
            // Validasi data detail penjualan
            $detailRules = [
                'penjualan_detail.*.barang_id' => ['required', 'integer', 'exists:m_barang,barang_id'],
                'penjualan_detail.*.jumlah' => ['required', 'integer', 'min:1']
            ];
    
            $validator = Validator::make($request->all(), array_merge($rules, $detailRules));
    
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'msgField' => $validator->errors()
                ]);
            }
    
            // Mulai transaksi database
            DB::beginTransaction();
    
            try {
                // Simpan data penjualan utama
                $penjualan = PenjualanModel::create([
                    'user_id' => $request->user_id,
                    'pembeli' => $request->pembeli,
                    'penjualan_kode' => $request->penjualan_kode,
                    'penjualan_tanggal' => $request->penjualan_tanggal
                ]);
    
                // Simpan data detail penjualan dan kurangi stok barang
                foreach ($request->penjualan_detail as $detail) {
                    // Ambil harga_jual dari tabel barang berdasarkan barang_id
                    $barang = BarangModel::find($detail['barang_id']);
                    if (!$barang) {
                        throw new \Exception("Barang dengan ID {$detail['barang_id']} tidak ditemukan.");
                    }
    
                    // Hitung harga sebagai hasil perkalian harga_jual dengan jumlah
                    $harga = $barang->harga_jual * $detail['jumlah'];
    
                    // Simpan data detail penjualan
                    PenjualanDetailModel::create([
                        'penjualan_id' => $penjualan->penjualan_id,
                        'barang_id' => $detail['barang_id'],
                        'harga' => $harga,
                        'jumlah' => $detail['jumlah']
                    ]);
    

                }
    
                // Commit transaksi
                DB::commit();
    
                return response()->json([
                    'status' => true,
                    'message' => 'Data berhasil disimpan'
                ]);
            } catch (\Exception $e) {
                // Rollback transaksi jika terjadi kesalahan
                DB::rollback();
    
                return response()->json([
                    'status' => false,
                    'message' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()
                ]);
            }
        }
    
        return redirect('/');
    }

    public function edit_ajax($id)
    {
        $penjualan = PenjualanModel::find($id);
        $user = UserModel::select('user_id', 'username')->get();
        return view('penjualan.edit_ajax', ['penjualan' => $penjualan, 'user' => $user]);
    }

    public function update_ajax(Request $request, $id)
    {
        // cek apakah request dari ajax
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'user_id' => ['required', 'integer', 'exists:m_user,user_id'],
                'pembeli' => ['required', 'string', 'max:50'],
                'penjualan_kode' => ['required', 'max:20', 'unique:t_penjualan,penjualan_kode'],
                'penjualan_tanggal' => ['required', 'datetime']
            ];

            // use Illuminate\Support\Facades\Validator;
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false, // respon json, true: berhasil, false: gagal
                    'message' => 'Validasi gagal.',
                    'msgField' => $validator->errors() // menunjukkan field mana yang error
                ]);
            }

            $check = PenjualanModel::find($id);
            if ($check) {
                $check->update($request->all());
                return response()->json([
                    'status' => true,
                    'message' => 'Data berhasil diupdate'
                ]);
            } else{
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ]);
            }
        }
        return redirect('/');
    }
            
    public function confirm_ajax($id)
    {
        $penjualan = PenjualanModel::find($id);
        return view('penjualan.confirm_ajax', ['penjualan' => $penjualan]);
    }
            
    public function delete_ajax(Request $request, $id)
    {
        if($request->ajax() || $request->wantsJson()){
            $penjualan = PenjualanModel::find($id);
            if($penjualan){ // jika sudah ditemuikan
                $penjualan->delete(); // penjualan di hapus
                return response()->json([
                    'status' => true,
                    'message' => 'Data berhasil dihapus'
                ]);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ]);
            }
        }
        return redirect('/');
    }
    public function import()
    {
        return view('penjualan.import');
    }

    public function import_ajax(Request $request)
    {
        if($request->ajax() || $request->wantsJson()){
            $rules = [
                // validasi file harus xls atau xlsx, max 1MB
                'file_penjualan' => ['required', 'mimes:xlsx', 'max:1024']
            ];

            $validator = Validator::make($request->all(), $rules);
            if($validator->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'msgField' => $validator->errors()
                ]);
            }

            $file = $request->file('file_penjualan'); // ambil file dari request

            $reader = IOFactory::createReader('Xlsx'); // load reader file excel
            $reader->setReadDataOnly(true); // hanya membaca data
            $spreadsheet = $reader->load($file->getRealPath()); // load file excel
            $sheet = $spreadsheet->getActiveSheet(); // ambil sheet yang aktif

            $data = $sheet->toArray(null, false, true, true); // ambil data excel

            $insert = [];
            if(count($data) > 1){ // jika data lebih dari 1 baris
                foreach ($data as $baris => $value) {
                    if($baris > 1){ // baris ke 1 adalah header, maka lewati
                        $insert[] = [
                            'user_id' => $value['A'],
                            'pembeli' => $value['B'],
                            'penjualan_kode' => $value['C'],
                            'penjualan_tanggal' => $value['D'],
                            'created_at' => now(),
                        ];
                    }
                }

                if(count($insert) > 0){
                    // insert data ke database, jika data sudah ada, maka diabaikan
                    PenjualanModel::insertOrIgnore($insert);
                }

                return response()->json([
                    'status' => true,
                    'message' => 'Data berhasil diimport'
                ]);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Tidak ada data yang diimport'
                ]);
            }
        }
        return redirect('/');
    }
    public function export_excel()
    {
        $penjualan = PenjualanModel::select('penjualan_id', 'user_id', 'pembeli', 'penjualan_kode', 'penjualan_tanggal')
            ->orderBy('penjualan_id')
            ->with('penjualan')
            ->get();
        // load library excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet(); // ambil sheet yang aktif

        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Nama User');
        $sheet->setCellValue('C1', 'Pembeli');
        $sheet->setCellValue('D1', 'Kode Penjualan');
        $sheet->setCellValue('E1', 'Tanggal');

        $sheet->getStyle('A1:F1')->getFont()->setBold(true); // bold header

        $no = 1; // nomor data dimulai dari 1
        $baris = 2; // baris data dimulai dari baris ke 2
        foreach ($penjualan as $key => $value) {
            $sheet->setCellValue('A' . $baris, $no);
            $sheet->setCellValue('B' . $baris, $value->user->username);
            $sheet->setCellValue('C' . $baris, $value->pembeli);
            $sheet->setCellValue('D' . $baris, $value->penjualan_kode);
            $sheet->setCellValue('E' . $baris, $value->penjualan_tanggal);
            $baris++;
            $no++;
        }

        foreach (range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true); // set auto size untuk kolom
        }

        $sheet->setTitle('Data Penjualan'); // set title sheet

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx'); //Membuat “penulis” file Excel dalam format .xlsx
        $filename = 'Data Penjualan_' . date('Y-m-d H:i:s') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); // memberi tahu bahwa ini adalah file excel
        header('Content-Disposition: attachment;filename="' . $filename . '"'); //Memberi tau browser supaya file langsung di-download, bukan dibuka di browser.  
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1'); //Supaya browser tidak menyimpan versi lama dari file ini.
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); //Tanggal kadaluarsa file ini ditetapkan ke masa lalu → artinya file ini harus dianggap baru setiap saat.  
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // memberi tahu bahwa sekarang adaah terakhir modifikasi.
        header('Cache-Control: cache, must-revalidate'); // File ini bisa di-cache, tapi harus diperiksa dulu ke server apakah ada versi terbaru.
        header('Pragma: public'); //Boleh disimpan (public cache) di beberapa kasus, untuk dukung browser lama.

        $writer->save('php://output');
        exit;
    }

    public function export_pdf(){
        $penjualan = PenjualanModel::select('penjualan_id', 'user_id', 'pembeli', 'penjualan_kode', 'penjualan_tanggal')
                    ->orderBy('penjualan_id')
                    ->orderBy('user_id')
                    ->with('user')
                    ->get();
        $pdf = Pdf::loadView('penjualan.export_pdf', ['penjualan' => $penjualan]);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions(['isRemoteEnabled' => true]);
        $pdf->render();

        return $pdf->stream('Data Penjualan_' . date('Y-m-d H:i:s') . '.pdf');
    }
}