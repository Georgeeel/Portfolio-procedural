<?php
session_start();
// on analyse ce qu'il y a à faire
$action = 'empty';
// si la clé "faire" est détecté dans $_POST (avec la balise caché)input type="hidden"
if(isset($_POST["faire"])){
    //notre variable action est égale à la valeur de la clé faire
    $action = $_POST["faire"];
}

//on utilise un switch pour vérifier l'action
switch($action) :
    // log admin correspond à value="log-admin" dans l'input caché du fichier admin/index.php
    case "log-admin";
        logAdmin();
    break;
    case "log-out";
        logOut();
    break;
    case "update";
        //modification d'utilisateur
        updateUser();
    break;
    case "delete_user";
        deleteUser();
endswitch;

function logAdmin(){
    //besoin de notre connexion
    require("connexion.php");
    //vérification de l'email de l'admin qui est unique,
    //préparation des données, formatage
    $login = trim(strtolower($_POST["login"]));
    // vérification de l'email de l'admin qui est unique 
    //écriture SQL(Read au niveau du CRUD) avec SELECT
    $sql = "SELECT * 
            FROM user
            WHERE email = '$login'
            ";
    $query = mysqli_query($connexion, $sql) or die(mysqli_error($connexion));
    //traitement des données
    //fonction mysqli_num_rows() qui compte de nombre de ligne 
    if(mysqli_num_rows($query) > 0){
        //on met sous forme de tableau associatif les données de l'admin récupérer 
        $user = mysqli_fetch_assoc($query);
        //ensuite il faut vérifier le mot de passe
        //le but c'est de verifier si le mot de passe saisie egal à l'encodage stocké en bdd avec 
        //la fonction password_verify() qui nous renvoie true ou false, on vérifie le mot de passe
        if(password_verify(trim($_POST['password']), $user['password'])){
            //vérifier le role
            //on dit que 1 c'est le role admin
            if($user["role"] != 1){
                // on envoie un message d'alerte 
                $_SESSION["message"] = "Vous n'êtes pas l'administrateur de ce site ";
                //redirection vers la page d'accueil
                header("Location:../index.php");
                exit();

            }else{
                // on crée plusieurs variables de session qui permettent un affichage personnalisé et de sécuriser l'accés du back-office
                $_SESSION["prenom"] = $user["prenom"];
                $_SESSION["isLog"] = true;
                $_SESSION["role"] = $user["role"];
                header("Location:../admin/accueilAdmin.php");
                exit();
            }
        }else{
            $_SESSION["message"] = "Erreur de mot de passe  !!";
            header("Location:../admin/index.php");
            exit();
        }

    } else {
        $_SESSION["message"] = "Désolé, pas d'administrateur identifié";
        header("Location:../admin/index.php");
        exit();
    }  
}
function logOut(){
    // pour déconecter l'admin il faut supprimer les variables de session 
    //on détruit la session  avec session_destroy();
    session_destroy();
    session_start();
    //message flash
    $_SESSION["message"] = "Vous êtes déconnecté !";
    // redirection vers page d'accueil du site
    header("Location:../index.php");
    exit();
}

// function modifier l'utilisateur
function updateUser(){
    // récupération des infos par le formulaire
    //vérification si les informations on bien été envoyées
    if(!isset($_POST["nom"], $_POST["prenom"], $_POST["email"], $_POST["password"], $_POST["role"], $_POST["id"])){
        $_SESSION["message"] = "Information manquantes dans le formulaire";
        header("Location:../admin/updateUser.php?id_user=" .$_POST["id"]);
        exit();
    }
    $nom = trim(ucfirst($_POST["nom"]));
    $prenom = trim(ucfirst($_POST["prenom"]));
    $email = trim(strtolower($_POST["email"]));
    $motDePasse = trim($_POST["password"]);
    $role = $_POST["role"];
    $id = $_POST["id"];

    if(strlen($nom) < 1 || strlen($nom) > 255){
        $_SESSION["message"] = "Le nom doit avoir entre 1 et 255 caractéres";
        header("Location:../admin/updateUser.php?id_user=" .$_POST["id"]);
        exit();
    }
    if(strlen($prenom) < 1 || strlen($prenom) > 255){
        $_SESSION["message"] = "Le nom doit avoir entre 1 et 255 caractéres";
        header("Location:../admin/updateUser.php?id_user=" .$_POST["id"]);
        exit();
    }
    if(strlen($email) < 1 || strlen($email) > 255 || !filter_var($email,FILTER_VALIDATE_EMAIL)){
        $_SESSION["message"] = "L'email est invalid";
        header("Location:../admin/updateUser.php?id_user=" .$_POST["id"]);
        exit();
    }
    if(strlen($motDePasse) < 1 ){
        $_SESSION["message"] = "Le mot de passe doit avoir au moins 1 caractére";
        header("Location:../admin/updateUser.php?id_user=" .$_POST["id"]);
        exit();
    }
    if($role !=1 && $role !=2){
        $_SESSION["message"] = "Le rôle est invalide ";
        header("Location:../admin/updateUser.php?id_user=" .$_POST["id"]);
        exit();
    }
    // encodage mot de passe
    $options = ['cost' => 12];
    $motDePasse = password_hash($motDePasse, PASSWORD_DEFAULT, $options);
    // les données préparons-nous  à les envoyer en base de données
    require("connexion.php");

    $sql = "UPDATE user
            SET `nom` = '$nom',
            `prenom` = '$prenom',
            `email` = '$email',
            `role` = '$role',
            `password` = '$motDePasse'
            WHERE `id_user` = $id
    ";
    $query = mysqli_query($connexion, $sql) or die(mysqli_error($connexion));
    $_SESSION["message"] = "Les données ont bien été mises à jour";
        header("Location:../admin/updateUser.php?id_user=" .$_POST["id"]);
        exit();
}
function deleteUser(){
    // récupération de la connexion
    require("connexion.php");
}
?>