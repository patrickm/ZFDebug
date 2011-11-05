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
class ZFDebug_Controller_Plugin_Debug_Plugin_Cache 
    extends ZFDebug_Controller_Plugin_Debug_Plugin 
    implements ZFDebug_Controller_Plugin_Debug_Plugin_Interface
{
    /**
     * Contains plugin identifier name
     *
     * @var string
     */
    protected $_identifier = 'cache';

    /**
     * @var Zend_Cache_Backend_ExtendedInterface
     */
    protected $_cacheBackends = array();

    /**
     * Create ZFDebug_Controller_Plugin_Debug_Plugin_Cache
     *
     * @param array $options
     * @return void
     */
    public function __construct(array $options = array())
    {
        if (!isset($options['backend'])) {
            throw new Zend_Exception("ZFDebug: Cache plugin needs 'backend' parameter");
        }
        is_array($options['backend']) || $options['backend'] = array($options['backend']);
        foreach ($options['backend'] as $name => $backend) {
            if ($backend instanceof Zend_Cache_Backend_ExtendedInterface ) {
                $this->_cacheBackends[$name] = $backend;
            }
        }
    }

    /**
     * Gets identifier for this plugin
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->_identifier;
    }
    
    /**
     * Returns the base64 encoded icon
     *
     * @return string
     **/
    public function getIconData()
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAAx5pQ0NQ
        SUNDIFByb2ZpbGUAAHgBhVTfa9NQFP7aZZ2w4Ys6ZxEJPmiRbmRTdEOctmtXus1a6ja3IUibpm1c msYk7X6wB9mLbzrFd/EHPvkHDNmDb3uSDcYUYfisiCJM9iKznps0TSdTA7n3u9/57jkn5+ReoPlx
        WtMULw8UVVNPxcL8xOQU3/IBXhxDK/xoTYuGFkomR0AP07J577PzDh7GbHTub9+r/mPVmpUMEfAc IL6UNcQi4VmgaVzUdBPg1onvnTE1ws1Mc1inBAkfZzhvY4HhjI0jlmY0NUCaCcIHxUI6S7hAOJhp
        4PMN2M6BFOQnJqmSLos8q0VSL+VkRbIM9vAfc4PyX7ColOmbrecQjW2aGU7RfIreHzl9cIzmANXB P18YvUG4nXBYNuOjNX5CzSSuESa95+50aZjtZZqXWSkSJRwkvGJUrjNsaeYLA4ma5tPt9FCSsJ80
        vzQzWfPj7VCVBOsxxfX2S0b0ur3X25GTB+M1Pqcp1n9A/r339XKK5XmS8Ou0Ho3V9FuSOlbb2+TN piPDxHdRPwNIQoKOHGQo4JFCDGGaY8Sq+EwWGSJKpNJpdFR5sjjWOyiTlu0cR3jJXw64Xhb1W7K4
        9uD7nt0l1468FYXFt6PYUe21gem6shOaG391YaWjbuE3ueWbG22rCwhR/orlsUheZcpPglHXdTbm kFOX/HULzzKU7iV2ElgMuqzwXvgqbApPhRfCl7/UyM7erRHLgHEsPqueU83G7CXSTNLrZLi/ivVC
        xhj5kDFDWp2+JE2cijny2lALJwZ3guvm4lwv1weeu8xd4fq5CK0uciOOwhf1RXwh8L4zvj5ft2+I YafbvtNk66MxWuu705G6orF6lI/bJ/Y9jqoTGbKxvrF+VggrtKJjakqzdE8AAyVtTpfzBZPvEYQL
        fIiuLYmPq2JXkE8rCm+ZDF6XDEmvSNkusDuP7QO2U9Zd5mlfcznzKnDpG/3D6y43VQZeGcDRcy4X oDN15AmwfF4s6xXbHzyet4CRO9tjrT1tYbqLPlar23QuWx4Buw+r1Z/PqtXd5+R/C3ij/AapIwrW
        wqFfMgAAAAlwSFlzAAALEwAACxMBAJqcGAAAAWRpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADx4 OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IlhNUCBDb3JlIDQuNC4w
        Ij4KICAgPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJk Zi1zeW50YXgtbnMjIj4KICAgICAgPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIKICAgICAg
        ICAgICAgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIj4KICAgICAgICAg PHhtcDpDcmVhdG9yVG9vbD5BZG9iZSBJbWFnZVJlYWR5PC94bXA6Q3JlYXRvclRvb2w+CiAgICAg
        IDwvcmRmOkRlc2NyaXB0aW9uPgogICA8L3JkZjpSREY+CjwveDp4bXBtZXRhPgob5XoOAAACSUlE QVQ4EY1TS4vSURQ/jv/MxBdoPpkWfYAghgn6BIMrF5ObNtJGIQqLoJnAQBcDJQRFIST5ICEIm0Wu
        UnTh7AUZaBJfSONjkYs2+n/p2DkXbsw0Rl04nPs453d+53FhuVzCvyQWi+0GAgHtKrs1+I81m81u ezyet6tMNYRKK5vNrququi1J0pYsyxsol3EPJHa7HRRFgXa7/Sqfzz84DcQAMpnMTTTYMZlMfoPB
        AHq9HjRrGljMF6DOVVgsFqDICtTrdWi1WnvFYjHKQYRkMrmOLHZcLpffarWCKIqsJoqswgk6IisG MJ/PQZIlQGY67kxaQPRts9nsx+j0yAA6nQ70ej12phTcbjdj0ml33pTL5cdnADDiFuUooqE4m0Gt
        VvuO6XxKp9OPuKHP5ztEJvVKpXKf33EtTKfT641GQ0Z6J5IoicjifS6Xe8oNSCPgR5vN9iyRSNzh heVawBY94QeuC4WCFu93g8HgHgFUq1WmcR6uIuO7GOwSBlvDYD9/t5EM+cKuZLH6G+FQ+Bq/i0aj
        L9Dh1uaNzStarRbG4zEc1A6+nAPA3F8bjcZ7VP3hcMjmgJhhCuD1ekF3UQfUEWpp81vzocAjkE6l UglydjqdrHUOh4MZq+hALcV6sXnodrtw9PXoMwbZPwOAkRThgsDaR8NDQtFIsCYwmUxgMBhAs9kk
        5+fYleNzKcTj8ZcWiyVCo9vv91kKNB94/iErch0nsoSDt18qlY4Z+1U/LBKJvAuFQoer3v68W/kb R6NRGNP5cLo+f9v/Am7q3vlPsPUBAAAAAElFTkSuQmCC';
    }

    /**
     * Gets menu tab for the Debugbar
     *
     * @return string
     */
    public function getTab()
    {
        return 'Cache';
    }

    /**
     * Gets content panel for the Debugbar
     *
     * @return string
     */
    public function getPanel()
    {
        $panel = '';
        
        $linebreak = "<br>";

        # Support for APC
        if (function_exists('apc_sma_info') && ini_get('apc.enabled')) {
            $mem = apc_sma_info();
            $memSize = $mem['num_seg'] * $mem['seg_size'];
            $memAvail = $mem['avail_mem'];
            $memUsed = $memSize - $memAvail;
            
            $cache = apc_cache_info();
            
            $panel .= '<h4>APC '.phpversion('apc').' Enabled</h4>';
            $panel .= round($memAvail/1024/1024, 1) . 'M available, ' 
                    . round($memUsed/1024/1024, 1) . 'M used' . $linebreak
                    . $cache['num_entries'].' Files cached (' 
                    . round($cache['mem_size']/1024/1024, 1) . 'M)' . $linebreak
                    . $cache['num_hits'] . ' Hits (' 
                    . round($cache['num_hits'] * 100 / ($cache['num_hits'] + $cache['num_misses']), 1) . '%)' 
                    . $linebreak
                    . $cache['expunges'] . ' Expunges (cache full count)'; 
        }

        foreach ($this->_cacheBackends as $name => $backend) {
            $fillingPercentage = $backend->getFillingPercentage();
            $ids = $backend->getIds();
            
            # Print full class name, backends might be custom
            $panel .= '<h4>Cache '.$name.' ('.get_class($backend).')</h4>';
            $panel .= count($ids).' Entr'.(count($ids)>1?'ies':'y').''.$linebreak
                    . 'Filling Percentage: '.$backend->getFillingPercentage().'%'.$linebreak;
            
            $cacheSize = 0;
            foreach ($ids as $id) {
                # Calculate valid cache size
                $memPre = memory_get_usage();
                if ($cached = $backend->load($id)) {
                    $memPost = memory_get_usage();
                    $cacheSize += $memPost - $memPre;
                    unset($cached);
                }                
            }
            $panel .= 'Valid Cache Size: ' . round($cacheSize/1024, 1) . 'K';
        }
        return $panel;
    }
}