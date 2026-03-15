<?php
// backend/models/Comment.php
class Comment {
    private $conn;
    private $table = "comments";
    
    public $id;
    public $task_id;
    public $user_id;
    public $content;
    public $created_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Ajouter un commentaire
    public function create() {
        $query = "INSERT INTO " . $this->table . "
                 (task_id, user_id, content)
                 VALUES (:task_id, :user_id, :content)";
        
        $stmt = $this->conn->prepare($query);
        
        $this->content = htmlspecialchars(strip_tags($this->content));
        
        $stmt->bindParam(":task_id", $this->task_id);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":content", $this->content);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            
            // Notifier l'utilisateur assigné
            $this->notifyTaskAssignee();
            
            return true;
        }
        return false;
    }
    
    // Récupérer les commentaires d'une tâche
    public function getTaskComments($task_id) {
        $query = "SELECT c.*, u.username, u.avatar
                 FROM " . $this->table . " c
                 JOIN users u ON c.user_id = u.id
                 WHERE c.task_id = :task_id
                 ORDER BY c.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":task_id", $task_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Notifier l'utilisateur assigné
    private function notifyTaskAssignee() {
        // Récupérer l'utilisateur assigné à la tâche
        $query = "SELECT assigned_to FROM tasks WHERE id = :task_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":task_id", $this->task_id);
        $stmt->execute();
        $task = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($task && $task['assigned_to'] && $task['assigned_to'] != $this->user_id) {
            $query = "INSERT INTO notifications (user_id, type, message, link)
                     VALUES (:user_id, 'comment', :message, :link)";
            
            $stmt = $this->conn->prepare($query);
            $message = "Nouveau commentaire sur votre tâche";
            $link = "project.php?task=" . $this->task_id;
            
            $stmt->bindParam(":user_id", $task['assigned_to']);
            $stmt->bindParam(":message", $message);
            $stmt->bindParam(":link", $link);
            $stmt->execute();
        }
    }
}
?>