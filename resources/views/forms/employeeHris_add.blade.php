@extends('template')
@section('content')
    @if(session('success'))
      <script type="text/javascript">
      function mssge() {
        Swal.fire({
          title: 'Good job!',
          text: 'You clicked the button!',
          icon: 'success',
          customClass: {
            confirmButton: 'btn btn-primary'
          },
          buttonsStyling: false
        });
      }
      window.onload = mssge;
      </script>
    @endif

    @if (count($errors) > 0)
      @foreach ($errors->all() as $error)
        <p class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">×</button>{{ $error }}</p>
      @endforeach
    @endif
    
    <div class="row">
      <div class="col-xxl">
        <div class="card mb-4">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">{{ isset($employee) ? 'Edit Employee' : 'Add New Employee' }}</h5>
            <a href="javascript:history.back()" class="btn btn-success">
              <i class="ti ti-chevrons-left me-1"></i>Back
            </a>
          </div>
          <div class="card-body">
            <form id="formAuthentication" class="form-horizontal" action="{{ isset($employee) ? route('employeeHris.update', $employee->id_employee) : route('employeeHris.save') }}" method="POST">
              @csrf
              @if(isset($employee))
                @method('PUT')
              @endif
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label" for="basic-default-name">No. Induk Karyawan</label>
                <div class="col-sm-3">
                  <input type="text" class="form-control" id="employee_number" name="employee_number" value="{{ old('employee_number', isset($employee) ? $employee->employee_number : '') }}" placeholder="" autofocus="autofocus" required>
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label" for="basic-default-name">Site</label>
                <div class="col-sm-4">
                  <select class="form-control select2 form-select" name="site_id" id="site_id" data-allow-clear="true">
                    <option value="" selected="selected">-- Site --</option>
                    @foreach ($siteHris as $sh)
                      <option value="{{ $sh->id_site }}"{{ old('site_id', isset($employee) ? $employee->site_id : '') == $sh->id_site ? 'selected' : '' }}>{{ $sh->site_name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label" for="basic-default-name">Department</label>
                <div class="col-sm-4">
                  <select class="form-control select2 form-select" name="dept_id" id="dept_id" data-allow-clear="true">
                    <option value="" selected="selected">-- Department --</option>
                    @foreach ($deptHris as $dh)
                      <option value="{{ $dh->id_dept }}"{{ old('dept_id', isset($employee) ? $employee->dept_id : '') == $dh->id_dept ? 'selected' : '' }}>{{ $dh->dept_name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label" for="basic-default-name">Nama</label>
                <div class="col-sm-6">
                  <input type="text" class="form-control" id="employee_name" name="employee_name" value="{{ old('employee_name', isset($employee) ? $employee->employee_name : '') }}" placeholder="" autofocus="autofocus" required>
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label" for="basic-default-name">No.Induk Kependudukan</label>
                <div class="col-sm-3">
                  <input type="text" class="form-control" id="nik" name="nik" value="{{ old('nik', isset($employee) ? $employee->nik : '') }}" placeholder="" autofocus="autofocus" required>
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label" for="basic-default-name">No.BPJS TK</label>
                <div class="col-sm-3">
                  <input type="text" class="form-control" id="bpjs_tk" name="bpjs_tk" value="{{ old('bpjs_tk', isset($employee) ? $employee->bpjs_tk : '') }}" placeholder="" autofocus="autofocus">
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label" for="basic-default-name">No.BPJS Kesehatan</label>
                <div class="col-sm-3">
                  <input type="text" class="form-control" id="bpjs_kes" name="bpjs_kes" value="{{ old('bpjs_kes', isset($employee) ? $employee->bpjs_kes : '') }}" placeholder="" autofocus="autofocus">
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label" for="basic-default-name">Tanggal Masuk</label>
                <div class="col-sm-3">
                  <input type="date" class="form-control" id="date_in" name="date_in" value="{{ old('date_in', isset($employee) ? $employee->date_in : '') }}" >
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label" for="basic-default-name">Tempat Lahir</label>
                <div class="col-sm-5">
                  <input type="text" class="form-control" id="place_birth" name="place_birth" value="{{ old('place_birth', isset($employee) ? $employee->place_birth : '') }}" placeholder="" autofocus="autofocus" >
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label" for="basic-default-name">Tanggal Lahir</label>
                <div class="col-sm-3">
                  <input type="date" class="form-control" id="date_birth" name="date_birth" value="{{ old('date_birth', isset($employee) ? $employee->date_birth : '') }}" >
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label" for="basic-default-name">Jabatan</label>
                <div class="col-sm-4">
                  <select class="form-control select2 form-select" name="position_id" id="position_id" data-allow-clear="true">
                    <option value="" selected="selected">-- Jabatan --</option>
                    @foreach ($positionHris as $ph)
                      <option value="{{ $ph->id_position }}"{{ old('position_id', isset($employee) ? $employee->position_id : '') == $ph->id_position ? 'selected' : '' }}>{{ $ph->position_name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label" for="basic-default-name">Marital Status</label>
                <div class="col-sm-2">
                  <select class="form-control select2 form-select" name="status_marital" id="status_marital" data-allow-clear="true">
                    <option value="" selected="selected">Select Status</option>
                    <option value="TK" {{ old('status_marital', isset($employee) ? $employee->status_marital : '') == 'TK' ? 'selected' : '' }}>TK</option>
                    <option value="TK1" {{ old('status_marital', isset($employee) ? $employee->status_marital : '') == 'TK1' ? 'selected' : '' }}>TK1</option>
                    <option value="TK2" {{ old('status_marital', isset($employee) ? $employee->status_marital : '') == 'TK2' ? 'selected' : '' }}>TK2</option>
                    <option value="K0" {{ old('status_marital', isset($employee) ? $employee->status_marital : '') == 'K0' ? 'selected' : '' }}>K0</option>
                    <option value="K1" {{ old('status_marital', isset($employee) ? $employee->status_marital : '') == 'K1' ? 'selected' : '' }}>K1</option>
                    <option value="K2" {{ old('status_marital', isset($employee) ? $employee->status_marital : '') == 'K2' ? 'selected' : '' }}>K2</option>
                    <option value="K3" {{ old('status_marital', isset($employee) ? $employee->status_marital : '') == 'K3' ? 'selected' : '' }}>K3</option>
                    <option value="K4" {{ old('status_marital', isset($employee) ? $employee->status_marital : '') == 'K4' ? 'selected' : '' }}>K4</option>
                    <option value="Janda" {{ old('status_marital', isset($employee) ? $employee->status_marital : '') == 'Janda' ? 'selected' : '' }}>Janda</option>
                  </select>
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label" for="basic-default-name">Gender</label>
                <div class="col-sm-3">
                  <div class="form-check form-check-inline mt-3">
                    <input
                      class="form-check-input"
                      type="radio"
                      name="gender"
                      id="inlineRadio1"
                      value="L" {{ old('gender', isset($employee) ? $employee->gender : '') == 'L' ? 'checked' : '' }}
                    />
                    <label class="form-check-label" for="inlineRadio1">L</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input
                      class="form-check-input"
                      type="radio"
                      name="gender"
                      id="inlineRadio2"
                      value="P" {{ old('gender', isset($employee) ? $employee->gender : '') == 'P' ? 'checked' : '' }}
                    />
                    <label class="form-check-label" for="inlineRadio2">P</label>
                  </div>
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label" for="basic-default-name">Status Gaji</label>
                <div class="col-sm-2">
                  <select class="form-control form-select" name="fee_status" id="fee_status" data-allow-clear="true">
                    <option value="" selected="selected">Select Status</option>
                    <option value="Harian" {{ old('fee_status', isset($employee) ? $employee->fee_status : '') == 'Harian' ? 'selected' : '' }}>Harian</option>
                    <option value="Bulanan" {{ old('fee_status', isset($employee) ? $employee->fee_status : '') == 'Bulanan' ? 'selected' : '' }}>Bulanan</option>
                    <option value="Borongan" {{ old('fee_status', isset($employee) ? $employee->fee_status : '') == 'Borongan' ? 'selected' : '' }}>Borongan</option>
                  </select>
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label" for="basic-default-name">Agama</label>
                <div class="col-sm-2">
                  <select class="form-control select2 form-select" name="religion" id="religion" data-allow-clear="true">
                    <option value="" selected="selected">Select Status</option>
                    <option value="Islam" {{ old('religion', isset($employee) ? $employee->religion : '') == 'Islam' ? 'selected' : '' }}>Islam</option>
                    <option value="Kristen" {{ old('religion', isset($employee) ? $employee->religion : '') == 'Kristen' ? 'selected' : '' }}>Kristen</option>
                    <option value="Katholik" {{ old('religion', isset($employee) ? $employee->religion : '') == 'Katholik' ? 'selected' : '' }}>Katholik</option>
                    <option value="Hindu" {{ old('religion', isset($employee) ? $employee->religion : '') == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                    <option value="Budha" {{ old('religion', isset($employee) ? $employee->religion : '') == 'Budha' ? 'selected' : '' }}>Budha</option>
                  </select>
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label" for="basic-default-name">Pendidikan</label>
                <div class="col-sm-2">
                  <select class="form-control select2 form-select" name="education" id="education" data-allow-clear="true">
                    <option value="" selected="selected">Select Pendidikan</option>
                    <option value="SD" {{ old('education', isset($employee) ? $employee->education : '') == 'SD' ? 'selected' : '' }}>SD</option>
                    <option value="SMP" {{ old('education', isset($employee) ? $employee->education : '') == 'SMP' ? 'selected' : '' }}>SMP</option>
                    <option value="SMA" {{ old('education', isset($employee) ? $employee->education : '') == 'SMA' ? 'selected' : '' }}>SMA</option>
                    <option value="SMK" {{ old('education', isset($employee) ? $employee->education : '') == 'SMK' ? 'selected' : '' }}>SMK</option>
                    <option value="D3" {{ old('education', isset($employee) ? $employee->education : '') == 'D3' ? 'selected' : '' }}>D3</option>
                    <option value="S1" {{ old('education', isset($employee) ? $employee->education : '') == 'S1' ? 'selected' : '' }}>S1</option>
                    <option value="S2" {{ old('education', isset($employee) ? $employee->education : '') == 'S2' ? 'selected' : '' }}>S2</option>
                  </select>
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label" for="basic-default-name">Alamat Penerimaan</label>
                <div class="col-sm-5">
                  <textarea id="autosize-demo" rows="3" class="form-control" name="recipient_address">{{ old('recipient_address', isset($employee) ? $employee->recipient_address : '') }}</textarea>
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label" for="basic-default-name">Awal Kontrak</label>
                <div class="col-sm-3">
                  <input type="date" class="form-control" id="startContract" name="start_contract" value="{{ old('start_contract', isset($employee) ? $employee->start_contract : '') }}" required>
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label" for="basic-default-name">Akhir Kontrak</label>
                <div class="col-sm-3">
                  <input type="date" class="form-control" id="endContract" name="end_contract" value="{{ old('end_contract', isset($employee) ? $employee->end_contract : '') }}" required>
                </div>
              </div>
              <div class="row mb-4">
                <label class="col-sm-3 col-form-label" for="basic-default-name">Durasi Kontrak (bulan)</label>
                <div class="col-sm-1">
                  <input type="text" class="form-control" id="contractDuration" name="duration_contract" value="{{ old('duration_contract', isset($employee) ? $employee->duration_contract : '') }}" placeholder="" autofocus="autofocus" required>
                </div>
              </div>
              <div class="row justify-content-end">
                <div class="col-sm-9">
                  <button type="submit" class="btn btn-success">Simpan</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>    
    </div>
@stop
@section('custom-js')
  <script type="text/javascript">
    // $(document).ready(function() {
    //   $('#endContract').on('keyup', function() {
    //     const startContract = $('#startContract').val();
    //     const endContract = $('#endContract').val();
    //     const contractDuration = $('#contractDuration');

    //     if (startContract && endContract) {
    //       const startDate = new Date(startContract);
    //       const endDate = new Date(endContract);

    //       if (!isNaN(startDate.getTime()) && !isNaN(endDate.getTime())) {
    //         const diffYears = endDate.getFullYear() - startDate.getFullYear();
    //         const diffMonths = endDate.getMonth() - startDate.getMonth();
    //         const diffDays = endDate.getDate() - startDate.getDate();

    //         let months = diffYears * 12 + diffMonths;

    //         // Correct the month difference if end day is less than start day
    //         if (diffDays < 0) {
    //           months--;
    //         }

    //         // Ensure at least one full month difference if days are exactly one year apart
    //         if (months === 0 && diffDays < 0) {
    //           months = 12;
    //         }

    //         contractDuration.val(months);
    //       } else {
    //         contractDuration.val("Invalid Date");
    //       }
    //     }
    //   });
    // });

    // $(document).ready(function() {
    //   $('#endContract').on('keyup', function() {
    //     const startContract = $('#startContract').val();
    //     const endContract = $('#endContract').val();
    //     const contractDuration = $('#contractDuration');

    //     if (startContract && endContract) {
    //       const startDate = new Date(startContract);
    //       const endDate = new Date(endContract);

    //       if (!isNaN(startDate.getTime()) && !isNaN(endDate.getTime())) {
    //         let diffYears = endDate.getFullYear() - startDate.getFullYear();
    //         let diffMonths = endDate.getMonth() - startDate.getMonth();
    //         let diffDays = endDate.getDate() - startDate.getDate();

    //         if (diffDays < 0) {
    //           diffMonths--;
    //           const prevMonth = new Date(endDate.getFullYear(), endDate.getMonth(), 0).getDate();
    //           diffDays += prevMonth;
    //         }

    //         if (diffMonths < 0) {
    //           diffYears--;
    //           diffMonths += 12;
    //         }

    //         const months = diffYears * 12 + diffMonths;

    //         contractDuration.val(months + " bulan, " + diffDays + " hari");
    //       } else {
    //         contractDuration.val("Invalid Date");
    //       }
    //     }
    //   });
    // });

  </script> 
 @stop