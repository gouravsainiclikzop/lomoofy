<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FrontendController extends Controller
{
    public function index()
    {
        return view('frontend.index');
    }

    public function shop()
    {
        return view('frontend.shop');
    }

    public function product()
    {
        return view('frontend.product');
    }

    public function aboutUs()
    {
        return view('frontend.about-us');
    }

    public function contact()
    {
        return view('frontend.contact');
    }

    public function privacy()
    {
        return view('frontend.privacy');
    }

    public function faq()
    {
        return view('frontend.faq');
    }

    public function myOrders()
    {
        return view('frontend.my-orders');
    }

    public function wishlist()
    {
        return view('frontend.wishlist');
    }

    public function profileInfo()
    {
        return view('frontend.profile-info');
    }

    public function addresses()
    {
        return view('frontend.addresses');
    }

    public function paymentMethode()
    {
        return view('frontend.payment-methode');
    }

    public function shopingCart()
    {
        return view('frontend.shoping-cart');
    }

    public function checkout()
    {
        return view('frontend.checkout');
    }

    public function completeOrder()
    {
        return view('frontend.complete-order');
    }
}
