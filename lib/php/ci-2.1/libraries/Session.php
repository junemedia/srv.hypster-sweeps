<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Native Session Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Sessions
 * @author		Adam Chalemian, Resolute Digital
 * @link		http://resolute.com
 */

/**
 * TODO:
 *
 * 1) Add ability to specify an alternate cookie 'lis' along with the
 *    session cookie.  -- eh, I really don't like the lis cookie anymore :(
 *    it can be derived from the sid.
 */

class CI_Session {

	protected $userdata;
	protected $_session_started	= false;
	protected $config			= array();

	// ------------------------------------------------------------------------

	/**
	 * Setup, Run session routines
	 *
	 * @access public
	 */
	public function __construct($params = array())
	{
		// prevent any output until cookies are set
		ob_start(array($this, 'shutdown'));

		log_message('debug', "Session Class Initializing");

		// gather the default cookie parameters
		$_cookie_params	= session_get_cookie_params();

		// default config: pulled from php.ini or any native updates
		// before this is executed
		$this->config = array(
							'session_name'     => session_name(),
							'session_lifetime' => @$_cookie_params['lifetime'],
							'session_path'     => @$_cookie_params['path'],
							'session_domain'   => @$_cookie_params['domain'],
							'session_secure'   => @$_cookie_params['secure'],
							'session_httponly' => @$_cookie_params['httponly'],
							'gc_maxlifetime'   => ini_get('session.gc_maxlifetime'),
							'flashdata_prefix' => 'flash_'
						 );


		// we will not need this outside of this __construct(),
		// so let's keep it local
		$CI =& get_instance();
		$CI->load->config(
					'session',
					true, // creates "session" array
					true // allows for an empty config/session.php file
		);

		// merge any overrides from config file and passed parameters
		$overrides = array_merge(
						(array) $CI->config->item('session'),
						(array) $params // takes priority over config file
					 );


		// evaluate/apply configuration overrides
		foreach (array_keys($this->config) as $key)
		{
			if (isset($overrides[$key]) && $params[$key] != $this->config[$key])
			{
				$this->config[$key] = $overrides[$key];
				// update PHP native session_name
				switch ($key) {
					case 'session_name':
						session_name($overrides[$key]);
						break;
					case 'gc_maxlifetime':
						ini_set('session.gc_maxlifetime', $overrides[$key]);
						break;
				}
			}
		}

		// update the cookie parameters with our new config
		session_set_cookie_params(
			$this->config['session_lifetime'],
			$this->config['session_path'],
			$this->config['session_domain'],
			$this->config['session_secure'],
			$this->config['session_httponly']
		);

		// Only create sessions if
		// 1) The $_COOKIE[<cookie_name>] exists in the request; meaning this
		// 	  is a resumed session.
		// 2) Someone calls the set_userdata() method

		if (@$_COOKIE[$this->config['session_name']])
		{
			$this->sess_create();
		}

	}

	// ------------------------------------------------------------------------

	/**
	 * Fetch a specific item from session
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public function userdata($item)
	{
		return @$this->userdata[$item];
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch all session data
	 *
	 * @access	public
	 * @return	array
	 */
	public function all_userdata()
	{
		return $this->userdata;
	}

	// --------------------------------------------------------------------

	/**
	 * Add or change data in the "userdata" array
	 *
	 * This is one of the only other (than cookie_name exists
	 * in the request) times we can session_start()
	 *
	 * CAUTION: $key cannot evaluate to NULL, FALSE, '' (empty), or NUMERIC
	 *
	 * @access	public
	 * @param	mixed
	 * @param	mixed
	 * @return	void
	 */
	public function set_userdata($key, $val = null, $is_flash = false)
	{
		// recursively call this method for arrays
		if (is_array($key))
		{
			foreach ($key as $k => $v)
			{
				$this->set_userdata($k, $v, $is_flash);
			}
			return;
		}

		// define the prefix if this is flashdata read
		$prefix = $is_flash ? $this->config['flashdata_prefix'] : '';

		// make sure session has been started
		$this->sess_create();

		// @$_SESSION[<undefined>] will return NULL in both cases
		// where it is explicitely defined as NULL or simply does
		// does not exist.  We can safely delete any $key’s where
		// the $val is explicitely null.  Infact, it serves as a
		// nice shortcut to unset_userdata().  Also, it will save
		// on bloat.
		if (is_null($val))
		{
			// special "auto-unset" case where $val is NULL
			$this->unset_userdata($prefix.$key);
		}
		else
		{
			// simple case of setting a simple key => value
			$this->userdata[$prefix.$key] = $val;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Delete a session variable from the "userdata" array
	 *
	 * This is one of the only other (than cookie_name exists
	 * in the request) times we can session_start()
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	public function unset_userdata($key = null)
	{
		// recursively call this method for arrays
		if (is_array($key))
		{
			foreach ($key as $k => $v)
			{
				$this->unset_userdata($k);
			}
			return;
		}

		if (!is_string($key))
		{
			return;
		}

		$this->sess_create();
		unset($GLOBALS['_SESSION'][$key]);
	}

	// ------------------------------------------------------------------------

	/**
	 * Flash data will be deleted once it is read
	 *
	 * @access	public
	 * @param	mixed
	 * @param	string
	 * @return	void
	 */
	public function set_flashdata($key, $val)
	{
		$this->set_userdata($key, $val, true);
	}

	// ------------------------------------------------------------------------

	/**
	 * Fetch a specific flashdata item from the session array
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public function flashdata($key)
	{
		if (isset($this->userdata[$this->config['flashdata_prefix'].$key]))
		{
			// save it so we can return it
			$val = $this->userdata[$this->config['flashdata_prefix'].$key];
			// remove it from session storage
			$this->unset_userdata($this->config['flashdata_prefix'].$key);
			return $val;
		}
		return false;
	}

	// ------------------------------------------------------------------------

	/**
	 * Fetch the current session data if it exists
	 *
	 * @access	public
	 * @return	bool
	 */
	public function sess_read()
	{
		$this->sess_create(); // no difference
	}

	// --------------------------------------------------------------------

	/**
	 * Create a new session
	 *
	 * @access	public
	 * @return	void
	 */
	public function sess_create()
	{
		if (!$this->_session_started)
		{
			$this->_session_started = true;
			log_message('debug', 'Session has been created: '.$this->config['session_name']);
			// since it is possible that someone else ran session_start(),
			// do not re-session_start().
			// Not the end of the world if you do, but then you'd need
			// to suppress the warning of running session_start() twice.
			if (session_id() == '')
			{
				session_start();
			}
			$this->userdata =& $_SESSION;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Destroy the current session
	 *
	 * @access	public
	 * @return	void
	 */
	public function sess_destroy()
	{
		if (!session_id())
		{
			return;
		}
		// Delete the server-side data
		session_destroy();
		// Delete this request’s $_SESSION variables
		foreach ($_SESSION as $key => $val)
		{
			unset($GLOBALS['_SESSION'][$key]);
		}
		// Unset the cookie
		setcookie(
			$this->config['session_name'],
			'',
			-1,
			$this->config['session_path'],
			$this->config['session_domain'],
			$this->config['session_secure'],
			$this->config['session_httponly']
		);
		// Remove session start lock
		$this->_session_started = false;
	}

	// --------------------------------------------------------------------

	/**
	 * Callback to ob_start() and used to destroy empty sessions
	 *
	 * @access	public
	 * @param   string $buf
	 * @return	mixed  (bool) false will output the buffer
	 */
	public function shutdown($buf)
	{
		// clean up empty sessions
		if (!@$_SESSION && $this->_session_started) {
			$this->sess_destroy();
		}

		// only needed so that you can open another session
		session_write_close();

		return false;
	}

	// --------------------------------------------------------------------

	/**
	 * Conform to CI session library
	 *
	 * @access	public
	 * @return	void
	 */
	public function sess_write() {}

	// --------------------------------------------------------------------

	/**
	 * Conform to CI session library
	 *
	 * @access	public
	 * @return	void
	 */
	public function sess_update() {}

	// --------------------------------------------------------------------

	/**
	 * Conform to CI session library
	 *
	 * @access public
	 */
	 public function __destruct() {}

}