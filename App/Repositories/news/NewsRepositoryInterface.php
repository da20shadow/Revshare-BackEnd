<?php

namespace App\Repositories\news;

use App\Models\news\NewsDTO;

interface NewsRepositoryInterface
{
    public function insert(NewsDTO $newsDTO);
    public function editTitle($title,$news_id);
    public function editDescription($title,$news_id);
    public function editDate($title,$news_id);
    public function getNews();
    public function deleteNews($news_id);
}