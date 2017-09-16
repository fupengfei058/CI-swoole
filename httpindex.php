<?php

class Httpindex
{
    public static $httpindex;

	private function __construct()
	{
		define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'development');

		switch (ENVIRONMENT)
		{
			case 'development':
				error_reporting(-1);
				ini_set('display_errors', 1);
			break;

			case 'testing':
			case 'production':
				ini_set('display_errors', 0);
				if (version_compare(PHP_VERSION, '5.3', '>='))
				{
					error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
				}
				else
				{
					error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
				}
			break;

			default:
				header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
				echo 'The application environment is not set correctly.';
				exit(1); // EXIT_ERROR
		}

		$system_path = 'system';

		$application_folder = 'application';

		// Set the current directory correctly for CLI requests
		if (defined('STDIN'))
		{
			chdir(dirname(__FILE__));
		}

		if (($_temp = realpath($system_path)) !== FALSE)
		{
			$system_path = $_temp.DIRECTORY_SEPARATOR;
		}
		else
		{
			// Ensure there's a trailing slash
			$system_path = strtr(
				rtrim($system_path, '/\\'),
				'/\\',
				DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
			).DIRECTORY_SEPARATOR;
		}

		// Is the system path correct?
		if ( ! is_dir($system_path))
		{
			header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
			echo 'Your system folder path does not appear to be set correctly. Please open the following file and correct this: '.pathinfo(__FILE__, PATHINFO_BASENAME);
			exit(3); // EXIT_CONFIG
		}

		// The name of THIS file
		define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

		// Path to the system directory
		define('BASEPATH', $system_path);

		// Path to the front controller (this file) directory
		define('FCPATH', dirname(__FILE__).DIRECTORY_SEPARATOR);

		// Name of the "system" directory
		define('SYSDIR', basename(BASEPATH));

		// The path to the "application" directory
		if (is_dir($application_folder))
		{
			if (($_temp = realpath($application_folder)) !== FALSE)
			{
				$application_folder = $_temp;
			}
			else
			{
				$application_folder = strtr(
					rtrim($application_folder, '/\\'),
					'/\\',
					DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
				);
			}
		}
		elseif (is_dir(BASEPATH.$application_folder.DIRECTORY_SEPARATOR))
		{
			$application_folder = BASEPATH.strtr(
				trim($application_folder, '/\\'),
				'/\\',
				DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
			);
		}
		else
		{
			header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
			echo 'Your application folder path does not appear to be set correctly. Please open the following file and correct this: '.SELF;
			exit(3); // EXIT_CONFIG
		}

		define('APPPATH', $application_folder.DIRECTORY_SEPARATOR);
	}

	public static function getInstance()
	{
		if(!self::$httpindex) {
			self::$httpindex = new self();
		}
		include BASEPATH.'core/CodeIgniter.php';
	}
}