<?php
require(dirname(__FILE__).'/../configuration/configuration.example.php');
require(dirname(__FILE__).'/../classes/loader_test.php');



class AdminTest extends PHPUnit_Framework_TestCase
{
	static $admin;
		
	public static function setUpBeforeClass()
    	{
        	self::$admin = new Admin();
    	}

       /**
        * @expectedException PHPUnit_Framework_Error
        */
	public function testLoginVide()
	{
		$this->assertEquals(false, self::$admin->login());
	}

       /**
        * @expectedException PHPUnit_Framework_Error
        */
	public function testLoginIncomplet()
	{
		$this->assertEquals(false, self::$admin->login('test'));
	}

	public function testLoginBidon()
	{
		$this->assertEquals(false, self::$admin->login('', 'test'));
	}

	public function testLoginCorrect()
	{
		$this->assertEquals(true, self::$admin->login('djpate', 'okichris'));	
	}

	public function testNom(){
		$this->assertType('string',self::$admin->get('nom'));		
	}

	public function testSetNom(){
		self::$admin->set('nom', 'bbbbb');
		$this->assertEquals('bbbbb',self::$admin->get('nom'));		
	}

	public function testPrenom(){
		$this->assertType('string',self::$admin->get('prenom'));		
	}

	public function testSetPreom(){
		self::$admin->set('prenom', 'aaaaa');
		$this->assertEquals('aaaaa',self::$admin->get('prenom'));		
	}

	public function testIsConnect(){
		$this->assertNotEquals(0,self::$admin->id);	
	}

	public function testLogout(){
		self::$admin->logout();
		$this->assertEquals(0,self::$admin->id);	
	}
	
	
}
?>
