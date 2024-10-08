@extends('template')
@section('content')
    @if(session('success'))
      <script type="text/javascript">
      function mssge() {
        Swal.fire({
          title: "{{ session('success') }}",
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
    <!-- Users List Table -->
      <div class="col-xxl">
          <div class="card mb-4">
              <div class="card-header d-flex justify-content-between align-items-center">
                  <h5 class="mb-0">
                    <!-- Checkbox for active/inactive filter -->
                    <label>
                        <input type="checkbox" id="cek_active" name="cek_active">
                        Show employees resigned
                    </label>
                  </h5>
                  <a href="{{ route('employeeHris_add') }}" class="btn rounded-pill btn-success">
                      <span class="ti-xs ti ti-plus me-1"></span>Add New
                  </a>
              </div>
              <div class="card">
                  <div class="card-datatable text-nowrap">
                      <table id="employeeList" class="datatables-ajax table">
                          <thead>
                              <tr>
                                  <th>No.</th>
                                  <th>Employee Number</th>
                                  <th>Site</th>
                                  <th>Department</th>
                                  <th>Employee Name</th>
                                  <th>No.Induk Kependudukan</th>
                                  <th>No.BPJS-TK</th>
                                  <th>No.BPJS Kesehatan</th>
                                  <th>Tanggal Masuk</th>
                                  <th>Tempat Tinggal</th>
                                  <th>Tanggal Lahir</th>
                                  <th>Jabatan</th>
                                  <th>Status Marital</th>
                                  <th>Gender</th>
                                  <th>Jns Penggajian</th>
                                  <th>Agama</th>
                                  <th>Pendidikan</th>
                                  <th>Alamat Pernerimaan</th>
                                  <th>Awal Kontrak</th>
                                  <th>Akhir Kontrak</th>
                                  <th>Masa Kontrak</th>
                                  <th>Action</th>
                              </tr>
                          </thead>
                      </table>
                  </div>
              </div>
          </div>
      </div>
    </div>

    <!-- Modal Renewal Show -->
    <div class="modal fade" id="modalRenewal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <form class="form-horizontal" action="{{ route('employeeHris.emp_renewal') }}" method="POST">
        @csrf
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel1">Renewal Form</h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>
          <input type="hidden" id="id_employee_x" name="id_employee" class="form-control" />
          <input type="hidden" id="nik_x" name="nik" class="form-control" />
          <div class="modal-body">
            <div class="row">
              <div class="col mb-3">
                <label for="nameBasic" class="form-label" style="display: block;">Nama Karyawan</label>
                <span style="font-weight: bold;" id="employee_name_x"></span>
              </div>
            </div>
            <div class="row g-2">
              <div class="col mb-0">
                <label for="" class="form-label">Awal Contract</label>
                <input type="date" class="form-control" id="start_contract_x" name="start_contract" value="" autofocus="autofocus">
              </div>
              <div class="col mb-0">
                <label for="" class="form-label">Akhir Contract</label>
                <input type="date" class="form-control" id="end_contract_x" name="end_contract" value="" autofocus="autofocus">
              </div>
            </div>
            <div class="row">
              <div class="col mb-1">
                <label for="nameBasic" class="form-label" style="display: block;">Masa Kontrak</label>
                <input type="text" class="form-control" id="duration_contract_x" name="duration_contract" value="">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
              Tutup
            </button>
            <button type="submit" class="btn btn-success">Simpan</button>
          </div>
        </div>
        </form>
      </div>
    </div>
    <!-- END Modal Renewal -->

    <!-- Modal Delete -->
    <div class="modal fade" id="modalDelete" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <form class="form-horizontal" action="{{ route('employeeHris.emp_del') }}" method="POST">
        @csrf
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel1">Delete Status Form</h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>
          <input type="hidden" id="id_employee_z" name="id_employee" class="form-control" />
          <div class="modal-body">
            <div class="row">
              <label for="" class="col-sm-3 col-form-label">Nama Karyawan</label>
              <div class="col mb-3">                
                <span style="font-weight: bold;" id="employee_name_z"></span>
              </div>
            </div>
            <div class="row">
              <label class="col-sm-3 col-form-label" for="">Status Karyawan</label>
              <div class="col mb-3">
                <select class="form-control form-select" name="information_status" id="information_status" data-allow-clear="true">
                    <option value="" selected="selected">Information Status</option>
                    <option value="expired">Expired</option>
                    <option value="resign">Resign</option>
                    <option value="cancel">Cancel</option>
                </select>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
              Tutup
            </button>
            <button type="submit" class="btn btn-success">Simpan</button>
          </div>
        </div>
        </form>
      </div>
    </div>
    <!-- END Modal Delete -->
@stop
@section('custom-js')
    <script type="text/javascript">
    $(document).ready(function() {
      let table = $('#employeeList').DataTable({
            // responsive: true, //for responsive show + button in first column
            processing: true,
            serverSide: true,
            ajax: {
                url: '{!! route('employeeHris.data') !!}',
                data: function (d) {
                    d.cek_active = $('#cek_active').is(':checked') ? 'yes' : 'no';
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'employee_number', name: 'employee_number' },
                { data: 'siteName', name: 'siteName' },
                { data: 'deptName', name: 'deptName' },
                { data: 'employee_name', name: 'employee_name' },
                { data: 'nik', name: 'nik' },
                { data: 'bpjs_tk', name: 'bpjs_tk' },
                { data: 'bpjs_kes', name: 'bpjs_kes' },
                { data: 'date_in', name: 'date_in' },
                { data: 'place_birth', name: 'place_birth' },
                { data: 'date_birth', name: 'date_birth' },
                { data: 'positionName', name: 'positionName' },
                { data: 'status_marital', name: 'status_marital' },
                { data: 'gender', name: 'gender' },
                { data: 'fee_status', name: 'fee_status' },
                { data: 'religion', name: 'religion' },
                { data: 'education', name: 'education' },
                { data: 'recipient_address', name: 'recipient_address' },
                { data: 'start_contract', name: 'start_contract' },
                { data: 'end_contract', name: 'end_contract' },
                { data: 'duration_contract', name: 'duration_contract' },
                {
                    data: 'action',
                    render: function ( data, type, row, meta ) {
                    let actionHtml = '<div class="d-inline-block">' +
                                        '<a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="text-primary ti ti-dots-vertical"></i></a>' +
                                        '<ul class="dropdown-menu dropdown-menu-end m-0">';
                        // Conditionally edit option based on information_status
                        if (row.information_status === 'active') {    
                            actionHtml +=`<li><a href="{{ url('employeeHris/edit/${row.id_employee}') }}" class="dropdown-item text-primary">Edit</a></li>`;
                        }
                            actionHtml +=`<li><a href="#" data-bs-toggle="modal"
                                        data-bs-target="#modalRenewal" data-id_em="${row.id_employee}" data-nik="${row.nik}" data-nm_em="${row.employee_name}" data-s_contract="${row.start_contract}" data-e_contract="${row.end_contract}" data-dur_contract="${row.duration_contract}" class="dropdown-item text-success ren_emp">Renewal</a></li>`;

                        // Conditionally delete option based on information_status
                        if (row.information_status === 'active') {
                            actionHtml += '<div class="dropdown-divider"></div>' +
                                        `<li><a href="#" data-bs-toggle="modal"
                                        data-bs-target="#modalDelete" data-id_em="${row.id_employee}" data-nm_em="${row.employee_name}" class="dropdown-item text-danger del_emp">Delete</a></li>`;
                        }

                        actionHtml += '</ul>' +
                                    '</div>' +
                                    `<a href="{{ url('employeeHris/detail/${row.nik}') }}" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-primary" title="Detail" class="btn btn-sm btn-icon item-edit"><i class="text-primary ti ti-eye"></i></a>`;

                        return actionHtml;
                    },
                    orderable: false, searchable: false
                }
            ],
            drawCallback: function(settings) {
                // Initialize tooltips
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl, {
                        customClass: tooltipTriggerEl.getAttribute('data-bs-custom-class') || ''
                    });
                });
            },
            scrollX: true,
        });
        // Redraw the table when the checkbox state changes
        $('#cek_active').change(function() {
            table.ajax.reload();
        });
    });

    $(document).on('click', '.ren_emp', function() {
        let id_em = $(this).attr('data-id_em');
        let nik = $(this).attr('data-nik');
        let nm_em = $(this).attr('data-nm_em');
        let s_contract = $(this).attr('data-s_contract');
        let e_contract = $(this).attr('data-e_contract');
        let dur_contract = $(this).attr('data-dur_contract');
        // alert(id_em);
        
        $('#id_employee_x').val(id_em);
        $('#nik_x').val(nik);
        $('#employee_name_x').text(nm_em);
        $('#start_contract_x').val(s_contract);
        $('#end_contract_x').val(e_contract);
        $('#duration_contract_x').val(dur_contract);
    });

    $(document).on('click', '.del_emp', function() {
        let id_em = $(this).attr('data-id_em');
        let nm_em = $(this).attr('data-nm_em');
        
        $('#id_employee_z').val(id_em);
        $('#employee_name_z').text(nm_em);                
    });

    // $(document).ready(function() {
    //   $('#start_contract_x, #end_contract_x').on('keyup', function() {
    //     const start_contract = $('#start_contract_x').val();
    //     const end_contract = $('#end_contract_x').val();
    //     const duration_contract = $('#duration_contract_x');

    //     if (start_contract && end_contract) {
    //       const startDate = new Date(start_contract);
    //       const endDate = new Date(end_contract);

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

    //         duration_contract.val(months);
    //       } else {
    //         duration_contract.val("Invalid Date");
    //       }
    //     }
    //   });
    // });
    </script> 
 @stop