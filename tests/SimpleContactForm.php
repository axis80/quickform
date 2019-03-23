<?php

use QuickForm\QuickForm;

/* Path to composer autoload.php.  You may need to adjust this path */
require_once(__DIR__ . '/vendor/autoload.php');

/* Your form configuration */
$qfConfig = [
  'to' =>  [
    'sales@example.com',
    'info@example.com'
  ],
  'from' =>  'website@example.com',
  'replyto' =>  'bill@example.com',
  'subject' => 'Web Site Form Submission',

  // PHPMailer settings - see their docs for details
  'phpMailer' => [
    'smtpDebug' => 0,
    'isSmtp' => true,
    'host' => 'smtp.example.com',
    'smtpAuth' => true,
    'username' => 'smtpuser@example.com',
    'password' => '<SMTP User Password Goes Here>',
    'smtpSecure' => 'tls',
    'port' => 587
  ],

  // Field definitions
  'fields' => [
    'name' => [
      'type' => 'text',
      'required' => true,
      'inputClass' => 'form-control',
      'label' => 'Your Name',
      'labelClass' => 'control-label',
      'wrapperClass' => 'form-group'
    ],
    'email' => [
      'type' => 'email',
      'required' => true,
      'inputClass' => 'form-control',
      'wrapperClass' => 'form-group',
      'label' => 'Your Email Address',
      'labelClass' => 'control-label',
      'helpText' => 'Enter your email address',
      'helpClass' => 'form-text text-muted'
    ],
    'pizza' => [
      'label' => 'Do you like pizza?',
      'labelClass' => 'control-label',
      'type' => 'radio',
      'options' => ['Yes', 'No'],
      'required' => true,
      'wrapperClass' => 'form-group',
      'inputClass' => 'form-control'
    ],
    'comments' => [
      'type' => 'textarea',
      'required' => true,
      'inputClass' => 'form-control',
      'wrapperClass' => 'form-group',
      'label' => 'Your Comments',
      'labelClass' => 'control-label'
    ]
  ]

];

/* Instantiate the class using the above configuration */
$qf = new Quickform($qfConfig);

/* Call the handler that intercepts and processes form POST submissions */
$qf->formPostHandler();

?>
<html lang="en" dir="ltr">
<head>
    <title>QuickForm: Simple Contact Form</title>
</head>

<body>

  <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
  <?php $qf->outputFormFields(); ?>
  <button type="submit">Submit</button>
  </form>

</body>
</html>
