<?php
// backend/api/routes.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

// Vérifier l'authentification pour les routes protégées
if(!isset($_SESSION['logged_in']) && $_GET['endpoint'] != 'login' && $_GET['endpoint'] != 'register') {
    http_response_code(401);
    echo json_encode(['error' => 'Non authentifié']);
    exit();
}

require_once '../config/Database.php';
require_once '../models/Project.php';
require_once '../models/Task.php';
require_once '../models/Comment.php';
require_once '../models/User.php';

$db = new Database();
$conn = $db->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$endpoint = $_GET['endpoint'] ?? '';

switch($endpoint) {
    case 'projects':
        $project = new Project($conn);
        if($method == 'GET') {
            // Récupérer tous les projets de l'utilisateur
            $projects = $project->getUserProjects($_SESSION['user_id']);
            echo json_encode($projects);
        }
        break;
        
    case 'tasks':
        $task = new Task($conn);
        if($method == 'POST') {
            // Créer une tâche
            $data = json_decode(file_get_contents('php://input'), true);
            $task->column_id = $data['column_id'];
            $task->title = $data['title'];
            $task->description = $data['description'];
            $task->assigned_to = $data['assigned_to'] ?: null;
            $task->priority = $data['priority'];
            $task->due_date = $data['due_date'] ?: null;
            $task->created_by = $_SESSION['user_id'];
            
            if($task->create()) {
                echo json_encode(['success' => true, 'message' => 'Tâche créée avec succès']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la création']);
            }
        }
        else if($method == 'PUT') {
            // Mettre à jour la position d'une tâche
            $data = json_decode(file_get_contents('php://input'), true);
            $task->updatePosition($data['id'], $data['column_id'], $data['position']);
            echo json_encode(['success' => true]);
        }
        break;
        
    case 'comments':
        $comment = new Comment($conn);
        if($method == 'GET') {
            // Récupérer les commentaires d'une tâche
            $task_id = $_GET['task_id'];
            $comments = $comment->getTaskComments($task_id);
            echo json_encode($comments);
        }
        else if($method == 'POST') {
            // Ajouter un commentaire
            $data = json_decode(file_get_contents('php://input'), true);
            $comment->task_id = $data['task_id'];
            $comment->user_id = $_SESSION['user_id'];
            $comment->content = $data['content'];
            
            if($comment->create()) {
                echo json_encode(['success' => true, 'message' => 'Commentaire ajouté']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout']);
            }
        }
        break;
        
    case 'members':
        if($method == 'GET') {
            // Récupérer les membres d'un projet
            $project_id = $_GET['project_id'];
            $query = "SELECT u.id, u.username, u.email, pm.role
                     FROM users u
                     JOIN project_members pm ON u.id = pm.user_id
                     WHERE pm.project_id = :project_id";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":project_id", $project_id);
            $stmt->execute();
            
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;
        
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint non trouvé']);
}
?>