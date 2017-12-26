<?php
//----------------------------------------------------------------------------------------------------------------------
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
  /**
   * Test with getting non-existing key.
   *
   * @expectedException \RuntimeException
   */
  public function testGetInvalid01()
  {
    $vault1 = new FileConfigVault($this->path);

    $vault1->putValue(__CLASS__, 'key1', 'value1');
    $vault1->putValue(__CLASS__, 'key2', 'value2');
    $vault1->putValue(__CLASS__, 'key3', 'value3');

    unset($vault1);
    $vault2 = new FileConfigVault($this->path);

    $vault2->getValue(__CLASS__, 'key4');
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

    $vault1->putValue(__CLASS__, 'key1', 'value1');
    $vault1->putValue(__CLASS__, 'key2', 'value2');
    $vault1->putValue(__CLASS__, 'key3', 'value3');

    unset($vault1);
    $vault2 = new FileConfigVault($this->path);

    $vault2->getValue(__METHOD__, 'key1');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test with simple put and get values to the vault.
   */
  public function testPutAndGet01()
  {
    $vault1 = new FileConfigVault($this->path);

    $vault1->putValue(__CLASS__, 'key1', 'value1');
    $vault1->putValue(__CLASS__, 'key2', 'value2');
    $vault1->putValue(__CLASS__, 'key3', 'value3');

    $vault1->putValue(__METHOD__, 'key1', 'value10');
    $vault1->putValue(__METHOD__, 'key2', 'value20');
    $vault1->putValue(__METHOD__, 'key3', 'value30');

    unset($vault1);
    $vault2 = new FileConfigVault($this->path);

    self::assertSame('value10', $vault2->getValue(__METHOD__, 'key1'));
    self::assertSame('value20', $vault2->getValue(__METHOD__, 'key2'));
    self::assertSame('value30', $vault2->getValue(__METHOD__, 'key3'));

    self::assertSame('value1', $vault2->getValue(__CLASS__, 'key1'));
    self::assertSame('value2', $vault2->getValue(__CLASS__, 'key2'));
    self::assertSame('value3', $vault2->getValue(__CLASS__, 'key3'));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test with put and get array.
   */
  public function testPutAndGet02()
  {
    $vault1 = new FileConfigVault($this->path);

    $data = ['key2' => 'value2',
             'key1' => 'value1',
             'key3' => 'value3'];
    $vault1->putValue(__CLASS__, null, $data);

    unset($vault1);
    $vault2 = new FileConfigVault($this->path);

    self::assertSame('value1', $vault2->getValue(__CLASS__, 'key1'));
    self::assertSame('value2', $vault2->getValue(__CLASS__, 'key2'));
    self::assertSame('value3', $vault2->getValue(__CLASS__, 'key3'));

    self::assertEquals($data, $vault2->getValue(__CLASS__));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Put a non-array in a domain.
   *
   * @expectedException \UnexpectedValueException
   */
  public function testPutInvalid01()
  {
    $vault1 = new FileConfigVault($this->path);

    $vault1->putValue(__CLASS__, null, 'hello world');
  }
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test unsetting a single key-value pair.
   */
  public function testUnset01()
  {
    $vault1 = new FileConfigVault($this->path);

    $vault1->putValue(__CLASS__, 'key1', 'value1');
    $vault1->putValue(__CLASS__, 'key2', 'value2');
    $vault1->putValue(__CLASS__, 'key3', 'value3');

    $vault1->putValue(__METHOD__, 'key1', 'value10');
    $vault1->putValue(__METHOD__, 'key2', 'value20');
    $vault1->putValue(__METHOD__, 'key3', 'value30');

    unset($vault1);
    $vault2 = new FileConfigVault($this->path);

    $vault2->unset(__CLASS__, 'key2');

    unset($vault2);
    $vault3 = new FileConfigVault($this->path);

    $data = ['key1' => 'value1',
             'key3' => 'value3'];

    self::assertEquals($data, $vault3->getValue(__CLASS__));
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

    $vault1->putValue(__CLASS__, 'key1', 'value1');
    $vault1->putValue(__CLASS__, 'key2', 'value2');
    $vault1->putValue(__CLASS__, 'key3', 'value3');

    $vault1->putValue(__METHOD__, 'key1', 'value10');
    $vault1->putValue(__METHOD__, 'key2', 'value20');
    $vault1->putValue(__METHOD__, 'key3', 'value30');

    unset($vault1);
    $vault2 = new FileConfigVault($this->path);

    $vault2->unset(__CLASS__);

    unset($vault2);
    $vault3 = new FileConfigVault($this->path);

    $data = ['key1' => 'value10',
             'key2' => 'value20',
             'key3' => 'value30'];

    self::assertEquals($data, $vault3->getValue(__METHOD__));

    $vault3->getValue(__CLASS__);
  }

  //--------------------------------------------------------------------------------------------------------------------

}

//----------------------------------------------------------------------------------------------------------------------
