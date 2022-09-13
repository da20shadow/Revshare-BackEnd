<?php

namespace App\Services\news;

use App\Models\news\NewsDTO;
use App\Repositories\news\NewsRepository;

class NewsService implements NewsServiceInterface
{
    private NewsRepository $newsRepository;

    public function __construct()
    {
        $this->newsRepository = new NewsRepository();
    }


    public function getNews()
    {
        $news = $this->newsRepository->getNews();

        if ($news === null) {
            http_response_code(403);
            echo json_encode([
                'message' => 'No News Yet!'
            ]);
            return;
        }

        $news = $this->generateNewsList($news);

        if ($news === []) {
            http_response_code(403);
            echo json_encode([
                'message' => 'No News Yet!'
            ]);
            return;
        }

        http_response_code(200);
        echo json_encode([
            'news' => $news
        ]);
    }

    public function publishNews($data)
    {
        if (!isset($data['title']) || !isset($data['description'])) {
            http_response_code(403);
            echo json_encode([
                'message' => 'Error! Invalid Title or Description!'
            ]);
            return;
        }
        $title = $data['title'];
        $description = $data['description'];

        if (strlen($title) > 150) {

            http_response_code(403);
            echo json_encode([
                'message' => 'Error! Title max length is 150!'
            ]);
            return;
        }

        try {

            $newsDTO = new NewsDTO();
            $newsDTO->setTitle($title);
            $newsDTO->setDescription($description);

        } catch (\Exception $exception) {

            http_response_code(403);
            echo json_encode([
                'message' => $exception->getMessage()
            ]);
            return;

        }

        $result = $this->newsRepository->insert($newsDTO);

        if ($result === false){
            http_response_code(403);
            return;
        }

        http_response_code(200);
        echo json_encode([
            'message' => 'Successfully Published News!'
        ]);
    }

    public function editNews($data, $news_id)
    {
        // TODO: Implement editNews() method.
    }

    public function deleteNews($news_id)
    {
        // TODO: Implement deleteNews() method.
    }

    public function generateNewsList($newsGenerator): array
    {
        $newsList = [];
        foreach ($newsGenerator as $news) {
            array_push($newsList, [
                'id' => $news->getId(),
                'title' => $news->getTitle(),
                'description' => $news->getDescription(),
                'date' => $news->getDate(),
            ]);
        }
        return $newsList;
    }
}