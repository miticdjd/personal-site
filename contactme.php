<?php

define(BASE_PATH, realpath(dirname(__FILE__)) . '/');
define(RECAPTCHA_PRIVATE_KEY, ''); // Your recaptch private key
define(SMTP_HOST, ''); // Smtp host for gmail smpt.gmail.com
define(SMTP_SECURE, ''); // What secure connection to use for gmail ssl
define(SMTP_PORT, 465); // Smtp port for gmail is 465
define(SMTP_USERNAME, ''); // Smtp username
define(SMTP_PASSWORD, ''); // Smtp password
define(SMTP_NAME, ''); // Name for email 
define(EMAIL_SEND_TO, ''); // Email to send message
define(EMAIL_SEND_TO_NAME, ''); // Name for email

require_once BASE_PATH . 'lib/recaptchalib.php';
require_once BASE_PATH . 'lib/class.phpmailer.php';

$name = strip_tags($_POST['name']);
$email = strip_tags($_POST['email']);
$subject = strip_tags($_POST['subject']);
$message = strip_tags($_POST['message']);
$recaptchaChallengeField = strip_tags($_POST['recaptcha_challenge_field']);
$recaptchaResponseField = strip_tags($_POST['recaptcha_response_field']);

$recaptcha = recaptcha_check_answer (RECAPTCHA_PRIVATE_KEY, $_SERVER["REMOTE_ADDR"], $recaptchaChallengeField, $recaptchaResponseField);

$errors = array();

if (!$recaptcha->is_valid) 
{
    $errors['captch_error'] = 'The reCAPTCHA wasn\'t entered correctly. Go back and try it again.' . $recaptcha->error;
}

if (empty($name))
{
    $errors['name_error'] = 'Please enter your name.';
}

if (empty($email))
{
    $errors['email_error'] = 'Please enter your email so I can contact you.';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL))
{
    $errors['email_validate_error'] = 'Please enter valid email address.';
}

if (empty($message))
{
    $errors['message_error'] = 'Please enter your message.';
}

if (count($errors) > 0)
{
    $response = array(
        'status' => false,
        'errors' => $errors
    );
}
else
{
    $mail = new PHPMailer();

    $body = file_get_contents(BASE_PATH . 'lib/mail.html');
    $body = str_replace('{name}', $name, $body);
    $body = str_replace('{subject}', $subject, $body);
    $body = str_replace('{email}', $email, $body);
    $body = str_replace('{message}', $message, $body);

    $mail->IsSMTP();
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->Host = SMTP_HOST;
    $mail->Port = SMTP_PORT;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;

    $mail->SetFrom(SMTP_USERNAME, SMTP_NAME);

    $mail->Subject = $subject;
    $mail->AltBody = "To view the message, please use an HTML compatible email viewer!";

    $mail->MsgHTML($body);
    $mail->AddAddress(EMAIL_SEND_TO, EMAIL_SEND_TO_NAME);

    if (!$mail->Send())
    {
        $response = array(
            'status' => false,
            'errors' => array('sending_fail' => 'Error while sending email.')
        );
    } else
    {
         $response = array(
            'status' => true
        );
    }

}

echo json_encode($response);

?>