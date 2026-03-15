<?php
session_start();
require('connexion.php');

if(isset($_POST['submit'])){
    //verifier si l'user a tous complete les champs

    if(!empty($_POST['name']) AND !empty($_POST['password']) ){
        //les donnees de l'utilisateur 

        $user_nom = htmlspecialchars($_POST['name']);
        $user_password = htmlspecialchars($_POST['password']);

        //verifier si l'utilisateur existe (si )
        $checkifUserExits = $bdd->prepare('SELECT * FROM users WHERE name = ? ');
        $checkifUserExits->execute(array($user_name));


        if($checkifUserExits->rowCount() > 0){
            // recuperer les donneer de l'utilisateur  
            $userInfos = $checkifUserExits->fetch();
            // verifier si le mot de passe est correct

            
            if(password_verify($user_password, $userInfos['mdp'])){
                //Authentifier l'user sur le site et recuperer ses donner dans des variable global session

                $_SESSION['auth'] = true;
                $_SESSION['id_utilisateur'] = $userInfos['id_utilisateur'];
                $_SESSION['name'] = $userInfos['name'];
                $_SESSION['surname'] = $userInfos['surname'];
                $_SESSION['email'] = $userInfos['email'];
            }else {
             $erromessage = "votre mot de passe est incorrect......";
            }    
        }else {
            $erromessage = "votre nom est incorrect....";
        }        
    }else {
        $erromessage = "veuillez remplire les champs...";
    }            
}
        