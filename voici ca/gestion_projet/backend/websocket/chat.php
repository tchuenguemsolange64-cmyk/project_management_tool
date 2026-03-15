<?php
// backend/websocket/Chat.php
namespace MyApp;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use PDO;

class Chat implements MessageComponentInterface {
    protected $clients;
    private $db;
    
    public function __construct() {
        $this->clients = new \SplObjectStorage;
        
        // Connexion à la base de données
        try {
            $this->db = new PDO("mysql:host=localhost;dbname=gestion_projet;charset=utf8", "root", "");
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(\PDOException $e) {
            echo "Erreur de connexion BDD: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Quand un nouveau client se connecte
     */
    public function onOpen(ConnectionInterface $conn) {
        // Stocker la connexion
        $this->clients->attach($conn);
        
        // Récupérer les paramètres de l'URL (ex: ?project_id=1&user_id=5)
        $queryString = $conn->httpRequest->getUri()->getQuery();
        parse_str($queryString, $params);
        
        // Stocker les infos du client
        $conn->project_id = $params['project_id'] ?? null;
        $conn->user_id = $params['user_id'] ?? null;
        
        echo "Nouvelle connexion ! (ID: {$conn->resourceId}, Projet: {$conn->project_id})\n";
        
        // Envoyer un message de bienvenue
        $conn->send(json_encode([
            'type' => 'system',
            'message' => 'Connecté au serveur WebSocket',
            'time' => date('H:i:s')
        ]));
    }
    
    /**
     * Quand un message est reçu
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        
        if(!$data) {
            echo "Message invalide reçu\n";
            return;
        }
        
        echo "Message reçu de {$from->resourceId}: " . $msg . "\n";
        
        switch($data['type']) {
            case 'task_created':
                // Nouvelle tâche créée
                $this->broadcastToProject($from->project_id, [
                    'type' => 'notification',
                    'event' => 'task_created',
                    'task' => $data['task'],
                    'message' => 'Nouvelle tâche créée'
                ]);
                break;
                
            case 'task_moved':
                // Tâche déplacée (drag & drop)
                $this->broadcastToProject($from->project_id, [
                    'type' => 'update',
                    'event' => 'task_moved',
                    'task_id' => $data['task_id'],
                    'from_column' => $data['from_column'],
                    'to_column' => $data['to_column'],
                    'message' => 'Tâche déplacée'
                ]);
                break;
                
            case 'comment_added':
                // Nouveau commentaire
                $this->broadcastToProject($from->project_id, [
                    'type' => 'notification',
                    'event' => 'comment_added',
                    'comment' => $data['comment'],
                    'task_id' => $data['task_id'],
                    'user' => $data['user'],
                    'message' => 'Nouveau commentaire'
                ]);
                
                // Notifier spécifiquement la personne assignée
                if(isset($data['assigned_to']) && $data['assigned_to'] != $from->user_id) {
                    $this->sendToUser($data['assigned_to'], [
                        'type' => 'notification',
                        'event' => 'mention',
                        'message' => 'Quelqu\'un a commenté sur votre tâche'
                    ]);
                }
                break;
                
            case 'ping':
                // Keep-alive ping
                $from->send(json_encode(['type' => 'pong', 'time' => date('H:i:s')]));
                break;
        }
    }
    
    /**
     * Quand un client se déconnecte
     */
    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connexion {$conn->resourceId} fermée\n";
        
        // Notifier les autres membres
        $this->broadcastToProject($conn->project_id, [
            'type' => 'system',
            'message' => 'Un utilisateur s\'est déconnecté'
        ]);
    }
    
    /**
     * Quand une erreur survient
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Erreur: {$e->getMessage()}\n";
        $conn->close();
    }
    
    /**
     * Diffuser un message à tous les clients d'un projet
     */
    private function broadcastToProject($project_id, $data) {
        foreach($this->clients as $client) {
            if($client->project_id == $project_id) {
                $client->send(json_encode($data));
            }
        }
    }
    
    /**
     * Envoyer un message à un utilisateur spécifique
     */
    private function sendToUser($user_id, $data) {
        foreach($this->clients as $client) {
            if($client->user_id == $user_id) {
                $client->send(json_encode($data));
                return true;
            }
        }
        return false;
    }
}
?>