@extends('layouts.template') 

@section('content') 
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">{{ $page->title }}</h3>
            <div class="card-tools"></div>
        </div>
        <div class="card-body">
            @empty($penjualan) 
                <div class="alert alert-danger alert-dismissible">
                    <h5><i class="icon fas fa-ban"></i> Kesalahan!</h5>
                    Data yang Anda cari tidak ditemukan.
                </div>
            @else 
                <table class="table table-bordered table-striped table-hover table-sm">
                    <tr>
                        <th>ID Detail</th>
                        <td>{{ $penjualan->penjualan_detail->detail_id }}</td>
                    </tr>
                    <tr>
                        <th>ID Penjualan</th>
                        <td>{{ $penjualan->penjualan_id }}</td>
                    </tr>
                    <tr>
                        <th>Barang</th>
                        <td>{{ $penjualan->penjualan_detail->barang->barang_id }}</td>
                    </tr>
                    <tr>
                        <th>Harga</th>
                        <td>{{ $penjualan->penjualan_detail->harga }}</td>
                    </tr>
                    <tr>
                        <th>Jumlah</th>
                        <td>{{ $penjualan->penjualan_detail->jumlah }}</td>
                    </tr>
                </table>
            @endempty 
            <a href="{{ url('penjualan') }}" class="btn btn-sm btn-default mt-2">Kembali</a>
        </div>
    </div>
@endsection

@push('css') 
@endpush

@push('js') @endpush