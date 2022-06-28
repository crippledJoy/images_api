<?php
namespace Services;

/*
 * Provides base64_encoded image
 */
class GetImageById implements IService
{

  private $id;
  private $encodedImage;

  public function run(): void
  {
    $this->setId();
    if(!$this->id)
    {
      Throw new \Exceptions\PublicException('Not a correct id.');
    }
    $path = $this->getImagePath();

    if(file_exists($path))
    {
      $img = file_get_contents($path);
      $this->encodedImage = base64_encode($img);
      return;
    }
    Throw new \Exceptions\PublicException('Image not found, sorry.');
  }

  public function getResult(): \stdClass
  {
    $result = new \stdClass;
    $result->image = $this->encodedImage;
    return $result;
  }

  public function setId(): void
  {
    $this->id = (int) $_GET['id'];
  }

  public function getImagePath(): string
  {
    if(!(int) $this->id)
    {
      return '';
    }
    $db = \Database\Db::getInstance();
    $query = "SELECT `picture_path` FROM `images` WHERE `pictureid` = ?;";
    $stmt = $db::$PDO->prepare($query, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
    $stmt->execute(array($this->id));
    if(!$path = $stmt->fetchColumn())
    {
      Throw new \Exceptions\PublicException('No image found for id '.$this->id);
    }
    return $path;
  }
}
