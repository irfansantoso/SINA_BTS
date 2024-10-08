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
            <form id="formAuthentication" class="form-horizontal" action="{{ route('siteHris.add') }}" method="POST">
              @csrf
              <div class="row mb-3">
                <label for="" class="col-sm-2 col-form-label">Site Name</label>
                <div class="col-sm-2">
                  <input type="text" class="form-control" id="site_name" name="site_name" placeholder="Site Name" autofocus="autofocus">
                </div>
              </div>
              <div class="row mb-3">
                <label for="" class="col-sm-2 col-form-label">Site Location</label>
                <div class="col-sm-6">
                  <input type="text" class="form-control" id="site_location" name="site_location" placeholder="Site Name">
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
            <table id="site_dt" class="table table-bordered table-striped">
              <thead>
              <tr>
                <th>Site Name</th>
                <th>Site Location</th>
              </tr>
              </thead>
              <tbody>
                @foreach ($siteHris as $etr)
                <tr>              
                    <td>{{ $etr->site_name }}</td>
                    <td>{{ $etr->site_location }}</td>
                </tr>
                @endforeach                     
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
@stop
@section('custom-js')
  <script type="text/javascript">
    $("#site_dt").DataTable({
      "responsive": true, 
      "lengthChange": false, 
      "autoWidth": false,
      "order": [],
      "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
    }).buttons().container().appendTo('#site_dt_wrapper .col-md-6:eq(0)');

  </script> 
 @stop