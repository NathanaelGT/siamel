<?php

namespace App\Filament\Student\Clusters\InformationSystemCluster\Resources;

use App\Filament\Resource;
use App\Filament\Student\Clusters\InformationSystemCluster;
use App\Filament\Student\Clusters\InformationSystemCluster\Resources\SubjectResource\Pages;
use App\Filament\Student\Clusters\InformationSystemCluster\Resources\SubjectResource\RelationManagers;
use App\Models\Semester;
use App\Models\Subject;
use App\Period\Period;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static ?string $navigationIcon = 'lucide-book-open';

    protected static ?string $cluster = InformationSystemCluster::class;

    protected static ?int $navigationSort = 1;

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
        return Gate::check(Period::KRS);
    }

    public static function canDelete(Model $record): bool
    {
        return Gate::check(Period::KRS) && $record->semester_id === Semester::current()->id;
    }
}
