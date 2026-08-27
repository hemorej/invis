<?php

/**
 * Page model for journal articles (template: article).
 * Adds a helper to read the `published` field as a Unix timestamp.
 */
class ArticlePage extends Page
{
    /**
     * @return false|int Unix timestamp of the published date, or false on failure
     */
    public function publishDate(): false|int
    {
        return strtotime($this->published());
    }
}
