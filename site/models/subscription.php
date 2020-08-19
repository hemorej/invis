<?php

class SubscriptionPage extends Page
{
    public function publishDate()
    {
        return strtotime($this->published());
    }
}
