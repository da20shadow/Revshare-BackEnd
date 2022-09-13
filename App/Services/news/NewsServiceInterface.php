<?php

namespace App\Services\news;

interface NewsServiceInterface
{
    public function getNews();
    public function publishNews($data);
    public function editNews($data,$news_id);
    public function deleteNews($news_id);
}