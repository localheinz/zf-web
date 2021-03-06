<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Application
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: FrontcontrollerTest.php 17736 2009-08-21 20:57:03Z matthew $
 */

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Zend_Application_Resource_CacheTest::main');
}

/**
 * Test helper
 */
require_once dirname(__FILE__) . '/../../../TestHelper.php';

/**
 * Zend_Loader_Autoloader
 */
require_once 'Zend/Loader/Autoloader.php';

/**
 * @category   Zend
 * @package    Zend_Application
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Application
 */
class Zend_Application_Resource_CacheTest extends PHPUnit_Framework_TestCase
{
    public static function main()
    {
        $suite  = new PHPUnit_Framework_TestSuite(__CLASS__);
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    public function setUp()
    {
        // Store original autoloaders
        $this->loaders = spl_autoload_functions();
        if (!is_array($this->loaders)) {
            // spl_autoload_functions does not return empty array when no
            // autoloaders registered...
            $this->loaders = array();
        }

        Zend_Loader_Autoloader::resetInstance();
        $this->autoloader = Zend_Loader_Autoloader::getInstance();

        $this->application = new Zend_Application('testing');

        require_once dirname(__FILE__) . '/../_files/ZfAppBootstrap.php';
        $this->bootstrap = new ZfAppBootstrap($this->application);
    }

    public function tearDown()
    {
        // Restore original autoloaders
        $loaders = spl_autoload_functions();
        foreach ($loaders as $loader) {
            spl_autoload_unregister($loader);
        }

        foreach ($this->loaders as $loader) {
            spl_autoload_register($loader);
        }

        // Reset autoloader instance so it doesn't affect other tests
        Zend_Loader_Autoloader::resetInstance();
    }

    public function testInitializationCreatesCacheInstance()
    {
        $resource = new Zend_Application_Resource_Cache(
        array('frontend'=>array('class'=>'core'),
              'backend'=>array('class'=>'file'),
        ));
        $cache = $resource->init();
        
        $this->assertTrue($resource->getCache() instanceof Zend_Cache_Core);
        $this->assertTrue($cache instanceof Zend_Cache_Core);
    }
    
    public function testGetters()
    {
        $resource = new Zend_Application_Resource_Cache(
        array('frontend'=>array('class'=>'core', 'params'=>array('lifetime'=>999)),
              'backend'=>array('class'=>'file', 'params'=>array('foo'=>'bar')),
        ));
        $cache = $resource->init();
        
        $this->assertEquals($resource->getBackendClassName(), 'file');
        $this->assertEquals($resource->getFrontendClassName(), 'core');
    }
    
    public function testThrowExceptionWhenNoClassNameDefined()
    {
        $this->setExpectedException('Zend_Application_Resource_Exception');
        $resource = new Zend_Application_Resource_Cache(
        array('frontend'=>array('class'=>'core', 'params'=>array('lifetime'=>999)),
              'backend'=>array('params'=>array('foo'=>'bar'))));
        $resource->init();
    }
    
    public function testThrowExceptionWhenGlobalParamsAreMissing()
    {
        $this->setExpectedException('Zend_Application_Resource_Exception');
        $resource = new Zend_Application_Resource_Cache(
        array());
        $resource->init();
    }
    
    public function testShareCacheToZendObjects()
    {
        $resource = new Zend_Application_Resource_Cache(
        array('frontend'=>array('class'=>'core'),
              'backend'=>array('class'=>'file'),
              'sharetozendobjects' => null
        ));
        $cache = $resource->init();
        $this->assertEquals($cache, Zend_Translate::getCache());
        /**
         * @todo Zend_Paginator::getCache() missing
         * $this->assertEquals($cache, Zend_Paginator::getCache());
         */
        $this->assertEquals($cache, Zend_Locale::getCache());
        $this->assertEquals($cache, Zend_Db_Table::getDefaultMetadataCache());
        $this->assertEquals($cache, Zend_Locale_Data::getCache());
    }
}

if (PHPUnit_MAIN_METHOD == 'Zend_Application_Resource_CacheTest::main') {
    Zend_Application_Resource_CacheTest::main();
}
