<?php
// session_start();
try {
    
    $bdd = new PDO('mysql:host=localhost; dbname=gestionstock; charset=utf8', 'root','');
} catch (PDOException $e) {
    die ('erreur de connexion :' .$e ->getMessage());
}

?>