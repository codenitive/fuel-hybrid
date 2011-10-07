<?php

/**
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2011 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Hybrid;

Factory::import('swift/swift_required', 'vendor');

/**
 * Hybrid 
 * 
 * A set of class that extends the functionality of FuelPHP without 
 * affecting the standard workflow when the application doesn't actually 
 * utilize Hybrid feature.
 * 
 * @package     Fuel
 * @subpackage  Hybrid
 * @category    Swiftmail
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

 class Swiftmail {
    
    /**
     * Creates a new instance of the email driver.
     *
     * @static
     * @access  public
     * @param   array   $config     An array to overwrite default config from config/email.php.
     * @return  self
     */
    public static function forge($config = array())
    {
        $initconfig = \Config::load('email', 'email', true);
        
        if (is_array($config) and is_array($initconfig))
        {
            $config = array_merge($initconfig, $config);
        }

        return new static($config);
    }

    /**
     * Shortcode to self::forge().
     *
     * @deprecated  1.3.0
     * @static
     * @access  public
     * @param   array   $config     An array to overwrite default config from config/email.php.
     * @return  self::forge()
     */
    public static function factory($config = array())
    {
        \Log::warning('This method is deprecated. Please use a forge() instead.', __METHOD__);
        
        return static::forge($config);
    }

    /**
     * Mailer object
     *
     * @access  protected
     * @var     Swift_Mailer
     */
    protected $mailer       = null;

    /**
     * Message object
     * 
     * @access  protected
     * @var     Swift_Message
     */
    protected $messager     = null;

    /**
     * Recipients list
     *
     * @access  protected
     * @var     array
     */
    protected $recipients   = array(
        'to'       => array(),
        'bcc'      => array(),
        'cc'       => array(),
        'from'     => array(),
        'reply_to' => array(),
    );

    /**
     * Contain all debugging message
     *
     * @access  protected
     * @var     object
     */
    protected $result       = null;

    public function __construct($config)
    {
        $this->config = $config;
        $transport    = "transport_" . $config['protocol'];
        
        $this->result = new Swiftmail_Result;

        if (method_exists($this, $transport))
        {
            $transport      = $this->{$transport}($config);
            $this->messager = new \Swift_Message();
            $this->mailer   = new \Swift_Mailer($transport);

            $this->messager->setCharset($config['charset']);

            switch($config['mailtype'])
            {
               case 'html' :
                    $this->messager->setContentType('plain/html');
                break; 

                case 'text' :
                default :
                    $this->messager->setContentType('plain/text');
                break;
            }
        }
        else
        {
            throw new \Fuel_Exception("\Hybrid\Swiftmail: Transport protocol " . $config['protocol'] . " does not exist.");
        }
    }

    /**
     * Sets the subject of the email.
     *
     * @param   string  $subject
     * @return  self
     */
    public function subject($subject)
    {
        $this->messager->setSubject($subject);

        return $this;
    }

    /**
     * Sets the message of the email, content type is determined by 'mailtype' config
     *
     * @access  public
     * @param   string  $content
     * @return  self
     */
    public function message($content)
    {
        $this->messager->setBody($content, $this->config['mailtype']);

        return $this;
    }

    /**
     * Sets the Plain Text content to place into the email.
     *
     * @access  public
     * @param   string  $content   The emails Plain Text
     * @return  self
     */
    public function text($content)
    {
        $this->messager->addPart($content, 'plain/text');

        return $this;
    }

    /**
     * Sets the HTML content to place into the email.
     *
     * @access  public
     * @param   string  $content   The emails HTML
     * @return  self
     */
    public function html($content)
    {
        $this->messager->addPart($content, 'plain/html');

        return $this;
    }

    /**
     * Adds a direct recipient
     *
     * @access  public
     * @param   string  $address    A single email
     * @param   string  $name       Recipient name
     * @return  self
     */
    public function to($address, $name = '') {
        $this->add_multiple_recipients('to', $address, $name);

        return $this;
    }

    /**
     * Adds a carbon copy recipient
     *
     * @access  public
     * @param   string  $address    A single email
     * @param   string  $name       Recipient name
     * @return  self
     */
    public function cc($address, $name = '') {
        $this->add_multiple_recipients('cc', $address, $name);

        return $this;
    }

    /**
     * Adds a blind carbon copy recipient
     *
     * @access  public
     * @param   string  $address    A single email
     * @param   string  $name       Recipient name
     * @return  self
     */
    public function bcc($address, $name = '') {
        $this->add_multiple_recipients('bcc', $address, $name);

        return $this;
    }

    /**
     * Adds a direct sender
     *
     * @access  public
     * @param   string  $address    A single email
     * @param   string  $name       Recipient name
     * @return  self
     */
    public function from($address, $name = '') {
        $this->add_multiple_recipients('from', $address, $name);

        return $this;
    }

    /**
     * Adds a direct reply-to
     *
     * @access  public
     * @param   string  $address    A single email
     * @param   string  $name       Recipient name
     * @return  self
     */
    public function reply_to($address, $name = '') {
        $this->add_multiple_recipients('reply_to', $address, $name);

        return $this;
    }

    /**
     * Add recipients or senders based on desired 'type'
     *
     * @access  protected
     * @param   string  $type       'to', 'cc', 'bcc', 'from' or 'reply_to'
     * @param   string  $address    A single email
     * @param   string  $name       Recipient name
     * @return  bool                Return true on success
     */
    protected function add_multiple_recipients($type, $address, $name = '')
    {
        if ( ! isset($this->recipients[$type]))
        {
            throw new \Fuel_Exception("Recipient type: {$type} does not exist");
        }

        if ( ! empty($name))
        {
            $this->recipients[$type][$address] = $name; 
        }

        $this->recipients[$type][] = $address;

        return true;
    }

    /**
     * Sends the email.
     *
     * @access  public
     * @param   bool    $debug      set to TRUE will return $this->result object instead of just the success status    
     * @return  bool|object  
     */
    public function send($debug = false)
    {
        $this->messager->setTo($this->recipients['to']);
        $this->messager->setFrom($this->recipients['from']);

        foreach (array('reply_to', 'cc', 'bcc') as $type)
        {
            if (count($this->recipients[$type]) > 0)
            {
                $method = 'set'.\Inflector::camelize($type);
                $this->messenger->{$method}($this->recipients[$type]);
            }
        }

        $result = $this->mailer->send($this->messager, $failure);

        $this->result->failure = $failure;

        if (intval($result) >= 1)
        {
            $this->result->success    = true;
            $this->result->total_sent = intval($result);
        }

        if (false === $debug)
        {
            return $this->result->success;
        }

        return $this->result();
    }

    /**
     * Get transport/mail debug object.
     *
     * @access  public
     * @return  object  containing success status, total email sent and failure during email sending
     */
    public function result()
    {
        return $this->result;
    }

    /**
     * Attaches a file in the local filesystem to the email.
     * 
     * @todo    not implemented yet
     * @access  public
     * @param   string  $filename       The file to be used.
     * @param   string  $disposition    Defaults to attachment, can also be inline?
     * @return  self
     */
    public function attach($filename, $disposition)
    {
        $attachment = \Swift_Attachment::fromPath($filename)->setDisposition($disposition);
        $this->messager->attach($attachment);
        
        return $this;
    }

    /**
     * Dynamically attaches a file to the email.
     *
     * @todo    not implemented yet
     * @access  public
     * @param   string  $contents       The contents of the attachment
     * @param   string  $filename       The filename to use in the email
     * @param   string  $disposition    Defaults to attachment, can also be inline?
     * @return  self
     */
    public static function dynamic_attach($contents, $filename, $disposition = 'attachment')
    {
        throw new \Fuel_Exception("\Hybrid\Swiftmail: Dynamic file attachment has not been implemented yet.");

        return $this;
    }

    /**
     * Initiate a new transport to use Sendmail protocol
     *
     * @access  protected
     * @param   array   $config
     * @return  Swift_SendmailTransport
     */
    protected function transport_sendmail($config)
    {
        return new \Swift_SendmailTransport($config['sendmail_path'] . ' -oi -t');
    }

    /**
     * Initiate a new transport to use mail protocol
     *
     * @access  protected
     * @param   array   $config
     * @return  Swift_MailTransport
     */
    protected function transport_mail($config)
    {
        return new \Swift_MailTransport();
    }

    /**
     * Initiate a new transport to use SMTP protocol
     *
     * @access  protected
     * @param   array   $config
     * @return  Swift_SmtpTransport
     */
    protected function transport_smtp($config)
    {
        if (is_array($config) and !empty($config))
        {
            extract($config);  
        }

        $ssl = null;

        if (preg_match('/^(ssl:\/\/)(.*)$/', $smtp_host, $matches))
        {
            $ssl        = 'ssl';
            $smtp_host  = $matches[2]; 
        }

        $transport = new \Swift_SmtpTransport($smtp_host, $smtp_port, $ssl);

        if ( ! empty($smtp_user))
        {
            $transport->setUsername($smtp_user);
        }

        if ( ! empty($smtp_pass))
        {
            $transport->setPassword($smtp_pass);
        }

        return $transport;
    }
}