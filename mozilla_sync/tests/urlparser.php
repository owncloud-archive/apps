<?php

OC_App::loadApp('mozilla_sync');
class Test_UrlParser extends PHPUnit_Framework_TestCase {

  function testUserApi_1() {

    $urlParser = new OCA_mozilla_sync\UrlParser('/1.0/aaa/');
    $this->assertTrue($urlParser->isValid());
    $this->assertTrue($urlParser->getUserName() === 'aaa');
    $this->assertTrue($urlParser->getVersion() === '1.0');
    $this->assertTrue($urlParser->getCommands() === array());

  }

  function testUserApi_2() {

    $urlParser = new OCA_mozilla_sync\UrlParser('/1.0/');
    $this->assertFalse($urlParser->isValid());

  }

  function testUserApi_3() {

    $urlParser = new OCA_mozilla_sync\UrlParser('/1.0/123/node/weave');
    $this->assertTrue($urlParser->isValid());
    $this->assertTrue($urlParser->getUserName() === '123');
    $this->assertTrue($urlParser->getVersion() === '1.0');
    $this->assertTrue($urlParser->getCommands() === array('node', 'weave'));
    $this->assertTrue($urlParser->commandMatch('/node/'));
    $this->assertTrue($urlParser->commandMatch('/node\/weave/'));
  }

  function testUserApi_4() {

    $urlParser = new OCA_mozilla_sync\UrlParser('/1.0/testuser/test@1234.pl');
    $this->assertTrue($urlParser->isValid());
    $this->assertTrue($urlParser->getUserName() === 'testuser');
    $this->assertTrue($urlParser->getVersion() === '1.0');
    $this->assertTrue($urlParser->getCommands() === array('test@1234.pl'));
  }

  function testUserApi_5() {

    $urlParser = new OCA_mozilla_sync\UrlParser('/1.0/testuser///');
    $this->assertTrue($urlParser->isValid());
    $this->assertTrue($urlParser->getUserName() === 'testuser');
    $this->assertTrue($urlParser->getVersion() === '1.0');
    $this->assertTrue($urlParser->getCommands() === array());
    $this->assertTrue($urlParser->commandCount() === 0);
  }

  function testStorageApi_1() {

    $urlParser = new OCA_mozilla_sync\UrlParser('/1.1/testuser/storage/collection/id');
    $this->assertTrue($urlParser->isValid());
    $this->assertTrue($urlParser->getUserName() === 'testuser');
    $this->assertTrue($urlParser->getVersion() === '1.1');
    $this->assertTrue($urlParser->getCommands() === array('storage', 'collection', 'id'));
    $this->assertTrue($urlParser->commandCount() === 3);
  }

  function testModifiers() {

    $urlParser = new OCA_mozilla_sync\UrlParser('/1.1/testuser/storage/tabs');
    $modifierArray = array();
    $this->assertTrue($urlParser->getCommandModifiers(1) === $modifierArray);

    $urlParser = new OCA_mozilla_sync\UrlParser('/1.1/testuser/storage/tabs?full=1');
    $modifierArray = array('full' => '1');
    $this->assertTrue($urlParser->getCommandModifiers(1) === $modifierArray);

    $urlParser = new OCA_mozilla_sync\UrlParser('/1.1/testuser/storage/tabs?full');
    $modifierArray = array();
    $this->assertTrue($urlParser->getCommandModifiers(1) === $modifierArray);

    $urlParser = new OCA_mozilla_sync\UrlParser('/1.1/testuser/storage/tabs?full=');
    $modifierArray = array('full' => '');
    $this->assertTrue($urlParser->getCommandModifiers(1) === $modifierArray);

    $urlParser = new OCA_mozilla_sync\UrlParser('/1.1/testuser/storage/tabs?full=1&sort=index&limit=100');
    $modifierArray = array('full' => '1', 'sort' => 'index', 'limit' => '100');
    $this->assertTrue($urlParser->getCommandModifiers(1) === $modifierArray);

    $urlParser = new OCA_mozilla_sync\UrlParser('/1.1/testuser/storage/tabs?full=1&ids=1,2,3,{12345}');
    $modifierArray = array('full' => '1', 'ids' => array('1', '2', '3', '{12345}'));
    $this->assertTrue($urlParser->getCommandModifiers(1) === $modifierArray);
  }

}
