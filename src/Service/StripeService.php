<?php
namespace App\Service;

    class StripeService
    {
        public function __construct(
            private string $stripeApiSecret, 
            private string $stripeApiCurrency
        )
        {    
        }

        public function getStripeApiSecret(): string
        {
            return $this->stripeApiSecret;
        }

        public function getStripeApiCurrency(): string
        {
            return $this->stripeApiCurrency;
        }
    }