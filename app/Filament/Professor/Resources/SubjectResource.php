<?php

namespace App\Filament\Professor\Resources;

use App\Filament\Professor\Resources\SubjectResource\Pages;
use App\Filament\Resource;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static ?string $navigationIcon = 'lucide-book-open-text';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('professor_id', Auth::user()->info_id);
    }

    public static function canView(Model $record): bool
    {
        return true;
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
            'index'      => Pages\ListSubjects::route('/'),
            'view'       => Pages\ViewSubject::route('/{record}'),
            'post'       => Pages\PostDetail::route('/{record}/{postId}'),
            'attendance' => Pages\Attendance::route('/{record}/absensi/pertemuan-{meetingNo}'),
            'group'      => Pages\Group::route('/{subject}/kelompok/{record}'),
            'submission' => Pages\SubmissionDetail::route('/{record}/{postId}/{submissionId}'),
        ];
    }
}
