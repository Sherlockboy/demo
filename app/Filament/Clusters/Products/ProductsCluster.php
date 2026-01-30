<?php

namespace App\Filament\Clusters\Products;

use BackedEnum;
use Filament\Clusters\Cluster;

class ProductsCluster extends Cluster
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-squares-2x2';

    public static function getNavigationGroup(): ?string
    {
        return __('filament.shop');
    }

    protected static ?int $navigationSort = 0;

    protected static ?string $slug = 'shop/products';
}
