<?php

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
            $page->update(array('synced' => "false:cannot create product"));
        }
    }

    public function update($page, $oldPage)
    {
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

            $variants = $page->variants()->toStructure();

            if(!empty($variants) && count($variants) > 0)
            { //if it has variants, need to update
                foreach($variants as $variant)
                {
                    if(empty($variant->sku->value()))
                    {// new variant, add to stripe
                        try{
                            $sku = \Stripe\SKU::create(array(
                              "product" => $page->product_id()->value(),
                              "attributes" => array(
                                (string)$page->type()->value() => $variant->name()->value(),
                              ),
                              "price" => intval($variant->price()->value())*100,
                              "currency" => "cad",
                              "inventory" => array(
                                "type" => "finite",
                                "quantity" => $variant->stock()->value()
                              )
                            ));

                            // update variant locally with sku
                            $updatedVariant = array();
                            $updatedVariant['sku'] = $sku->id;
                            $updatedVariant['name'] = $variant->name->value();
                            $updatedVariant['price'] = $variant->price->value();
                            $updatedVariant['stock'] = $variant->stock->value();

                            addToStructure($page, 'variants', $updatedVariant);

                        }catch(\Stripe\Error\Base $e) {
                            $page->update(array('synced' => "false:cannot create sku"));
                        }
                    }else{
                        // did we remove some variants? update stripe
                        $this->removeOldVariants($page, $oldPage);

                        try{
                            // sync local attributes to stripe
                            $sku = \Stripe\SKU::retrieve($variant->sku->value());

                            $sku->attributes = array((string)$page->type()->value() => $variant->name()->value());
                            $sku->price = intval($variant->price()->value())*100;
                            $sku->inventory = array('type' => 'finite', 'quantity' => $variant->stock()->value());
                            $sku->save();
                        }catch(\Stripe\Error\Base $e) {
                            $page->update(array('synced' => "false:cannot update sku on stripe"));
                        }
                    }
                }
            }else{
                // no variants, remove from stripe if we had any
                
                $this->removeOldVariants($page, $oldPage);
            }

            $product->save();
            $page->update(array('synced' => "true"));

        }catch (\Stripe\Error\Base $e) {
            $page->update(array('synced' => "false:cannot retrieve product"));
        }
    }

    public function delete($page)
    {
        try{
            foreach($page->variants()->toStructure() as $variant){
                $this->deleteOrDeactivateSKU($variant->sku->value());
            }

            $product = \Stripe\Product::retrieve($page->product_id());
            $product->delete();
        }catch (\Stripe\Error\Base $e) {}
    }

    public function removeOldVariants($page, $oldPage){
        
        $variants = $page->variants()->yaml();
        $oldVariants = $oldPage->variants()->yaml();

        if(count($variants) != count($oldVariants)){
            
            foreach($oldVariants as $oldVariant){
                $key = array_search($oldVariant['name'], array_column($variants, 'name'));
                if($key === false){
                    
                    $this->deleteOrDeactivateSKU($oldVariant['sku']);
                }
            }
        }
    }

    public function deleteOrDeactivateSKU($id)
    {
        
        $sku = \Stripe\SKU::retrieve($id);

        try{
            
            $sku->delete();
            return true;
        }catch (\Stripe\Error\Base $e) {
            try{
                
                $sku->active = false;
                $sku->save();
                return true;
            }catch (\Stripe\Error\Base $e) {
                return false;
            }
        }

    }
}
