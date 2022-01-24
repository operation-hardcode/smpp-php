<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol;

enum DataCoding: int
{
    case DATA_CODING_DEFAULT = 0;
    case DATA_CODING_IA5 = 1;
    case DATA_CODING_BINARY_ALIAS = 2;
    case DATA_CODING_ISO8859_1 = 3;
    case DATA_CODING_BINARY = 4;
    case DATA_CODING_JIS = 5;
    case DATA_CODING_ISO8859_5 = 6;
    case DATA_CODING_ISO8859_8 = 7;
    case DATA_CODING_UCS2 = 8;
    case DATA_CODING_PICTOGRAM = 9;
    case DATA_CODING_ISO2022_JP = 10;
    case DATA_CODING_KANJI = 13;
    case DATA_CODING_KSC5601 = 14;
}
