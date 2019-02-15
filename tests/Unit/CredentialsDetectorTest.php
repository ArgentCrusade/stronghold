<?php

namespace ArgentCrusade\Stronghold\Tests\Unit;

use ArgentCrusade\Stronghold\CredentialsDetector;
use ArgentCrusade\Stronghold\CredentialsDetectorResult;
use ArgentCrusade\Stronghold\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

class CredentialsDetectorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param array  $query
     * @param string $expectedType
     * @dataProvider authenticationMethodDataProvider
     */
    public function testItShouldDetectAuthenticationMethod(array $query, string $expectedType)
    {
        $request = new Request($query);
        $detector = new CredentialsDetector();

        $result = $detector->detect($request);
        $this->assertInstanceOf(CredentialsDetectorResult::class, $result);
        $this->assertSame($expectedType, $result->type);
    }

    /**
     * @param array $query
     * @param array $expected
     * @dataProvider credentialsDataProvider
     */
    public function testItShouldGenerateCredentialsArray(array $query, array $expected)
    {
        $request = new Request($query);
        $detector = new CredentialsDetector();

        $result = $detector->detect($request);
        $this->assertInstanceOf(CredentialsDetectorResult::class, $result);

        $actual = $result->credentials($query['password'] ?? '');
        $this->assertSame($expected, $actual);
    }

    public function authenticationMethodDataProvider()
    {
        return [
            [
                'query' => ['email' => 'hello@example.org'],
                'expectedType' => 'email',
            ],
            [
                'query' => ['phone' => '+79641234567'],
                'expectedType' => 'phone',
            ],
            [
                'query' => ['phone' => '+7 (964) 123-45-67'],
                'expectedType' => 'phone',
            ],
            [
                'query' => ['phone' => '+7 964 123-45-67'],
                'expectedType' => 'phone',
            ],
        ];
    }

    public function credentialsDataProvider()
    {
        return [
            [
                'query' => [
                    'email' => 'hello@example.org',
                    'password' => 'secret',
                ],
                'expected' => [
                    'email' => 'hello@example.org',
                    'password' => 'secret',
                ],
            ],
            [
                'query' => [
                    'phone' => '+79641234567',
                    'password' => 'secret',
                ],
                'expected' => [
                    'phone' => '+79641234567',
                    'password' => 'secret',
                ],
            ],
            [
                'query' => [
                    'phone' => '+7 (964) 123-45-67',
                    'password' => 'secret',
                ],
                'expected' => [
                    'phone' => '+79641234567',
                    'password' => 'secret',
                ],
            ],
        ];
    }
}
