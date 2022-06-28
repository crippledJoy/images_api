<?php
namespace Responses;

Interface IResponse
{
  public function setErrorMessage(string $message): void;

  public function emit(): void;

  public function setData(\stdClass $data): void;
}
