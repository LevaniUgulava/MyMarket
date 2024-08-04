<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Category;
use App\Models\Maincategory;
use App\Models\Product;
use App\Models\Subcategory;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(1)->schema([
                    TextInput::make('name')
                        ->label('Name')
                        ->required(),
                    TextInput::make('description')
                        ->label('Description')
                        ->required(),
                    TextInput::make('price')
                        ->label('price')
                        ->required(),
                    Select::make('maincategory_id')
                        ->options(function () {
                            return Maincategory::pluck('name', 'id');
                        })
                        ->label('MainCategrory')
                        ->reactive()
                        ->required(),

                    Select::make('category_id')
                        ->options(function (callable $get) {
                            $maincategoryid = $get('maincategory_id');
                            if ($maincategoryid) {
                                return Category::where('maincategory_id', $maincategoryid)->pluck('name', 'id');
                            }
                        })
                        ->label('Category')
                        ->reactive()
                        ->required(),
                    Select::make('subcategory_id')
                        ->options(function (callable $get) {
                            $maincategoryid = $get('maincategory_id');
                            $categoryid = $get('maincategory_id');

                            if ($maincategoryid) {
                                return Subcategory::where('maincategory_id', $maincategoryid)->where('category_id', $categoryid)->pluck('name', 'id');
                            }
                        })
                        ->label('Category')
                        ->reactive()
                        ->required(),




                    SpatieMediaLibraryFileUpload::make('image')
                        ->label('Image')
                        ->multiple()
                        ->required(),
                ])

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Description')
                    ->formatStateUsing(fn ($state) => Str::limit($state, 10)),
                TextColumn::make('price')
                    ->label('Price'),
                TextColumn::make('discount')
                    ->label('Discount'),
                TextColumn::make('discountprice')
                    ->label('Disprice'),
                TextColumn::make('Maincategory.name')
                    ->label('maincategory')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('Category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('Subcategory.name')
                    ->label('Subcategroy')
                    ->searchable()
                    ->sortable(),
                ImageColumn::make('images')
                    ->label('Image')
                    ->getStateUsing(fn (Product $record) => str_replace('localhost', '127.0.0.1:8000', $record->getFirstMediaUrl('default'))),
                ToggleColumn::make('active')
                    ->label('Active')
                    ->visible(fn () => Auth::user()->can('manageIsActive', Product::class)),


            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }



    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
