<?php

class requestHandler
{
  private $response;
  private $requiredService;
  private $service;

  public function handleRequest(): void
  {
    global $mode;
    $this->response = new Responses\JSONResponse;
    $this->requiredService = (string) $_REQUEST['service'];
    $this->checkForErrors();

    try
    {
      $service = new $this->service;
      $service->run();
      $this->response->setData($service->getResult());
    }catch(\Exceptions\PublicException $pe)
    {
      $this->response->setErrorMessage($pe->getMessage());
    }catch(Throwable $t)
    {
      if($mode == 'Development') {
        $this->response->setErrorMessage($t->getFile().' '.$t->getLine().' '. $t->getMessage());
      }
      if($mode == 'Production')
      {
        file_put_contents('imagesApi.log', $t->getFile().' '.$t->getLine().' '. $t->getMessage()."\n", FILE_APPEND);
        $this->response->setErrorMessage('Service could not be provided for. We are sorry.');
      }
    }finally
    {
      $this->response->emit();
    }
  }

  public function checkForErrors()
  {
    if(!$this->isAuthenticated())
    {
      $this->response->setErrorMessage('Not authenticated');
      return;
    }

    if(!$this->requiredService)
    {
      $this->response->setErrorMessage('Please provide service');
      return;
    }

    $service = ucfirst(strtolower($this->requiredService));
    $this->service = 'Services\\'.$service;
    if(!class_exists($this->service))
    {
      $this->response->setErrorMessage('This service does not exist');
      return;
    }
  }

  public function isAuthenticated()
  {
    //For the future: call on some other service
    return TRUE;
  }
}