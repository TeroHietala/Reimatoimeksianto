<?php
// Alustetaan istunto
session_start();
 
// Tarkistetaan onko käyttäjä kirjautunut sisään, muussa tapauksessa ohjaa kirjautumissivulle
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
 
// Sisällytetään asetustiedosto
require_once "config.php";
 
// Määrittellään muuttujat, sanitoidaan ja alustetaan tyhjillä arvoilla
$new_password = filter_var($confirm_password = "", FILTER_SANITIZE_STRING);
$new_password_err = filter_var($confirm_password_err = "", FILTER_SANITIZE_STRING);
 
// Lomaketietojen käsittely lomaketta lähetettäessä
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Vahvistetaan uusi salasana
    if(empty(trim($_POST["new_password"]))){
        $new_password_err = "Please enter the new password.";     
    } elseif(strlen(trim($_POST["new_password"])) < 6){
        $new_password_err = "Password must have atleast 6 characters.";
    } else{
        $new_password = trim($_POST["new_password"]);
    }
    
    // Vahvistetaan vahvistettu salasana
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm the password.";
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($new_password_err) && ($new_password != $confirm_password)){
            $confirm_password_err = "Password did not match.";
        }
    }
        
    // Tarkistetaan syöttövirheet ennen tietokannan päivittämistä
    if(empty($new_password_err) && empty($confirm_password_err)){
        // Valmistellaan päivityslausunto
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Sidotaan muuttujat valmisteltuun lauseeseen parametreina
            mysqli_stmt_bind_param($stmt, "si", $param_password, $param_id);
            
            // Asetetaan parametrit
            $param_password = password_hash($new_password, PASSWORD_DEFAULT);
            $param_id = $_SESSION["id"];
            
            // Yritetään suorittaa valmis lausunto
            if(mysqli_stmt_execute($stmt)){
                // Salasana päivitetty onnistuneesti. Tuhoa istunto ja ohjaa kirjautumissivulle
                session_destroy();
                header("location: login.php");
                exit();
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Suljetaan
            mysqli_stmt_close($stmt);
        }
    }
    
    // Suljetaan yhteys
    mysqli_close($link);
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nollaa salasana</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body{ font: 14px sans-serif; }
        .wrapper{ width: 360px; padding: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Nollaa salasana</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"> 
            <div class="form-group">
                <label>Uusi salasana</label>
                <input type="password" name="new_password" class="form-control <?php echo (!empty($new_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $new_password; ?>">
                <span class="invalid-feedback"><?php echo $new_password_err; ?></span>
            </div>
            <div class="form-group">
                <label>Vahvista salasana</label>
                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit">
                <a class="btn btn-link ml-2" href="welcome.php">Peruuta</a>
            </div>
        </form>
    </div>    
</body>
</html>