<?php
namespace App\Http\Controllers;

use App\Models\KategoriModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
class KategoriController extends Controller
{
    public function index (){

        $activeMenu = 'kategori';
        $breadcrumb = (object) [
            'title' => 'Data Kategori',
            'list' => ['Home', 'Kategori']
        ];

        return view('kategori.index', compact('activeMenu', 'breadcrumb'));
    }

    public function list(Request $request)
    {
        $kategori = KategoriModel::select('kategori_id', 'kategori_kode', 'kategori_nama');
        return DataTables::of($kategori)
            ->addIndexColumn()
            ->addColumn('aksi', function ($kategori) { // menambahkan kolom aksi
                /*$btn = '<a href="'.url('/kategori/' . $kategori->kategori_id).'" class="btn btn-info btn-sm">Detail</a> ';
                $btn .= '<a href="'.url('/kategori/' . $kategori->kategori_id . '/edit').'"class="btn btn-warning btn-sm">Edit</a> ';
                $btn .= '<form class="d-inline-block" method="POST" action="'.
                url('/kategori/'.$kategori->kategori_id).'">'
                . csrf_field() . method_field('DELETE') .
                '<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Apakah Kita yakit menghapus data ini?\');">Hapus</button></form>';*/
                $btn = '<button onclick="modalAction(\''.url('/kategori/' . $kategori->kategori_id . '/show_ajax').'\')" class="btn btn-info btn-sm">Detail</button> ';
                $btn .= '<button onclick="modalAction(\''.url('/kategori/' . $kategori->kategori_id . '/edit_ajax').'\')" class="btn btn-warning btn-sm">Edit</button> ';
                $btn .= '<button onclick="modalAction(\''.url('/kategori/' . $kategori->kategori_id . '/delete_ajax').'\')" class="btn btn-danger btn-sm">Hapus</button> ';
                return $btn;
            })
            ->rawColumns(['aksi']) // ada teks html
            ->make(true);
    }

    public function create_ajax()
    {
        return view('kategori.create_ajax');
    }

    public function store_ajax(Request $request)
    {
        if($request->ajax() || $request->wantsJson()){
            $rules = [
                'kategori_kode' => ['required', 'max:5', 'unique:m_kategori,kategori_kode'],
                'kategori_nama' => ['required', 'string', 'max:100']
            ];

            $validator = Validator::make($request->all(), $rules);
            if($validator->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'msgField' => $validator->errors()
                ]);
            }

            KategoriModel::create($request->all());
            return response()->json([
                'status' => true,
                'message' => 'Data berhasil disimpan'
            ]);
        }
        redirect('/');
    }

    public function edit_ajax($id)
    {
        $kategori = KategoriModel::find($id);
        $kategori = KategoriModel::select('kategori_id', 'kategori_nama')->get();
        return view('kategori.edit_ajax', ['kategori' => $kategori, 'kategori' => $kategori]);
    }

    public function update_ajax(Request $request, $id)
    {
        // cek apakah request dari ajax
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'kategori_kode' => ['required', 'max:5', 'unique:m_kategori,kategori_kode'],
                'kategori_nama' => ['required', 'string', 'max:100']
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

            $check = KategoriModel::find($id);
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
        $kategori = KategoriModel::find($id);
        return view('kategori.confirm_ajax', ['kategori' => $kategori]);
    }
            
    public function delete_ajax(Request $request, $id)
    {
        if($request->ajax() || $request->wantsJson()){
            $kategori = KategoriModel::find($id);
            if($kategori){ // jika sudah ditemuikan
                $kategori->delete(); // kategori di hapus
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
        return view('kategori.import');
    }

    public function import_ajax(Request $request)
    {
        if($request->ajax() || $request->wantsJson()){
            $rules = [
                // validasi file harus xls atau xlsx, max 1MB
                'file_kategori' => ['required', 'mimes:xlsx', 'max:1024']
            ];

            $validator = Validator::make($request->all(), $rules);
            if($validator->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'msgField' => $validator->errors()
                ]);
            }

            $file = $request->file('file_kategori'); // ambil file dari request

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
                            'kategori_kode' => $value['A'],
                            'kategori_nama' => $value['B'],
                            'created_at' => now(),
                        ];
                    }
                }

                if(count($insert) > 0){
                    // insert data ke database, jika data sudah ada, maka diabaikan
                    KategoriModel::insertOrIgnore($insert);
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
        $kategori = KategoriModel::select('kategori_kode','kategori_nama')
            ->orderBy('kategori_kode')
            ->with('kategori')
            ->get();
        // load library excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet(); // ambil sheet yang aktif

        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Kode Kategori');
        $sheet->setCellValue('C1', 'Nama Kategori');

        $sheet->getStyle('A1:F1')->getFont()->setBold(true); // bold header

        $no = 1; // nomor data dimulai dari 1
        $baris = 2; // baris data dimulai dari baris ke 2
        foreach ($kategori as $key => $value) {
            $sheet->setCellValue('A' . $baris, $no);
            $sheet->setCellValue('B' . $baris, $value->kategori_kode);
            $sheet->setCellValue('C' . $baris, $value->kategori_nama);
            $baris++;
            $no++;
        }

        foreach (range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true); // set auto size untuk kolom
        }

        $sheet->setTitle('Data Kategori'); // set title sheet

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx'); //Membuat “penulis” file Excel dalam format .xlsx
        $filename = 'Data Kategori_' . date('Y-m-d H:i:s') . '.xlsx';

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
        $kategori = KategoriModel::select('kategori_kode', 'kategori_nama')
                    ->orderBy('kategori_kode')
                    ->get();
        $pdf = Pdf::loadView('kategori.export_pdf', ['kategori' => $kategori]);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions(['isRemoteEnabled' => true]);
        $pdf->render();

        return $pdf->stream('Data Kategori_' . date('Y-m-d H:i:s') . '.pdf');
    }
}