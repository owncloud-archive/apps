<?php

OC_App::loadApp('mozilla_sync');
class Test_OutputData extends PHPUnit_Framework_TestCase {

  function test_SimpleOutput() {

    OCA_mozilla_sync\OutputData::$outputFlag = OCA_mozilla_sync\OutputData::ConstOutputBuffer;
    OCA_mozilla_sync\OutputData::$outputBuffer = '';

    OCA_mozilla_sync\OutputData::write('test 1');
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === 'test 1');

  }

  function test_JsonOutput() {

    OCA_mozilla_sync\OutputData::$outputFlag = OCA_mozilla_sync\OutputData::ConstOutputBuffer;
    OCA_mozilla_sync\OutputData::$outputBuffer = '';

    $outputArray = array(
        "sortindex" => 1000000,
        "id" => "menu",
        "modified" => 1338657406.35
    );

    $outputBuffer = "{\"sortindex\":1000000,\"id\":\"menu\",\"modified\":1338657406.35}\n";

    OCA_mozilla_sync\OutputData::write($outputArray);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === $outputBuffer);
  }

  function test_JsonOutputNoIndexArray() {

    OCA_mozilla_sync\OutputData::$outputFlag = OCA_mozilla_sync\OutputData::ConstOutputBuffer;
    OCA_mozilla_sync\OutputData::$outputBuffer = '';

    $outputArray = array();
    $outputArray[] = "element1";
    $outputArray[] = "element2";
    $outputArray[] = "element3";

    $outputBuffer = "[\"element1\",\"element2\",\"element3\"]\n";

    OCA_mozilla_sync\OutputData::write($outputArray);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === $outputBuffer);
  }

  function test_EmptyArray()
  {
    OCA_mozilla_sync\OutputData::$outputFlag = OCA_mozilla_sync\OutputData::ConstOutputBuffer;
    OCA_mozilla_sync\OutputData::$outputBuffer = '';

    OCA_mozilla_sync\OutputData::write( array() );
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === "[]\n");
  }

}
