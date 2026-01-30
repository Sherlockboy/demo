<?php

namespace App\Filament\Clusters\Products\Resources\Products\Schemas;

use App\Filament\Clusters\Products\Resources\Brands\RelationManagers\ProductsRelationManager;
use App\Models\Shop\Product;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $operation, $state, Set $set): void {
                                        if ($operation !== 'create') {
                                            return;
                                        }

                                        $set('slug', Str::slug($state));
                                    }),

                                TextInput::make('slug')
                                    ->disabled()
                                    ->dehydrated()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Product::class, 'slug', ignoreRecord: true),

                                RichEditor::make('description')
                                    ->columnSpan('full'),
                            ])
                            ->columns(2),

                        Section::make(__('filament.images'))
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('media')
                                    ->collection('product-images')
                                    ->multiple()
                                    ->maxFiles(5)
                                    ->reorderable()
                                    ->acceptedFileTypes(['image/jpeg'])
                                    ->hiddenLabel(),
                            ])
                            ->collapsible(),

                        Section::make(__('filament.pricing'))
                            ->schema([
                                TextInput::make('price')
                                    ->numeric()
                                    ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                                    ->required(),

                                TextInput::make('old_price')
                                    ->label(__('filament.compare_at_price'))
                                    ->numeric()
                                    ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                                    ->required(),

                                TextInput::make('cost')
                                    ->label(__('filament.cost_per_item'))
                                    ->helperText(__('filament.customers_wont_see_price'))
                                    ->numeric()
                                    ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                                    ->required(),
                            ])
                            ->columns(2),
                        Section::make(__('filament.inventory'))
                            ->schema([
                                TextInput::make('sku')
                                    ->label(__('filament.sku_full'))
                                    ->unique(Product::class, 'sku', ignoreRecord: true)
                                    ->maxLength(255)
                                    ->required(),

                                TextInput::make('barcode')
                                    ->label(__('filament.barcode_full'))
                                    ->unique(Product::class, 'barcode', ignoreRecord: true)
                                    ->maxLength(255)
                                    ->required(),

                                TextInput::make('qty')
                                    ->label(__('filament.quantity'))
                                    ->numeric()
                                    ->rules(['integer', 'min:0'])
                                    ->required(),

                                TextInput::make('security_stock')
                                    ->helperText(__('filament.safety_stock_helper'))
                                    ->numeric()
                                    ->rules(['integer', 'min:0'])
                                    ->required(),
                            ])
                            ->columns(2),

                        Section::make(__('filament.shipping'))
                            ->schema([
                                Checkbox::make('backorder')
                                    ->label(__('filament.product_can_be_returned')),

                                Checkbox::make('requires_shipping')
                                    ->label(__('filament.product_will_be_shipped')),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpan(['lg' => 2]),

                Group::make()
                    ->schema([
                        Section::make(__('filament.status'))
                            ->schema([
                                Toggle::make('is_visible')
                                    ->label(__('filament.visibility'))
                                    ->helperText(__('filament.product_hidden_from_channels'))
                                    ->default(true),

                                DatePicker::make('published_at')
                                    ->label(__('filament.publishing_date'))
                                    ->default(now())
                                    ->required(),
                            ]),

                        Section::make(__('filament.associations'))
                            ->schema([
                                Select::make('shop_brand_id')
                                    ->relationship('brand', 'name')
                                    ->searchable()
                                    ->hiddenOn(ProductsRelationManager::class),

                                Select::make('categories')
                                    ->relationship('categories', 'name')
                                    ->multiple()
                                    ->required(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }
}
