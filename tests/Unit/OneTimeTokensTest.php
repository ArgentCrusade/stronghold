<?php

namespace ArgentCrusade\Stronghold\Tests\Unit;

use ArgentCrusade\Stronghold\Events\OneTimeTokenCreated;
use ArgentCrusade\Stronghold\OneTimeToken;
use ArgentCrusade\Stronghold\OneTimeTokensGenerator;
use ArgentCrusade\Stronghold\Tests\Fakes\User;
use ArgentCrusade\Stronghold\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

class OneTimeTokensTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param int $min
     * @param int $max
     * @dataProvider oneTimeTokensDataProvider
     */
    public function testItShouldGenerateOneTimeTokens(int $min, int $max)
    {
        $user = factory(User::class)->create();
        $generator = $this->createTokensGenerator($min, $max);

        $result = $generator->generate($user, 'test');
        $this->assertInstanceOf(OneTimeToken::class, $result);
        $this->assertInternalType('integer', $result->code);
        $this->assertTrue($result->code >= $min);
        $this->assertTrue($result->code <= $max);
    }

    public function testItCanHavePayload()
    {
        $user = factory(User::class)->create();
        $generator = $this->createTokensGenerator();

        $result = $generator->generate($user, 'test', json_encode(['test' => 'example']));
        $this->assertInstanceOf(OneTimeToken::class, $result);
        $this->assertSame(['test' => 'example'], json_decode($result->payload, true));
    }

    /**
     * @param int $min
     * @param int $max
     * @dataProvider minMaxSettingsDataProvider
     */
    public function testItShouldAllowToRefreshMinMaxSettingsOnTheFly(int $min, int $max)
    {
        $user = factory(User::class)->create();
        $generator = $this->createTokensGenerator(1, 5);
        $first = $generator->generate($user, 'test', json_encode(['test' => 'example']));
        $this->assertTrue($first->code >= 1);
        $this->assertTrue($first->code <= 5);

        $generator->using($min, $max);

        $first = $generator->generate($user, 'test2', json_encode(['test' => 'example']));
        $this->assertTrue($first->code >= $min);
        $this->assertTrue($first->code <= $max);
    }

    public function testItShouldInvalidateUnusedTokensPerOperation()
    {
        $user = factory(User::class)->create();
        $generator = $this->createTokensGenerator();

        $first = $generator->generate($user, 'first');
        $second = $generator->generate($user, 'second');
        $this->assertNotSame($first->id, $second->id);

        $third = $generator->generate($user, 'first');
        $this->assertSame($first->id, $third->id);

        $this->assertSame(1, $generator->invalidateUnused($user, 'first'));

        $fourth = $generator->generate($user, 'first');
        $this->assertNotSame($first->id, $fourth->id);
    }

    public function testItShouldInvalidateUnusedTokensForAllOperations()
    {
        $user = factory(User::class)->create();
        $generator = $this->createTokensGenerator();

        $first = $generator->generate($user, 'first');
        $second = $generator->generate($user, 'second');

        $this->assertNotSame($first->id, $second->id);

        $this->assertSame(2, OneTimeToken::unusedFor($user->id)->count());

        $generator->invalidateUnused($user);
        $this->assertSame(0, OneTimeToken::unusedFor($user->id)->count());
    }

    public function testItShouldEmitTokenCreatedEvent()
    {
        Event::fake();

        $user = factory(User::class)->create();
        $generator = $this->createTokensGenerator();

        $result = $generator->generate($user, 'test');
        $this->assertInstanceOf(OneTimeToken::class, $result);

        Event::assertDispatched(OneTimeTokenCreated::class, function (OneTimeTokenCreated $event) use ($result) {
            return $event->token->id === $result->id;
        });
    }

    /**
     * @param int $min
     * @param int $max
     * @dataProvider oneTimeTokensDataProvider
     */
    public function testOneTimeTokenCanBeRetrievedByIdentifier(int $min, int $max)
    {
        $user = factory(User::class)->create();
        $generator = $this->createTokensGenerator($min, $max);

        $result = $generator->generate($user, 'test');
        $this->assertInstanceOf(OneTimeToken::class, $result);

        $token = OneTimeToken::identifiedBy($result->identifier);
        $this->assertInstanceOf(OneTimeToken::class, $token);
    }

    public function testItShouldCheckCodes()
    {
        $user = factory(User::class)->create();
        $generator = $this->createTokensGenerator();

        $result = $generator->generate($user, 'test');
        $this->assertInstanceOf(OneTimeToken::class, $result);

        $this->assertFalse(OneTimeToken::codeMatches($result->identifier, $result->code - 1));
        $this->assertTrue(OneTimeToken::codeMatches($result->identifier, $result->code));
    }

    public function testItShouldNotGenerateDuplicatesIfUserHasUnusedTokenForTheSameOperation()
    {
        $user = factory(User::class)->create();
        $generator = $this->createTokensGenerator();

        $firstToken = $generator->generate($user, 'test');
        $this->assertInstanceOf(OneTimeToken::class, $firstToken);

        $secondToken = $generator->generate($user, 'test');
        $this->assertInstanceOf(OneTimeToken::class, $secondToken);

        $this->assertSame($firstToken->id, $secondToken->id);
    }

    public function testItShouldGenerateNewTokensForDifferentOperations()
    {
        $user = factory(User::class)->create();
        $generator = $this->createTokensGenerator();

        $firstToken = $generator->generate($user, 'first');
        $this->assertInstanceOf(OneTimeToken::class, $firstToken);

        $secondToken = $generator->generate($user, 'second');
        $this->assertInstanceOf(OneTimeToken::class, $secondToken);

        $this->assertNotSame($firstToken->id, $secondToken->id);
    }

    public function oneTimeTokensDataProvider()
    {
        return [
            ['min' => 1000, 'max' => 9999],
            ['min' => 5000, 'max' => 15000],
            ['min' => 1, 'max' => 5],
        ];
    }

    public function minMaxSettingsDataProvider()
    {
        return [
            ['min' => 1000, 'max' => 9999],
            ['min' => 10000, 'max' => 99999],
            ['min' => 5000, 'max' => 100000],
            ['min' => 6, 'max' => 10],
        ];
    }
}
