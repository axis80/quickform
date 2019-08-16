# Quickform for PHP
For small web sites built in HTML, a contact form is often the only thing on the
site that requires use of a server-side language such as PHP.  Adding a framework
such as Laravel is overkill, but without one the developer is often left with
no choice but to write a standalone script to handle form submissions.  This is
not a terribly difficult thing to do in PHP, but it's hella tedious, as is the
process of writing the markup to output the form itself.

Quickform is designed to minimize the time and effort it takes to get a
new contact form up and running on a simple HTML site hosted on a PHP server.
Form attributes and fields are defined in a config array, and submissions
are sent via email using PHPMailer.

Quickform does not currently perform validation of the values passed to it in
the form fields. It merely checks whether required fields have been filled out.
This is sufficient for most small web sites.

## Requirements
- PHP 7+ web server (may work in earlier versions but is untested)
- Composer
- An SMTP mail server, or a web server able to send using PHP's mail() function

## Usage
Install the package using composer, by typing:
`composer require axis80/quickform`

This will in turn install PHPMailer as a dependency.

Create an HTML page for your contact form.  It must be saved with a .php
extension, or you must enable PHP parsing for .html files.

At the top of the file, you have to include the `/vendor/autoload.php` file
that was created by Composer.  You also need to define your form, instantiate
the class, and call the handler that processes form POST submissions.  Here's
everything that goes at the top of your file:

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
      'replyto' =>  'bill@example.com', // optional
      'subject' => 'Web Site Form Submission',
      'successURL' => 'https://example.com/formsuccess.html',

      'form' => [
        'formClass' => 'your-form-class', // optional
        'buttonClass' => 'your-button-class' // optional
      ],

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
          'inputClass' => 'form-control', // optional
          'label' => 'Your Name',
          'labelClass' => 'control-label', // optional
          'wrapperClass' => 'form-group' // optional
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
        'toppings' => [
          'type' => 'checkbox',
          'required' => true,
          'options' => ['Pepperoni','Mushrooms','Onions','Anchovies'],
          'inputClass' => 'form-check-input',
          'wrapperClass' => 'form-group',
          'label' => 'Which toppings do you like?',
          'labelClass' => 'control-label',
          'itemLabelClass' => 'form-check-label'
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

Further on down your page, add the following code wherever you want your form
to appear:

    <?php $qf->renderForm(); ?>

QuickForm renders the entire form for you, including a submit button at the
bottom.  

A complete HTML contact page can be found in tests/SimpleContactForm.php,  You
can use this as a starting point for building your own contact form.

That's pretty much it.  If your SMTP settings are correct, you should receive a
notification email at the address(es) you specified every time the form is
submitted.

## Support
I'll do my best to keep up with issue reports that relate to bugs and/or
enhancements, as well as with any pull requests that come in. For anything else
you should probably have low expectations of receiving a response. I simply do
not have the time to answer questions about basic PHP scripting, working with
different web hosts, etc. Sorry :-(

## Contributions
Pull requests are welcome, particularly those which improve the documentation
and examples.
