<?php

namespace App\Filament\Student\Clusters\InformationSystemCluster\Resources;

use App\Filament\Resource;
use App\Filament\Student\Clusters\InformationSystemCluster;
use App\Filament\Student\Clusters\InformationSystemCluster\Resources\SubjectResource\Pages;
use App\Filament\Student\Clusters\InformationSystemCluster\Resources\SubjectResource\RelationManagers;
use App\Models\Semester;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Model;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = InformationSystemCluster::class;

    protected static ?string $modelLabel = 'KRS';

    protected static ?string $slug = 'krs';

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\SelectedSubjects::route('/'),
            'create' => Pages\ListSubject::route('/daftar'),
        ];
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function canDelete(Model $record): bool
    {
        return $record->semester_id === Semester::current()->id;
    }
}
