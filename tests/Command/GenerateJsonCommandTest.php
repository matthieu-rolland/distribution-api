<?php

declare(strict_types=1);

namespace Tests\Command;

use App\Command\GenerateJsonCommand;
use App\Util\ModuleUtils;
use Github\Client as GithubClient;
use GuzzleHttp\Client;
use Psssst\ModuleParser;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class GenerateJsonCommandTest extends AbstractCommandTestCase
{
    private GenerateJsonCommand $command;

    public function setUp(): void
    {
        parent::setUp();
        (new Filesystem())->remove((new Finder())->in(__DIR__ . '/../output'));
        $githubClient = $this->createMock(GithubClient::class);
        $moduleUtils = $this->getMockBuilder(ModuleUtils::class)
            ->setConstructorArgs([
                new ModuleParser(),
                $this->createMock(Client::class),
                $githubClient,
                __DIR__ . '/../ressources/modules',
                __DIR__ . '/../../var/tmp',
            ])
            ->onlyMethods(['download', 'getModules'])
            ->getMock()
        ;
        $moduleUtils->method('getModules')->willReturn([
            'autoupgrade' => ['v4.10.1', 'v4.11.0', 'v4.12.0'],
            'psgdpr' => ['v1.2.0', 'v1.2.1', 'v1.3.0'],
        ]);

        $this->command = $this->getMockBuilder(GenerateJsonCommand::class)
            ->setConstructorArgs([
                $moduleUtils,
                $githubClient,
                __DIR__ . '/../output',
            ])
            ->onlyMethods(['getPrestaShopVersions'])
            ->getMock()
        ;
        $this->command->method('getPrestaShopVersions')->willReturn([
            '1.6.1.4', '1.6.1.24', '1.7.0.0', '1.7.7.8', '1.7.8.1',
        ]);
    }

    public function testGenerateJson()
    {
        $this->command->execute($this->input, $this->output);
        $baseOutput = __DIR__ . '/../output';
        $baseExpected = __DIR__ . '/../ressources/json';

        $this->assertJsonFileEqualsJsonFile(
            $baseExpected . '/1.6.1.4/modules.json',
            $baseOutput . '/1.6.1.4/modules.json'
        );
        $this->assertJsonFileEqualsJsonFile(
            $baseExpected . '/1.6.1.4/modules.json',
            $baseOutput . '/1.6.1.24/modules.json'
        );
        $this->assertJsonFileEqualsJsonFile(
            $baseExpected . '/1.7.0.0/modules.json',
            $baseOutput . '/1.7.0.0/modules.json'
        );
        $this->assertJsonFileEqualsJsonFile(
            $baseExpected . '/1.7.7.8/modules.json',
            $baseOutput . '/1.7.7.8/modules.json'
        );
        $this->assertJsonFileEqualsJsonFile(
            $baseExpected . '/1.7.8.1/modules.json',
            $baseOutput . '/1.7.8.1/modules.json'
        );

        $this->assertJsonFileEqualsJsonFile(
            $baseExpected . '/autoupgrade/1.6.1.4.json',
            $baseOutput . '/autoupgrade/1.6.1.4.json'
        );
        $this->assertJsonFileEqualsJsonFile(
            $baseExpected . '/autoupgrade/1.6.1.24.json',
            $baseOutput . '/autoupgrade/1.6.1.24.json'
        );
        $this->assertJsonFileEqualsJsonFile(
            $baseExpected . '/autoupgrade/1.7.0.0.json',
            $baseOutput . '/autoupgrade/1.7.0.0.json'
        );
        $this->assertJsonFileEqualsJsonFile(
            $baseExpected . '/autoupgrade/1.7.7.8.json',
            $baseOutput . '/autoupgrade/1.7.7.8.json'
        );
        $this->assertJsonFileEqualsJsonFile(
            $baseExpected . '/autoupgrade/1.7.8.1.json',
            $baseOutput . '/autoupgrade/1.7.8.1.json'
        );
        $this->assertJsonFileEqualsJsonFile(
            $baseExpected . '/autoupgrade/latest.json',
            $baseOutput . '/autoupgrade/latest.json'
        );

        $this->assertFileDoesNotExist($baseOutput . '/psgdpr/1.6.1.4.json');
        $this->assertFileDoesNotExist($baseOutput . '/psgdpr/1.6.1.24.json');
        $this->assertJsonFileEqualsJsonFile(
            $baseExpected . '/psgdpr/1.7.0.0.json',
            $baseOutput . '/psgdpr/1.7.0.0.json'
        );
        $this->assertJsonFileEqualsJsonFile(
            $baseExpected . '/psgdpr/1.7.7.8.json',
            $baseOutput . '/psgdpr/1.7.7.8.json'
        );
        $this->assertJsonFileEqualsJsonFile(
            $baseExpected . '/psgdpr/1.7.8.1.json',
            $baseOutput . '/psgdpr/1.7.8.1.json'
        );
        $this->assertJsonFileEqualsJsonFile(
            $baseExpected . '/psgdpr/latest.json',
            $baseOutput . '/psgdpr/latest.json'
        );
    }
}