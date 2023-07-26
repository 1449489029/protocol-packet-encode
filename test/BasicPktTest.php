<?php

namespace test;

use PHPUnit\Framework\TestCase;

class BasicPktTest extends TestCase
{

    // 测试Ping消息的打包与解包 带有内容
    public function testPackAndUnpackPingMessageWhitBody()
    {
        // 数据准备
        $code = BasicPkt::PING; // 2 字节
        // 隐藏字段(Body Length) 2 字节
        $body = 'sdasdasdasdas'; // 13 字节
        $bytesLength = 2 + 2 + 13;


        // 打包
        $basicPktProtocol = new BasicPkt();
        $basicPktProtocol->setCode($code);
        $basicPktProtocol->setBody($body);
        $string = $basicPktProtocol->encode();

        // 验证编码后的长度
        $this->assertEquals(strlen($string), $bytesLength);

        // 解包
        $newBasicPktProtocol = new BasicPkt();
        $newBasicPktProtocol->decode($string);

        // 验证解包后的长度
        $this->assertEquals(0, strlen($string));

        // 验证
        $this->assertEquals($newBasicPktProtocol->getCode(), $code);
        $this->assertEquals($newBasicPktProtocol->getBody(), $body);
    }

    // 测试Pong消息的打包与解包 带有内容
    public function testPackAndUnpackPongMessageWhitBody()
    {
        // 数据准备
        $code = BasicPkt::PONG; // 2 字节
        // 隐藏字段(Body Length)    2 字节
        $body = 'dasdasdasdas'; // 12 字节
        $bytesLength = 2 + 2 + 12;

        // 打包
        $basicPktProtocol = new BasicPkt();
        $basicPktProtocol->setCode($code);
        $basicPktProtocol->setBody($body);
        $string = $basicPktProtocol->encode();

        // 验证编码后的长度
        $this->assertEquals(strlen($string), $bytesLength);

        // 解包
        $newBasicPktProtocol = new BasicPkt();
        $newBasicPktProtocol->decode($string);

        // 验证解包后的长度
        $this->assertEquals(0, strlen($string));

        // 验证
        $this->assertEquals($newBasicPktProtocol->getCode(), $code);
        $this->assertEquals($newBasicPktProtocol->getBody(), $body);
    }
}