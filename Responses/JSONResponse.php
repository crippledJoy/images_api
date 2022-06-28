<?php
namespace Responses;

class JSONResponse implements IResponse
{
  private $errorMessage;
  private $data;

  public function setErrorMessage(string $message): void
  {
    $this->errorMessage = $message;
  }

  public function getData()
  {
    return $this->data;
  }

  public function setData(\stdClass $data): void
  {
    $this->data = $data;
  }

  public function emit(): void
  {
    $response = new \stdClass;

    if($this->errorMessage)
    {
      $response->error = new \stdClass;
      $response->error->message = $this->errorMessage;
      echo json_encode($response);
      return;
    }
    $response->data = $this->getData();
    echo json_encode($response);
  }
}
