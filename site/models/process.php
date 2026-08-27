<?php

/**
 * Page model for "process" (design write-up) pages.
 * Adds a helper to read the `published` field as a Unix timestamp.
 */
class ProcessPage extends Page
{
    /**
     * @return false|int Unix timestamp of the published date, or false on failure
     */
    public function publishDate(): false|int
    {
        return strtotime($this->published());
    }
}
