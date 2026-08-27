<?php

/**
 * Page model for print-store products (template: product).
 * Adds a helper to read the `published` field as a Unix timestamp.
 */
class ProductPage extends Page
{
	/**
	 * @return false|int Unix timestamp of the published date, or false on failure
	 */
	public function publishDate(): false|int
	{
        return strtotime($this->published());
    }
}
