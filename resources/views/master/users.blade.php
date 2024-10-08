@extends('template')
@section('content')
    <!-- @if(session('success'))
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
    @endif -->
    
    <div class="row">
      <div class="col-xxl">
        <div class="card mb-4">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Add Form</h5>
            
          </div>
          <div class="card-body">
            <form id="formAuthentication" class="form-horizontal">
              @csrf
              <!-- Tambahkan metode spoof PUT untuk update, diatur secara dinamis dengan JS -->
              <input type="hidden" name="_method" id="formMethod" value="POST">
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label" for="basic-default-name">Name</label>
                <div class="col-sm-5">
                  <input type="text" class="form-control" id="name" name="name" placeholder="Nama" autofocus="autofocus" required>
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label" for="basic-default-username">Username</label>
                <div class="col-sm-5">
                  <input type="text" class="form-control" id="username" name="username" placeholder="Username">
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label" for="basic-default-password">Password</label>
                <div class="col-sm-5">
                  <div class="input-group input-group-merge">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password">                    
                  </div>
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label" for="basic-default-passwordconfirm">Password Confirm</label>
                <div class="col-sm-5">
                  <input type="password" class="form-control" id="password_confirm" name="password_confirm" placeholder="Password Confirmation">
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label" for="basic-default-level">Level</label>
                <div class="col-sm-5">
                  <select class="form-control" name="level" id="level">
                    <option value="" selected="selected">-- Level --</option>
                    <option value="administrator">Administrator</option>
                    <option value="user">User</option>
                  </select>
                </div>
              </div>
              <div class="row justify-content-end">
                <div class="col-sm-10">
                  <button type="button" id="addUserBtn" class="btn btn-success">Simpan</button>
                  <button type="button" id="clear" class="btn btn-warning">Clear</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>      

      <!-- Users List Table -->
      <div class="col-xxl">
        <div class="card mb-4">
          <div class="card-datatable table-responsive">
            <table id="userlist" class="datatables table border-top">
              <thead>
                <tr>
                  <th width="15px">No.</th>
                  <th>User</th>
                  <th>Username</th>
                  <th>level</th>
                  <th>Action</th>
                </tr>
              </thead>              
            </table>
          </div>
        </div>
      </div>
    </div>
@stop
@section('custom-js')
<script type="text/javascript">    
var table; // Declare table variable in global scope
$(document).ready(function() {
        table = $('#userlist').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{!! route('users.data') !!}', // Memanggil route yang menampilkan data JSON
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex' },
            { data: 'name', name: 'name' },
            { data: 'username', name: 'username' },
            { data: 'level', name: 'level' },
            {
                data: 'action',
                render: function (data, type, row, meta) {
                    return (
                        `<a href="{{ url('users/reset/${row.user_id}') }}" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-success" title="Reset Password" class="btn btn-sm btn-icon"><i class="text-success ti ti-refresh"></i></a>` +
                        `<a href="javascript:;" onclick="editUser(${row.user_id})" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-warning" title="Edit" class="btn btn-sm btn-icon item-edit"><i class="text-warning ti ti-pencil"></i></a>`
                    );
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

    // Handle Add New User Inline
    $('#addUserBtn').on('click', function(event) {
        event.preventDefault(); // Prevent the form from submitting the traditional way
        addUser(); // Call addUser function to handle the add operation
    });

    $('#clear').on('click', function(event) {
        resetFormAndButton();
    });

});

    function addUser() {
        var name = $('#name').val();
        var username = $('#username').val();
        var password = $('#password').val();
        var password_confirm = $('#password_confirm').val();
        var level = $('#level').val();

        if (name === '' || username === '' || password === '' || password_confirm === '' || level === '') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Semua field harus diisi!',
                customClass: {
                  confirmButton: 'btn btn-danger'
                },
                buttonsStyling: false
            });
            return;
        }

        if (password !== password_confirm) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Password dan Password Confirmation tidak cocok!',
                customClass: {
                  confirmButton: 'btn btn-danger'
                },
                buttonsStyling: false
            });
            return;
        }

        $.ajax({
            url: '{{ route('users.add') }}', // Route for saving data
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                name: name,
                username: username,
                password: password,
                password_confirm: password_confirm,
                level: level
            },
            success: function(user) {
                // Reset input fields
                $('#name').val('');
                $('#username').val('');
                $('#password').val('');
                $('#password_confirm').val('');
                $('#level').val('');

                // Reload table without resetting pagination
                table.ajax.reload(null, false);

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Data berhasil disimpan!',
                    customClass: {
                      confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });

                // Revert button text and function to default
                resetFormAndButton();
            },
            error: function(xhr) {
                alert('Terjadi kesalahan saat menambahkan data.');
            }
        });
    }
    
    function editUser(user_id) {
        $.ajax({
            url: `users/${user_id}`, // Fetch user data by ID
            type: 'GET',
            success: function(user) {
                // Populate form fields with the user's data
                $('#name').val(user.name);
                $('#username').val(user.username);
                $('#level').val(user.level);

                // Change the form action to update the user and the button text
                $('#formAuthentication').attr('action', `users/update/${user_id}`);
                $('#addUserBtn').text('Edit');

                $('#formMethod').val('PUT');

                // Change the button's click event to trigger an update instead of a create
                $('#addUserBtn').off('click').on('click', function(event) {
                    event.preventDefault();
                    updateUser(user_id);
                });
            },
            error: function(xhr) {
                alert('Terjadi kesalahan saat mengambil data user.');
            }
        });
    }

    function updateUser(user_id) {
        var name = $('#name').val();
        var username = $('#username').val();
        var password = $('#password').val();
        var password_confirm = $('#password_confirm').val();
        var level = $('#level').val();

        $.ajax({
            url: `users/update/${user_id}`, // Update route
            type: 'POST',  // Use POST method
            data: {
                _method: 'PUT',  // Spoof method to PUT
                _token: '{{ csrf_token() }}',
                name: name,
                username: username,
                password: password,
                password_confirm: password_confirm,
                level: level
            },
            success: function(user) {
                // Reset form fields
                $('#name').val('');
                $('#username').val('');
                $('#password').val('');
                $('#password_confirm').val('');
                $('#level').val('');

                // Reload table and revert button text
                table.ajax.reload(null, false);
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Data berhasil disimpan!',
                    customClass: {
                      confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });
                resetFormAndButton();
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error', 
                    title: 'Gagal',
                    text: 'Pastikan username tidak boleh sama!',
                    customClass: {
                      confirmButton: 'btn btn-danger'
                    },
                    buttonsStyling: false
                });
            }
        });
    }

    function resetFormAndButton() {
        $('#name').val('');
        $('#username').val('');
        $('#password').val('');
        $('#password_confirm').val('');
        $('#level').val('');

        $('#addUserBtn').text('Simpan'); // Change button text back to "Simpan"
        $('#formMethod').val('POST'); // Reset form method to POST

        // Reset button click event to add new user
        $('#addUserBtn').off('click').on('click', function(event) {
            event.preventDefault();
            addUser();
        });
    }

    
</script>
 
@stop