<?php

namespace SetBased\Abc\ConfigVault\Test;

use PHPUnit\Framework\TestCase;
use SetBased\Abc\ConfigVault\FileConfigVault;

/**
 * Test cases for class FileConfigVault.
 */
class FileConfigVaultTest extends TestCase
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The path to the vault.
   *
   * @var string
   */
  private $path;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * FileConfigVaultTest constructor.
   *
   * @param string|null $name
   * @param array       $data
   * @param string      $dataName
   */
  public function __construct(string $name = null, array $data = [], string $dataName = '')
  {
    parent::__construct($name, $data, $dataName);

    $this->path = __DIR__.'/config-vault.json';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Creates and empty vault.
   */
  public function setUp()
  {
    if (is_file($this->path)) unlink($this->path);

    touch($this->path);
    chmod($this->path, 0600);

    file_put_contents($this->path, \json_encode(null));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test with getting non-existing key.
   *
   * @expectedException \RuntimeException
   */
  public function testGetInvalid01()
  {
    $vault1 = new FileConfigVault($this->path);

    $vault1->putString(__CLASS__, 'key1', 'value1');
    $vault1->putString(__CLASS__, 'key2', 'value2');
    $vault1->putString(__CLASS__, 'key3', 'value3');

    unset($vault1);
    $vault2 = new FileConfigVault($this->path);

    $vault2->getString(__CLASS__, 'key4');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test with getting non-existing domain.
   *
   * @expectedException \RuntimeException
   */
  public function testGetInvalid02()
  {
    $vault1 = new FileConfigVault($this->path);

    $vault1->putString(__CLASS__, 'key1', 'value1');
    $vault1->putString(__CLASS__, 'key2', 'value2');
    $vault1->putString(__CLASS__, 'key3', 'value3');

    unset($vault1);
    $vault2 = new FileConfigVault($this->path);

    $vault2->getString(__METHOD__, 'key1');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test vault with invalid JSON.
   *
   * @expectedException \RuntimeException
   */
  public function testInvalidJson()
  {
    file_put_contents($this->path, '[Ceci n\'est pas une pipe.}');

    new FileConfigVault($this->path);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test with simple put and get values to the vault.
   */
  public function testPutAndGet01()
  {
    $vault1 = new FileConfigVault($this->path);

    $vault1->putBool(__CLASS__, 'bool-true', true);
    $vault1->putBool(__CLASS__, 'bool-false', false);
    $vault1->putBool(__CLASS__, 'bool-null', null);

    $vault1->putFloat(__CLASS__, 'float-int', 1);
    $vault1->putFloat(__CLASS__, 'float-pi', pi());
    $vault1->putFloat(__CLASS__, 'float-null', null);

    $vault1->putInt(__CLASS__, 'int1', 1);
    $vault1->putInt(__CLASS__, 'int123', -123);
    $vault1->putInt(__CLASS__, 'int-null', null);

    $vault1->putString(__CLASS__, 'hello-world', 'hello-world');
    $vault1->putString(__CLASS__, 'string-int', -123);
    $vault1->putString(__CLASS__, 'string-null', null);

    $vault1->putString(__METHOD__, 'key1', 'value10');
    $vault1->putString(__METHOD__, 'key2', 'value20');
    $vault1->putString(__METHOD__, 'key3', 'value30');

    unset($vault1);
    $vault2 = new FileConfigVault($this->path);

    self::assertSame(true, $vault2->getBool(__CLASS__, 'bool-true'));
    self::assertSame(false, $vault2->getBool(__CLASS__, 'bool-false'));
    self::assertSame(null, $vault2->getBool(__CLASS__, 'bool-null'));

    self::assertSame(1.0, $vault2->getFloat(__CLASS__, 'float-int'));
    self::assertSame(pi(), $vault2->getFloat(__CLASS__, 'float-pi'));
    self::assertSame(null, $vault2->getFloat(__CLASS__, 'float-null'));

    self::assertSame(1, $vault2->getInt(__CLASS__, 'int1'));
    self::assertSame(-123, $vault2->getInt(__CLASS__, 'int123'));
    self::assertSame(null, $vault2->getInt(__CLASS__, 'int-null'));

    self::assertSame('hello-world', $vault2->getString(__CLASS__, 'hello-world'));
    self::assertSame('-123', $vault2->getString(__CLASS__, 'string-int'));
    self::assertSame(null, $vault2->getString(__CLASS__, 'string-null'));

    self::assertSame('value10', $vault2->getString(__METHOD__, 'key1'));
    self::assertSame('value20', $vault2->getString(__METHOD__, 'key2'));
    self::assertSame('value30', $vault2->getString(__METHOD__, 'key3'));
  }
  //--------------------------------------------------------------------------------------------------------------------
  /**
   *
   * Test unsetting a single key-value pair.
   */
  public function testUnset01()
  {
    $vault1 = new FileConfigVault($this->path);

    $vault1->putString(__CLASS__, 'key1', 'value1');
    $vault1->putString(__CLASS__, 'key2', 'value2');
    $vault1->putString(__CLASS__, 'key3', 'value3');

    $vault1->putString(__METHOD__, 'key1', 'value10');
    $vault1->putString(__METHOD__, 'key2', 'value20');
    $vault1->putString(__METHOD__, 'key3', 'value30');

    unset($vault1);
    $vault2 = new FileConfigVault($this->path);

    $vault2->unsetKey(__CLASS__, 'key2');

    unset($vault2);
    $vault3 = new FileConfigVault($this->path);

    $data = ['key1' => 'value1',
             'key3' => 'value3'];

    self::assertEquals($data, $vault3->getDomain(__CLASS__));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test unsetting a whole domain.
   *
   * @expectedException \RuntimeException
   */
  public function testUnset02()
  {
    $vault1 = new FileConfigVault($this->path);

    $vault1->putString(__CLASS__, 'key1', 'value1');
    $vault1->putString(__CLASS__, 'key2', 'value2');
    $vault1->putString(__CLASS__, 'key3', 'value3');

    $vault1->putString(__METHOD__, 'key1', 'value10');
    $vault1->putString(__METHOD__, 'key2', 'value20');
    $vault1->putString(__METHOD__, 'key3', 'value30');

    unset($vault1);
    $vault2 = new FileConfigVault($this->path);

    $vault2->unsetDomain(__CLASS__);

    unset($vault2);
    $vault3 = new FileConfigVault($this->path);

    $data = ['key1' => 'value10',
             'key2' => 'value20',
             'key3' => 'value30'];

    self::assertEquals($data, $vault3->getDomain(__METHOD__));

    $vault3->getDomain(__CLASS__);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test vault with wrong permission mode.
   *
   * @expectedException \RuntimeException
   */
  public function testWrongMode()
  {
    chmod($this->path, 0640);

    new FileConfigVault($this->path);
  }

  //--------------------------------------------------------------------------------------------------------------------

}

//----------------------------------------------------------------------------------------------------------------------
