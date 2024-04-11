<?php

namespace Tests\Commands;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Entries\CollectionRepository as CollectionRepositoryContract;
use Statamic\Contracts\Entries\Collection as CollectionContract;
use Statamic\Contracts\Entries\EntryRepository as EntryRepositoryContract;
use Statamic\Contracts\Structures\CollectionTreeRepository as CollectionTreeRepositoryContract;
use Statamic\Contracts\Structures\CollectionTree as CollectionTreeContract;
use Statamic\Eloquent\Collections\CollectionModel;
use Statamic\Eloquent\Structures\TreeModel;
use Statamic\Facades\Collection as CollectionFacade;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Facades\Entry;
use Statamic\Structures\CollectionStructure;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ImportCollectionsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        Facade::clearResolvedInstance(CollectionRepositoryContract::class);
        Facade::clearResolvedInstance(CollectionTreeRepositoryContract::class);

        app()->bind(CollectionContract::class, \Statamic\Entries\Collection::class);
        app()->bind(CollectionTreeContract::class, \Statamic\Structures\CollectionTree::class);
        app()->bind(CollectionRepositoryContract::class, \Statamic\Stache\Repositories\CollectionRepository::class);
        app()->bind(CollectionTreeRepositoryContract::class, \Statamic\Stache\Repositories\CollectionTreeRepository::class);

        // TODO: Can we try running these tests with both Stache & Eloquent entries?
        app()->bind(EntryRepositoryContract::class, \Statamic\Stache\Repositories\EntryRepository::class);
        app()->bind(EntryContract::class, \Statamic\Entries\Entry::class);
    }

    /** @test */
    public function it_imports_collections_and_collection_trees()
    {
        $collection = tap(\Statamic\Facades\Collection::make('pages')->title('Pages'))->save();
        $collection->structure(new CollectionStructure)->save();

        Entry::make()->collection($collection)->id('foo')->save();
        Entry::make()->collection($collection)->id('bar')->save();

        $collection->structure()->in('en')->tree([
            ['entry' => 'foo'],
            ['entry' => 'bar'],
        ])->save();

        $this->assertCount(0, CollectionModel::all());
        $this->assertCount(0, TreeModel::all());

        $this->artisan('statamic:eloquent:import-collections')
            ->expectsQuestion('Do you want to import collections?', true)
            ->expectsQuestion('Do you want to import collections trees?', true)
            ->expectsOutput('Collections imported')
            ->assertExitCode(0);

        $this->assertCount(1, CollectionModel::all());
        $this->assertCount(1, TreeModel::all());
    }

    /** @test */
    public function it_imports_collections_and_collection_trees_with_force_argument()
    {
        $collection = tap(\Statamic\Facades\Collection::make('pages')->title('Pages'))->save();
        $collection->structure(new CollectionStructure)->save();

        Entry::make()->collection($collection)->id('foo')->save();
        Entry::make()->collection($collection)->id('bar')->save();

        $collection->structure()->in('en')->tree([
            ['entry' => 'foo'],
            ['entry' => 'bar'],
        ])->save();

        $this->assertCount(0, CollectionModel::all());
        $this->assertCount(0, TreeModel::all());

        $this->artisan('statamic:eloquent:import-collections', ['--force' => true])
            ->expectsOutput('Collections imported')
            ->assertExitCode(0);

        $this->assertCount(1, CollectionModel::all());
        $this->assertCount(1, TreeModel::all());
    }

    /** @test */
    public function it_imports_collections_with_console_question()
    {
        $collection = tap(\Statamic\Facades\Collection::make('pages')->title('Pages'))->save();
        $collection->structure(new CollectionStructure)->save();

        Entry::make()->collection($collection)->id('foo')->save();
        Entry::make()->collection($collection)->id('bar')->save();

        $collection->structure()->in('en')->tree([
            ['entry' => 'foo'],
            ['entry' => 'bar'],
        ])->save();

        $this->assertCount(0, CollectionModel::all());
        $this->assertCount(0, TreeModel::all());

        $this->artisan('statamic:eloquent:import-collections')
            ->expectsQuestion('Do you want to import collections?', true)
            ->expectsQuestion('Do you want to import collections trees?', false)
            ->expectsOutput('Collections imported')
            ->assertExitCode(0);

        $this->assertCount(1, CollectionModel::all());
        $this->assertCount(0, TreeModel::all());
    }

    /** @test */
    public function it_imports_collections_with_only_collections_argument()
    {
        $collection = tap(\Statamic\Facades\Collection::make('pages')->title('Pages'))->save();
        $collection->structure(new CollectionStructure)->save();

        Entry::make()->collection($collection)->id('foo')->save();
        Entry::make()->collection($collection)->id('bar')->save();

        $collection->structure()->in('en')->tree([
            ['entry' => 'foo'],
            ['entry' => 'bar'],
        ])->save();

        $this->assertCount(0, CollectionModel::all());
        $this->assertCount(0, TreeModel::all());

        $this->artisan('statamic:eloquent:import-collections', ['--only-collections' => true])
            ->expectsOutput('Collections imported')
            ->assertExitCode(0);

        $this->assertCount(1, CollectionModel::all());
        $this->assertCount(0, TreeModel::all());
    }

    /** @test */
    public function it_imports_collection_trees_with_console_question()
    {
        $collection = tap(\Statamic\Facades\Collection::make('pages')->title('Pages'))->save();
        $collection->structure(new CollectionStructure)->save();

        Entry::make()->collection($collection)->id('foo')->save();
        Entry::make()->collection($collection)->id('bar')->save();

        $collection->structure()->in('en')->tree([
            ['entry' => 'foo'],
            ['entry' => 'bar'],
        ])->save();

        $this->assertCount(0, CollectionModel::all());
        $this->assertCount(0, TreeModel::all());

        $this->artisan('statamic:eloquent:import-collections')
            ->expectsQuestion('Do you want to import collections?', false)
            ->expectsQuestion('Do you want to import collections trees?', true)
            ->expectsOutput('Collections imported')
            ->assertExitCode(0);

        $this->assertCount(0, CollectionModel::all());
        $this->assertCount(1, TreeModel::all());
    }

    /** @test */
    public function it_imports_collection_trees_with_only_collections_argument()
    {
        $collection = tap(\Statamic\Facades\Collection::make('pages')->title('Pages'))->save();
        $collection->structure(new CollectionStructure)->save();

        Entry::make()->collection($collection)->id('foo')->save();
        Entry::make()->collection($collection)->id('bar')->save();

        $collection->structure()->in('en')->tree([
            ['entry' => 'foo'],
            ['entry' => 'bar'],
        ])->save();

        $this->assertCount(0, CollectionModel::all());
        $this->assertCount(0, TreeModel::all());

        $this->artisan('statamic:eloquent:import-collections', ['--only-collection-trees' => true])
            ->expectsOutput('Collections imported')
            ->assertExitCode(0);

        $this->assertCount(0, CollectionModel::all());
        $this->assertCount(1, TreeModel::all());
    }
}