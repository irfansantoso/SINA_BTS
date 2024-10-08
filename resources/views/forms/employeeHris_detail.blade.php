@extends('template')
@section('content')

    @if (count($errors) > 0)
      @foreach ($errors->all() as $error)
        <p class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">×</button>{{ $error }}</p>
      @endforeach
    @endif

    @php
    use \Carbon\Carbon;
    @endphp
    <!-- Header -->
    <div class="row">
      <div class="col-12">
        <div class="card mb-4">
          <div class="user-profile-header-banner">
            <img src="{{asset('admin/assets/img/pages/profile-banner.png')}}" alt="Banner image" class="rounded-top" />
          </div>
          <div class="user-profile-header d-flex flex-column flex-sm-row text-sm-start text-center mb-4">
            <div class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto">
              <img
                src="{{asset('admin/assets/img/avatars/user.png')}}"
                alt="user image"
                class="d-block h-auto ms-0 ms-sm-4 rounded user-profile-img"
              />
            </div>
            <div class="flex-grow-1 mt-3 mt-sm-5">
              <div
                class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-4 flex-md-row flex-column gap-4"
              >
                <div class="user-profile-info">
                  <h4>{{$employee->employee_name}}</h4>
                  <ul
                    class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-2"
                  >
                    <li class="list-inline-item text-uppercase"><i class="ti ti-color-swatch"></i> {{$positionHris->position_name}}</li>
                    <li class="list-inline-item text-uppercase"><i class="ti ti-map-pin"></i> {{$siteHris->site_name}}</li>
                    <li class="list-inline-item text-uppercase"><i class="ti ti-calendar"></i> Joined {{ Carbon::parse($employee->date_in)->format('d-m-Y') }}</li>
                  </ul>
                </div>
                <a href="javascript:history.back()" class="btn btn-success">
                  <i class="ti ti-chevrons-left me-1"></i>Back
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--/ Header -->
    
    <!-- User Profile Content -->
    <div class="row">
      <div class="col-xl-5">
        <!-- About User -->
        <div class="card mb-4">
          <div class="card-body">
            <small class="card-text text-uppercase">Overview</small>
            <ul class="list-unstyled mb-4 mt-3">
              <li class="d-flex align-items-center mb-3">
                <i class="ti ti-circle-check"></i><span class="fw-bold mx-2">No.Karyawan:</span> <span>{{$employee->employee_number}}</span>
              </li>
              <li class="d-flex align-items-center mb-3">
                <i class="ti ti-circle-check"></i><span class="fw-bold mx-2">Department:</span> <span>{{$deptHris->dept_name}}</span>
              </li>
              <li class="d-flex align-items-center mb-3">
                <i class="ti ti-circle-check"></i><span class="fw-bold mx-2">NIK:</span> <span>{{$employee->nik}}</span>
              </li>
              <li class="d-flex align-items-center mb-3">
                <i class="ti ti-circle-check"></i><span class="fw-bold mx-2">BPJS TK:</span> <span>{{$employee->bpjs_tk}}</span>
              </li>
              <li class="d-flex align-items-center mb-3">
                <i class="ti ti-circle-check"></i><span class="fw-bold mx-2">BPJS KES:</span>
                <span>{{$employee->bpjs_kes}}</span>
              </li>
              <li class="d-flex align-items-center mb-3">
                <i class="ti ti-circle-check"></i><span class="fw-bold mx-2">Tempat Lahir:</span>
                <span>{{$employee->place_birth}}</span>
              </li>
              <li class="d-flex align-items-center mb-3">
                <i class="ti ti-circle-check"></i><span class="fw-bold mx-2">Tanggal Lahir:</span>
                <span>{{ Carbon::parse($employee->date_birth)->format('d-m-Y') }}</span>
              </li>
              <li class="d-flex align-items-center mb-3">
                <i class="ti ti-circle-check"></i><span class="fw-bold mx-2">Marital status:</span>
                <span>{{$employee->status_marital}}</span>
              </li>
              <li class="d-flex align-items-center mb-3">
                <i class="ti ti-circle-check"></i><span class="fw-bold mx-2">Gender:</span>
                <span>{{$employee->gender}}</span>
              </li>
              <li class="d-flex align-items-center mb-3">
                <i class="ti ti-circle-check"></i><span class="fw-bold mx-2">Status Gaji:</span>
                <span>{{$employee->fee_status}}</span>
              </li>
              <li class="d-flex align-items-center mb-3">
                <i class="ti ti-circle-check"></i><span class="fw-bold mx-2">Agama:</span>
                <span>{{$employee->religion}}</span>
              </li>
              <li class="d-flex align-items-center mb-3">
                <i class="ti ti-circle-check"></i><span class="fw-bold mx-2">Pendidikan:</span>
                <span>{{$employee->education}}</span>
              </li>
            </ul>
                        
          </div>
        </div>
        <!--/ About User --> 
        <!-- Profile Overview -->
        <div class="card mb-4">
          <div class="card-body">
            <small class="card-text text-uppercase">Others</small>
            <ul class="list-unstyled mb-0">
              <li class="d-flex align-items-center mb-3">
                <i class="ti ti-home"></i><span class="fw-bold mx-2">Alamat:</span>
                <span>{{$employee->recipient_address}}</span>
              </li>
            </ul>
          </div>
        </div>
        <!--/ Profile Overview -->      
      </div>

      <div class="col-xl-7 col-lg-7 col-md-7">        
        <!-- Projects table -->
        <div class="card mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
              <small class="card-text text-uppercase">History</small>
          </div>
          <div class="card-datatable table-responsive">          
            <table id="contract_tbl" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Awal Contract</th>
                  <th>Akhir Contract</th>
                </tr>
              </thead>
              <tbody>
                @php $counter = 1; @endphp
                @foreach ($histRenewEmp as $hr)
                <tr>
                    <td>{{ $counter }}</td>              
                    <td>{{ Carbon::parse($hr->start_contract_renew)->format('d-m-Y') }}</td>
                    <td>{{ Carbon::parse($hr->end_contract_renew)->format('d-m-Y') }}</td>
                </tr>
                @php $counter++; @endphp
                @endforeach                     
              </tbody>
            </table>
          </div>
        </div>
        <!--/ Projects table -->
      </div>
    </div>
    <!--/ User Profile Content -->
@stop
@section('custom-js')
  <script type="text/javascript">
    $("#contract_tbl").DataTable({
      "responsive": true, 
      "lengthChange": false, 
      "autoWidth": false,
      "order": [],
      "dom": 'lrtip'
    }).buttons().container().appendTo('#contract_tbl_wrapper .col-md-6:eq(0)');

  </script> 
 @stop