<?php
// backend/controllers/ProjectController.php
session_start();
require_once '../config/Database.php';
require_once '../models/Project.php';
require_once '../models/Task.php';
require_once '../models/Comment.php';

class ProjectController {
    private $db;
    private $project;
    
    public function __construct() {
        // Vérifier si l'utilisateur est connecté
        if(!isset($_SESSION['logged_in'])) {
            header("Location: ../../frontend/pages/login.html");
            exit();
        }
        
        $database = new Database();
        $this->db = $database->getConnection();
        $this->project = new Project($this->db);
    }
    
    // Créer un projet
    public function create() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->project->name = $_POST['name'];
            $this->project->description = $_POST['description'];
            $this->project->created_by = $_SESSION['user_id'];
            
            if($this->project->create()) {
                $_SESSION['success'] = "Projet créé avec succès !";
                header("Location: ../../frontend/pages/dashboard.html");
            } else {
                $_SESSION['error'] = "Erreur lors de la création du projet";
                header("Location: ../../frontend/pages/dashboard.html");
            }
        }
    }
    
    // Afficher les détails d'un projet (pour la page project.html)
    public function view($project_id) {
        $project_details = $this->project->getProjectDetails($project_id);
        $columns = $this->project->getProjectColumns($project_id);
        
        // Récupérer les tâches pour chaque colonne
        $task = new Task($this->db);
        foreach($columns as &$column) {
            $column['tasks'] = $task->getColumnTasks($column['id']);
        }
        
        return [
            'project' => $project_details,
            'columns' => $columns
        ];
    }
    
    // API - Récupérer tous les projets (pour AJAX)
    public function getProjects() {
        header('Content-Type: application/json');
        $projects = $this->project->getUserProjects($_SESSION['user_id']);
        echo json_encode($projects);
    }
}

// Router
if(isset($_GET['action'])) {
    $controller = new ProjectController();
    
    switch($_GET['action']) {
        case 'create':
            $controller->create();
            break;
        case 'get':
            $controller->getProjects();
            break;
    }
}
?>