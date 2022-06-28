<?php
namespace Services;

/*
 * POST request
 * Responses with message of successful upload
 */
class Upload implements IService
{
  private $typeUploader;

  function __construct()
  {
    $filetype = (string) $_POST['filetype'];
    if(!$filetype)
    {
      Throw new \Exceptions\PublicException('Unknown filetype');
    }
    $class = 'Services\Upload'.strtoupper($filetype);

    if(!class_exists($class)){
      Throw new \Exceptions\PublicException('Unaccepted filetype');
    }

    $this->typeUploader = new $class;
  }

  public function run(): void
  {
    $this->typeUploader->run();
  }

  public function getResult(): \stdClass
  {
    return $this->typeUploader->getResult();
  }

}
