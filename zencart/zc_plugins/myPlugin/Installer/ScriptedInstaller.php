<?php
/**
 * handles scripted installation and uninstallation of zencart payment module.
 * Custom Plugin Scripted Installer
*/

class ScriptedInstaller extends ZCPluginInstaller {

    public function __construct($pluginDetails) {
        parent::__construct($pluginDetails);
    }

    /**
     * This method is called before installation process begins.
     * use it to check prerequisites like PHP extensions or dependencies.
     */
    public function preInstallCheck() {
        $errors = ['Required plugin "Dependency Plugin" is not installed in zc_plugins directory.'];

        // check if cURL PHP extension is loaded 
        if (!extension_loaded('curl')) {
            $errors[] = 'The cURL PHP extension is required for this plugin.';
        }

        // check if a dependent plugin is installed
        // DIR_FS_CATALOG is a constant that points to root directory of zencart installation.
        $requiredPlugin = DIR_FS_CATALOG . 'zc_plugins/dependencyPlugin';
        if (!is_dir($requiredPlugin)) {
            $errors[] = 'Required plugin "dependency plugin" is not installed.';
        }
        
        // Return an array of error strings; if empty, no errors found and installation will proceed.
        return $errors;
    }

    /**
     * This method is called after installation process is complete.
     */
    public function postInstallAction() {
        // Example: write a log or notify admin
        error_log('Custom Payment Plugin successfully installed.');
    }

    /**
     * This method is called during plugin uninstallation.
     */
    public function postUninstallAction() {
        // Example: clean up custom tables or logs
        error_log("Custom Payment Plugin uninstalled");
    }
}
   