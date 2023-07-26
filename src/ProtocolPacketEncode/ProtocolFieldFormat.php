<?php

namespace ProtocolPacketEncode;

class ProtocolFieldFormat
{
    // 字段名称
    public string $name;
    // 字段类型
    public int $type;
    // 是否是固定长度
    public bool $isFixedLength = true;
    // 个别字段类型设置在固定长度时的
    // 例如：(ProtocolFormat::DataTypeString|ProtocolFormat::DataTypeArrayUint32|ProtocolFormat::DataTypeBuffer)
    public int $realityLength = 0;
    // 是否是数组
    public bool $isArray = false;
    // 数组长度
    public int $arrayLength = 0;

    // 数据序列化出来后，是否需要注入到协议包对象里。
    public bool $isInject = true;


    public function __construct(
        int    $type,
        string $name,
        bool   $isFixedLength = true,
        int    $realityLength = 0,
        bool   $isArray = false,
        int    $arrayLength = 0,
        bool   $isInject = true
    )
    {
        $this->name = $name;
        $this->type = $type;
        $this->isFixedLength = $isFixedLength;
        $this->realityLength = $realityLength;
        $this->isArray = $isArray;
        $this->arrayLength = $arrayLength;
        $this->isInject = $isInject;
    }
}