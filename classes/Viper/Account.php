<?php defined('SYSPATH') OR die('Direct access is never permitted.');

/**
 * Account User Library Class.
 *
 * @package      Viper/Account
 * @category     Base
 * @name         User
 * @author       Michael Noël <mike@viperframework.com>
 * @author       Viper Team
 * @copyright    (c) 2015 Viper Framework
 * @license      https://viperframework.com/
 * @version      1.2.0
 */

class Viper_Account {

	// ID constants.
	const GUEST_ID      = 1; // Guest user ID
	const ADMIN_ID      = 2; // Main admin user ID
	const GUEST_ROLE_ID = 1; // Anonymous role ID
	const LOGIN_ROLE_ID = 2; // Login role ID
	const USER_ROLE_ID  = 3; // User role ID
	const ADMIN_ROLE_ID = 4; // Admin role ID

	/**
	 * @access  protected
     * @var     array  $roles  All Roles
	 */
	protected static $roles = array();
    
	/**
	 * Return the active user. If there's no active user, return the guest user.
	 * 
     * @todo  (maybe) cache this object so we're not always doing session lookups.
     * 
     * @static
     * @access  public
	 * @return  Model_User
     * @uses  Auth::instance
	 */
	public static function active_user()
	{
		return ! (Auth::instance()->get_user() ? self::guest() : Auth::instance()->get_user());
	} // End Method
    
	/**
	 * Check if current user is guest.
	 * 
     * @static
     * @access  public
	 * @return  boolean  TRUE if current user is guest
     * @uses  Auth::instance
	 */
	public static function is_guest()
	{
		return ! Auth::instance()->get_user() ? TRUE : FALSE;
	} // End Method
    
	/**
     * Check if current user is admin.
	 * 
     * @static
     * @access  public
	 * @return  boolean  TRUE if current user is admin
     * @uses  Account::is_guest
     * @uses  Auth::instance
     * @uses  Account::$roles
	 */
	public static function is_admin()
	{
		if (Account::is_guest())
		{
			return FALSE;
		} // End If
        
		$user = Auth::instance()->get_user();
        
		// To reduce the number of SQL queries, we cache the user's roles in a static variable.
		if ( ! isset(Account::$roles[$user->id]))
		{
			// @todo  Fetch and save in session to avoid recursive lookups.
			Account::$roles[$user->id] = self::roles($user);
		} // End If
        
		if (in_array('admin', Account::$roles[$user->id]) OR  array_key_exists(4, Account::$roles[$user->id]))
		{
			return TRUE;
		} // End If
        
		return FALSE;
	} // End Method
    
	/**
	 * Generates a default anonymous $user object.
	 * 
     * @static
     * @access  public
	 * @return  object  The user object
	 */
	public static function guest()
	{
		return self::lookup(1);
	} // End Method
    
	/**
	 * Counting all users.
	 * 
     * @static
     * @access  public
	 * @return  integer  Total number of registered users
	 */
	public static function count_all()
	{
		// Initialize the cache.
		$cache = Cache::instance('users');

		// To first check cache
		if ( ! $all = $cache->get('count_all'))
		{
			// Counting from database.
			$all = ORM::factory('User')->count_all();
            
			// Save to cache on an hour.
			$cache->set('count_all', $all, Date::HOUR);
		} // End If
        
		// Return the amount of users.
		return $all;
	} // End Method
    
	/**
	 * Checks if user belongs to group(s).
	 * 
     * @static
     * @access  public
	 * @param   mixed  $groups  Group(s)
	 * @return  boolean  TRUE if user belongs to group(s)
     * @uses  Auth::instance
     * @uses  Account::$roles
	 */
	public static function belongsto($groups)
	{
		if ($groups == 'all' OR ($groups == NULL))
		{
			return TRUE;
		} // End If
        
		if ( ! is_array($groups))
		{
			$groups = @explode(',', $groups);
		} // End If
        
		if (Auth::instance()->logged_in())
		{
			$user = Auth::instance()->get_user();
            
			// To reduce the number of SQL queries, we cache the user's roles in a static variable.
			if ( ! isset(Account::$roles[$user->id]))
			{
				// @todo  Fetch and save in session to avoid recursive lookups.
				Account::$roles[$user->id] = $user->roles();
			} // End If
            
			// `array_diff` is not safe.
			if (array_intersect(array_values($groups), array_keys(Account::$roles[$user->id])))
			{
				return TRUE;
			} // End If
            
			return FALSE;
		} // End If
        
		if (in_array('guest', $groups) OR array_key_exists(1, $groups))
		{
			return TRUE;
		} // End If
        
		return FALSE;
	} // End Method
    
	/**
	 * Look up a user by id.
     * 
     * @static
     * @access  public
	 * @param   integer  $id  The user id
	 * @return  Model_User  The user object, or boolean if the id was invalid.
	 */
	public static function lookup($id)
	{
		return self::_lookup_by_field('id', $id);
	} // End Method
    
	/**
	 * Look up a user by name.
     * 
     * @static
     * @access  public
	 * @param   integer  $name  The user name
	 * @return  Model_User  The user object, or boolean if the name was invalid.
	 */
	public static function lookup_by_name($name)
	{
		return self::_lookup_by_field('name', $name);
	} // End Method
    
	/**
	 * Look up a user by email.
     * 
     * @static
     * @access  public
	 * @param   integer  $email  The user email
	 * @return  Model_User  The user object, or boolean if the email was invalid.
	 */
	public static function lookup_by_email($email)
	{
		return self::_lookup_by_field('email', $email);
	} // End Method
    
	/**
	 * Look up a user by field value.
	 * 
     * @todo  Needs work!
     * 
     * @static
     * @access  private
	 * @param   string  $field  Search field
	 * @param   string  $value  Search value
	 * @return  Model_User  the user object, or boolean if the name was invalid.
     * @uses  ORM::factory
	 */
	private static function _lookup_by_field($field, $value)
	{
		try
		{
			$user = ORM::factory('User_Profile')
                ->where($field, '=', $value)
                ->find();
            
			if ($user->loaded())
			{
				return $user;
			} // End If
		}
		catch (Exception $e)
		{
			return FALSE;
		} // End Try
        
		return FALSE;
	} // End Method
    
	/**
	 * Get role by id.
	 * 
     * getRoleByID => get_role_by_id
     * 
     * @static
     * @access  public
	 * @param   integer  $id  Role id
	 * @return  Model_Role|boolean  The Role object, or FALSE if ID is invalid or not found
     * @since   1.2.0
     * @uses  ORM::factory
	 */
	public static function get_role_by_id($id)
	{
		try
		{
			$role = ORM::factory('Role', $id);
            
			if ($role->loaded())
			{
				return $role;
			} // End If
		}
		catch (Exception $e)
		{
			return FALSE;
		} // End Try
        
		return FALSE;
	} // End Method
    
	/**
	 * Is the password provided correct? Supporting old/drupal style 
     * md5 and new hash.
	 * 
     * @static
     * @access  public
	 * @param   Model_User  $user      User data object
	 * @param   string      $password  A plaintext password
	 * @return  boolean  TRUE if the password is correct
	 * @uses  Auth::Hash
	 */
	public static function check_password($user, $password)
	{
		if ( ! isset($user) OR ! isset($password))
        {
            return FALSE;
        } // End If
        
		$valid = $user->password;
        
		// Support for old (Drupal md5 password sum):
		$guess = (strlen($valid) == 32) ? md5($password) : Auth::instance()->Hash($password);
        
		if ( ! strcmp($guess, $valid))
		{
			return TRUE;
		} // End If
        
		return FALSE;
	} // End Method
    
	/**
	 * Saves visitor information as a cookie so it can be reused.
	 * 
     * @static
     * @access  public
	 * @param   mixed  $values  An array of key/value pairs to be saved into a cookie.
     * @return  void
     * @uses  Cookie::set
	 */
	public static function cookie_save(array $values)
	{
        // Set cookie to expire in 365 days from now.
        $expires = time() + 31536000;
        
		foreach ($values as $field => $value)
		{
			Cookie::set('Viper.visitor.'.$field, rawurlencode($value), $expires);
		} // End Foreach
	} // End Method
    
	/**
	 * Delete a visitor information cookie.
	 * 
     * @static
     * @access  public
	 * @param   string  $cookie_name  A cookie name such as 'homepage'.
     * @return  void
     * @uses  Cookie::set
	 */
	public static function cookie_delete($cookie_name)
	{
        // Set cookie to expire in the past.
        $expires = time() - 3600;
        
		Cookie::set('Viper.visitor.'.$cookie_name, '', $expires);
	} // End Method
    
	/**
	 * Check whether that id exists in our identities table (provider_id field).
	 * 
     * @static
     * @access  public
	 * @param   string  $provider_id    The provider user id
	 * @param   string  $provider_name  The provider name (facebook, google, live etc)
	 * @return  mixed user object or FALSE
     * @uses  DB::select
     * @uses  ORM::factory
	 */
	public static function check_identity($provider_id, $provider_name)
	{
		$uid = (int) DB::select('user_id')
			->from('user_identities')
			->where('provider', '=',  $provider_name)
			->where('provider_id', '=', $provider_id)
			->execute()
			->get('user_id');
        
		// If the user id is found return the user object
		if ($uid AND $uid > 1) 
        {
            return ORM::factory('User', $uid);
        } // End If
        
		return FALSE;
	} // End Method
    
	/**
	 * Themed list of providers to print.
	 * 
	 * @todo  Move to HTML class
     * 
     * @static
     * @access  public
	 * @return  string  HTML to display
     * @uses  Auth::instance
     * @uses  View::factory
	 */
	public static function providers()
	{
		if ( ! Auth::instance()->logged_in())
		{
			$providers = array_filter(Auth::providers());
            
			return View::factory('oauth/providers')->set('providers', $providers);
		} // End If
	} // End Method
    
	/**
	 * Themed list of roles to print.
	 * 
     * @static
     * @access  public
	 * @param   ORM  $user  The user object
	 * @return  string  HTML to display
     * @uses  Text::plain
	 */
	public static function roles(ORM $user)
	{
		$roles = '<ul class="user-roles">';
        
		foreach ($user->roles->find_all()->as_array() as $role)
		{
			$roles .= '<li>'.Text::plain($role).'</li>';
		} // End Foreach
        
		$roles .= '</ul>';
        
		return $roles;
	} // End Method
    
} // End Viper_User Class
