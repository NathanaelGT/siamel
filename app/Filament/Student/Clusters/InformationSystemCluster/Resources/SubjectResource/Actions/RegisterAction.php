<?php

namespace App\Filament\Student\Clusters\InformationSystemCluster\Resources\SubjectResource\Actions;

use App\Models\Subject;
use Filament\Tables\Actions\Action;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Cache;
use Throwable;

class RegisterAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'register';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Daftar');

        $this->modalHeading('Daftar');

        $this->modalDescription(function (Subject $record) {
            return "Apakah anda yakin ingin bergabung dengan kelas $record->course_name $record->parallel$record->code?";
        });

        $this->modalSubmitActionLabel('Daftarkan diri');

        $this->successNotificationTitle(function (Subject $record) {
            return "Anda berhasil mendaftarkan diri pada kelas $record->course_name";
        });

        $this->icon('heroicon-m-plus');

        $this->action(function (Subject $record) {
            $lock = Cache::lock('register-student-subject:' . $record->id, 5);

            try {
                $lock->block(30);

                if ($record->students()->count() >= $record->capacity) {
                    $this->failureNotificationTitle('Kelas sudah penuh');
                    $this->failure();

                    return;
                }

                $record->students()->attach(auth()->user()->info_id, [
                    'registered_at' => now(),
                ], touch: false);

                $this->success();
            } catch (Throwable $e) {
                if ($e instanceof UniqueConstraintViolationException) {
                    $this->failureNotificationTitle('Anda sudah terdaftar pada kelas ini');
                } elseif ($e instanceof LockTimeoutException) {
                    $this->failureNotificationTitle('Sistem sedang sibuk, silahkan coba lagi nanti');
                }

                $this->failure();
            } finally {
                $lock->release();
            }
        });
    }
}
