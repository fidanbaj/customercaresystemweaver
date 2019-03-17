<?
//Constants
const SUPPORT_EMAIL = "info@webweaves.ch";
const LOGIN_PW_SALT = "weaverPW2019salt";

//Helpers
function sendMail($email, $subject, $message) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    mail($email, $subject, $message, $headers);
}
    
function html2text($inputText) {
    $convertedString = str_replace('&nbsp;', ' ', $inputText);
    $convertedString = html_entity_decode($convertedString, ENT_QUOTES | ENT_COMPAT , 'UTF-8');
    $convertedString = html_entity_decode($convertedString, ENT_HTML5, 'UTF-8');
    $convertedString = html_entity_decode($convertedString);
    $convertedString = htmlspecialchars_decode($convertedString);
    $convertedString = strip_tags($convertedString);

    return $convertedString;
}
?>