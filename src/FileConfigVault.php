<?php

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
  public function __construct(string $path)
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
   * Returns a boolean stored under a key in a domain.
   *
   * @param string $domain The name of the domain.
   * @param string $key    The key
   *
   * @return bool
   */
  public function getBool(string $domain, string $key): ?bool
  {
    return $this->getValue($domain, $key);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns all key-value pairs as an associative array in a domain.
   *
   * @param string $domain The name of the domain.
   *
   * @return array
   */
  public function getDomain(string $domain): array
  {
    // Test domain exists.
    if (!isset($this->data[$domain]))
    {
      throw new RuntimeException("Domain '%s' does not exists in configuration vault '%s'", $domain, $this->path);
    }

    return $this->data[$domain];
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns a float stored under a key in a domain.
   *
   * @param string $domain The name of the domain.
   * @param string $key    The key
   *
   * @return float
   */
  public function getFloat(string $domain, string $key): ?float
  {
    return $this->getValue($domain, $key);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns an integer stored under a key in a domain.
   *
   * @param string $domain The name of the domain.
   * @param string $key    The key
   *
   * @return int
   */
  public function getInt(string $domain, string $key): ?int
  {
    return $this->getValue($domain, $key);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns a string stored under a key in a domain.
   *
   * @param string $domain The name of the domain.
   * @param string $key    The key
   *
   * @return string
   */
  public function getString(string $domain, string $key): ?string
  {
    return $this->getValue($domain, $key);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Stores a boolean under a key in a domain.
   *
   * @param string    $domain The name of the domain.
   * @param string    $key    The key under which the integer must be stored.
   * @param bool|null $value  The value.
   *
   * @return void
   */
  public function putBool(string $domain, string $key, ?bool $value): void
  {
    $this->putValue($domain, $key, $value);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Stores a float under a key in a domain.
   *
   * @param string     $domain The name of the domain.
   * @param string     $key    The key under which the integer must be stored.
   * @param float|null $value  The value.
   *
   * @return void
   */
  public function putFloat(string $domain, string $key, ?float $value): void
  {
    $this->putValue($domain, $key, $value);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Stores an integer under a key in a domain.
   *
   * @param string   $domain The name of the domain.
   * @param string   $key    The key under which the integer must be stored.
   * @param int|null $value  The value.
   *
   * @return void
   */
  public function putInt(string $domain, string $key, ?int $value): void
  {
    $this->putValue($domain, $key, $value);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Stores a string under a key in a domain.
   *
   * @param string      $domain The name of the domain.
   * @param string      $key    The key under which the integer must be stored.
   * @param string|null $value  The value.
   *
   * @return void
   */
  public function putString(string $domain, string $key, ?string $value): void
  {
    $this->putValue($domain, $key, $value);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Removes a whole domain.
   *
   * @param string $domain The name of the domain.
   *
   * @return void
   */
  public function unsetDomain(string $domain): void
  {
    unset($this->data[$domain]);

    $this->save();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Removes a key from a domain
   *
   * @param string $domain The name of the domain.
   * @param string $key    The key.
   *
   * @return void
   */
  public function unsetKey(string $domain, string $key): void
  {
    unset($this->data[$domain][$key]);

    $this->save();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the value stored under a key in a domain or all key-value pairs as an associative array in a domain.
   *
   * @param string $domain The name of the domain.
   * @param string $key    The key. If null all key-value pairs in the domain are returned.
   *
   * @return mixed
   */
  private function getValue(string $domain, string $key)
  {
    // Test domain exists.
    if (!isset($this->data[$domain]))
    {
      throw new RuntimeException("Domain '%s' does not exists in configuration vault '%s'", $domain, $this->path);
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
   * Stores a value under a key in a domain.
   *
   * @param string $domain The name of the domain.
   * @param string $key    The key.
   * @param mixed  $value  The value.
   *
   * @return void
   */
  private function putValue(string $domain, string $key, $value): void
  {
    $this->data[$domain][$key] = $value;

    // Sort the domains and the keys in the domain.
    ksort($this->data);
    ksort($this->data[$domain]);

    $this->save();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Saves the configuration vault.
   */
  private function save(): void
  {
    file_put_contents($this->path, \json_encode($this->data));
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
