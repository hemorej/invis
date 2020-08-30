<?php

use \Payments\StripeConnector as Stripe;
use \Logger\Logger;
use \Mailbun\Mailbun;

return function($site, $page, $kirby)
{
    $instance = new Logger('webhook');
    $logger = $instance->getLogger();

    if($_SERVER['REQUEST_METHOD'] == 'POST') {
      $endpoint_secret = $kirby->option('webhook_secret');
      $payload = @file_get_contents('php://input');
      $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
      $event = null;
      $subscriptionID = null;
      
      try{
        $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);

        // filter incoming events for processing
        if(($event->type == 'customer.subscription.deleted' && $event->data->object->status == 'canceled') 
          || ($event->type == 'customer.subscription.updated') && $event->data->object->status == 'active') {
            $logger->info("Received new $event->type event");
  
            $session = $event->data->object;
            $subscriptionID = $session->id;
            $stripe = new Stripe();
            $customer = $stripe->retrieveCustomer($session->customer);
            $product = $stripe->retrieveProduct($session->items->data[0]->plan->product);
            $subscription = $stripe->retrieveSubscription($subscriptionID);
            $order = $site->find("prints/orders/$subscriptionID");

            $shipping = \Yaml::encode([]);
            if(!empty($order) && !empty($order->shipping())){
              $shipping = $order->shipping();
            }

            $page = new \Page([
              'dirname' => "3_prints/orders/$subscriptionID",
              'slug' => $subscriptionID,
              'draft' => true,
              'template' => 'subscriptioncustomer',
              'content' => [
                'customer' => $session->customer,
                'stripe_subscription' => $subscriptionID,
                'customer_email' => $customer->email,
                'store_subscription' => $product->name,
                'since' => date('m/d/Y H:i:s', $subscription->start_date),
                'substatus' => $session->status == 'active' ? 'active' : 'canceled',
                'shipping' => $shipping
                ]
            ]);
            $page->save();

            $logger->info("Event $event->type processed successfully");

            $subscriptionInfo = array( 
              'subscription' => $subscriptionID,
              'type' => 'subscription',
              'product' => $product->name,
              'amount' => intval($subscription->plan->amount)/100,
              'status' => $subscription->status
            );

            if(!empty(\Yaml::decode($shipping))){
              $shipping = \Yaml::decode($shipping);
              $subscriptionInfo['address']['name'] = $shipping['name'];
              $subscriptionInfo['address']['city'] = $shipping['city'];
              $subscriptionInfo['address']['country'] = $shipping['country'];
              $subscriptionInfo['address']['line1'] = $shipping['line1'];
              $subscriptionInfo['address']['line2'] = $shipping['line2'];
              $subscriptionInfo['address']['postal_code'] = $shipping['postal_code'];
              $subscriptionInfo['address']['state'] = $shipping['state'];
              $subscriptionInfo['address']['email'] = $customer->email;
            }

            if(!empty($customer)){
              $mailbun = new Mailbun();
              $mailbun->send(
                $customer->email,
                "Your subscription to The Invisible Cities is now $session->status",
                'confirm', 
                \A::merge($subscriptionInfo, array(
                  'title' => "Your subscription to The Invisible Cities is now $session->status",
                  'subtitle' => "Subscription $session->status",
                  'preview' => "Subscription summary"
              )));

              $logger->info("email confirmation sent for subscription $session->status with id $subscriptionID");

              $mailbun->send(
                $kirby->option('alert_address'),
                "New $session->status subscription at The Invisible Cities!",
                'confirm',
                \A::merge($subscriptionInfo, array(
                  'title' => "A subscription at the Invisible Cities is now $session->status",
                  'subtitle' => 'Subscription summary',
                  'preview' => 'Subscription summary',
                  'headline' => "A subscription is now $session->status"
              )));

              $logger->info("admin notification sent for subscription $subscriptionID");
            }else{
              $description = "customer email not found when trying to notify subscription id " . $subscriptionID;
              sendAlert($subscriptionID, $subscriptionID, $description);
            }
        }elseif($event->type == 'checkout.session.completed' && $event->data->object->mode == 'subscription' && !empty($event->data->object->shipping)){
          $logger->info("Received new $event->type event");
          $session = $event->data->object;
          $subscriptionID = $session->subscription;

          $address = [
            "name" => $session->shipping->name,
            "city" => $session->shipping->address->city,
            "country" => $session->shipping->address->country,
            "line1" => $session->shipping->address->line1,
            "line2" => $session->shipping->address->line2,
            "postal_code" => $session->shipping->address->postal_code,
            "state" => $session->shipping->address->state];

          $page = new \Page([
            'dirname' => "3_prints/orders/$subscriptionID",
            'slug' => $subscriptionID,
            'draft' => true,
            'template' => 'subscriptioncustomer',
            'content' => ['shipping' => \Yaml::encode($address)]
          ]);
          $page->save();

          $logger->info("Event $event->type processed successfully");
        }elseif($event->type == 'invoice.payment_failed'){

          $session = $event->data->object;
          $subscriptionID = $session->subscription;
          $stripe = new Stripe();
          $customer = $stripe->retrieveCustomer($session->customer);
          $product = $stripe->retrieveProduct($session->lines->data[0]->price->product);
          $subscription = $stripe->retrieveSubscription($subscriptionID);
        
          $kirby->impersonate('kirby');
          $site->page("prints/orders/$subscriptionID")->update(['substatus' => 'payment_failed']);

          $subscriptionInfo = array( 
            'subscription' => "$subscriptionID by $customer->email",
            'type' => 'subscription',
            'product' => $product->name,
            'amount' => intval($subscription->plan->amount)/100,
            'status' => 'payment_failed'
          );

          $mailbun = new Mailbun();
          $mailbun->send(
            $kirby->option('alert_address'),
            "Failed subscription payment at The Invisible Cities",
            'confirm',
              \A::merge($subscriptionInfo, array(
                'title' => "A subscription at the Invisible Cities has a failed payment",
                'subtitle' => 'Subscription summary',
                'preview' => 'Subscription summary',
                'headline' => "A subscription has a failed payment"
            )));
        }

        http_response_code(200);
      } catch(\UnexpectedValueException $e) {
        http_response_code(400); exit();// Invalid payload
      } catch(\Stripe\Exception\SignatureVerificationException $e) {
        http_response_code(400); exit(); // Invalid signature
      } catch(\Error $err){
        $description = "email confirmation error for subscription id " . $subscriptionID . ": " . $err->getMessage();
        sendAlert($subscriptionID, $subscriptionID, $description);
        $logger->error($err);

        http_response_code(400);exit();
      } catch(\Exception $e){
        $description = "email confirmation error for subscription id " . $subscriptionID . ": " . $e->getMessage();
        sendAlert($subscriptionID, $subscriptionID, $description);
        $logger->error($e);
        
        http_response_code(400);exit();
      }
    }
};