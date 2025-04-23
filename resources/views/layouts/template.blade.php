<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'PWL Laravel Starter Code')}}</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('Adminlte/plugins/fontawesome-free/css/all.min.css')}}">
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('Adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{ asset('Adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{ asset('Adminlte/plugins/datatables-buttons/css/buttons.bootstrap4.min.css')}}">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="{{ asset('Adminlte/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('Adminlte/dist/css/adminlte.min.css')}}">

    @stack('css')
</head>

<body class="hold-transition sidebar-mini">
    <!-- Site wrapper -->
    <div class="wrapper">
        <!-- Navbar -->
        @include('layouts.header')
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="{{ url('/')}}" class="brand-link">
                <img src="{{ asset('Adminlte')}}/dist/img/AdminLTELogo.png" alt="AdminLTE Logo"
                    class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light">PWL - Starter Code</span>
            </a>

            <!-- Sidebar -->
            @include('layouts.sidebar')
            <!-- /.sidebar -->
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            @include('layouts.breadcrumb')

            <!-- Main content -->
            <section class="content">
                @yield('content')
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->
        @include('layouts.footer')
    </div>
    <!-- ./wrapper -->

    <!-- Modal Profil Lengkap -->
    <div class="modal fade" id="modalProfilLengkap" tabindex="-1" role="dialog" aria-labelledby="modalProfilLengkapLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="modal-header">
            <h5 class="modal-title">Profil Saya</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <div class="modal-body text-center">

            <!-- Foto Profil -->
            <img id="fotoPreview"
            src="{{ Auth::user()->foto ? asset('storage/foto/' . Auth::user()->foto) : asset('images/default.png') }}"
            class="img-thumbnail rounded-circle mb-3" width="150">

            <!-- Form Fields -->
            <div class="form-group text-left">
            <label for="nama">Nama</label>
            <input type="text" name="nama" id="nama" class="form-control" value="{{ Auth::user()->nama }}" readonly>
            </div>

            <div class="form-group text-left">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" class="form-control" value="{{ Auth::user()->username }}"
                readonly>
            </div>

            <div class="form-group text-left">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="********" readonly>
            </div>

            <!-- Ganti Foto -->
            <div class="form-group text-left">
            <label for="foto">Ganti Foto</label>
            <input type="file" name="foto" id="foto" class="form-control-file" onchange="previewFoto()">
            </div>

        </div>

        <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            <button type="button" class="btn btn-danger" onclick="hapusFoto()">Hapus Foto</button>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
        </form>
    </div>
    </div>
    </div>

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- JavaScript untuk Preview Foto -->
    <script>
    function previewFoto() {
    const input = document.getElementById('foto');
    const preview = document.getElementById('fotoPreview');
    const file = input.files[0];
    const reader = new FileReader();

    reader.onloadend = function () {
        preview.src = reader.result;
    }

    if (file) {
        reader.readAsDataURL(file);
    }
    }

    function hapusFoto() {
    const foto = document.getElementById("fotoPreview").src;

    if (foto.includes('default.png')) {
        Swal.fire({
        title: 'Gagal!',
        text: 'Tidak ada foto yang dapat dihapus.',
        icon: 'error',
        confirmButtonText: 'OK'
        });
        return;
    }

    Swal.fire({
        title: 'Apakah anda yakin untuk menghapus foto?',
        text: 'Foto profil akan dihapus.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
        fetch("{{ route('profile.delete') }}", {
            method: "DELETE",
            headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
            }
        }).then(response => {
            if (response.ok) {
            Swal.fire({
                title: 'Foto berhasil dihapus!',
                text: 'Foto profil telah dihapus.',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                location.reload();
            });
            } else {
            Swal.fire('Gagal!', 'Tidak bisa menghapus foto.', 'error');
            }
        }).catch(() => {
            Swal.fire('Gagal!', 'Terjadi kesalahan jaringan.', 'error');
        });
        }
    });
    }
    </script>

    <!-- Tampilkan SweetAlert dari Session -->
    @if (session('success'))
    <script>
    Swal.fire({
    title: 'Berhasil!',
    text: '{{ session("success") }}',
    icon: 'success',
    confirmButtonText: 'OK'
    }).then(() => {
    location.reload(false); 
    });
    </script>
    @endif


    @if (session('error'))
    <script>
    Swal.fire({
    title: 'Gagal!',
    text: '{{ session("error") }}',
    icon: 'error',
    confirmButtonText: 'OK'
    });
    </script>
    @endif

    </script>

    <!-- jQuery -->
    <script src="{{ asset('Adminlte/plugins/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('Adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!--DataTables & Plugins -->
    <script src="{{ asset('Adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('Adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('Adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('Adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('Adminlte/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('Adminlte/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('Adminlte/plugins/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('Adminlte/plugins/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('Adminlte/plugins/pdfmake/vfs_fonts.js') }}"></script>
    <script src="{{ asset('Adminlte/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('Adminlte/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('Adminlte/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
    
    <!-- jquery-validation -->
    <script src="{{ asset('Adminlte/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('Adminlte/plugins/jquery-validation/additional-methods.min.js') }}"></script>

    <!-- SweetAlert2 -->
    <script src="{{ asset('Adminlte/plugins/sweetalert2/sweetalert2.min.js') }}"></script>

    <!--AdminLTE App -->
    <script src="{{ asset('Adminlte/dist/js/adminlte.min.js') }}"></script>
    <script>
        // Untuk mengirimkan token Laravel CSRF pada setiap request ajax
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    </script>
    @stack('js') <!-- Digunakan untuk memanggil custom js dari perintah push('js') pada masing-masing view -->
</body>

</html>