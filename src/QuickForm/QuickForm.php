<?php

namespace QuickForm;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class QuickForm
{

  private $_config;
  private $_fieldsWithErrors;

  function __construct($config) {
    $this->_config = $config;
    $this->_fieldsWithErrors = array();
  }

/**
  * Outputs the form
  *
  * @return void
  */
  public function renderForm()
  {

    $currentUrl = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    echo
      '<form ' .
      'method="POST" ' .
      'action="' . $currentUrl . '" ' .
      (isset($this->_config['form']['formClass']) ? 'class="' . $this->_config['form']['formClass'] . '" ' : '') .
      '>';

    if (sizeof($this->_fieldsWithErrors)) {
      echo '<div style="display: inline-block; background-color: #c00; color: #fff; padding: 7px;">Required fields were not completed. Please fill in the missing values below and resubmit the form.</div>';
      echo '<br>';
      echo '<br>';
    }

    foreach($this->_config['fields'] as $fieldId => $fieldParams) {

      // Open wrapper class
      if (!empty(@$fieldParams['wrapperClass'])) {
        echo
          '<div ' .
          'class="' . $fieldParams['wrapperClass'] . '">';
      }

      if (!empty(@$fieldParams['label'])) {
        echo
          '<label ' .
          'class="' . @$fieldParams['labelClass'] . '"' .
          'for="' . $fieldId . '"' .
          '>' .
          $fieldParams['label'] .
          (in_array($fieldId, $this->_fieldsWithErrors) ? ' &nbsp; <span style="background-color: #c00; color: #fff; padding: 3px;">Required</span>' : '') .
           '</label>';
      }

      if (isset($_POST[$fieldId])) {
        $value = $_POST[$fieldId];
      } else {
        $value = '';
      }

      switch(@$fieldParams['type']) {

        case 'text':
        case 'email':
        case 'tel':

          echo
            '<input ' .
            'type="' . $fieldParams['type'] . '" ' .
            'id="' . $fieldId . '" ' .
            'name="' . $fieldId . '" ' .
            'class="' . @$fieldParams['inputClass'] . '" ' .
            'value="' . htmlentities($value, ENT_QUOTES, "UTF-8") . '" ' .
            (@$fieldParams['required'] === true ? 'required' : '') .
            '>';
          break;

        case 'textarea':
          echo
            '<textarea ' .
            'id="' . $fieldId . '" ' .
            'name="' . $fieldId . '" ' .
            'class="' . @$fieldParams['inputClass'] . '" ' .
            (@$fieldParams['required'] === true ? 'required' : '') .
            '>' .
            htmlentities($value, ENT_QUOTES, "UTF-8") .
            '</textarea>';
          break;

          case 'radio':
            $count = 1;
            foreach(@$fieldParams['options'] as $option) {
              echo
                '<label for="' . $fieldId . '-' . $count . '">' .
                '<input ' .
                'id="' . $fieldId . '-' . $count . '" ' .
                'type="radio" ' .
                'name="' . $fieldId . '" ' .
                'value="' . $option .  '" ' .
                ($option === $value ? 'checked' : '') .
                '>' .
                ' ' .
                '<span>' . htmlentities($option, ENT_QUOTES, "UTF-8") . '</span>' .
                '</label>';
              $count++;
            }
            break;

          case 'checkbox':
            $count = 1;
            foreach(@$fieldParams['options'] as $option) {
              echo
                '<label for="' . $fieldId . '-' . $count . '">' .
                '<input ' .
                'id="' . $fieldId . '-' . $count . '" ' .
                'type="checkbox" ' .
                'name="' . $fieldId . '[]" ' .
                'value="' . $option .  '" ' .
                (@in_array($option, $value) ? 'checked' : '') .
                '>' .
                ' ' .
                '<span>' . htmlentities($option, ENT_QUOTES, "UTF-8") . '</span>' .
                '</label>';
              $count++;
            }
            break;
      }

      if (!empty(@$fieldParams['helpText'])) {
        echo '<div class="' . @$fieldParams['helpClass'] . '">' . $fieldParams['helpText'] . '</label>';
      }

      // Close wrapper class
      if (!empty(@$fieldParams['wrapperClass'])) {
        echo '</div>';
      }

    }

    echo
      '<button ' .
      'type="submit" ' .
      (isset($this->_config['form']['buttonClass']) ? 'class="' . $this->_config['form']['buttonClass'] . '" ' : '') .
      '>Submit</button>';

    echo '</form>';

  }

  /**
    * Traps form POST submissions, validates the presence of required fields.
    * If errors, this will return and allow form to be re-rendered.
    * If no errors, sends email to the recipients specified in the config.
    *
    * @return void
    */
  function formPostHandler()
  {
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
      return;
    }

    // We start out assuming it's valid
    $formValid = true;

    // Check all fields
    foreach($this->_config['fields'] as $fieldId => $fieldParams) {

      if (@$fieldParams['required'] == true) {
        if (isset($_POST[$fieldId])) {
          if (trim($_POST[$fieldId]) === '') {
            $formValid = false;
            $this->_fieldsWithErrors[] = $fieldId;
          }
        } else {
          $formValid = false;
          $this->_fieldsWithErrors[] = $fieldId;
        }

      }

    }

    // If validation of required fields failed, return now
    if (sizeof($this->_fieldsWithErrors)) {
      return;
    }

    // Load PHPMailer
    $mail = new PHPMailer(true);

    try {

        // Mail server settings
        $mail->SMTPDebug = $this->_config['phpMailer']['smtpDebug'];

        if ($this->_config['phpMailer']['isSmtp']) {
          $mail->isSMTP();
          $mail->Host = $this->_config['phpMailer']['host'];
          $mail->SMTPAuth = $this->_config['phpMailer']['smtpAuth'];
          $mail->Username = $this->_config['phpMailer']['username'];
          $mail->Password = $this->_config['phpMailer']['password'];
          $mail->SMTPSecure = $this->_config['phpMailer']['smtpSecure'];
          $mail->Port = $this->_config['phpMailer']['port'];
        }

        // Set sender, recipients, and reply-to
        $mail->setFrom($this->_config['from']);
        if (is_array($this->_config['to'])) {
          foreach($this->_config['to'] as $to) {
            $mail->addAddress($to);
          }
        } else {
          $mail->addAddress($this->_config['to']);
        }

        if (isset($this->_config['replyto'])) {
          $mail->addReplyTo($this->_config['replyto']);
        }

        // Content of email
        $mail->isHTML(true);
        $mail->Subject = $this->_config['subject'];

        // Loop over form fields and assemble both HTML and plaintext emails
        $htmlContent = '';
        $plaintextContent = '';
        foreach($this->_config['fields'] as $fieldId => $fieldParams) {

          $htmlContent .= '<h4>' . $fieldParams['label'] . '</h4>';
          $plaintextContent .= $fieldParams['label'] . "\r\n";

          if (is_array(@$_POST[$fieldId])) {

            $htmlContent .= '<p>';
            foreach($_POST[$fieldId] as $thisValue) {
              $htmlContent .= htmlentities($thisValue, ENT_QUOTES, "UTF-8") . '<br>';
              $plaintextContent .= $thisValue . "\r\n";
            }
            $htmlContent .= '</p>';

          } else {
            $htmlContent .= '<p>' . htmlentities(@$_POST[$fieldId], ENT_QUOTES, "UTF-8") . '</p>';
            $plaintextContent .= @$_POST[$fieldId] . "\r\n";
          }

          $plaintextContent .= "\r\n";
        }

        $mail->Body    = $htmlContent;
        $mail->AltBody = $plaintextContent;

        $mail->send();

        header('Location: ' . $this->_config['successURL']);
        exit;

    } catch (Exception $e) {

      $error = htmlentities($mail->ErrorInfo, ENT_QUOTES, "UTF-8");
      // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";

      echo '<html><head><title>Submission Error</title><body><br><div style="text-align: center; background-color: #c00; color: #fff; padding: 7px;">ERROR: Form submission failed due to a server error. Please click your browser\'s &quot;back&quot; button to return to the form and try again. If the problem persists please contact us to let us know.</div></body></html>';
      exit;
    }

  }

}

?>
