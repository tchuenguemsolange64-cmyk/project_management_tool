<?php
// backend/controllers/AuthController.php
session_start();
require_once '../config/Database.php';
require_once '../models/User.php';

class AuthController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    // Traitement de l'inscription
    public function register() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->user->username = $_POST['username'];
            $this->user->email = $_POST['email'];
            $this->user->password = $_POST['password'];
            
            if($this->user->register()) {
                $_SESSION['success'] = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
                header("Location: ../../frontend/pages/login.html");
            } else {
                $_SESSION['error'] = "Erreur lors de l'inscription. Email peut-être déjà utilisé.";
                header("Location: ../../frontend/pages/register.html");
            }
        }
    }

    // Traitement de la connexion
    public function login() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->user->email = $_POST['email'];
            $this->user->password = $_POST['password'];
            
            if($this->user->login()) {
                $_SESSION['user_id'] = $this->user->id;
                $_SESSION['username'] = $this->user->username;
                $_SESSION['logged_in'] = true;
                
                header("Location: ../../frontend/pages/dashboard.html");
            } else {
                $_SESSION['error'] = "Email ou mot de passe incorrect";
                header("Location: ../../frontend/pages/login.html");
            }
        }
    }

    // Déconnexion
    public function logout() {
        session_destroy();
        header("Location: ../../frontend/index.html");
    }
}

// Router simple
if(isset($_GET['action'])) {
    $auth = new AuthController();
    switch($_GET['action']) {
        case 'register':
            $auth->register();
            break;
        case 'login':
            $auth->login();
            break;
        case 'logout':
            $auth->logout();
            break;
    }
}
?>