<!DOCTYPE html>
<!-- frontend/pages/register.html -->
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Gestion Projet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #1cc88a;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .register-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
            padding: 40px;
            animation: slideIn 0.5s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .register-header h1 {
            color: #333;
            font-size: 28px;
            font-weight: 600;
            margin: 15px 0 5px;
        }
        
        .register-header p {
            color: #666;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .form-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            z-index: 10;
        }
        
        .form-control {
            padding: 12px 15px 12px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
            height: auto;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
            outline: none;
        }
        
        .btn-register {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            padding: 12px;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(78, 115, 223, 0.4);
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        
        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .password-strength {
            height: 5px;
            background: #e0e0e0;
            border-radius: 5px;
            margin-top: 5px;
            overflow: hidden;
        }
        
        .strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s;
        }
        
        .strength-text {
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <i class="fas fa-tasks fa-3x" style="color: var(--primary-color);"></i>
            <h1>Créer un compte</h1>
            <p>Rejoignez GestionProjet dès maintenant</p>
        </div>
        
        <?php
        session_start();
        if(isset($_SESSION['error'])) {
            echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
            unset($_SESSION['error']);
        }
        ?>
        
        <form action="../../backend/controllers/AuthController.php?action=register" method="POST" id="registerForm">
            <div class="form-group">
                <i class="fas fa-user"></i>
                <input type="text" class="form-control" name="username" placeholder="Nom d'utilisateur" required minlength="3">
            </div>
            
            <div class="form-group">
                <i class="fas fa-envelope"></i>
                <input type="email" class="form-control" name="email" placeholder="Adresse email" required>
            </div>
            
            <div class="form-group">
                <i class="fas fa-lock"></i>
                <input type="password" class="form-control" name="password" id="password" placeholder="Mot de passe" required minlength="6">
                <div class="password-strength">
                    <div class="strength-bar" id="strengthBar"></div>
                </div>
                <small class="strength-text" id="strengthText"></small>
            </div>
            
            <div class="form-group">
                <i class="fas fa-lock"></i>
                <input type="password" class="form-control" id="confirmPassword" placeholder="Confirmer le mot de passe" required>
                <small id="passwordMatch" class="text-danger"></small>
            </div>
            
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="terms" required>
                <label class="form-check-label" for="terms">
                    J'accepte les <a href="#" class="text-primary">conditions d'utilisation</a>
                </label>
            </div>
            
            <button type="submit" class="btn-register" id="submitBtn">
                <i class="fas fa-user-plus me-2"></i>S'inscrire
            </button>
        </form>
        
        <div class="login-link">
            Déjà un compte ? <a href="login.html">Connectez-vous</a>
        </div>
    </div>

    <script>
        // Validation du mot de passe en temps réel
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirmPassword');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');
        const submitBtn = document.getElementById('submitBtn');
        
        password.addEventListener('input', checkPasswordStrength);
        confirmPassword.addEventListener('input', checkPasswordMatch);
        
        function checkPasswordStrength() {
            const value = password.value;
            let strength = 0;
            
            if(value.length >= 6) strength += 25;
            if(value.match(/[a-z]+/)) strength += 25;
            if(value.match(/[A-Z]+/)) strength += 25;
            if(value.match(/[0-9]+/)) strength += 25;
            
            strengthBar.style.width = strength + '%';
            
            if(strength <= 25) {
                strengthBar.style.background = '#dc3545';
                strengthText.textContent = 'Faible';
                strengthText.style.color = '#dc3545';
            } else if(strength <= 50) {
                strengthBar.style.background = '#ffc107';
                strengthText.textContent = 'Moyen';
                strengthText.style.color = '#ffc107';
            } else if(strength <= 75) {
                strengthBar.style.background = '#17a2b8';
                strengthText.textContent = 'Bon';
                strengthText.style.color = '#17a2b8';
            } else {
                strengthBar.style.background = '#28a745';
                strengthText.textContent = 'Excellent';
                strengthText.style.color = '#28a745';
            }
        }
        
        function checkPasswordMatch() {
            if(confirmPassword.value) {
                if(password.value !== confirmPassword.value) {
                    document.getElementById('passwordMatch').textContent = 'Les mots de passe ne correspondent pas';
                    submitBtn.disabled = true;
                } else {
                    document.getElementById('passwordMatch').textContent = '';
                    submitBtn.disabled = false;
                }
            }
        }
    </script>
</body>
</html>