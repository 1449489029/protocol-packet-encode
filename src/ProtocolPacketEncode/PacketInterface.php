<?php

namespace ProtocolPacketEncode;

interface PacketInterface
{
    /**
     * 创建一个数据包对象
     *
     * @param ProtocolFormat $format 协议格式定义
     */
    public function __construct(ProtocolFormat $format);

    /**
     * 编码
     *
     * @param Protocol $protocol 需要编码的协议包对象
     * @return string 序列化后的字符串
     */
    public function encode(Protocol $protocol): string;

    /**
     * 解码
     *
     * @param Protocol $protocol 协议包对象 => 在解码后会将数据注入到协议包中
     * @param string $data 需要解析的协议包
     * @return void
     */
    public function decode(Protocol $protocol, string $data): void;
}