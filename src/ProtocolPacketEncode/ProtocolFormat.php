<?php

namespace ProtocolPacketEncode;


class ProtocolFormat
{
    // 暂时支持的数据类型
    public const DataTypeInt8 = 1;
    public const DataTypeUint8 = 2;
    public const DataTypeInt16 = 3;
    public const DataTypeUint16 = 4;
    public const DataTypeInt32 = 5;
    public const DataTypeUint32 = 6;
    public const DataTypeInt64 = 7;
    public const DataTypeUint64 = 8;
    public const DataTypeFloat = 9;
    public const DataTypeDouble = 10;
    // 该字段前面需要有一个 uint32 的长度字段，表示这种可变长度类型的实际长度。
    public const DataTypeString = 11;

    protected array $format = [];

    public function addField(int $type, string $name, bool $isFixedLength = true, int $realityLength = 0, bool $isArray = false, int $arrayLength = 0, bool $isInject = true): self
    {
        $this->format[$name] = new ProtocolFieldFormat($type, $name, $isFixedLength, $realityLength, $isArray, $arrayLength, $isInject);

        return $this;
    }

    public function getFormat(): array
    {
        return $this->format;
    }
}