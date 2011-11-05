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
 * @category   ZFDebug
 * @package    ZFDebug_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/zfdebug)
 * @license    http://code.google.com/p/zfdebug/wiki/License     New BSD License
 */
class ZFDebug_Controller_Plugin_Debug_Plugin_Log
    extends Zend_Controller_Plugin_Abstract 
    implements ZFDebug_Controller_Plugin_Debug_Plugin_Interface
{
    const ZFLOG = 10;
    
    protected $_logger;
    protected $_writer;
    
    protected $_marks = array();
    
    public function __construct()
    {
        Zend_Controller_Front::getInstance()->registerPlugin($this);
        $this->_writer = new ZFDebug_Controller_Plugin_Debug_Plugin_Log_Writer();
        $this->_logger = new Zend_Log($this->_writer);
        $this->_logger->addPriority('ZFLOG', self::ZFLOG);
    }
    
    public function __call($method, $params)
    {
        $this->_logger->$method(array_shift($params));
    }
    
    public function getLog()
    {
        return $this->_logger;
    }
    
    public function getWriter()
    {
        return $this->_writer;
    }
    
    /**
     * Has to return html code for the menu tab
     *
     * @return string
     */
    public function getTab()
    {
        // $this->_logger->zflog('test');
        $tab = " Log";
        if ($this->_writer->getErrorCount()) {
            $tab .= " (".$this->_writer->getErrorCount().")";
            $_COOKIE['ZFDebugCollapsed'] = 'ZFDebug_'.$this->getIdentifier();
        }
        return $tab;
    }

    /**
     * Has to return html code for the content panel
     *
     * @return string
     */
    public function getPanel()
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $module = $request->getModuleName();
        if ('default' !== $module) {
            $module = " ($module module)";
        } else {
            $module = '';
        }
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        
        $panel = "<h4>Event log for {$controller}Controller->{$action}Action() {$module}</h4>";
        $panel .= '<table cellpadding="0" cellspacing="0">'.implode('', $this->_writer->getMessages()).'</table>';
        return $panel;
    }

    /**
     * Has to return a unique identifier for the specific plugin
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'log';
    }
    
    
    /**
     * Return the path to an icon
     *
     * @return string
     */
    public function getIconData()
    {
		return false;
    }
    
    /**
     * Sets a time mark identified with $name
     *
     * @param string $name
     */
    public function mark($name, $logFirst = false) {
        if (isset($this->_marks[$name])) {
            $this->_marks[$name]['time'] = round((microtime(true)-$_SERVER['REQUEST_TIME'])*1000-$this->_marks[$name]['time']).'ms';
            if (function_exists('memory_get_usage')) {
                $this->_marks[$name]['memory'] = round((memory_get_usage()-$this->_marks[$name]['memory'])/1024) . 'K';
            } else {
                $this->_marks[$name]['memory'] = 'N/A';
            }
            $this->_logger->zflog(
                array('time' => $this->_marks[$name]['time'], 
                      'memory' => $this->_marks[$name]['memory'],
                      'message' => $name
                )
            );
        } else {
            $this->_marks[$name]['time'] = (microtime(true)-$_SERVER['REQUEST_TIME'])*1000;
            if (function_exists('memory_get_usage')) {
                $this->_marks[$name]['memory'] = memory_get_usage();
            } else {
                $this->_marks[$name]['memory'] = 'N/A';
            }
            if ($logFirst) {
                $this->_logger->zflog(
                    array('time' => round($this->_marks[$name]['time']).'ms', 
                          'memory' => round($this->_marks[$name]['memory']/1024).'K',
                          'message' => $name
                    )
                );
            }
        }
    }
    
    /**
     * Defined by Zend_Controller_Plugin_Abstract
     *
     * @param Zend_Controller_Request_Abstract
     * @return void
     */
    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {
        $this->mark('Route');
    }

    /**
     * Defined by Zend_Controller_Plugin_Abstract
     *
     * @param Zend_Controller_Request_Abstract
     * @return void
     */
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        $this->mark('Route');
    }
    
    /**
     * Defined by Zend_Controller_Plugin_Abstract
     *
     * @param Zend_Controller_Request_Abstract
     * @return void
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $this->mark(
            $request->getControllerName() . 'Controller::'.
            $request->getActionName() .'Action'
        );
    }
    
    /**
     * Defined by Zend_Controller_Plugin_Abstract
     *
     * @param Zend_Controller_Request_Abstract
     * @return void
     */
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $this->mark(
            $request->getControllerName() . 'Controller::'.
            $request->getActionName() .'Action'
        );
    }
    
    /**
     * Defined by Zend_Controller_Plugin_Abstract
     *
     * @param Zend_Controller_Request_Abstract
     * @return void
     */
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        $this->mark('Dispatch');
    }

    /**
     * Defined by Zend_Controller_Plugin_Abstract
     *
     * @param Zend_Controller_Request_Abstract
     * @return void
     */
    public function dispatchLoopShutdown()
    {
        $this->mark('Dispatch');
    }
}