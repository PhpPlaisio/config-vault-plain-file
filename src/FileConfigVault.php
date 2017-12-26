<?php
//----------------------------------------------------------------------------------------------------------------------
namespace SetBased\Abc\ConfigVault;

use SetBased\Exception\RuntimeException;

/**
 * A configuration vault using a plain file (outside the path of the web server) for storing sensitive configuration
 * data using key-value pairs.
 */
class FileConfigVault implements ConfigVault
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The data in the vault.
   *
   * @var array
   */
  private $data;

  /**
   * The path where the configuration vault is been stored.
   *
   * @var string
   */
  private $path;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Object constructor.
   *
   * @param string $path The path where the configuration vault is stored.
   */
  public function __construct($path)
  {
    $this->path = $path;

    $stat = stat($path);
    if ($stat['mode'] & 0077)
    {
      throw new RuntimeException("Wrong mode %o for vault '%s'", $stat['mode'], $path);
    }

    $this->data = \json_decode(file_get_contents($path), true);
    if ($this->data===null && \json_last_error()!==JSON_ERROR_NONE)
    {
      throw new RuntimeException("File '%s' is not valid JSON. Cause: %s", $path, \json_last_error_msg());
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the value stored under a key in a domain or all key-value pairs as an associative array in a domain.
   *
   * @param string      $domain The name of the domain.
   * @param string|null $key    The key. If null all key-value pairs in the domain are returned.
   *
   * @return mixed
   */
  public function getValue($domain, $key = null)
  {
    // Test domain exists.
    if (!isset($this->data[$domain]))
    {
      throw new RuntimeException("Domain '%s' does not exists in configuration vault '%s'", $domain, $this->path);
    }

    // If key is null return the whole domain.
    if ($key===null)
    {
      return $this->data[$domain];
    }

    // Test key exists in domain.
    if (!array_key_exists($key, $this->data[$domain]))
    {
      throw new RuntimeException("Key '%s' does not exists in domain '%s' in configuration vault '%s'",
                                 $key,
                                 $domain,
                                 $this->path);
    }

    return $this->data[$domain][$key];
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Stores a value under a key in a domain or replaces all key-value pairs in a domain with key-value pairs given as an
   * associative array.
   *
   * @param string      $domain The name of the domain.
   * @param string|null $key    The key. If null the value must be an associative array.
   * @param mixed       $value  The value.
   *
   * @return void
   */
  public function putValue($domain, $key, $value)
  {
    if ($key===null)
    {
      if (!is_array($value))
      {
        throw new \UnexpectedValueException('$value must be an array');
      }

      $this->data[$domain] = $value;
    }
    else
    {
      $this->data[$domain][$key] = $value;
    }

    // Sort the domains and the keys in the domain.
    ksort($this->data);
    ksort($this->data[$domain]);

    $this->save();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Removes a key from a domain or removes a whole domain.
   *
   * @param string      $domain The name of the domain.
   * @param string|null $key    The key. If null a whole domain will be removed.
   *
   * @return void
   */
  public function unset($domain, $key = null)
  {
    if ($key===null)
    {
      unset($this->data[$domain]);
    }
    else
    {
      unset($this->data[$domain][$key]);
    }

    $this->save();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Saves the configuration vault.
   */
  private function save()
  {
    file_put_contents($this->path, \json_encode($this->data));
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
