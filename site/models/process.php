<?php

class ProcessPage extends Page
{
    public function publishDate()
    {
        return strtotime($this->published());
    }
}
