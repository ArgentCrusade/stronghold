<?php

namespace ArgentCrusade\Stronghold\Tests;

use ArgentCrusade\Stronghold\OneTimeTokensGenerator;
use ArgentCrusade\Stronghold\Tests\Fakes\User;
use ArgentCrusade\Stronghold\Tests\Migrations\CreateUsersTable;
use ArgentCrusade\Stronghold\Providers\StrongholdServiceProvider;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Propaganistas\LaravelPhone\PhoneServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function setUp()
    {
        parent::setUp();

        (new CreateUsersTable())->up();

        $factory = app(Factory::class);
        $factory->define(User::class, function (Generator $faker) {
            return [
                'name' => $faker->name,
                'email' => $faker->safeEmail,
                'phone' => $faker->phoneNumber,
                'password' => '$2y$10$j.wyukOehQBns1QsohRbr.tC20lkpJL2bUuH8aNBhMMB4ffM7MTpe', // 'secret'
                'timezone' => $faker->timezone,
            ];
        });
    }

    protected function getPackageProviders($app)
    {
        return [
            StrongholdServiceProvider::class,
            PhoneServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /**
     * Create new instance of tokens generator.
     *
     * @param int $min = 1000
     * @param int $max = 9999
     *
     * @return OneTimeTokensGenerator
     */
    protected function createTokensGenerator(int $min = 1000, int $max = 9999)
    {
        /** @var OneTimeTokensGenerator $generator */
        $generator = app(OneTimeTokensGenerator::class);

        return $generator->using($min, $max);
    }
}
