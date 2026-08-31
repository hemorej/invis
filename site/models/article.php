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

    /**
     * Whether this article is a long-form written piece (type: text) rather
     * than a photo series. An unset type counts as a picture article.
     *
     * @return bool
     */
    public function isTextArticle(): bool
    {
        return $this->type()->value() === 'text';
    }

    /**
     * The lead image: the explicit `preview` file for a text article, falling
     * back to the first image file on the page. Also used as the hover preview
     * in the index.
     *
     * @return \Kirby\Cms\File|null
     */
    public function previewImage(): ?\Kirby\Cms\File
    {
        return $this->preview()->toFile() ?? $this->images()->first();
    }
}
