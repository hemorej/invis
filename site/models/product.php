<?php
class ProductPage extends Page
{
    public function publishDate()
    {
        return strtotime($this->published());
    }
}
