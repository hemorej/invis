<?php
use \Monolog\Logger;
use \Monolog\Handler\RotatingFileHandler;

class StripeHandler
{
    protected $logger;
    function __construct(){
        $this->logger = new Logger('stripe');
        $this->logger->pushHandler(new RotatingFileHandler(__DIR__.'/../../logs/invis.log', Logger::DEBUG));
    }

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
        $this->logger->info("stripe handler called create");
        try{
            $product = \Stripe\Product::create(array(
              "name" => $page->title(),
              "url" => $page->url(),
              "attributes" => [$page->type()]
            ));
            $this->logger->info("stripe created product $product->id");

            $page->update(array(
                'product_id' => $product->id,
                'synced' => "true"
            ));
        }catch (\Stripe\Error\Base $e) {
            $this->logger->error("product creation failed, stripe error", array('exception' => $this->getStripeErrorMessage($e)));
            $page->update(array('synced' => "false:cannot create product"));
        }catch(\Exception $e){
            $this->logger->error("product creation failed, general error", array('exception' => $e->getMessage()));
        }
    }

    public function update($page, $oldPage)
    {
        $this->logger->info("stripe handler called update");
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
                            $this->logger->info("stripe created sku $sku->id for product " . $page->product_id()->value());

                            // update variant locally with sku
                            $updatedVariant = array();
                            $updatedVariant['sku'] = $sku->id;
                            $updatedVariant['name'] = $variant->name->value();
                            $updatedVariant['price'] = $variant->price->value();
                            $updatedVariant['stock'] = $variant->stock->value();

                            addToStructure($page, 'variants', $updatedVariant);

                        }catch(\Stripe\Error\Base $e) {
                            $this->logger->error("sku creation failed, stripe error", array('exception' => $this->getStripeErrorMessage($e)));
                            $page->update(array('synced' => "false:cannot create sku"));
                        }catch(\Exception $e){
                            $this->logger->error("sku creation failed, general error", array('exception' => $e->getMessage()));
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
                            $this->logger->info("stripe update sku $sku->id");
                        }catch(\Stripe\Error\Base $e) {
                            $this->logger->error("sku update failed, stripe error", array('exception' => $this->getStripeErrorMessage($e)));
                            $page->update(array('synced' => "false:cannot update sku on stripe"));
                        }catch(\Exception $e){
                            $this->logger->error("sku update failed, general error", array('exception' => $e->getMessage()));
                        }
                    }
                }
            }else{
                // no variants, remove from stripe if we had any
                $this->removeOldVariants($page, $oldPage);
            }

            $product->save();
            $page->update(array('synced' => "true"));
            $this->logger->info("stripe updated product $product->id");
        }catch (\Stripe\Error\Base $e) {
            $this->logger->error("product update failed, stripe error", array('exception' => $this->getStripeErrorMessage($e)));
            $page->update(array('synced' => "false:cannot retrieve product"));
        }catch(\Exception $e){
            $this->logger->error("product update failed, general error", array('exception' => $e->getMessage()));
        }
    }

    public function delete($page)
    {
        $this->logger->info("stripe handler called delete");
        try{
            foreach($page->variants()->toStructure() as $variant){
                $this->deleteOrDeactivateSKU($variant->sku->value());
            }

            $product = \Stripe\Product::retrieve($page->product_id());
            $product->delete();
            $this->logger->info("stripe deleted product " . $page->product_id());
        }catch (\Stripe\Error\Base $e) {
            $this->logger->error("product deletion failed, stripe error", array('exception' => $this->getStripeErrorMessage($e)));
        }catch(\Exception $e){
            $this->logger->error("product deletion failed, general error", array('exception' => $e->getMessage()));
        }
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
            $this->logger->info("stripe deleted sku $id");
            return true;
        }catch (\Stripe\Error\Base $e) {
            try{
                $sku->active = false;
                $sku->save();
                $this->logger->info("stripe deactivated sku $id");
                return true;
            }catch (\Stripe\Error\Base $e) {
                $this->logger->error("sku deactivation failed, stripe error", array('exception' => $this->getStripeErrorMessage($e)));
                return false;
            }
        }catch(\Exception $e){
            $this->logger->error("sku deletion failed, general error", array('exception' => $e->getMessage()));
        }

    }

    private function getStripeErrorMessage($e){
        $body = $e->getJsonBody();
        $err  = $body['error'];
        return $err['message'];
    }
}

