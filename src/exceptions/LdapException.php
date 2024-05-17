<?php
namespace NZord\Exceptions;

class LdapException extends \Exception
{
  public function __construct($message) {
    parent::__construct($message);
  }
}
