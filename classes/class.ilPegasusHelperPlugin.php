<?php

use ILIAS\DI\Container;

require_once __DIR__ . '/../bootstrap.php';

/**
 * Class ilPegasusHelperPlugin
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @author Martin Studer <ms@studer-raimann.ch>
 */
final class ilPegasusHelperPlugin extends ilUserInterfaceHookPlugin
{

    /**
     * @var ilPegasusHelperPlugin
     */
    private static $instance;

    /**
     * @var ilPluginAdmin $plugin
     */
    private $plugin;

    /**
     * @return ilPegasusHelperPlugin
     */
    public static function getInstance()
    {
        if (!isset(ilPegasusHelperPlugin::$instance)) {
            ilPegasusHelperPlugin::$instance = new self();
        }

        return ilPegasusHelperPlugin::$instance;
    }

    public function __construct()
    {
        parent::__construct();

        /**
         * @var Container $DIC
         */
        global $DIC;

        $this->plugin = $DIC['ilPluginAdmin'];
    }

    /**
     * @return string
     */
    public function getPluginName()
    {
        return 'PegasusHelper';
    }

    /**
     * Before update processing
     */
    protected function beforeUpdate()
    {
        if (!$this->plugin->isActive(IL_COMP_SERVICE, 'UIComponent', 'uihk', 'REST')) {
            ilUtil::sendFailure('Please install the ILIAS REST Plugin first!', true);
            return false;
        }
        return true;
    }

    /**
     * Before uninstall processing
     */
    protected function beforeUninstall()
    {
        try {
            global $ilDB;
            $ilDB->dropTable("ui_uihk_pegasus_theme", false);

            global $ilPluginAdmin;
            if ($ilPluginAdmin->isActive(IL_COMP_SERVICE, 'UIComponent', 'uihk', 'REST')) {
                require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/PegasusHelper/bootstrap.php';

                $rest = new SRAG\PegasusHelper\rest\RestSetup();
                $rest->deleteClient();
            }
            return true;
        } catch (Exception $e) {
            ilUtil::sendFailure("There was a problem when uninstalling the PegasuHelper plugin", true);
            return false;
        }
    }
}
