<?php

namespace test;

use PHPUnit\Framework\TestCase;

class BasicPktTest extends TestCase
{

    // 测试Ping消息的打包与解包 带有内容
    public function testPackAndUnpackPingMessageWhitBody()
    {
        // 数据准备
        $code = BasicPkt::PING;
        $body = 'sdasdasdasdas';

        // 打包
        $basicPktProtocol = new BasicPkt();
        $basicPktProtocol->setCode($code);
        $basicPktProtocol->setBody($body);
        $string = $basicPktProtocol->encode();

        // 解包
        $newBasicPktProtocol = new BasicPkt();
        $newBasicPktProtocol->decode($string);

        // 验证
        $this->assertEquals($newBasicPktProtocol->getCode(), $code);
        $this->assertEquals($newBasicPktProtocol->getBody(), $body);
    }

    // 测试Pong消息的打包与解包 带有内容
    public function testPackAndUnpackPongMessageWhitBody()
    {
        // 数据准备
        $code = BasicPkt::PONG;
        $body = 'dasdasdasdas';

        // 打包
        $basicPktProtocol = new BasicPkt();
        $basicPktProtocol->setCode($code);
        $basicPktProtocol->setBody($body);
        $string = $basicPktProtocol->encode();

        // 解包
        $newBasicPktProtocol = new BasicPkt();
        $newBasicPktProtocol->decode($string);

        // 验证
        $this->assertEquals($newBasicPktProtocol->getCode(), $code);
        $this->assertEquals($newBasicPktProtocol->getBody(), $body);
    }
}