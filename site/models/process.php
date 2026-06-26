<?php

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
