<?php
namespace Services;

class GetAllMetaData implements IService
{
  private $result;
  private $currentPage;
  private $totalPages;
  private $totalRecords;
  private $metadata;

  public function run(): void
  {
    $this->setTotals();
    $this->setCurrentPage();
    if($this->currentPage > $this->totalPages)
    {
      Throw new \Exceptions\PublicException('Page does not exist');
    }
    $this->setResult();
  }

  public function setResult(): void
  {
    $this->metadata = $this->getMetadata();
  }

  public function getResult(): \stdClass
  {
    global $paginationLimit;

    $result = new \stdClass;
    $result->currentPage = $this->currentPage;
    $result->totalPages = $this->totalPages;
    $result->maxRecordsPerPage = $paginationLimit;
    $result->totalRecords = $this->totalRecords;
    $result->metadata = $this->metadata;
    return $result;

  }

  public function setTotals(): void
  {
    global $paginationLimit;

    $db = \Database\Db::getInstance();
    $query = "SELECT COUNT(`pictureid`) FROM `images` WHERE 1;";
    $stmt = $db::$PDO->prepare($query, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
    $stmt->execute(array());
    $this->totalRecords = $stmt->fetchColumn();
    $this->totalPages = ceil($this->totalRecords / $paginationLimit);
  }

  public function setCurrentPage(): void
  {
    $this->currentPage = (int) $_GET['page'] ? : 1;
  }

  public function getMetadata(): Iterable
  {
    global $paginationLimit;

    $startingFrom = ($this->currentPage-1) * $paginationLimit;

    $db = \Database\Db::getInstance();
    $query = "SELECT `pictureid`, `picture_title`, `picture_url`, `picture_description` from `images` LIMIT $startingFrom, $paginationLimit ;";
    $stmt = $db::$PDO->prepare($query, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
    $stmt->execute(array());
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
  }
}
