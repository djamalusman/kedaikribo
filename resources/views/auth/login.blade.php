<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kedai Kribo</title>

    <!--Boxicons CDN-->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <!--Custom CSS-->
    <link rel="stylesheet" href="{{ asset('assets/login/style.css') }}">

</head>

<body>
    <div class="wrapper">

        <span class="rotate-bg"></span>
        <span class="rotate-bg2"></span>
        <div class="form-box login">
            <h2 class="title animation" style="--i:0; --j:21">Login</h2>
           <form method="POST" class="col-lg-12" id="sign_in" action="{{ url('/login') }}">
            @csrf
                <div class="input-box animation" style="--i:1; --j:22">
                     <input type="email" name="email" value="{{ old('email') }}"
                        class="form-control @error('email') is-invalid @enderror" required autofocus>
                    <label class="form-label">Username</label>
                </div>
                <div class="input-box animation" style="--i:2; --j:23">
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                    <label class="form-label">Password</label>
                </div>

                <button type="submit" class="btn animation" style="--i:3; --j:24">Login</button>
            </form>
        </div>

        <div class="info-text login">
            <h2 class="animation" style="--i:0; --j:20">Welcome Back!</h2>
            <p class="animation" style="--i:1; --j:21">Lorem ipsum dolor sit amet consectetur adipisicing elit.</p>
        </div>
    </div>
    <!--Script.js-->
    <script src="{{ asset('assets/login/app.js') }}"></script>
</body>

</html>
