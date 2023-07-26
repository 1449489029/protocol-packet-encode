<?php

namespace test;

use ProtocolPacketEncode\Protocol;
use ProtocolPacketEncode\ProtocolFormat;
use ProtocolPacketEncode\BinaryPacket;

class BasicPkt extends Protocol
{
    // 代码类型
    public const PING = 1;
    public const PONG = 2;

    // 代码
    public int $code;
    // 消息载体
    public string $body = '';


    /**
     * 定义协议包格式
     *
     * @return void
     */
    protected function definePacketFormat(): void
    {
        $protocolFormat = (new ProtocolFormat())
            ->addField(ProtocolFormat::DataTypeUint16, 'code')
            ->addField(ProtocolFormat::DataTypeString, 'body', false);
        $this->packet = new BinaryPacket($protocolFormat);
    }


    public function getCode(): int
    {
        return $this->code;
    }

    public function setCode(int $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }
}