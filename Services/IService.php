<?php
namespace Services;

interface IService
{

  public function run(): void;
  public function getResult(): \stdClass;

}