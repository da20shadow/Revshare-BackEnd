<?php

namespace App\Repositories\news;

use App\Models\news\NewsDTO;
use Database\DBConnector;
use Database\PDODatabase;

class NewsRepository implements NewsRepositoryInterface
{

    private PDODatabase $db;

    public function __construct()
    {
        $this->db = DBConnector::create();
    }

    public function insert(NewsDTO $newsDTO): bool
    {
        try {
            $this->db->query("
                INSERT INTO news (title, description) 
                VALUES (:title, :description)
            ")->execute(array(
                ':title' => $newsDTO->getTitle(),
                ':description' => $newsDTO->getDescription()
            ));
            return true;
        }catch (\PDOException $exception) {
            echo json_encode([
                'Error' => $exception->getMessage()
            ]);
            return false;
        }
    }

    public function editTitle($title, $news_id)
    {
        // TODO: Implement editTitle() method.
    }

    public function editDescription($title, $news_id)
    {
        // TODO: Implement editDescription() method.
    }

    public function editDate($title, $news_id)
    {
        // TODO: Implement editDate() method.
    }

    public function deleteNews($news_id)
    {
        // TODO: Implement deleteNews() method.
    }

    public function getNews(): ?\Generator
    {
        $news = null;
        try {

            $news = $this->db->query("
                SELECT id, title, description, date FROM news ORDER BY date DESC 
            ")->execute()->fetch(NewsDTO::class);

        } catch (\PDOException $exception) {
            echo json_encode([
                'Error' => $exception->getMessage()
            ]);
            return null;
        }
        return $news;
    }
}