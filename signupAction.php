<?php
session_start();
require('connexion.php');

//validation du formulaire      

if(isset($_POST['submit'])){

    //verifier si l'user a tous complete les champs

    if(!empty($_POST['name']) AND !empty($_POST['surname']) AND !empty(['email']) AND !empty(['password'])){

        $user_nom = htmlspecialchars($_POST['name']);
        $user_pernom = htmlspecialchars($_POST['surname']);
        $user_email = htmlspecialchars($_POST['email']);
        $user_password = password_hash($_POST['password'],PASSWORD_DEFAULT);

        //verifier si l'user excite deja 

        $userAlredy = $bdd->prepare('SELECT name FROM users WHERE name = ?');
        $userAlredy->execute(array($user_name));

        if($userAlredy->rowCount() == 0){
            
            //inserer l'user dans la bdd
            $insertUser = $bdd->prepare('INSERT INTO users(name, surname, email, mdp)VALUES(?, ?, ?, ?)');
            $insertUser->execute(array($user_name, $user_surname, $user_email, $user_password));

            //recuperer les informations de l'user
            $getInfoUser = $bdd->prepare('SELECT  id_utilisateur, name, surname, email FROM users WHERE name = ? AND surname = ? AND email = ?');
            $getInfoUser->execute(array($user_name, $user_surname, $user_email));

            $userInfo = $getInfoUser->fetch();

            //Authentifier l'user sur le site et recuperer ses donner dans des variable global session

            $_SESSION['auth'] = true;
            $_SESSION['id_utilisateur'] = $userInfo['id_utilisateur'];
            $_SESSION['name'] = $userInfo['name'];
            $_SESSION['surname'] = $userInfo['surname'];
            $_SESSION['email'] = $userInfo['email'];

              //redirigere l'user vers la page d'acceuil

              header('Location: dashbord.php');



        }else{
            $erromessage = "l'utilisateur existe deja sur le site";
           }
   
   
   
    }else{
        $erromessage = "veuillez completer tous les champs.....";
    }
}
?>

