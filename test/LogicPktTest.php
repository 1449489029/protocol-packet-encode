<?php

namespace test;

use PHPUnit\Framework\TestCase;

class LogicPktTest extends TestCase
{

    public function testLogicPkt()
    {
        $command = LogicPkt::COMMAND_LOGIN;
        $channelId = '1_2_3';
        $flag = LogicPkt::FLAG_REQUEST;
        $sequence = rand(1000, 9999);
        $status = 'OK';
        $destinationType = 1;
        $destination = [
            1, 2, 3, 4, 5
        ];
        $body = 'dasdasdasdqwqweqweqw';

        // 编码
        $logicPkt = new LogicPkt();
        $string = $logicPkt->setCommand(LogicPkt::COMMAND_LOGIN)->setChannelId($channelId)->setFlag($flag)->setSequence($sequence)
            ->setDestinationType($destinationType)
            ->setDestination($destination)
            ->setStatus($status)
            ->setBody($body)->encode();

        // 解码
        $newLogicPkt = new LogicPkt();
        $newLogicPkt->decode($string);


        // 验证
        $this->assertEquals($newLogicPkt->getCommand(), $command);
        $this->assertEquals($newLogicPkt->getChannelId(), $channelId);
        $this->assertEquals($newLogicPkt->getFlag(), $flag);
        $this->assertEquals($newLogicPkt->getSequence(), $sequence);
        $this->assertEquals($newLogicPkt->getStatus(), $status);
        $this->assertEquals($newLogicPkt->getDestinationType(), $destinationType);
        $this->assertIsArray($newLogicPkt->getDestination());
        $this->assertEquals($newLogicPkt->getDestination(), $destination);
        $this->assertEquals($newLogicPkt->getBody(), $body);

    }

    // 基准测试
    public function testBinaryBenchMark()
    {
        $array = [
            'command' => LogicPkt::COMMAND_LOGIN,
            'channelId' => '1_2_3',
            'flag' => LogicPkt::FLAG_REQUEST,
            'sequence' => rand(1000, 9999),
            'status' => 'OK',
            'destinationType' => 1,
            'destination' => [
                1, 2, 3, 4, 5
            ],
            'body' => 'dasdasdasdqwqweqweqw',
        ];

        // 编码
        $logicPkt = new LogicPkt();


        // 编码
        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        for ($i = 0; $i < 100000; $i++) {
            $string = $logicPkt->setCommand($array['command'])->setChannelId($array['channelId'])->setFlag($array['flag'])->setSequence($array['sequence'])
                ->setDestinationType($array['destinationType'])
                ->setDestination($array['destination'])
                ->setStatus($array['status'])
                ->setBody($array['body'])->encode();
        }
        var_export(sprintf(PHP_EOL . '二进制编码耗时：%f', (microtime(true) - $startTime)));
        var_export(sprintf(PHP_EOL . '二进制编码内存消耗：%f', (memory_get_usage() - $startMemory)));

        // 解码
        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        for ($i = 0; $i < 100000; $i++) {
            $newString = $string;
            $newLogicPkt = new LogicPkt();
            $newLogicPkt->decode($newString);
        }
        var_export(sprintf(PHP_EOL . '二进制解码耗时：%f', (microtime(true) - $startTime)));
        var_export(sprintf(PHP_EOL . '二进制解码内存消耗：%f', (memory_get_usage() - $startMemory)));


    }

    public function testJsonBenchMark()
    {
        $array = [
            'command' => LogicPkt::COMMAND_LOGIN,
            'channelId' => '1_2_3',
            'flag' => LogicPkt::FLAG_REQUEST,
            'sequence' => rand(1000, 9999),
            'status' => 'OK',
            'destinationType' => 1,
            'destination' => [
                1, 2, 3, 4, 5
            ],
            'body' => 'dasdasdasdqwqweqweqw',
        ];

        // 编码
        $logicPkt = new LogicPkt();


        // 编码
        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        for ($i = 0; $i < 100000; $i++) {
            $string = $logicPkt->setCommand(LogicPkt::COMMAND_LOGIN)->setChannelId($array['channelId'])->setFlag($array['flag'])->setSequence($array['sequence'])
                ->setDestinationType($array['destinationType'])
                ->setDestination($array['destination'])
                ->setStatus($array['status'])
                ->setBody($array['body'])->encode();
        }
        var_export(sprintf(PHP_EOL . 'JSON编码耗时：%f', (microtime(true) - $startTime)));
        var_export(sprintf(PHP_EOL . 'JSON编码内存消耗：%f', (memory_get_usage() - $startMemory)));

        // 解码
        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        for ($i = 0; $i < 100000; $i++) {
            $newString = $string;
            $newLogicPkt = new LogicPkt();
            $newLogicPkt->decode($newString);
        }
        var_export(sprintf(PHP_EOL . 'JSON解码耗时：%f', (microtime(true) - $startTime)));
        var_export(sprintf(PHP_EOL . 'JSON解码内存消耗：%f', (memory_get_usage() - $startMemory)));

    }
}