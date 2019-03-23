<?php

namespace QuickForm;

class QuickForm
{

  private $_config;

  function __construct($config) {
    $this->_config = $config;
  }

/**
  * Outputs all form fields defined in the configuration.
  *
  * Iterates over all defined form fields and outputs them as HTML5.
  *
  * @return void
  */
  public function outputFormFields()
  {
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
           '</label>';
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

  }

  /**
    * Traps form POST submissions, validates the presence of required fields,
    * and sends email to the recipients specified in the config.
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
          if (is_empty(trim($_POST[$fieldId]))) {
            $formValid = false;
          }
        } else {
          $formValid = false;
        }

      }

    }

    // If validation of required fields failed, output an error page
    if (!$formValid) {
      $this->_outputErrorPage();
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
          $htmlContent .= '<p>' . htmlentities(@$_POST[$fieldId], ENT_QUOTES, "UTF-8") . '</p>';
          $htmlContent .= '<br>';

          $plaintextContent .= $fieldParams['label'] . "\r\n";
          $plaintextContent .= @$_POST[$fieldId] . "\r\n";
          $plaintextContent .= "\r\n";
        }

        $mail->Body    = $htmlContent;
        $mail->AltBody = $plaintextContent;

        $mail->send();
        echo 'Message has been sent';

    } catch (Exception $e) {

        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }

  }

}

?>
