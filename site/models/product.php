<?php
class ProductPage extends Page
{
	/**
	 * @return false|int
	 */
	public function publishDate(): false|int
	{
        return strtotime($this->published());
    }
}
