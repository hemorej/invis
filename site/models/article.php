<?php

class ArticlePage extends Page
{
    public function publishDate()
    {
        return strtotime($this->published());
    }
}
