<?php
// backend/models/Project.php
class Project {
    private $conn;
    private $table = "projects";
    
    public $id;
    public $name;
    public $description;
    public $created_by;
    public $status;
    public $created_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Créer un projet
    public function create() {
        $query = "INSERT INTO " . $this->table . "
                 (name, description, created_by, status)
                 VALUES (:name, :description, :created_by, 'actif')";
        
        $stmt = $this->conn->prepare($query);
        
        // Nettoyage
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->created_by = htmlspecialchars(strip_tags($this->created_by));
        
        // Binding
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":created_by", $this->created_by);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            
            // Ajouter le créateur comme membre admin
            $this->addMember($this->id, $this->created_by, 'admin');
            
            // Créer les colonnes par défaut
            $this->createDefaultColumns($this->id);
            
            return true;
        }
        return false;
    }
    
    // Ajouter un membre au projet
    public function addMember($project_id, $user_id, $role = 'member') {
        $query = "INSERT INTO project_members (project_id, user_id, role)
                 VALUES (:project_id, :user_id, :role)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":project_id", $project_id);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":role", $role);
        
        return $stmt->execute();
    }
    
    // Créer les colonnes par défaut (À faire, En cours, Terminé)
    private function createDefaultColumns($project_id) {
        $columns = [
            ['À faire', 1],
            ['En cours', 2],
            ['Terminé', 3]
        ];
        
        $query = "INSERT INTO columns (project_id, title, position) 
                 VALUES (:project_id, :title, :position)";
        $stmt = $this->conn->prepare($query);
        
        foreach($columns as $col) {
            $stmt->bindParam(":project_id", $project_id);
            $stmt->bindParam(":title", $col[0]);
            $stmt->bindParam(":position", $col[1]);
            $stmt->execute();
        }
    }
    
    // Récupérer tous les projets d'un utilisateur
    public function getUserProjects($user_id) {
        $query = "SELECT p.*, 
                         (SELECT COUNT(*) FROM tasks t 
                          JOIN columns c ON t.column_id = c.id 
                          WHERE c.project_id = p.id) as total_tasks,
                         (SELECT COUNT(*) FROM project_members 
                          WHERE project_id = p.id) as total_members
                  FROM projects p
                  JOIN project_members pm ON p.id = pm.project_id
                  WHERE pm.user_id = :user_id
                  ORDER BY p.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Récupérer un projet avec ses détails
    public function getProjectDetails($project_id) {
        $query = "SELECT p.*, u.username as creator_name
                 FROM " . $this->table . " p
                 JOIN users u ON p.created_by = u.id
                 WHERE p.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $project_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Récupérer les colonnes d'un projet
    public function getProjectColumns($project_id) {
        $query = "SELECT * FROM columns 
                 WHERE project_id = :project_id 
                 ORDER BY position";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":project_id", $project_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>