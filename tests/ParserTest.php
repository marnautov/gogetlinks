<?php

use PHPUnit\Framework\TestCase;

use Amxm\Gogetlinks\Parser;

class ParserTest extends TestCase {

    private function getSnipped($filename) 
    {
        $filePath = __DIR__ . '/snippets/' . $filename;

        if (!file_exists($filePath)) {
            $this->fail("Snippet file not found: $filename");
        }

        return file_get_contents($filePath);
    }

    public function testHasAuthenticatedMarkup()
    {
        $html = $this->getSnipped('is-logged.html');
        $this->assertTrue(Parser::hasAuthenticatedMarkup($html));

        $html = file_get_contents(__DIR__.'/snippets/not-logged-signin-page.html');
        $this->assertFalse(Parser::hasAuthenticatedMarkup($html));      
    }

    public function testMySites () {

        $sites = Parser::parseSites($this->getSnipped('mysites.html'));
        $this->assertCount(9, $sites);

        $testArray = [
            'domain'    =>  'itpressa.ru',
            'site_id'   =>  353793,
            'status'    =>  'AVAILABLE',
            'yandex_iks'=>  150,
            'tc_cf'     =>  6,
            'pr_cy'     =>  63,
            'traffic'   =>  89,
            'trust'     =>  7,
            'posting_speed' =>  7.0
        ];

        // $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($testArray, $sites[1], array_keys($testArray));
        $this->assertArrayIsIdenticalToArrayOnlyConsideringListOfKeys($testArray, $sites[1], array_keys($testArray));

        // dd($sites[1]);

    }

    public function testMoneyBlock ()
    {
        $this->assertSame(5.32, Parser::parseMoneyBlock('<span>5.32 руб.</span>'));
        $this->assertSame(5.32, Parser::parseMoneyBlock('<span>5,32 руб.</span>'));
        $this->assertSame(5.0, Parser::parseMoneyBlock('<span>5 руб.</span>'));
        $this->assertSame(0.35, Parser::parseMoneyBlock('<span>.35 руб.</span>'));
        $this->assertSame(195861.75, Parser::parseMoneyBlock('<span>баланс: 195 861.75</span>'));
        $this->assertSame(0.0, Parser::parseMoneyBlock('<span>0 </span>'));
        $this->assertSame(null, Parser::parseMoneyBlock('<span>  </span>'));
    }

    public function testParseMoneyBlock()
    {
        $info = Parser::parseBalance($this->getSnipped('money-block.html'));
        $this->assertSame(1232.84, $info['all']);
        $this->assertSame(1232.84, $info['open']);
        $this->assertSame(0.0, $info['block']);
        $this->assertSame(49.99, $info['expected']);
    }
    
    public function testParseTaskTabs()
    {
        $html = $this->getSnipped('task-tabs.html');
        $tasksInfo = Parser::parseTaskTabs($html);
        $this->assertEquals('Новые', $tasksInfo['active']);

    }
    



}