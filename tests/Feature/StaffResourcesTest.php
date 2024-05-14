<?php

use App\Models\Staff;
use Filament\Facades\Filament;
use Illuminate\Support\Arr;
use function Pest\Laravel\actingAs;

$panel = filament()->getPanel('staff');

beforeEach(function () use ($panel) {
    Filament::setCurrentPanel($panel);

    actingAs(Staff::factory()->admin()->createOne()->account);
});

it('can render list page', function ($resource) {
    if (! isset($resource::getPages()['index'])) {
        $this->markTestSkipped(basename($resource) . ' does not have a list page.');
    }

    $this->get($resource::getUrl())->assertOk();
})->with('resources');

it('can render create page', function ($resource) {
    if (! isset($resource::getPages()['create'])) {
        $this->markTestSkipped(basename($resource) . ' does not have a create page.');
    }

    $this->get($resource::getUrl('create'))->assertOk();
})->with('resources');

it('can render view page', function ($resource) {
    if (! isset($resource::getPages()['view'])) {
        $this->markTestSkipped(basename($resource) . ' does not have a view page.');
    }

    $record = $resource::getModel()::factory()->createOne();

    $this->get($resource::getUrl('create', ['record' => $record]))->assertOk();
})->with('resources');

it('can render edit page', function ($resource) {
    if (! isset($resource::getPages()['edit'])) {
        $this->markTestSkipped(basename($resource) . ' does not have a edit page.');
    }

    $record = $resource::getModel()::factory()->createOne();

    $this->get($resource::getUrl('edit', ['record' => $record]))->assertOk();
})->with('resources');

dataset('resources', Arr::mapWithKeys($panel->getResources(), fn($resource) => [basename($resource) => $resource]));
