<?php

namespace Webkul\DHLShipping\Repositories;

use Illuminate\Container\Container as App;
use Webkul\Core\Eloquent\Repository;
use Webkul\Core\Repositories\CountryRepository;

class DhlDetailRepository
{

    /**
     * CountryRepository class
     *
     * @var \Webkul\Core\Repositories\CountryRepository
     */
    protected $countryRepository;

    /**
     * Create a new repository instance.
     *
     * @param  \Webkul\Core\Repositories\CountryRepository  $countryRepository
     * @return void
     */
    public function __construct(CountryRepository $countryRepository, App $app)
    {
        $this->countryRepository = $countryRepository;

        // parent::__construct($app);
    }

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function getShippingAddressAttribute()
    {
        $cart = Cart::getCart();

        $address = $this->cartAddress->findWhere([
            'cart_id'=> $cart->id,
            'address_type' => 'cart_shipping'
        ])->first();

        return $address;
    }

    public function ValidItems($cartItems)
    {
        $adminProducts = [];

        foreach ($cartItems as $item) {
            if ($item->product->type != 'virtual' && $item->product->type != 'downloadable' && $item->product->type != 'booking') {

                array_push($adminProducts, $item);
            }
        }

        return $adminProducts;
    }

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function getCountries()
    {
        $countries = [];

        foreach ($this->countryRepository->all() as $country) {

            $countries[$country->code] = $country->name;
        }

        return $countries;
    }

}