<?php
use \Logger\Logger;
use \Mailbun\Mailbun;

class ShippingHandler
{
    protected $logger;
    function __construct(){
        $this->logger = (new Logger('shipping'))->getLogger();
    }

    public function handle($page, $oldPage)
    {
        if(!empty($page->parent()) && $page->parent()->uid() == 'orders')
            $this->notify($page, $oldPage);
    }

    public function notify($page, $oldPage)
    {
        $this->logger->info("handler called notify");
        $status = (string)$page->content()->get('orderstatus');
        $oldStatus = (string)$oldPage->content()->get('orderstatus');

        if($status == 'shipped' && $oldStatus != $status)
        {
            $customer = $page->customer()->yaml();

            $collection = new \Collection();
            $subtotal = 0;

            $items = $page->products()->toStructure();
            foreach ($items as $key => $item){
                $collection->append($key, $item);
                $subtotal += intval($item->quantity()->value * $item->amount()->value);
            }

            $discount = $page->discount()->yaml();

            if(!empty($discount)){
                $total = $subtotal - (intval($discount['amount']) / 100) * $subtotal;
            }else{
                $total = $subtotal;
            }

            try{
                $mailbun = new Mailbun();
                $mailbun->send($customer['email'], 'Your order from The Invisible Cities has been shipped', 'confirm', array(
                        'order' => $page->autoid(),
                        'items' => $items,
                        'fullName' => $customer['name'],
                        'street' => $customer['address']['address_line_1'] . $customer['address']['address_line_2'],
                        'city' => $customer['address']['city'],
                        'province' => $customer['address']['state'],
                        'country' => $customer['address']['country'],
                        'postcode' => $customer['address']['postal_code'],
                        'email' => $customer['email'],
                        'discount' => empty($discount['code']) ? null : $discount['code'],
                        'discountAmount' => empty($discount['amount']) ? null : $discount['amount'],
                        'total' => $total,
                        'title' => 'Your order from The Invisible Cities has been shipped',
                        'subtitle' => 'Shipping confirmation',
                        'preview' => 'Order shipping confirmation. Your order has been shipped.',
                        'headline' => 'Your order is on the way! Delivery is normally 5-10 business days to the US and Europe, but shipping times may vary.',
                        'type' => 'order'
                    ));

                $this->logger->info("email shipping confirmation sent for order id " . $page->autoid());
            }catch(\Error $e){
                $this->logger->error("email shipping confirmation error for order id " . $page->autoid() . ": " . $e->getMessage());
            }

            $page->update(array(
                'shipping_date' => date('m/d/Y H:i:s', time())
            ));
        }
    }
}