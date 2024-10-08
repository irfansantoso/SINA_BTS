@extends('app')
@section('content')
<div class="authentication-inner row">
    <!-- /Left Text -->
    <div class="d-none d-lg-flex col-lg-7 p-0">
      <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center">
        <img
          src="{{asset('admin/assets/img/illustrations/acconting-book.png')}}"
          alt="auth-login-cover"
          class="img-fluid my-5 auth-illustration"
          data-app-light-img="illustrations/acconting-book.png"
          data-app-dark-img="illustrations/acconting-book.png"
        />

        <img
          src="{{asset('admin/assets/img/illustrations/bg-shape-image-light.png')}}"
          alt="auth-login-cover"
          class="platform-bg"
          data-app-light-img="illustrations/bg-shape-image-light.png"
          data-app-dark-img="illustrations/bg-shape-image-dark.png"
        />
      </div>
    </div>
    <!-- /Left Text -->

    <!-- Login -->
    <div class="d-flex col-12 col-lg-5 align-items-center p-sm-5 p-4">
      <div class="w-px-400 mx-auto">

        <h3 class="mb-1 fw-bold">Welcome to SINA!</h3>
        <p class="mb-4">Please sign-in to your account and start the adventure</p>
        @if(session('success'))
        <p class="alert alert-success">{{ session('success') }}</p>
        @endif
        @if($errors->any())
        @foreach($errors->all() as $err)
        <p class="alert alert-danger">{{ $err }}</p>
        @endforeach
        @endif

        <form id="formAuthentication" class="mb-3" action="{{ route('login.action') }}" method="POST">
        @csrf
          <div class="mb-3">
            <label for="email" class="form-label">Username</label>
            <input
              type="username"
              class="form-control"
              id=""
              name="username"
              placeholder="Enter your username"
              autofocus
            />
          </div>
          <div class="mb-3 form-password-toggle">
            <div class="d-flex justify-content-between">
              <label class="form-label" for="password">Password</label>
              
            </div>
            <div class="input-group input-group-merge">
              <input
                type="password"
                id=""
                class="form-control"
                name="password"
                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
              />
              <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
            </div>
          </div>
          <button type="submit" class="btn btn-primary d-grid w-100">Sign in</button>
        </form>        

      </div>
    </div>
    <!-- /Login -->
  </div>
@endsection