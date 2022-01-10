# Async SMPP v3.4 protocol implementation for PHP.

See the [specification](https://smpp.org/SMPP_v3_4_Issue1_2.pdf) for more information about protocol.

### Contents

- [Installation](#installation)
- [Requirements](#requirements)
- [Features](#features)
- [Usage](#usage)
- [Testing](#testing)
- [License](#license)

## Installation

--------

```bash
composer require operation-hardcode/smpp-php
```

## Requirements

--------

This library requires PHP 8.1 or later.

It is recommended to install the [phpinnacle/ext-buffer](https://github.com/phpinnacle/ext-buffer) extension to speed up the [phpinnacle/buffer](https://github.com/phpinnacle/buffer).

## Features

--------

- [x] BIND_RECEIVER
- [x] BIND_TRANSMITTER
- [x] BIND_TRANSCEIVER
- [x] ALERT_NOTIFICATION
- [x] CANCEL_SM
- [x] DATA_SM
- [x] DELIVER_SM
- [x] ENQUIRE_LINK
- [x] GENERIC_NACK
- [x] OUTBIND
- [x] QUERY_SM
- [x] REPLACE_SM
- [x] SUBMIT_SM
- [x] UNBIND
- [ ] SUBMIT_MULTI

## Usage

--------

### Receiver

```php
<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use OperationHardcode\Smpp;
use OperationHardcode\Smpp\Interaction\Connector;
use OperationHardcode\Smpp\Protocol\PDU;
use OperationHardcode\Smpp\Transport\ConnectionContext;

Amp\Loop::run(function (): \Generator {
    $executor = Connector::connect()
        ->asReceiver(ConnectionContext::default(uri: 'smscsim.melroselabs.com:2775', systemId: '900238', password: 'c58775'));

    try {
        yield $executor->consume(function (PDU $pdu, Smpp\Interaction\SmppExecutor $executor): \Generator {
            var_dump($pdu);

            yield $executor->fin();
        });
    } catch (Smpp\Interaction\ConnectionWasNotEstablished) {
        yield $executor->fin();

        Amp\Loop::stop();
    }
});
```

### Transmitter

```php
<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use OperationHardcode\Smpp\Interaction\Connector;
use OperationHardcode\Smpp\Protocol\Command\SubmitSm;
use OperationHardcode\Smpp\Protocol\Destination;
use OperationHardcode\Smpp\Transport\ConnectionContext;

Amp\Loop::run(function (): \Generator {
    $transmitter = Connector::connect()
        ->asTransmitter(ConnectionContext::default(uri: 'smscsim.melroselabs.com:2775', systemId: '900238', password: 'c58775'));

    yield $transmitter->produce(new SubmitSm(new Destination('xxxx'), new Destination('xxxxx'), 'Hello, world'));
    yield $transmitter->fin();
});
```

### Transceiver

```php
<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\PsrLogMessageProcessor;
use OperationHardcode\Smpp;
use OperationHardcode\Smpp\Interaction\Connector;
use OperationHardcode\Smpp\Protocol\PDU;
use OperationHardcode\Smpp\Transport\ConnectionContext;
use Psr\Log\LoggerInterface;

function stdoutLogger(string $loggerName): LoggerInterface
{
    $handler = new StreamHandler(Amp\ByteStream\getStdout());
    $handler->setFormatter(new ConsoleFormatter());

    return new Logger($loggerName, [$handler], [new PsrLogMessageProcessor(), new MemoryUsageProcessor(), new MemoryPeakUsageProcessor()]);
}

Amp\Loop::run(function (): \Generator {
    $transceiver = Connector::connect()->asTransceiver(
        ConnectionContext::default(uri: 'smscsim.melroselabs.com:2775', systemId: '900238', password: 'c58775'),
        stdoutLogger('transceiver'),
    );

    try {
        yield $transceiver->consume(function (PDU $pdu, Smpp\Interaction\SmppExecutor $executor): \Generator {
            if ($pdu instanceof Smpp\Protocol\Command\Replyable) {
                yield $executor->produce($pdu->reply());
            }

            var_dump($pdu);

            return new Amp\Success();
        });
    } catch (\Throwable $e) {
        echo $e->getMessage() . \PHP_EOL;

        Amp\Loop::stop();
    }
});
```

## Testing

--------

``` bash
$ composer test
```  

## License

--------

The MIT License (MIT). See [License File](LICENSE) for more information.