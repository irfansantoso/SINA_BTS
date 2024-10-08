@extends('template')
@section('content')
<!-- Content Header (Page header) -->
{{-- <section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>@yield('title', $title)</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="#">@yield('title', $title)</a></li>
          <li class="breadcrumb-item active">Blank Page</li>
        </ol>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section> --}}
    <!-- Default box -->
    <br>
    @if(session('success'))
      <p class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button>{{ session('success') }}</p>
    @endif

    @if (count($errors) > 0)
      @foreach ($errors->all() as $error)
        <p class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">×</button>{{ $error }}</p>
      @endforeach
    @endif
    <div class="card card-info">
      <div class="card-header">
        <h3 class="card-title">Add Form</h3>
      </div>
      <!-- /.card-header -->
      <!-- form start -->

      <form class="form-horizontal" action="{{ route('periodeOperasional.add') }}" method="POST">
         @csrf        
        <div class="card-body">          
          <div class="row">
            <div class="col-sm-1">
              <div class="form-group">
                <label>Tahun</label>                
                  <input type="text" class="form-control" name="tahun_periode" data-inputmask-alias="datetime" data-inputmask-inputformat="yyyy" data-mask>                
              </div>
            </div>
            <div class="col-sm-1">
              <div class="form-group">
                <label>Nomor</label>              
                  <input type="text" class="form-control" id="no_periode" name="no_periode" placeholder="">    
              </div>
            </div>
            <div class="col-sm-2">
              <div class="form-group">
                <label>Awal</label>                
                  <input type="text" class="form-control" name="awal_tgl" data-inputmask-alias="datetime" data-inputmask-inputformat="yyyy-mm-dd" data-mask>                
              </div>
            </div>
            <div class="col-sm-2">
              <div class="form-group">
                <label>Akhir</label>                
                  <input type="text" class="form-control" name="akhir_tgl" data-inputmask-alias="datetime" data-inputmask-inputformat="yyyy-mm-dd" data-mask>                
              </div>
            </div>
            <div class="col-sm-1">
              <div class="form-group">
                <label>Kode</label>                
                  <input type="text" class="form-control" id="kode_periode" name="kode_periode" placeholder="">
              </div>
            </div>             
          </div>
        <!-- /.card-body -->
        </div>
        <div class="card-footer">
          <button class="btn btn-info">Simpan</button>
        </div>
        <!-- /.card-footer -->
      </form>              
    </div>
      <!-- /.card -->
    
    <div class="card">
      <!-- /.card-header -->
      <div class="card-body">
        <table id="example1" class="table table-bordered table-striped">
          <thead>
          <tr>
            <th>Tahun</th>
            <th>No</th>
            <th>Awal</th>
            <th>Akhir</th>
            <th>Kode</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
          </thead>
          <tbody>
            @foreach ($periodeOperasional as $perOper)
            <tr>              
                <td>{{ $perOper->tahun_periode }}</td>
                <td>{{ $perOper->no_periode }}</td>
                <td>{{ $perOper->awal_tgl }}</td>
                <td>{{ $perOper->akhir_tgl }}</td>
                <td>{{ $perOper->kode_periode }}</td>
                @if($perOper->status_periode == 0)
                <td style="color:red;">Inactive <a href="{{ route('periodeOperasional.actived', $perOper->id_periode) }}" class="btn btn-xs btn-success editArusKas">Pilih</a></td>
                @else
                <td style="color:blue;">active</td>
                @endif
                <td>
                  <!-- <button type="button" class="btn btn-xs btn-success editArusKas"  data-id="{{ $perOper->kode_periode }}" title="Pilih"><i class="fas fa-play"></i></button> -->
                </td>
            </tr>
            @endforeach                     
          </tbody>
          <tfoot>
          <tr>
            <th>Tahun</th>
            <th>No</th>
            <th>Awal</th>
            <th>Akhir</th>
            <th>Kode</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
          </tfoot>
        </table>
      </div>
      <!-- /.card-body -->
    </div>
    <!-- /.card -->
@endsection