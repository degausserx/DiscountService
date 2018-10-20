<p align="center"><img src="https://laravel.com/assets/img/components/logo-laravel.svg"></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/license.svg" alt="License"></a>
</p>

## DiscountService

DiscountService is a small online service which takes a collection of orders in json format, and outputs them with applied discounts. There isn't too much boilerplate
in laravel, but I'll provide an overview on the project

- Two routes are used, one as an optional view to send the request, and /discounts to display processed data.
- One controller is used, named DiscountsController. 
- App\Containers\DiscountContainer is used as an injected service for the controller, acting as bridge between the controller and service.
- App\Services\DiscountService provides the discount service through container. It collects all resources and carries out it's tasks
- App\Classes\Datamodels\ are a small concept, providing an object soley for specific data, perhaps like a struct in swift?
- App\Classes\Builders\DiscountBuilder is a class defining a discounts properties
- App\Classes\Objects\Discounts\Discount requires an instance of the previous object, and can then process orders
- DiscountOnCheapestFromTwo is an extension of Discount, used as an example
- App\HookLoaders\DiscountHookLoader contains defined discounts to be loaded by the DiscountContainer. This load method can be changed

Maybe worth mentioning that the customers and products json files are found in storage/app/. The app is lit with comments, so I'll leave it there