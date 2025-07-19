<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Asset Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .login-container { min-height: 100vh; display: flex; align-items: center; }
        .login-card { border: none; border-radius: 15px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="container login-container">
        <div class="row justify-content-center w-100">
            <div class="col-md-6 col-lg-4">
                <div class="card login-card">
                    <div class="card-header text-center bg-primary text-white">
                        <h4><i class="fas fa-boxes"></i> Asset Management</h4>
                        <p class="mb-0">Please sign in to continue</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="fas fa-user"></i> Username
                                </label>
                                <input type="text" class="form-control" id="username" name="username" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock"></i> Password
                                </label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <strong>Demo Accounts:</strong><br>
                                admin/password | manager/password | user/password
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>