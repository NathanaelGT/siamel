<?php

use App\Enums\Gender;
use App\Models\Faculty;
use App\Models\Professor;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudyProgram;
use App\Service\Auth\CreateAccount;
use Filament\Events\Auth\Registered;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\assertDatabaseHas;

describe('CreateAccount', function () {
    it('create staff account', function () {
        $data = [
            'account' => [
                'name' => $name = 'John Doe',
                'email' => 'johndoe@mail.test',
                'gender' => Gender::Male,
                'phone_number' => '081234567890',
            ],
            'id' => $id = '19801206200603682',
            'faculty_id' => Faculty::factory()->create()->id,
        ];

        Event::fake(Registered::class);

        expect(CreateAccount::staff($data))
            ->toBeInstanceOf(Staff::class)
            ->exists->toBeTrue();

        assertDatabaseHas('users', ['name' => $name]);
        assertDatabaseHas('staff', ['id' => $id]);

        Event::assertDispatched(Registered::class);
    });

    it('create professor account', function () {
        $data = [
            'account' => [
                'name' => $name = 'John Doe',
                'email' => 'johndoe@mail.test',
                'gender' => Gender::Male,
                'phone_number' => '081234567890',
            ],
            'id' => $id = '19801206200603682',
            'faculty_id' => Faculty::factory()->create()->id,
        ];

        Event::fake(Registered::class);

        expect(CreateAccount::professor($data))
            ->toBeInstanceOf(Professor::class)
            ->exists->toBeTrue();

        assertDatabaseHas('users', ['name' => $name]);
        assertDatabaseHas('professors', ['id' => $id]);

        Event::assertDispatched(Registered::class);
    });

    it('create student account', function () {
        $data = [
            'account' => [
                'name' => $name = 'John Doe',
                'email' => 'johndoe@mail.test',
                'gender' => Gender::Male,
                'phone_number' => '081234567890',
            ],
            'study_program_id' => StudyProgram::factory()->create()->id,
            'hometown' => 'Jakarta',
            'enrollment_type' => 'Mandiri',
            'parent_name' => $parent_name = 'John Davis',
            'parent_phone' => '081234567891',
            'parent_address' => 'Jl. Merdeka No. 1',
            'parent_job' => 'Presiden',
        ];

        Event::fake(Registered::class);

        expect(CreateAccount::student($data))
            ->toBeInstanceOf(Student::class)
            ->exists->toBeTrue();

        assertDatabaseHas('users', ['name' => $name]);
        assertDatabaseHas('students', ['parent_name' => $parent_name]);

        Event::assertDispatched(Registered::class);
    });
});
