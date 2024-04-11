<?php

namespace Tests\Commands;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Storage;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Contracts\Assets\AssetContainer as AssetContainerContract;
use Statamic\Eloquent\Assets\AssetContainerModel;
use Statamic\Eloquent\Assets\AssetModel;
use Statamic\Eloquent\Fields\BlueprintModel;
use Statamic\Eloquent\Fields\FieldsetModel;
use Statamic\Eloquent\Forms\FormModel;
use Statamic\Eloquent\Forms\SubmissionModel;
use Statamic\Facades\Blueprint as BlueprintFacade;
use Statamic\Facades\Fieldset as FieldsetFacade;
use Statamic\Fields\Blueprint;
use Statamic\Fields\BlueprintRepository;
use Statamic\Fields\Fieldset;
use Statamic\Fields\FieldsetRepository;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ImportBlueprintsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        Facade::clearResolvedInstance(BlueprintRepository::class);
        Facade::clearResolvedInstance(FieldsetRepository::class);

        app()->bind(Blueprint::class, Blueprint::class);
        app()->bind(Fieldset::class, Fieldset::class);
        app()->bind(BlueprintRepository::class, function () {
            return (new BlueprintRepository)->setDirectory(resource_path('blueprints'));
        });
        app()->bind(FieldsetRepository::class, function () {
            return (new FieldsetRepository)->setDirectory(resource_path('fieldsets'));
        });

        // Statamic will automatically generate a default blueprint. For the purpose of this test, we'll delete it.
        BlueprintModel::all()->each->delete();
    }

    /** @test */
    public function it_imports_blueprints_and_fieldsets()
    {
        BlueprintFacade::make('user')->setContents([
            'fields' => [
                ['handle' => 'name', 'field' => ['type' => 'text']],
                ['handle' => 'email', 'field' => ['type' => 'text'], 'validate' => 'required'],
            ],
        ])->save();

        FieldsetFacade::make('test')->setContents([
            'fields' => [
                ['handle' => 'foo', 'field' => ['type' => 'text']],
                ['handle' => 'bar', 'field' => ['type' => 'textarea', 'validate' => 'required']],
            ],
        ])->save();

        $this->assertCount(0, BlueprintModel::all());
        $this->assertCount(0, FieldsetModel::all());

        $this->artisan('statamic:eloquent:import-blueprints')
            ->expectsOutput('Blueprints imported')
            ->expectsOutput('Fieldsets imported')
            ->assertExitCode(0);

        $this->assertCount(2, BlueprintModel::all()); // It'll be 2, since Statamic will generate a default blueprint.
        $this->assertCount(1, FieldsetModel::all());
    }
}