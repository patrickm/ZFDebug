<?php
/**
 * ZFDebug Zend Additions
 *
 * @category   ZFDebug
 * @package    ZFDebug_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/zfdebug)
 * @license    http://code.google.com/p/zfdebug/wiki/License     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Controller_Exception
 */
require_once 'Zend/Controller/Exception.php';

/**
 * @see Zend_Version
 */
require_once 'Zend/Version.php';

/**
 * @see ZFDebug_Controller_Plugin_Debug_Plugin_Text
 */
require_once 'ZFDebug/Controller/Plugin/Debug/Plugin/Text.php';

/**
 * @category   ZFDebug
 * @package    ZFDebug_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/zfdebug)
 * @license    http://code.google.com/p/zfdebug/wiki/License     New BSD License
 */
class ZFDebug_Controller_Plugin_Debug extends Zend_Controller_Plugin_Abstract
{
    /**
     * Contains registered plugins
     *
     * @var array
     */
    protected $_plugins = array();

    /**
     * Contains options to change Debug Bar behavior
     */
    protected $_options = array(
        'plugins'           => array(
            'Variables' => null,
            'Time' => null,
            'Memory' => null),
        'image_path'        => null,
        'static_path'        => '/ZFDebug/'
    );
    
    /**
     * Standard plugins
     *
     * @var array
     */
    public static $standardPlugins = array(
        'Cache', 
        'Html', 
        'Database', 
        'Exception', 
        'File', 
        'Memory', 
        'Registry', 
        'Time', 
        'Variables',
        'Log'
        );

    /**
     * Debug Bar Version Number
     * for internal use only
     *
     * @var string
     */
    protected $_version = '1.6';

    /**
     * Creates a new instance of the Debug Bar
     *
     * @param array|Zend_Config $options
     * @throws Zend_Controller_Exception
     * @return void
     */

    protected $_closingBracket = null;

    public function __construct($options = null)
    {
        if (isset($options)) {
            if ($options instanceof Zend_Config) {
                $options = $options->toArray();
            }

            /*
             * Verify that adapter parameters are in an array.
             */
            if (!is_array($options)) {
                throw new Zend_Exception('Debug parameters must be in an array or a Zend_Config object');
            }

            $this->setOptions($options);
        }
        
        /**
         * Creating ZF Version Tab always shown
         */
        $version = new ZFDebug_Controller_Plugin_Debug_Plugin_Text();
        $version->setPanel($this->_getVersionPanel())
                ->setTab($this->_getVersionTab())
                ->setIdentifier('copyright')
                ->setIconData('');
        $this->registerPlugin($version);

        /**
         * Creating the log tab
         */
        $logger = new ZFDebug_Controller_Plugin_Debug_Plugin_Log();
        $this->registerPlugin($logger);
        $logger->mark('Startup - ZFDebug construct()', true);

        /**
         * Loading already defined plugins
         */
        $this->_loadPlugins();
    }
    
    /**
     * Get the ZFDebug logger
     *
     * @return Zend_Log
     */
    public function getLogger()
    {
        return $this->getPlugin('Log')->logger();
    }

    /**
     * Sets options of the Debug Bar
     *
     * @param array $options
     * @return ZFDebug_Controller_Plugin_Debug
     */
    public function setOptions(array $options = array())
    {
        if (isset($options['image_path'])) {
            $this->_options['image_path'] = $options['image_path'];
        }
        
        if (isset($options['plugins'])) {
            $this->_options['plugins'] = $options['plugins'];
        }

        if (isset($options['static_path'])) {
            $this->_options['static_path'] = $options['static_path'];
        }

        return $this;
    }

    /**
     * Register a new plugin in the Debug Bar
     *
     * @param ZFDebug_Controller_Plugin_Debug_Plugin_Interface
     * @return ZFDebug_Controller_Plugin_Debug
     */
    public function registerPlugin(ZFDebug_Controller_Plugin_Debug_Plugin_Interface $plugin)
    {
        $this->_plugins[$plugin->getIdentifier()] = $plugin;
        return $this;
    }

    /**
     * Unregister a plugin in the Debug Bar
     *
     * @param string $plugin
     * @return ZFDebug_Controller_Plugin_Debug
     */
    public function unregisterPlugin($plugin)
    {
        if (false !== strpos($plugin, '_')) {
            foreach ($this->_plugins as $key => $_plugin) {
                if ($plugin == get_class($_plugin)) {
                    unset($this->_plugins[$key]);
                }
            }
        } else {
            $plugin = strtolower($plugin);
            if (isset($this->_plugins[$plugin])) {
                unset($this->_plugins[$plugin]);
            }
        }
        return $this;
    }
    
    /**
     * Get a registered plugin in the Debug Bar
     *
     * @param string $identifier
     * @return ZFDebug_Controller_Plugin_Debug_Plugin_Interface
     */
    public function getPlugin($identifier)
    {
        $identifier = strtolower($identifier);
        if (isset($this->_plugins[$identifier])) {
            return $this->_plugins[$identifier];
        }
        return false;
    }
    
    /**
     * Defined by Zend_Controller_Plugin_Abstract
     */
    public function dispatchLoopShutdown()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return;
        }
        $disable = Zend_Controller_Front::getInstance()->getRequest()->getParam('ZFDEBUG_DISABLE');
        if (isset($disable)) {
            return;
        }
        
        $html = '';

        $html .= "<div id='ZFDebug_info'>\n";
        $html .= "\t<span class='ZFDebug_span close' style='padding-right:0px;' onclick='ZFDebugPanel(\"close\");'></span>\n";

        /**
         * Creating panel content for all registered plugins
         */
        foreach ($this->_plugins as $plugin) {
            $tab = $plugin->getTab();
            if ($tab == '') {
                continue;
            }

            if (null !== $this->_options['image_path'] && 
                file_exists($this->_options['image_path'] .'/'. $plugin->getIdentifier() .'.png')) {
                
                $pluginIcon = $this->_options['image_path'] .'/'. $plugin->getIdentifier() .'.png';
            } else {
                $pluginIcon = $plugin->getIconData();
            }

            /* @var $plugin ZFDebug_Controller_Plugin_Debug_Plugin_Interface */
            $showPanel = ($plugin->getPanel() == '') ? 'log' : $plugin->getIdentifier();
            $html .= "\t".'<span id="ZFDebugInfo_'.$plugin->getIdentifier()
                   . '" class="ZFDebug_span clickable" onclick="ZFDebugPanel(\'ZFDebug_' 
                   . $showPanel . '\');">' . "\n";
            if ($pluginIcon) {
                $html .= "\t\t".'<img src="' . $pluginIcon . '" style="vertical-align:middle" alt="' 
                       . $plugin->getIdentifier() . '" title="' 
                       . $plugin->getIdentifier() . '"> ' . "\n";
            }
            $html .= $tab . "</span>\n";
        }
        
        $html .= '<span id="ZFDebugInfo_Request" class="ZFDebug_span">'
               . "\n"
               . round(memory_get_peak_usage()/1024) . 'K in '
               . round((microtime(true)-$_SERVER['REQUEST_TIME'])*1000) . 'ms'
               . '</span>' . "\n";

        $html .= "</div>\n";
        $html .= '<div id="ZFDebugResize"></div>';

        /**
         * Creating menu tab for all registered plugins
         */
        $this->getPlugin('log')->mark('Shutdown', true);
        foreach ($this->_plugins as $plugin) {
            $panel = $plugin->getPanel();
            if ($panel == '') {
                continue;
            }

            /* @var $plugin ZFDebug_Controller_Plugin_Debug_Plugin_Interface */
            $html .= "\n" . '<div id="ZFDebug_' . $plugin->getIdentifier()
                  . '" class="ZFDebug_panel" name="ZFDebug_panel">' . "\n" . $panel . "\n</div>\n";
        }

        $this->_output($html);
    }

    ### INTERNAL METHODS BELOW ###

    /**
     * Load plugins set in config option
     *
     * @return void;
     */
    protected function _loadPlugins()
    {
        foreach ($this->_options['plugins'] as $plugin => $options) {
            if (is_numeric($plugin)) {
                # Plugin passed as array value instead of key
                $plugin = $options;
                $options = array();
            }
            
            // Register an instance
            if (is_object($plugin) && in_array('ZFDebug_Controller_Plugin_Debug_Plugin_Interface', class_implements($plugin))) {
                $this->registerPlugin($plugin);
                continue;
            }
            
            if (!is_string($plugin)) {
                throw new Exception("Invalid plugin name", 1);
            }
            $plugin = ucfirst($plugin);
            
            // Register a classname
            if (in_array($plugin, ZFDebug_Controller_Plugin_Debug::$standardPlugins)) {
                // standard plugin
                $pluginClass = 'ZFDebug_Controller_Plugin_Debug_Plugin_' . $plugin;
            } else {
                // we use a custom plugin
                if (!preg_match('~^[\w]+$~D', $plugin)) {
                    throw new Zend_Exception("ZFDebug: Invalid plugin name [$plugin]");
                }
                $pluginClass = $plugin;
            }

            require_once str_replace('_', DIRECTORY_SEPARATOR, $pluginClass) . '.php';
            $object = new $pluginClass($options);
            $this->registerPlugin($object);
        }
    }

    /**
     * Return version tab
     *
     * @return string
     */
    protected function _getVersionTab()
    {
        return '<strong>ZFDebug</strong>';
        // return ' ' . Zend_Version::VERSION . '/'.phpversion();
    }

    /**
     * Returns version panel
     *
     * @return string
     */
    protected function _getVersionPanel()
    {
        $panel = "<h4>ZFDebug $this->_version – Zend Framework " 
               . Zend_Version::VERSION . " on PHP " . phpversion() . "</h4>\n"
               . "<p>©2008-2009 <a href='http://jokke.dk'>Joakim Nygård</a><br>"
               . "with contributions by <a href='http://www.bangal.de'>Andreas Pankratz</a> and others</p>"
               . "<p>The project is hosted at <a href='http://code.google.com/p/zfdebug/'>http://zfdebug.googlecode.com</a>"
               . " and released under the BSD License<br>"
               . "Includes images from the <a href='http://www.famfamfam.com/lab/icons/silk/'>Silk Icon set</a> by Mark James</p>"
               . "<p>Disable ZFDebug temporarily by sending ZFDEBUG_DISABLE as a GET/POST parameter</p>";
        // $panel .= '<h4>Zend Framework '.Zend_Version::VERSION.' / PHP '.phpversion().' with extensions:</h4>';
        // $extensions = get_loaded_extensions();
        // natcasesort($extensions);
        // $panel .= implode('<br>', $extensions);
        return $panel;
    }

    /**
     * Returns html header for the Debug Bar
     *
     * @return string
     */
    protected function _headerOutput() 
    {
        return ('<script type="text/javascript" src="' . $this->_options['static_path'] . 'zfdebug.js"></script>
				<link rel="stylesheet" href="' . $this->_options['static_path'] . 'zfdebug.css" media="screen, projection" />');
    }

    /**
     * Appends Debug Bar html output to the original page
     *
     * @param string $html
     * @return void
     */
    protected function _output($html)
    {
        $html = "<div id='ZFDebug_offset'></div>\n<div id='ZFDebug'>\n$html\n</div>\n</body>";
        $response = $this->getResponse();
        // $response->setBody(preg_replace('/(<\/head>)/i', $this->_headerOutput() . '$1', $response->getBody()));
        $response->setBody(str_ireplace('</body>', $this->_headerOutput() . $html, $response->getBody()));
    }
}