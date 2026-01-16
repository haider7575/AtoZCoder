<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AtoZ Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-dark d-flex align-items-center justify-content-center" style="height: 100vh;">
    <div class="card p-4" style="width: 350px;">
        <h4 class="text-center mb-4">Login</h4>
        @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <form action="/login" method="POST">
            @csrf
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="admin@admin.com">
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" value="password">
            </div>
            <button class="btn btn-primary w-100" type="submit">Login</button>
        </form>
    </div>
</body>

</html>