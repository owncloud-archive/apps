<?php
/**
 * Copyright (c) 2015 Christian Bönning <christian.boenning@wmdb.de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

class Test_User_AtlasCrowd extends \Test\TestCase {
	/**
	 * @var OC_User_AtlasCrowd $instance
	 */
	private $instance;

	private function getConfig() {
		return include(__DIR__.'/config.php');
	}

	function skip() {
		$config=$this->getConfig();
		$this->skipUnless($config['imap']['run']);
	}

	protected function setUp() {
		parent::setUp();

		$config=$this->getConfig();
		$this->instance=new OC_User_AtlasCrowd($config['host'], $config['secure'], $config['appuri'], $config['appname'], $config['apppassword']);
	}

	function testLogin() {
		$config=$this->getConfig();
		$this->assertEquals($config['crowd']['user'], $this->instance->checkPassword($config['crowd']['user'], $config['crowd']['password']));
		$this->assertFalse($this->instance->checkPassword($config['crowd']['user'], $config['crowd']['password'] . 'foo'));
	}
}
