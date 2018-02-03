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
        }elseif($page->parent() == 'prints/orders'){
            if($type == 'panel.page.update')
                $this->notify($page, $oldPage);
        }
    }

    public function notify($page, $oldPage)
    {
        $this->logger->info("handler called notify");
        if($page->status()->value() == 'shipped' && $oldPage->status()->value() != $page->status()->value())
        {
            $customer = $page->customer()->toStructure();

            $items = array();
            $total = 0;
            foreach(yaml::decode($page->products()) as $product)
            {   
                $items[] = array('variant' => $product['variant'], 'name' => $product['name'], 'quantity' => $product['quantity'], 'price' => $product['amount']);
                $total += intval($product['quantity'] * $product['amount']);
            }

            $email = email(array(
              'to'      => $customer->email()->value(),
              'from'    => 'The Invisible Cities <jerome@the-invisible-cities.com>',
              'subject' => 'Your order from The Invisible Cities has been shipped',
              'service' => 'mailgun',
              'options' => array(
                'key'    => \c::get('mailgun_key'),
                'domain' => \c::get('mailgun_domain')
              ),
              'body'    => snippet('order-confirm', 
                                array(
                                     'order' => $page->order_id()->value(),
                                     'items' => $items,
                                     'fullName' => $customer->name()->value(),
                                     'street' => $customer->address()->street()->value(),
                                     'city' => $customer->address()->city()->value(),
                                     'province' => $customer->address()->state()->value(),
                                     'country' => $customer->address()->country()->value(),
                                     'postcode' => $customer->address()->postal_code()->value(),
                                     'email' => $customer->email()->value(),
                                     'total' => $total*100,
                                     'title' => 'Your order from The Invisible Cities has been shipped',
                                     'subtitle' => 'Shipping confirmation',
                                     'preview' => 'Order shipping confirmation. Your order has been shipped.',
                                     'headline' => 'Your order is on the way! Delivery is normally 5-10 business days to the US and Europe, but shipping times may vary.'
                                    ), true)
            ));

            try{
                $email->send();
                $this->logger->info("email shipping confirmation sent for order id " . $page->order_id()->value());
            }catch(\Error $e){
                $this->logger->error("email shipping confirmation error for order id " . $page->order_id()->value() . ": " . $e->getMessage());
            }

            $page->update(array(
                'shipping_date' => date('m/d/Y H:i:s', time())
            ));
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