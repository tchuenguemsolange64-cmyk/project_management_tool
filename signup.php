<?php
require('signupAction.php');
?>


<!DOCTYPE html>
<html lang="en">
<body>

  
<form class = "container" method = "POST">

    <?php
    if(isset($erromessage)){
        echo $erromessage.'<p>'.$erromessage.'</p>';
    }
    ?>


    <h2>Creer  compte</h2> 


    
    <label for="name" >name</label>
    <input type="text"  name="name">
    

    
    <label for="surname" class="form-label">surname </label>
    <input type="text"  name="SURNAME">
    

   
    <label for="email" >email </label>
    <input type="email"  name="email">
    
    
    <label for="password" >Password</label>
    <input type="password"  name="password">
   

    
    <button type="submit" class="btn" name="submit">register</button>
    <br>
    
    <a href="login.php"><p>already have an account </p></a>
</form>