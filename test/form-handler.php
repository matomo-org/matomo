<?php
if ( !isset( $_SESSION ) ) session_start();
if ( !$_POST ) exit;
if ( !defined( "PHP_EOL" ) ) define( "PHP_EOL", "\r\n" );


$to = "contact@avicenegroupe.fr";
$subject = "Formulaire de contact";



foreach ($_POST as $key => $value) {
    if (ini_get('magic_quotes_gpc'))
        $_POST[$key] = stripslashes($_POST[$key]);
    $_POST[$key] = htmlspecialchars(strip_tags($_POST[$key]));
}

// Assign the input values to variables for easy reference
$name      = @$_POST["name"];
$email     = @$_POST["email"];
$phone     = @$_POST["phone"];
$message   = @$_POST["comment"];
$verify    = @$_POST["verify"];


// Test input values for errors
$errors = array();
 //php verif name
if(isset($_POST["name"])){
 
        if (!$name) {
            $errors[] = "Merci de nous préciser votre nom.";
        } elseif(strlen($name) < 2)  {
            $errors[] = "Votre nom doit comporter au moins 2 caractères";
        }
 
}
    //php verif email
if(isset($_POST["email"])){
    if (!$email) {
        $errors[] = "Merci de nous préciser votre email.";
    } else if (!validEmail($email)) {
        $errors[] = "Merci de nous donner une adresse email valide.";
    }
}
    //php verif phone
if(isset($_POST["phone"])){
    if (!$phone) {
        $errors[] = "Merci de nous préciser votre numéro de téléphone.";
    }
}



//php verif comment
if(isset($_POST["comment"])){
    if (strlen($message) < 10) {
        if (!$message) {
            $errors[] = "Merci de nous préciser votre message";
        } else {
            $errors[] = "Votre message doit faire un minimum de 10 caractères.";
        }
    }
}

//php verif captcha
if(isset($_POST["verify"])){
    if (!$verify) {
        $errors[] = "Merci de rentrer le code de sécurité";
    } else if (md5($verify) != $_SESSION['nekoCheck']['verify']) {
        $errors[] = "Le code de sécurité est incorrecte";
    }
}

if ($errors) {
        // Output errors and die with a failure message
    $errortext = "";
    foreach ($errors as $error) {
        $errortext .= '<li>'. $error . "</li>";
    }

    echo '<div class="alert alert-error">Erreurs:<br><ul>'. $errortext .'</ul></div>';

}else{



    // Send the email
    $headers  = "From: $email" . PHP_EOL;
    $headers .= "Reply-To: $email" . PHP_EOL;
    $headers .= "MIME-Version: 1.0" . PHP_EOL;
    $headers .= "Content-type: text/plain; charset=utf-8" . PHP_EOL;
    $headers .= "Content-Transfer-Encoding: quoted-printable" . PHP_EOL;

    $mailBody  = "Vous avez été contacté par $name" . PHP_EOL . PHP_EOL;
    $mailBody .= (!empty($company))?'Société: '. PHP_EOL.$company. PHP_EOL . PHP_EOL:'';
    $mailBody .= (!empty($quoteType))?'Type de projet: '. PHP_EOL.$quoteType. PHP_EOL . PHP_EOL:''; 
    $mailBody .= "Message :" . PHP_EOL;
    $mailBody .= $message . PHP_EOL . PHP_EOL;
    $mailBody .= "Vous pouvez contacter $name via email, $email.";
    $mailBody .= (isset($phone) && !empty($phone))?" Ou par téléphone $phone." . PHP_EOL . PHP_EOL:'';
    $mailBody .= "-------------------------------------------------------------------------------------------" . PHP_EOL;






    if(mail($to, $subject, $mailBody, $headers)){
        echo '<div class="alert alert-success">Votre message nous a bien été envoyé. Nous vous recontacterons dans les plus brefs délais.</div>';
    }
}

// FUNCTIONS 
function validEmail($email) {
    $isValid = true;
    $atIndex = strrpos($email, "@");
    if (is_bool($atIndex) && !$atIndex) {
        $isValid = false;
    } else {
        $domain = substr($email, $atIndex + 1);
        $local = substr($email, 0, $atIndex);
        $localLen = strlen($local);
        $domainLen = strlen($domain);
        if ($localLen < 1 || $localLen > 64) {
            // local part length exceeded
            $isValid = false;
        } else if ($domainLen < 1 || $domainLen > 255) {
            // domain part length exceeded
            $isValid = false;
        } else if ($local[0] == '.' || $local[$localLen - 1] == '.') {
            // local part starts or ends with '.'
            $isValid = false;
        } else if (preg_match('/\\.\\./', $local)) {
            // local part has two consecutive dots
            $isValid = false;
        } else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
            // character not valid in domain part
            $isValid = false;
        } else if (preg_match('/\\.\\./', $domain)) {
            // domain part has two consecutive dots
            $isValid = false;
        } else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\", "", $local))) {
            // character not valid in local part unless
            // local part is quoted
            if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\", "", $local))) {
                $isValid = false;
            }
        }
        if ($isValid && !(checkdnsrr($domain, "MX") || checkdnsrr($domain, "A"))) {
            // domain not found in DNS
            $isValid = false;
        }
    }
    return $isValid;
}

?>
