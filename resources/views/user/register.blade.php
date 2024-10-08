@extends('app')
@section('content')
<div class="container">
    <div class="col-md-4 col-md-offset-4">
        @if($errors->any())
        @foreach($errors->all() as $err)
        <p class="alert alert-danger">{{ $err }}</p>
        @endforeach
        @endif
        <h2 class="text-center"><br>Register TUK - BTS</h3>
        <hr>
        <form action="{{ route('register.action') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label>Name <span class="text-danger">*</span></label>
                <input class="form-control" type="text" name="name" value="{{ old('name') }}" />
            </div>
            <div class="mb-3">
                <label>Username <span class="text-danger">*</span></label>
                <input class="form-control" type="username" name="username" value="{{ old('username') }}" />
            </div>
            <div class="mb-3">
                <label>Password <span class="text-danger">*</span></label>
                <input class="form-control" type="password" name="password" />
            </div>
            <div class="form-group">
                <label>Password Confirmation<span class="text-danger">*</span></label>
                <input class="form-control" type="password" name="password_confirm" />
            </div>
            <!-- <div class="form-group">
                <label>Company<span class="text-danger">*</span></label>
                <select class="form-control select2" name="company" id="company" style="width: 100%;">
                    <option selected="selected">-- Choose Company --</option>
                    <option value="BTJ">BTJ</option>
                    <option value="BTS">BTS</option>
                </select>
            </div>        -->   
            <p class="text-center" style="margin-top:10px">
                <button class="btn btn-primary" style="width: 100px">Register</button>
                <a class="btn btn-danger" style="width: 100px" href="{{ route('home') }}">Back</a>
            </p>
        </form>
    </div>
</div>
@endsection