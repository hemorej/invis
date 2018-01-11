<?php
use \Monolog\Logger;
use \Monolog\Handler\RotatingFileHandler;

class UidHandler
{
    protected $logger;
    function __construct(){
        $this->logger = new Logger('uid');
        $this->logger->pushHandler(new RotatingFileHandler(__DIR__.'/../../logs/invis.log', Logger::DEBUG));
    }

    public function handle($page, $oldPage, $type){

        if($page->parent() == 'prints'){
            switch($type){
                case 'panel.page.create':
                    $this->create($page);
                    break;
                case 'panel.page.update':
                    $this->update($page, $oldPage);
                    break;
            }
        }
    }

    public function create($page)
    {
        $this->logger->info("handler called create");
        try{
            $productId = getUniqueId('product');
            $this->logger->info("created product id $productId");

            $page->update(array(
                'product_id' => $productId
            ));
        }catch(\Exception $e){
            $this->logger->error("product id failed", array('exception' => $e->getMessage()));
        }
    }

    public function update($page, $oldPage)
    {
        $this->logger->info("handler called update");
        try{
            $variants = $page->variants()->toStructure();

            if(!empty($variants) && count($variants) > 0)
            { //if it has variants, need to update
                foreach($variants as $variant)
                {
                    if(empty($variant->sku->value()))
                    {// new variant, make a sku
                        try{
                            $sku = getUniqueId();
                            $this->logger->info("created sku $sku for product " . $page->product_id()->value());

                            $updatedVariant = array();
                            $updatedVariant['sku'] = $sku;
                            $updatedVariant['name'] = $variant->name->value();
                            $updatedVariant['price'] = $variant->price->value();
                            $updatedVariant['stock'] = $variant->stock->value();

                            addToStructure($page, 'variants', $updatedVariant);

                        }catch(\Exception $e){
                            $this->logger->error("sku creation failed", array('exception' => $e->getMessage()));
                        }
                    }
                }
            }
        }catch(\Exception $e){
            $this->logger->error("product update failed", array('exception' => $e->getMessage()));
        }
    }
}