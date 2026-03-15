<?php
// backend/models/User.php
class User {
    private $conn;
    private $table = "users";

    public $id;
    public $username;
    public $email;
    public $password;
    public $avatar;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Inscription
    public function register() {
        $query = "INSERT INTO " . $this->table . "
                SET username=:username, email=:email, password=:password";
        
        $stmt = $this->conn->prepare($query);
        
        // Nettoyage des données
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        
        // Binding
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Connexion
    public function login() {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();
        
        if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if(password_verify($this->password, $row['password'])) {
                $this->id = $row['id'];
                $this->username = $row['username'];
                $this->avatar = $row['avatar'];
                return true;
            }
        }
        return false;
    }

    // Récupérer un utilisateur par ID
    public function getById($id) {
        $query = "SELECT id, username, email, avatar, created_at 
                 FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>