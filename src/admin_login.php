<?php

include_once 'connector.php';

if(!isset($_SESSION)){
    session_id("connector");
    session_start();
}

if(!is_user_registered()){
    header('Location: register.php');
    exit();
}


if(isset($_SESSION['authorized']) && !empty($_SESSION['authorized'])){
    if($_SESSION['authorized'] == true){
        if(mo_saml_is_customer_license_verified()){
            header("Location: setup.php");
            exit();
        } elseif(isset($_REQUEST['option'])) {
            header("Location: account.php");
            exit();
        }
    }
}
if(isset($_REQUEST['option']) && $_REQUEST['option'] == 'admin_login'){
    
    $email='';
    $password = '';
    if(isset($_POST['email']) && !empty($_POST['email']))
        $email = $_POST['email'];
    if(isset($_POST['password']) && !empty($_POST['password']))
        $password = $_POST['password'];
    if(!empty($password)){
        $password = sha1($password);
    }
        $str='';
        if((file_exists(dirname(__FILE__) . '\helper\data\credentials.json')))
            $str = file_get_contents(dirname(__FILE__) . '\helper\data\credentials.json');
        
        $credentials_array = json_decode($str, true);
        
        $password_check = '';
        if(is_array($credentials_array))
            if(array_key_exists($email, $credentials_array))
                $password_check = $credentials_array[$email];
            else {
                $_SESSION['invalid_credentials'] = true;

            }

        if(!empty($password_check)){
            if($password === $password_check){
                
                if(!isset($_SESSION['authorized']) || $_SESSION['authorized'] != true){
                    $_SESSION['authorized'] = true;
                }
                $_SESSION['admin_email'] = $email;
                if(mo_saml_is_customer_license_verified()){
                    header("Location: setup.php"); 
                    exit();
                } else {
                    header("Location: account.php");
                    exit();
                }
                
            } else {
                $_SESSION['invalid_credentials'] = true;

            }
        }
    
}



?>
