<?php
// backend/models/Task.php
class Task {
    private $conn;
    private $table = "tasks";
    
    public $id;
    public $column_id;
    public $title;
    public $description;
    public $assigned_to;
    public $priority;
    public $due_date;
    public $position;
    public $created_by;
    public $created_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Créer une tâche
    public function create() {
        // Obtenir la dernière position
        $query = "SELECT COALESCE(MAX(position), 0) + 1 as new_position 
                 FROM " . $this->table . " 
                 WHERE column_id = :column_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":column_id", $this->column_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->position = $row['new_position'];
        
        // Insertion
        $query = "INSERT INTO " . $this->table . "
                 (column_id, title, description, assigned_to, priority, 
                  due_date, position, created_by)
                 VALUES
                 (:column_id, :title, :description, :assigned_to, :priority,
                  :due_date, :position, :created_by)";
        
        $stmt = $this->conn->prepare($query);
        
        // Nettoyage
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        
        // Binding
        $stmt->bindParam(":column_id", $this->column_id);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":assigned_to", $this->assigned_to);
        $stmt->bindParam(":priority", $this->priority);
        $stmt->bindParam(":due_date", $this->due_date);
        $stmt->bindParam(":position", $this->position);
        $stmt->bindParam(":created_by", $this->created_by);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            $this->createNotification('task_assigned', 'Une nouvelle tâche vous a été assignée');
            return true;
        }
        return false;
    }
    
    // Récupérer les tâches d'une colonne
    public function getColumnTasks($column_id) {
        $query = "SELECT t.*, u.username as assigned_to_name,
                         (SELECT COUNT(*) FROM comments WHERE task_id = t.id) as comment_count
                  FROM " . $this->table . " t
                  LEFT JOIN users u ON t.assigned_to = u.id
                  WHERE t.column_id = :column_id
                  ORDER BY t.position";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":column_id", $column_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Mettre à jour la position (drag & drop)
    public function updatePosition($id, $new_column_id, $new_position) {
        $query = "UPDATE " . $this->table . "
                 SET column_id = :column_id, position = :position
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":column_id", $new_column_id);
        $stmt->bindParam(":position", $new_position);
        $stmt->bindParam(":id", $id);
        
        return $stmt->execute();
    }
    
    // Créer une notification
    private function createNotification($type, $message) {
        if($this->assigned_to) {
            $query = "INSERT INTO notifications (user_id, type, message, link)
                     VALUES (:user_id, :type, :message, :link)";
            
            $stmt = $this->conn->prepare($query);
            $link = "project.php?task=" . $this->id;
            
            $stmt->bindParam(":user_id", $this->assigned_to);
            $stmt->bindParam(":type", $type);
            $stmt->bindParam(":message", $message);
            $stmt->bindParam(":link", $link);
            
            $stmt->execute();
        }
    }
}
?>