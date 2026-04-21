<?php

namespace App\Filament\Resources\Branches;

use App\Filament\Concerns\SuperAdminOnly;
use App\Filament\Resources\Branches\Pages\CreateBranch;
use App\Filament\Resources\Branches\Pages\EditBranch;
use App\Filament\Resources\Branches\Pages\ListBranches;
use App\Filament\Resources\Branches\Schemas\BranchForm;
use App\Filament\Resources\Branches\Tables\BranchesTable;
use App\Models\Branch;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BranchResource extends Resource
{
    use SuperAdminOnly;

    protected static ?string $model = Branch::class;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;
    protected static string|\UnitEnum|null $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema { return BranchForm::configure($schema); }
    public static function table(Table $table): Table { return BranchesTable::configure($table); }
    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => ListBranches::route('/'),
            'create' => CreateBranch::route('/create'),
            'edit'   => EditBranch::route('/{record}/edit'),
        ];
    }
}
