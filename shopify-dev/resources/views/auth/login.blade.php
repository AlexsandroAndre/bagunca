@extends('layouts.app')

@section('content')
<!--
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Login') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="email" class="col-sm-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus>

                                @if ($errors->has('email'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>

                                @if ($errors->has('password'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6 offset-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                    <label class="form-check-label" for="remember">
                                        {{ __('Remember Me') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Login') }}
                                </button>

                                <a class="btn btn-link" href="{{ route('password.request') }}">
                                    {{ __('Forgot Your Password?') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

-->

<div class="row justify-content-center">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="text-center">
                    <h3>Create a new account</h3>
                    <p class="text-muted">Get our 30-day free trial and start increasing your sales today</p>
                </div>
                <hr class="mb-4">
                <form method="GET" action="{{ route('login.shopify') }}" aria-label="{{ __('Register') }}">
                    <div class="form-group">
                        <label for="domain">Domain</label>

                        <div class="input-group mb-3">
                            <input id="domain" type="text" class="form-control{{ $errors->has('domain') ? ' is-invalid' : '' }}" name="domain" value="{{ old('domain') }}" placeholder="yourshop" aria-describedby="myshopify" required autofocus>
                            <div class="input-group-append">
                                <span class="input-group-text" id="myshopify">myshopify.com</span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Continue</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="text-center mt-3">
            <p class="text-center text-muted">Already have an account? <a href="{{ route('login') }}">Sign in here</a></p>
        </div>
    </div>
</div>
@endsection
