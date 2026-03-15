<?php
// backend/websocket/server.php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use MyApp\Chat;

require_once __DIR__ . '/../../vendor/autoload.php';

// Configuration
$port = 8080; // Port pour WebSocket
$host = 'localhost';

echo "=====================================\n";
echo "🚀 Serveur WebSocket GestionProjet\n";
echo "=====================================\n";
echo "Démarrage du serveur sur {$host}:{$port}...\n";

try {
    // Création du serveur
    $server = IoServer::factory(
        new HttpServer(
            new WsServer(
                new Chat()
            )
        ),
        $port,
        $host
    );
    
    echo "✅ Serveur démarré avec succès !\n";
    echo "📡 En attente de connexions...\n";
    echo "Appuyez sur Ctrl+C pour arrêter\n";
    echo "=====================================\n\n";
    
    $server->run();
    
} catch(\Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "Vérifie que le port {$port} n'est pas déjà utilisé\n";
}
?>