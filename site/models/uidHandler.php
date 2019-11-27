<?php
use \Logger\Logger;

class UidHandler
{
    protected $logger;
    function __construct(){
        $this->logger = new Logger('uid');
    }

    public function handle($page, $oldPage, $type)
    {
        if($page->parent() == 'prints/orders'){
            if($type == 'page.update:after')
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
            foreach(Yaml::decode($page->products()) as $product)
            {   
                $items[] = array('variant' => $product['variant'], 'name' => $product['name'], 'quantity' => $product['quantity'], 'price' => $product['amount']);
                $total += intval($product['quantity'] * $product['amount']);
            }

            try{
                $kirby->email(array(
                  'to'      => $customer->email()->value(),
                  'from'    => 'The Invisible Cities <jerome@the-invisible-cities.com>',
                  'subject' => 'Your order from The Invisible Cities has been shipped',
                  'service' => 'mailgun',
                  'options' => array(
                    'key'    => option('mailgun_key'),
                    'domain' => option('mailgun_domain')
                  ),
                  'template' => 'confirm',
                  'data'    => array(
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
                    )
                ));

                $this->logger->info("email shipping confirmation sent for order id " . $page->order_id()->value());
            }catch(\Error $e){
                $this->logger->error("email shipping confirmation error for order id " . $page->order_id()->value() . ": " . $e->getMessage());
            }

            $page->update(array(
                'shipping_date' => date('m/d/Y H:i:s', time())
            ));
        }
    }
}