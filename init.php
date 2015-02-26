<?php defined('SYSPATH') OR die('Direct access is never permitted.');

if ( ! Route::cache())
{
    // Account User Backend routes
    
	Route::set('admin/permission', 'admin/permissions(/<action>)(/<id>)', array(
		    'id' => '\d+',
		    'action' => 'list|role|user'
	    ))
	    ->defaults(array(
		    'directory'  => 'admin',
		    'controller' => 'Permission',
		    'action'     => 'list'
	    ));
    
	Route::set('admin/role', 'admin/roles(/<action>(/<id>))(/p<page>)', array(
		    'id'         => '\d+',
		    'page'       => '\d+',
		    'action'     => 'list|add|edit|delete'
	    ))
	    ->defaults(array(
		    'directory'  => 'admin',
		    'controller' => 'Role',
		    'action'     => 'list'
	    ));
    
	Route::set('admin/user', 'admin/users(/<action>(/<id>))(/p<page>)', array(
		    'id'         => '\d+',
		    'page'       => '\d+',
		    'action'     => 'list|add|edit|delete'
	    ))
	    ->defaults(array(
		    'directory'  => 'admin',
		    'controller' => 'User',
		    'action'     => 'list',
	    ));
    
	// Account User Frontend routes
    
    //Route::set('account', '<directory>(/<controller>(/<action>(/<id>(/<method>))))', array(
    //        'directory'  => '(account)',
    //        'controller' => '([a-zA-Z\-_]+)',
    //        'action'     => '([a-z\-]+)',
    //        'id'         => '([0-9\-]+)',
    //        'method'     => '([a-z\-]+)',
    //    ))
    //    ->filter('Router::filter')
    //    ->defaults(array(
    //        'controller' => 'Account_Login',
    //        'action'     => 'index',
    //        'id'         => NULL,
    //        'method'     => NULL,
    //    ));
    
	Route::set('account', 'account(/<action>)(/<id>)(/<token>)', array(
		    'action'     => 'edit|login|logout|view|register|confirm|password|profile|photo',
		    'id'         => '\d+'
	    ))
	    ->defaults(array(
		    'controller' => 'User',
		    'action'     => 'view',
		    'token'      => NULL,
	    ));

	Route::set('account/oauth', 'oauth/<controller>(/<action>)')
	    ->defaults(array(
		    'directory'  => 'oauth',
		    'action'     => 'index',
	    ));

	Route::set('account/reset', 'account/reset(/<action>)(/<id>)(/<token>)(/<time>)', array(
		    'action'     => 'password|confirm_password',
		    'id'         => '\d+',
		    'time'       => '\d+'
	    ))
	    ->defaults(array(
		    'controller' => 'User',
		    'action'     => 'confirm_password',
		    'token'      => NULL,
		    'time'       => NULL,
	    ));

	Route::set('account/buddy', 'buddy(/<action>)(/<id>)(/p<page>)', array(
		    'action'     => 'index|add|accept|reject|delete|sent|pending',
		    'id'         => '\d+',
		    'page'       => '\d+',
	    ))
	    ->defaults(array(
		    'controller' => 'Buddy',
		    'action'     => 'index',
	    ));

	Route::set('account/message', 'message(/<action>)(/<id>)', array(
		    'id'         => '\d+',
		    'action'     => 'index|inbox|outbox|drafts|list|view|edit|compose|delete|bulk'
	    ))
	    ->defaults(array(
		    'controller' => 'Message',
		    'action'     => 'index'
	    ));
    
    // Account logged in or out pinger.
    
    Route::set('ping', 'v1(/<controller>)', array(
            'controller' => '([a-z\-]+)',
        ))
        ->defaults(array(
            'directory'  => 'Account',
            'controller' => 'Ping',
            'action'     => 'index',
        ));
} // End If

/**
 * Define Module specific Permissions.
 *
 * Definition of user privileges by default if the ACL is present in the system.
 * Note: Parameter `restrict access` indicates that these privileges have serious
 * implications for safety.
 *
 * ACL Used to define the privileges.
 * 
 * @uses  ACL::cache
 * @uses  ACL::set
 */

if ( ! ACL::cache())
{
	ACL::set('user', array(
		'administer permissions' => array(
			'title'           => __('Administer permissions'),
			'restrict access' => TRUE,
			'description'     => __('Managing user authority'),
		),
		'administer users' => array(
			'title'           => __('Administer users'),
			'restrict access' => TRUE,
			'description'     => __('Users management'),
		),
		'access profiles' => array(
			'title'           => __('Access profiles'),
			'restrict access' => FALSE,
			'description'     => __('Access to all profiles'),
		),
		'edit profile' => array(
			'title'           => __('Editing profile'),
			'restrict access' => FALSE,
			'description'     => __('The ability to change profile'),
		),
		'change own username' => array(
			'title'           => __('Change own username'),
			'restrict access' => TRUE,
			'description'     => __('The ability to change own username'),
		)
	));

	// Cache the module specific permissions in production.
	ACL::cache(FALSE, Viper::$environment === Viper::PRODUCTION);
    
} // End If
