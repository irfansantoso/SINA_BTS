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
      <div class="col-xxl">
        <div class="card mb-4">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Add Form</h5>            
          </div>

          <div class="card-body">
            <form id="formAuthentication" class="form-horizontal" action="{{ route('emailReceiverHris.add') }}" method="POST">
              @csrf
              <div class="row mb-3">
                <label for="" class="col-sm-2 col-form-label">Name</label>
                <div class="col-sm-6">
                  <input type="text" class="form-control" id="name" name="name" placeholder="Name" autofocus="autofocus" required>
                </div>
              </div>
              <div class="row mb-3">
                <label for="" class="col-sm-2 col-form-label">Email</label>
                <div class="col-sm-6">
                  <input type="email" class="form-control" id="formValidationEmail" name="email" placeholder="Email" required>
                </div>
              </div>              

              <div class="row justify-content-end">
                <div class="col-sm-10">
                  <button type="submit" class="btn btn-success">Save</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>      

      <div class="col-xxl">
        <div class="card mb-4">
          <div class="card-datatable table-responsive">
            <table id="emailReceiver_dt" class="table table-bordered table-striped">
              <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Action</th>
              </tr>
              </thead>
              <tbody>
                @foreach ($emailReceiver as $er)
                <tr>              
                    <td>{{ $er->name }}</td>
                    <td>{{ $er->email }}</td>
                    <td><a href="#" data-bs-toggle="modal" data-bs-placement="top" data-bs-custom-class="tooltip-primary" data-bs-target="#modalEdit" data-id="{{ $er->id_receiver }}" data-name="{{ $er->name }}" data-email="{{ $er->email }}" title="Edit" class="btn btn-sm btn-icon item-edit"><i class="text-primary ti ti-pencil"></i></a></td>
                </tr>
                @endforeach                     
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Edit Show -->
    <div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <form class="form-horizontal" action="{{ route('emailReceiverHris.edit') }}" method="POST">
        @csrf
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel1">Edit Form</h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>
          <input type="hidden" id="id_x" name="id_receiver" class="form-control" />
          <div class="modal-body">
            <div class="row mb-3">
              <label for="" class="col-sm-2 col-form-label">Name</label>
              <div class="col-sm-6">
                <input type="text" class="form-control" id="name_x" name="name" placeholder="Name" autofocus="autofocus" required>
              </div>
            </div>
            <div class="row mb-3">
              <label for="" class="col-sm-2 col-form-label">Email</label>
              <div class="col-sm-6">
                <input type="email" class="form-control" id="email_x" name="email" placeholder="Email" required>
              </div>
            </div> 
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-success">Simpan</button>
          </div>
        </div>
        </form>
      </div>
    </div>
    <!-- END Modal Renewal -->
@stop
@section('custom-js')
  <script type="text/javascript">
    $("#emailReceiver_dt").DataTable({
      "lengthChange": false, 
      "autoWidth": false,
      "order": [],
      "scrollX": true,
      "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
    }).buttons().container().appendTo('#site_dt_wrapper .col-md-6:eq(0)');

    $(document).on('click', '.item-edit', function() {
        let id = $(this).attr('data-id');
        let name = $(this).attr('data-name');
        let email = $(this).attr('data-email');
        // alert(id_em);
        
        $('#id_x').val(id);
        $('#name_x').val(name);
        $('#email_x').val(email);
    });
  </script> 
 @stop