@extends('template')
@section('content')

    <!-- Default box -->
    <br>
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

    <form class="form-horizontal" method="POST" action="{{ route('profile.edit') }}" enctype="multipart/form-data">
    @csrf  
      <div class="container-fluid">
        <div class="row">          
            <div class="col-md-3">
              <!-- Profile Image -->
              <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                  <div class="text-center">
                    @auth
                    @if(Auth::user()->photo_img != "")
                    <img class="profile-user-img img-fluid img-circle"
                         src="@auth{{asset('photos/'.Auth::user()->photo_img)}}@endauth"
                         alt="User profile picture">
                    @else
                    <img class="profile-user-img img-fluid img-circle"
                         src="{{asset('admin/dist/img/person.png')}}"
                         alt="User profile picture">
                    @endif
                    @endauth
                  </div>

                  <h3 class="profile-username text-center">@auth{{ Auth::user()->name }}@endauth</h3>

                  <p class="text-muted text-center">@auth{{ Auth::user()->level }}@endauth</p>                                  
                </div>
                <!-- /.card-body -->
              </div>
              <!-- /.card -->            
            </div>
            <!-- /.col -->
            <div class="col-md-9">
              <div class="card card-primary card-outline">
                <div class="card-header d-flex align-items-center justify-content-between">
                  <h5 class="mb-0">Biodata</h5>                  
                </div>
                <div class="card-body">
                  <div class="tab-content">                  
                    <div class="active tab-pane" id="settings">
                      <form class="form-horizontal">
                        <div class="row mb-3">
                          <label class="col-sm-2 col-form-label" for="basic-default-name">Name</label>
                          <div class="col-sm-10">
                            <input type="text" class="form-control" id="name" name="name" value="@auth{{ Auth::user()->name }}@endauth">
                          </div>
                        </div>
                        <div class="row mb-3">
                          <label class="col-sm-2 col-form-label">Username</label>
                          <div class="col-sm-10">
                            <input type="text" class="form-control" id="username" name="username" value="@auth{{ Auth::user()->username }}@endauth" disabled>
                          </div>
                        </div>
                        <div class="row mb-3">
                          <label class="col-sm-2 col-form-label">New Password</label>
                          <div class="col-sm-10">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Kosongkan jika tidak ingin dirubah">
                          </div>
                        </div>
                        <div class="row mb-3">
                          <label class="col-sm-2 col-form-label">Level</label>
                          <div class="col-sm-10">
                            <select class="form-control" name="level" id="level" style="width: 30%;">
                              @auth
                              @if(Auth::user()->level == "administrator")
                              <option value="administrator">Administrator</option>
                              @else
                              <option value="user">User</option>
                              @endif
                              @endauth
                            </select>
                          </div>
                        </div>
                        <div class="row mb-3">
                          <label class="col-sm-2 col-form-label">New Photo</label>
                          <div class="col-sm-10">
                            <input type="file" class="form-control" name="photo_img">
                          </div>
                        </div>
                        <div class="row mb-3">
                          <div class="offset-sm-2 col-sm-10">
                            <button type="submit" class="btn btn-success">Update</button>
                          </div>
                        </div>
                      </form>
                    </div>
                    <!-- /.tab-pane -->
                  </div>
                  <!-- /.tab-content -->
                </div><!-- /.card-body -->
              </div>
              <!-- /.card -->
            </div>          
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </form>
@endsection