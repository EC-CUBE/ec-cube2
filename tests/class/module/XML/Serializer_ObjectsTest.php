<?php
$HOME = realpath(dirname(__FILE__)) . "/../../../..";
require_once($HOME . "/tests/class/Common_TestCase.php");
/*
 * @see https://github.com/pear/XML_Serializer/blob/master/tests/Serializer_ObjectsTest.php
 */
/**
 * Unit Tests for serializing arrays
 *
 * @package    XML_Serializer
 * @subpackage tests
 * @author     Stephan Schmidt <schst@php-tools.net>
 * @author     Chuck Burgess <ashnazg@php.net>
 */

require_once 'XML/Serializer.php';

/**
 * Unit Tests for serializing arrays
 *
 * @package    XML_Serializer
 * @subpackage tests
 * @author     Stephan Schmidt <schst@php-tools.net>
 * @author     Chuck Burgess <ashnazg@php.net>
 */
class XML_Serializer_Objects_TestCase extends Common_TestCase
{
    private $options = array(
        XML_SERIALIZER_OPTION_INDENT     => '',
        XML_SERIALIZER_OPTION_LINEBREAKS => '',
    );

   /**
    * Test serializing an object without any properties
    */
    public function testEmptyObject()
    {
        $s = new XML_Serializer($this->options);
        $s->serialize(new stdClass());
        $this->assertEquals('<stdClass />', $s->getSerializedData());
    }

   /**
    * Test serializing a simple object
    */
    public function testSimpleObject()
    {
        $obj = new stdClass();
        $obj->foo = 'bar';
        $s = new XML_Serializer($this->options);
        $s->serialize($obj);
        $this->assertEquals('<stdClass><foo>bar</foo></stdClass>', $s->getSerializedData());
    }

   /**
    * Test serializing a nested object
    */
    public function testNestedObject()
    {
        $obj = new stdClass();
        $obj->foo = new stdClass();
        $obj->foo->bar = 'nested';
        $s = new XML_Serializer($this->options);
        $s->serialize($obj);
        $this->assertEquals('<stdClass><foo><bar>nested</bar></foo></stdClass>', $s->getSerializedData());
    }

   /**
    * Test serializing an object, that supports __sleep
    */
    public function testSleep()
    {
        $obj = new MyClass('foo', 'bar');
        $s = new XML_Serializer($this->options);
        $s->serialize($obj);
        $this->assertEquals('<MyClass><foo>foo</foo></MyClass>', $s->getSerializedData());
    }

}

class MyClass
{
    public $foo;
    public $bar;

    public function __construct($foo, $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }

    public function __sleep()
    {
        return array('foo');
    }
}
