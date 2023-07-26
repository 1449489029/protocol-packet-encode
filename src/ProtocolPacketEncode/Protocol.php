<?php

namespace ProtocolPacketEncode;

abstract class Protocol
{
    protected PacketInterface $packet;

    public function __construct()
    {
        $this->definePacketFormat();
    }

    /**
     * 定义协议包格式
     *
     * @return void
     */
    abstract protected function definePacketFormat(): void;

    /**
     * 编码
     *
     * @return string 编码后的字符串
     */
    public function encode(): string
    {
        return $this->packet->encode($this);
    }

    /**
     * 解码
     *
     * @param string $data
     * @return void
     */
    public function decode(string $data): void
    {
        $this->packet->decode($this, $data);
    }
}