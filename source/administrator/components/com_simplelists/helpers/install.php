<?php
/*
 * Joomla! Yireo Library
 *
 * @author Yireo (info@yireo.com)
 * @package YireoLib
 * @copyright Copyright 2016
 * @license GNU Public License
 * @link https://www.yireo.com
 * @version 0.6.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Yireo Install Helper
 */
class YireoHelperInstall
{
	/**
	 * @param $url
	 * @param $label
	 *
	 * @return bool
	 * @throws Exception
	 */
	static public function installExtension($url, $label)
	{
		// Include Joomla! libraries
		jimport('joomla.installer.installer');
		jimport('joomla.installer.helper');

		// System variables
		$app = JFactory::getApplication();

		// Download the package-file
		$package_file = self::downloadPackage($url);

		// Simple check for the result
		if ($package_file == false)
		{
			throw new Exception(JText::sprintf('LIB_YIREO_HELPER_INSTALL_DOWNLOAD_FILE_EMPTY', $url));
		}

		// Check if the downloaded file exists
		$tmp_path = $app->getCfg('tmp_path');
		$package_path = $tmp_path . '/' . $package_file;

		if (!is_file($package_path))
		{
			throw new Exception(JText::sprintf('LIB_YIREO_HELPER_INSTALL_DOWNLOAD_FILE_NOT_EXIST', $package_path));
		}

		// Check if the file is readable
		if (!is_readable($package_path))
		{
			throw new Exception(JText::sprintf('LIB_YIREO_HELPER_INSTALL_DOWNLOAD_FILE_NOT_READABLE', $package_path));
		}

		// Now we assume this is an archive, so let's unpack it
		$package = JInstallerHelper::unpack($package_path);

		if ($package == false)
		{
			throw new Exception(JText::sprintf('LIB_YIREO_HELPER_INSTALL_DOWNLOAD_NO_ARCHIVE', $package['name']));
		}

		// Call the actual installer to install the package
		$installer = JInstaller::getInstance();

		if ($installer->install($package['dir']) == false)
		{
			throw new Exception(JText::sprintf('LIB_YIREO_HELPER_INSTALL_EXTENSION_FAIL', $package['name']));
		}

		// Get the name of downloaded package
		if (!is_file($package['packagefile']))
		{
			$config = JFactory::getConfig();
			$package['packagefile'] = $config->get('tmp_path') . '/' . $package['packagefile'];
		}

		// Clean up the installation
		@JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
		JError::raiseNotice('SOME_ERROR_CODE', JText::sprintf('LIB_YIREO_HELPER_INSTALL_EXTENSION_SUCCESS', $label));

		// Clean the Joomla! plugins cache
		$options = array('defaultgroup' => 'com_plugins', 'cachebase' => JPATH_ADMINISTRATOR . '/cache');
		$cache = JCache::getInstance('callback', $options);
		$cache->clean();

		return true;
	}

	/*
	 * Download a specific package using the MageBridge Proxy (CURL-based)
	 *
	 * @param string $url
	 * @param string $file
	 *
	 * @return string
	 */
	static public function downloadPackage($url, $file = null)
	{
		// System variables
		$app = JFactory::getApplication();
		$config = JFactory::getConfig();

		// Use fopen() instead
		if (ini_get('allow_url_fopen') == 1)
		{
			return JInstallerHelper::downloadPackage($url, $file);
		}

		// Set the target path if not given
		if (empty($file))
		{
			$file = $config->get('tmp_path') . '/' . JInstallerHelper::getFilenameFromURL($url);
		}
		else
		{
			$file = $config->get('tmp_path') . '/' . basename($file);
		}

		// Open the remote server socket for reading
		$ch = curl_init($url);

		curl_setopt_array($ch, array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER => false,
			CURLOPT_MAXREDIRS => 2,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_FRESH_CONNECT => false,
			CURLOPT_FORBID_REUSE => false,
			CURLOPT_BUFFERSIZE => 8192
		));

		$data = curl_exec($ch);
		curl_close($ch);

		if (empty($data))
		{
			JError::raiseWarning(42, JText::_('LIB_YIREO_HELPER_INSTALL_REMOTE_DOWNLOAD_FAILED') . ', ' . curl_error($ch));

			return false;
		}

		// Write received data to file
		JFile::write($file, $data);

		// Return the name of the downloaded package
		return basename($file);
	}

	static public function hasLibraryInstalled($library)
	{
		if (is_dir(JPATH_SITE . '/libraries/' . $library))
		{
			$query = 'SELECT `name` FROM `#__extensions` WHERE `type`="library" AND `element`="' . $library . '"';
			$db = JFactory::getDbo();
			$db->setQuery($query);

			return (bool) $db->loadObject();
		}

		return false;
	}

	static public function autoInstallLibrary($library, $url, $label)
	{
		// If the library is already installed, exit
		if (self::hasLibraryInstalled($library))
		{
			return true;
		}

		// Otherwise first, try to install the library
		if (self::installExtension($url, $label) == false)
		{
			JError::raiseWarning('SOME_ERROR_CODE', JText::sprintf('LIB_YIREO_HELPER_INSTALL_MISSING', $label));
		}
	}
}
