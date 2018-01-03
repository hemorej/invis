<?php

namespace Models;

class StripeHandler
{
	public function handle($page, $oldPage, $type){
		if($page->parent() == 'prints'){
    		\Stripe\Stripe::setApiKey(\c::get('stripe_key_prv'));
			
			switch($type){
				case 'panel.page.create':
					$this->create($page);
					break;
				case 'panel.page.update':
					$this->update($page, $oldPage);
					break;
				case 'panel.page.delete':
					$this->delete($page);
					break;
			}
		}
	}

    public function create($page)
    {
    	try{
	    	$product = \Stripe\Product::create(array(
			  "name" => $page->title(),
			  "url" => $page->url(),
			  "attributes" => [$page->type()]
			));

			$page->update(array(
			    'product_id' => $product->id,
			    'synced' => "true"
			));
		}catch (\Stripe\Error\Base $e) {
		  	$page->update(array(
			    'synced' => "false"
			));
		}
    }

    public function update($page, $oldPage)
    {
    	file_put_contents('/tmp/test.txt', "image url: ".$page->images()->first()->url());
    	try{
    		$product = \Stripe\Product::retrieve($page->product_id());

    		if($page->title() != $oldPage->title()){
    			$product->name = $page->title();
    			$product->url = $page->url();
    		}

    		if($page->description() != $oldPage->description() && !empty($page->description()))
    			$product->description = $page->description();

    		if($page->type() != $oldPage->type())
    			$product->attributes = [$page->type()];

    		if($page->hasImages())
    			$product->images = [$page->images()->first()->url()];

    		//variants

			$product->save();

			$page->update(array(
			    'synced' => "true"
			));
		}catch (\Stripe\Error\Base $e) {
		  	$page->update(array(
			    'synced' => "false"
			));
		}
    }

    public function delete($page)
    {
    	try{
			$product = \Stripe\Product::retrieve($page->product_id());
			$product->delete();
		}catch (\Stripe\Error\Base $e) {}
    }
}
