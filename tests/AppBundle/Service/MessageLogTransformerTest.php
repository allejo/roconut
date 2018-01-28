<?php

/**
 * @copyright 2017-2018 Vladimir Jimenez
 * @license   https://github.com/allejo/roconut/blob/master/LICENSE.md MIT
 */

declare(strict_types=1);

namespace Tests\AppBundle\Service;

use AppBundle\MessageLogFilter\ServerMessageFilter;
use AppBundle\Service\AnsiHtmlTransformer;
use AppBundle\Service\MessageLogTransformer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

class MessageLogTransformerTest extends TestCase
{
    private static function ansiToHtml(string $log): string
    {
        $converter = new AnsiHtmlTransformer();

        return $converter->convert($log);
    }

    public static function messageLogTransformerDataProvider(): array
    {
        $testDefinitions = [];

        $finder = new Finder();
        $files = $finder
            ->in(__DIR__ . '/MessageLogTransformerTests')
            ->name('*.yml')
            ->files()
        ;

        $logTransformer = new \ReflectionClass(MessageLogTransformer::class);
        $constants = $logTransformer->getConstants();

        $msgTransformer = new MessageLogTransformer();
        $msgTransformer
            ->registerMessageFilter(new ServerMessageFilter())
        ;

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $definition = Yaml::parse($file->getContents(), Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);

            $cleanedUp = str_replace('\e', "\e", $definition['original']);
            $chatFilter = array_reduce($definition['filters'] ?? [], function ($a, $b) use ($constants) {
                return $a | $constants[$b];
            });

            $msgTransformer->setRawMessage(self::ansiToHtml($cleanedUp));
            $filteredMessage = $msgTransformer->filterLog($chatFilter)->displayMessages();

            $actualMessage = htmlspecialchars_decode(strip_tags($filteredMessage), ENT_QUOTES | ENT_HTML5);
            $actualMessage = preg_replace('#\s*\R#', "\n", $actualMessage);

            $testDefinitions[] = [
                $definition['expected'],
                $actualMessage,
                $file->getFilename(),
            ];
        }

        return $testDefinitions;
    }

    /**
     * @dataProvider messageLogTransformerDataProvider
     */
    public function testExpectedLogMessagesFromDefinitionFiles(string $expected, string $actual, string $filename)
    {
        $this->assertEquals($expected, $actual, "The assertion for the '${filename}' test case failed.");
    }

    ///
    // Specific tests that aren't defined in YAML files
    ///

    public function testGettingPrivateMessages()
    {
        $chat = <<<FEED
\e[38;2;255;255;255m\e[5m[02345n-xOwU->]\e[0;1m \e[36mmessage from 02345n\e[0;1m
\e[38;2;255;255;255m\e[5m[Bertman->]\e[0;1m \e[36mmessage from Bertman\e[0;1m
\e[38;2;255;255;255m\e[5m[->02345n-xOwU][action message from me]\e[0;1m
\e[38;2;255;255;255m\e[5m[02345n-xOwU->]\e[0;1m \e[36mmessage from 02345n\e[0;1m
\e[38;2;255;255;255m\e[5m[->Bertman]\e[0;1m \e[36mmessage to Bertman\e[0;1m
\e[38;2;255;255;255m\e[5m[Bertman->]\e[0;1m \e[36mmessage from Bertman\e[0;1m
\e[38;2;255;255;255m\e[5m[02345n-xOwU->]\e[0;1m \e[36mmessage from 02345n\e[0;1m
\e[38;2;255;255;255m\e[5m[02345n-xOwU->]\e[0;1m \e[36mmessage from 02345n\e[0;1m
FEED;
        $converted = self::ansiToHtml($chat);
        $transformer = new MessageLogTransformer();
        $transformer->setRawMessage($converted);
        $conversations = $transformer->findPrivateMessages();

        $this->assertCount(2, $conversations);
        $this->assertContains('Bertman', $conversations);
        $this->assertContains('02345n-xOwU', $conversations);
    }

    public function testFilterPrivateMessages()
    {
        $chat = <<<FEED
\e[38;2;255;255;255m\e[5m[02345n-xOwU->]\e[0;1m \e[36mmessage from 02345n\e[0;1m
\e[38;2;255;255;255m\e[5m[Bertman->]\e[0;1m \e[36mmessage from Bertman\e[0;1m
\e[38;2;255;255;255m\e[5m[->02345n-xOwU][action message from me]\e[0;1m
\e[38;2;255;255;255m\e[5m[02345n-xOwU->]\e[0;1m \e[36mmessage from 02345n\e[0;1m
\e[38;2;255;255;255m\e[5m[->Bertman]\e[0;1m \e[36mmessage to Bertman\e[0;1m
\e[38;2;255;255;255m\e[5m[Bertman->]\e[0;1m \e[36mmessage from Bertman\e[0;1m
\e[38;2;255;255;255m\e[5m[02345n-xOwU->]\e[0;1m \e[36mmessage from 02345n\e[0;1m
\e[38;2;255;255;255m\e[5m[02345n-xOwU->]\e[0;1m \e[36mmessage from 02345n\e[0;1m
FEED;
        $converted = self::ansiToHtml($chat);
        $transformer = new MessageLogTransformer();
        $transformer->setRawMessage($converted);
        $conversations = $transformer
            ->filterPrivateMessages(['Bertman'])
            ->displayMessages()
        ;

        $this->assertNotContains('02345n-xOwU', $conversations);
    }
}
