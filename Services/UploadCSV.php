<?php
namespace Services;

//For mac eol
ini_set('auto_detect_line_endings',TRUE);

class UploadCSV extends Upload implements IService
{

  private $result;
  private $files = array();
  private $rowWarnings = array();

  function __construct()
  {
    $this->setFiles();
  }

  public function run(): void
  {
    foreach($this->getFiles() as $file)
    {
      $this->runFile($file);
    }
  }

  public function runFile(array $file): void
  {
    global $csvSeparator;

    $fp = fopen($file['tmp_name'], 'r');
    if ( $fp === FALSE )
    {
      Throw new \Exceptions\PublicException('Cannot open file '. (string) $file['name']);
      return;
    }
    $columnNames = fgetcsv($fp, 0, $csvSeparator);
    $columnNames = array_map('trim', $columnNames);
    $columnNames = array_map('strtolower', $columnNames);
    $rowCounter = 1;
    while(($newData = fgetcsv($fp, 0, $csvSeparator)) !== FALSE)
    {
      $rowCounter++;
      if(count($columnNames) != count($newData))
      {
        $this->rowWarnings[] = 'Row columns not ok, skip row '.$rowCounter.'.';
        continue;
      }
      $newData = array_map('trim', $newData);
      $newData = array_combine($columnNames, $newData);

      $cleanTitle = $this->getCleanString($newData['picture_title']);
      if(!$cleanTitle){
        $this->rowWarnings[] = 'Title not ok, skip row '.$rowCounter.'.';
        continue;
      }

      $url = $this->getCleanURL($newData['picture_url']);
      if(!$url)
      {
        $this->rowWarnings[] = 'Url not ok, skip row '.$rowCounter.'.';
        continue;
      }
      $newFileName = $this->getNewFileName($url, $cleanTitle);
      if(!$newFileName)
      {
        $this->rowWarnings[] = 'Not a correct image, skip row '.$rowCounter.'.';
        continue;
      }
      $this->downloadImageFromURL($url, $newFileName);
      $this->insertOrUpdateInDbase($cleanTitle, $url, $newFileName, $newData['picture_description']);
    }
    $this->setResult($this->getUploadLog());
  }

 public function insertOrUpdateInDbase(string $title, string $url, string $path, string $description): void
  {
    if(!$this->existsInDbase($title))
    {
      $this->insertIntoDbase($title, $url, $path, $description);
      return;
    }
    $this->updateInDbase($title, $url, $path, $description);
  }

  public function setResult(string $uploadLog): void
  {
    $this->result = $uploadLog;
  }

  public function getResult(): \stdClass
  {
    $result = new \stdClass;
    $result->uploadLog = $this->result;
    return $result;
  }

  public function setFiles(): void
  {
    if(!$_FILES)
    {
      Throw new \Exceptions\PublicException('No files found.');
    }
    foreach($_FILES as $file)
    {
      if($file['error'])
      {
        Throw new \Exception('Error '.$file['error']);
      }
      $this->files[] = $file;
    }
  }

  public function getFiles(): Iterable
  {
    return $this->files;
  }

  public function existsInDbase(string $title): bool
  {
    $db = \Database\Db::getInstance();
    $query = "SELECT COUNT(`pictureid`) FROM `images` WHERE `picture_title` = ?;";
    $stmt = $db::$PDO->prepare($query, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
    $stmt->execute(array($title));
    return $stmt->fetchColumn() ? TRUE : FALSE;
  }

  public function insertIntoDbase(string $title, string $url, string $path, string $description = NULL): void
  {
    $db = \Database\Db::getInstance();
    $query = "INSERT INTO `images` (`picture_title`, `picture_url`, `picture_description`, `picture_path`) VALUES (?, ?, ?, ?);";
    $stmt = $db::$PDO->prepare($query, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
    $stmt->execute(array($title, $url, $description, $path));
  }

  public function updateInDbase(string $title, string $url, string $path, string $description = NULL): void
  {
    $db = \Database\Db::getInstance();
    $query = "UPDATE `images` SET `picture_url`= ?,`picture_description`= ?, `picture_path`=? WHERE `picture_title` = ?";
    $stmt = $db::$PDO->prepare($query, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
    $stmt->execute(array($url, $description, $path, $title));
  }

  /*
   *  Replace whitespace with underscore
   *  Allow only letters, numbers and underscores
   */
  public function getCleanString(string $string): string
  {
    $newString = preg_replace('#\s#', '_', $string);
    $pattern = '#[^A-Za-z0-9_]#';
    $newString = preg_replace($pattern, '', $string);
    return $newString ? : '';
  }

  public function getCleanURL(string $url): string
  {
    if(!filter_var($url, FILTER_VALIDATE_URL))
    {
      return '';
    }
    return $url;
  }

  public function getNewFileName(string $url, string $name): string
  {
    global $storageDir;

    $extension = pathinfo($url, PATHINFO_EXTENSION);
    if(!$extension) { return ''; }

    $newFileName = $name.'.'.$extension;
    return $storageDir . $newFileName;
  }

  public function downloadImageFromURL(string $url, string $newFileName): void
  {
    $ch = curl_init($url);
    $fp = fopen($newFileName, 'wb');
    if(!$fp)
    {
      $this->rowWarnings[] = 'Could not save image from '.$url.'.';
      return;
    }
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $success = curl_exec($ch);
    if($success === FALSE || curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200)
    {
      $this->rowWarnings[] = 'Could not download '.$url.'.';
      return;
    }
    curl_close($ch);
    fclose($fp);
  }

  public function getUploadLog():string
  {
   $result = '';
   if($this->rowWarnings)
   {
    $result = implode(';', $this->rowWarnings).' ';
    $result .= ';';
    $result .= 'Rest of the data was accepted.';
    return $result;
   }
    return 'Data was accepted.';
  }

}
