<?php

namespace AppBundle\Sylius\Taxation;

use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Taxation\Repository\TaxCategoryRepositoryInterface;

class TaxesProvider
{
    public const SERVICE = 'service';
    public const SERVICE_TAX_EXEMPT = 'service_tax_exempt';

    private static $serviceRates = [
        'ca-bc' => 0.05000, // We always apply GST on delivery fee
        'be'    => 0.21000,
        'de'    => 0.19000,
        'es'    => 0.21000,
        'fr'    => 0.20000,
        'gb'    => 0.20000,
        'pl'    => 0.23000,
    ];

    private static $countries = [
        'ca-bc', // We always apply GST on delivery fee
        'be',
        'de',
        'es',
        'fr',
        'gb',
        'pl',
    ];

    private static $rates = [
        'be' => [
            'standard'     => 0.21,
            'intermediary' => 0.12,
            'reduced'      => 0.06,
        ],
        'de' => [
            'standard'     => 0.19,
            'reduced'      => 0.07,
        ],
        'es' => [
            'standard'     => 0.21,
            'intermediary' => 0.10,
            'reduced'      => 0.04,
        ],
        'fr' => [
            'standard'     => 0.20,
            'intermediary' => 0.10,
            'reduced'      => 0.055,
        ],
        'gb' => [
            'standard'     => 0.20,
            'intermediary' => 0.10,
            'reduced'      => 0.055,
        ],
        'gb' => [
            'standard'     => 0.20,
            'intermediary' => 0.10,
            'reduced'      => 0.055,
        ],
    ];

    private static $categories = [
        // 'service' => [
        //     // 'ca-bc' => 'gst', // We always apply GST on delivery fee
        //     '*' => 'standard'
        // ],
        'drink' => [
            'fr' => 'reduced',
        ],
        'alcoholic_drink' => [
            'fr' => 'standard',
            'de' => 'standard',
        ],
        'food_on_the_spot' => [
            'fr' => 'reduced',
        ],
        'food_takeaway' => [
            'fr' => 'intermediary',
        ],
    ];

    private static $defaultCategories = [
        'standard',
        'intermediary',
        'reduced',
    ];

    public function __construct(
        TaxCategoryRepositoryInterface $taxCategoryRepository,
        FactoryInterface $taxCategoryFactory,
        FactoryInterface $taxRateFactory)
    {
        $this->taxCategoryRepository = $taxCategoryRepository;
        $this->taxCategoryFactory = $taxCategoryFactory;
        $this->taxRateFactory = $taxRateFactory;
    }

    private function getRate($country, $type)
    {
        return self::$rates[$country][$type];
    }

    public function getCategories()
    {
        $categories = [];

        // foreach (self::$categories as $code => $rates) {

        //     var_dump($code);

        //     $c = $this->taxCategoryRepository->findOneByCode(strtoupper($code));

        //     if (null === $c) {
        //         $c = $this->taxCategoryFactory->createNew();
        //     }

        //     $c->setName(
        //         sprintf('tax_category.%s', $code)
        //     );

        //     $wildcard = $rates['*'] ?? false;

        //     // if ($wildcard) {
        //     //     var_dump($wildcard);
        //     // } else {

        //     // }

        //     # code...
        // }

        foreach (self::$defaultCategories as $code) {

            $c = $this->taxCategoryRepository->findOneByCode(strtoupper($code));

            if (null === $c) {
                $c = $this->taxCategoryFactory->createNew();
            }

            $c->setName(
                sprintf('tax_category.%s', $code)
            );

            foreach (self::$countries as $country) {

                $rates = self::$rates[$country] ?? [];

                if (empty($rates)) {
                    continue;
                }

                if (!isset($rates[$code])) {
                    continue;
                }

                $amount = $rates[$code];

                $rate = $this->taxRateFactory->createNew();

                $rate->setCode(strtoupper(sprintf('%s_%s', $country, $code)));
                $rate->setName(sprintf('tax_rate.%s', $code));
                $rate->setCountry($country);
                $rate->setAmount($amount);

                $rate->setCalculator('default');
                $rate->setIncludedInPrice(true);

                $c->addRate($rate);
            }

            if (count($c->getRates()) === 0) {
                continue;
            }

            var_dump($code);

            // $wildcard = $rates['*'] ?? false;

            // if ($wildcard) {
            //     var_dump($wildcard);
            // } else {

            // }

            # code...
        }

        // $service = $this->taxCategoryFactory->createNew();
        // $service->setCode(strtoupper(self::SERVICE));
        // $service->setName(sprintf('tax_category.%s', self::SERVICE));

        // $serviceTaxExempt = $this->taxCategoryFactory->createNew();
        // $serviceTaxExempt->setCode(strtoupper(self::SERVICE_TAX_EXEMPT));
        // $serviceTaxExempt->setName(sprintf('tax_category.%s', self::SERVICE_TAX_EXEMPT));

        // foreach (self::$serviceRates as $country => $amount) {

        //     $rate = $this->taxRateFactory->createNew();

        //     $rate->setCountry($country);
        //     $rate->setCode(sprintf('%s_SERVICE_STANDARD', strtoupper($country)));
        //     $rate->setName('tax_rate.standard');
        //     $rate->setCalculator('default');
        //     $rate->setIncludedInPrice(true);
        //     $rate->setAmount($amount);

        //     $service->addRate($rate);

        //     $zeroRate = $this->taxRateFactory->createNew();

        //     $zeroRate->setCountry($country);
        //     $zeroRate->setCode(sprintf('%s_SERVICE_ZERO', strtoupper($country)));
        //     $zeroRate->setName('tax_rate.zero');
        //     $zeroRate->setCalculator('default');
        //     $zeroRate->setIncludedInPrice(true);
        //     $zeroRate->setAmount(0.0);

        //     $serviceTaxExempt->addRate($zeroRate);
        // }

        // $categories[] = $service;
        // $categories[] = $serviceTaxExempt;

        // return $categories;
    }
}
