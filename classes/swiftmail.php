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

class Swiftmail 
{
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
		$initconfig = \Config::load('swiftmail', 'switftmail', true);
		
		if (is_array($config) and is_array($initconfig))
		{
			$config = array_merge($initconfig, $config);
		}

		return new static($config);
	}

	/**
	 * Creates a new instance of the email driver.
	 *
	 * @static
	 * @access  public
	 * @param   array   $config     An array to overwrite default config from config/email.php.
	 * @return  self
	 */
	public static function make($config = array())
	{
		return static::forge($config);
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
		$transport    = "transport_".$config['protocol'];
		
		$this->result = new Swiftmail_Result;

		if (method_exists($this, $transport))
		{
			// set transport, messenger and mailer using Swiftmail
			$transport      = $this->{$transport}($config);
			$this->messager = new \Swift_Message();
			$this->mailer   = new \Swift_Mailer($transport);

			$this->messager->setCharset($config['charset']);

			// set current mailtype, either it plain text or html
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
			throw new \FuelException(__METHOD__.": Transport protocol ".$config['protocol']." does not exist.");
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
	 * Alias to to(), cc(), bcc(), reply_to(), from()
	 *
	 * @access  public
	 * @param   string  $name  Should be one of the available recipients
	 * @param   array   $args   
	 */
	public function __call($name, $args)
	{
		// check if called method is a valid recipients type
		if (array_key_exists($name, $this->recipients))
		{
			$email_name    = null;
			$email_address = null;

			switch (true)
			{
				case count($args) > 1 :
					$email_name    = $args[1];
				case count($args) > 0 :
					$email_address = $args[0];
				break;
			}

			// add to recipient list
			$this->add_multiple_recipients($name, $email_address, $email_name);

			return $this;
		}
		else
		{
			throw new \FuelException(__CLASS__."::{$name}: method does not exist.");
		}
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
			throw new \FuelException(__METHOD__.": Recipient type {$type} does not exist");
		}

		// add new address to the list
		if ( ! empty($name))
		{
			$this->recipients[$type][$address] = $name; 
		}
		else
		{
			$this->recipients[$type][] = $address;
		}

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
		// if the from recipient list is empty, load from address from configuration
		if (empty($this->recipients['from']))
		{
			$this->add_multiple_recipients('from', $this->config['from']['address'], $this->config['from']['name']);
		}
		
		// loop every type of recipient, if for instance to or from is missing, let Swift_Message::send() return the failure.
		foreach (array('to', 'from', 'reply_to', 'cc', 'bcc') as $type)
		{
			if (count($this->recipients[$type]) > 0)
			{
				$method = 'set'.\Inflector::camelize($type);
				$this->messager->{$method}($this->recipients[$type]);
			}
		}

		// try to send the email, and return the failure message if any.
		$result = $this->mailer->send($this->messager, $failure);

		// set Swiftmail_Result data
		$this->result->failure = $failure;

		if (intval($result) >= 1)
		{
			$this->result->success    = true;
			$this->result->total_sent = intval($result);
		}

		// based on the $debug, return success status or Swiftmail_Result
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
		throw new \FuelException(__METHOD__.": Dynamic file attachment has not been implemented yet.");

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
		return new \Swift_SendmailTransport($config['sendmail_path'].' -oi -t');
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