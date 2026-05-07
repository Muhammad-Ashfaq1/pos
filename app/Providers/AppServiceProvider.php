<?php

namespace App\Providers;

use App\Models\User;
use App\Repositories\CategoriesRepository;
use App\Repositories\CustomersRepository;
use App\Repositories\DiscountsRepository;
use App\Repositories\Interface\CategoryRepositoryInterface;
use App\Repositories\Interface\CustomerRepositoryInterface;
use App\Repositories\Interface\DiscountRepositoryInterface;
use App\Repositories\Interface\ProductRepositoryInterface;
use App\Repositories\Interface\ServiceRepositoryInterface;
use App\Repositories\Interface\ShopSettingsRepositoryInterface;
use App\Repositories\Interface\SubCategoryRepositoryInterface;
use App\Repositories\Interface\VehicleRepositoryInterface;
use App\Repositories\ProductsRepository;
use App\Repositories\ServicesRepository;
use App\Repositories\ShopSettingsRepository;
use App\Repositories\SubCategoriesRepository;
use App\Repositories\VehiclesRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CategoryRepositoryInterface::class, CategoriesRepository::class);
        $this->app->bind(SubCategoryRepositoryInterface::class, SubCategoriesRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductsRepository::class);
        $this->app->bind(ServiceRepositoryInterface::class, ServicesRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, CustomersRepository::class);
        $this->app->bind(VehicleRepositoryInterface::class, VehiclesRepository::class);
        $this->app->bind(DiscountRepositoryInterface::class, DiscountsRepository::class);
        $this->app->bind(ShopSettingsRepositoryInterface::class, ShopSettingsRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user): ?bool {
            return $user->isSuperAdmin() ? true : null;
        });
    }
}
