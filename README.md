# Async SMPP v3.4 protocol implementation for PHP.

See the [specification](https://smpp.org/SMPP_v3_4_Issue1_2.pdf) for more information about protocol.

### Contents

- [Installation](#installation)
- [Requirements](#requirements)
- [Features](#features)
- [Usage](#usage)
  - [Receiver](#receiver)
  - [Transmitter](#transmitter)
  - [Transceiver](#transceiver)
  - [Signals](#signals)
  - [Extensions](#extensions)
  - [Heartbeat](#heartbeat)
- [Testing](#testing)
- [License](#license)

## Installation

```bash
composer require operation-hardcode/smpp-php
```

## Requirements

This library requires PHP 8.1 or later.

It is recommended to install the [phpinnacle/ext-buffer](https://github.com/phpinnacle/ext-buffer) extension to speed up the [phpinnacle/buffer](https://github.com/phpinnacle/buffer).

## Features

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

### Receiver

-------

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

-------

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

-------

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

### Signals

-------

```php
<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use Amp;
use OperationHardcode\Smpp\Interaction\Connector;
use OperationHardcode\Smpp\Transport\ConnectionContext;
use OperationHardcode\Smpp;

Amp\Loop::run(function (): \Generator {
    $logger = stdoutLogger('transceiver');

    $transceiver = Connector::connect()
        ->asTransceiver(
            ConnectionContext::default(uri: 'smscsim.melroselabs.com:2775', systemId: '900238', password: 'c58775'),
            $logger
        );

    Amp\Loop::unreference(
        Amp\Loop::onSignal(\SIGINT, function () use ($transceiver): \Generator {
            yield $transceiver->fin();
        })
    );

    try {
        yield $transceiver->consume(function (PDU $pdu, SmppExecutor $executor): \Generator {
           if ($pdu instanceof Smpp\Protocol\Command\Replyable) {
                $reply = $pdu->reply();

                yield $executor->produce($reply);
            }

            return new Amp\Success();
        });
    } catch (\Throwable $e) {
        echo $e->getMessage() . \PHP_EOL;

        Amp\Loop::stop();
    }
});
```

### Extensions

-------

If you need more options when working with library, you can write an extension. The library provides 4 hooks that are called by the executor at different times of their work:

- If you want to extend the behaviour on successful connection, implement the `OperationHardcode\Smpp\Interaction\Extensions\AfterConnectionEstablishedExtension` interface.
- If you want to extend the behaviour on disconnection, implement the `OperationHardcode\Smpp\Interaction\Extensions\AfterConnectionClosedExtension` interface.
- If you want to extend the behaviour on each `PDU` produced by executor, implement the `OperationHardcode\Smpp\Interaction\Extensions\AfterPduProducedExtension` interface.
- Or vice versa, if you want to extend the behaviour on each `PDU` consumed by executor, implement the `OperationHardcode\Smpp\Interaction\Extensions\AfterPduConsumedExtension` interface.

```php
<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use Amp;
use OperationHardcode\Smpp\Interaction\Connector;
use OperationHardcode\Smpp\Transport\ConnectionContext;
use OperationHardcode\Smpp;
use OperationHardcode\Smpp\Interaction\SmppExecutor;
use OperationHardcode\Smpp\Protocol\PDU;
use Psr\Log\LoggerInterface;
use OperationHardcode\Smpp\Interaction\Extensions\AfterConnectionEstablishedExtension;
use OperationHardcode\Smpp\Interaction\Extensions\AfterConnectionClosedExtension;
use OperationHardcode\Smpp\Interaction\Extensions\AfterPduConsumedExtension;
use OperationHardcode\Smpp\Interaction\Extensions\AfterPduProducedExtension;

final class Debug implements
    AfterConnectionEstablishedExtension,
    AfterConnectionClosedExtension,
    AfterPduConsumedExtension,
    AfterPduProducedExtension
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function afterConnectionEstablished(SmppExecutor $smppExecutor): Amp\Promise
    {
        return Amp\call(function (): void {
           $this->logger->debug('Connection was established.');
        });
    }

    public function afterConnectionClosed(?\Throwable $e = null): Amp\Promise
    {
        return Amp\call(function () use ($e): void {
            $this->logger->debug('Connection was closed.');
        });
    }

    public function afterPduConsumed(PDU $pdu, SmppExecutor $smppExecutor): Amp\Promise
    {
        return Amp\call(function () use ($pdu): void {
            $this->logger->debug('The pdu "{pdu}" was consumed.', [
                'pdu' => get_class($pdu),
            ]);
        });
    }

    public function afterPduProduced(PDU $pdu, SmppExecutor $smppExecutor): Amp\Promise
    {
        return Amp\call(function () use ($pdu): void {
            $this->logger->debug('The pdu "{pdu}" was produced.', [
                'pdu' => get_class($pdu),
            ]);
        });
    }
}

Amp\Loop::run(function (): \Generator {
    $logger = stdoutLogger('transceiver');

    $transceiver = Connector::connect()
        ->asTransceiver(
            ConnectionContext::default(uri: 'smscsim.melroselabs.com:2775', systemId: '900238', password: 'c58775'),
            $logger
        )
        ->withExtensions([
            new Debug($logger),
        ]);

    Amp\Loop::unreference(
        Amp\Loop::onSignal(\SIGINT, function () use ($transceiver): \Generator {
            yield $transceiver->fin();
        })
    );

    try {
        yield $transceiver->consume(function (PDU $pdu, SmppExecutor $executor): \Generator {
           if ($pdu instanceof Smpp\Protocol\Command\Replyable) {
                $reply = $pdu->reply();

                yield $executor->produce($reply);
            }

            return new Amp\Success();
        });
    } catch (\Throwable $e) {
        echo $e->getMessage() . \PHP_EOL;

        Amp\Loop::stop();
    }
});
```

### Heartbeat

-------

The library provides the `Heartbeat` extension which periodically send the `ENQUIRE_LINK` command, required by SMPP specification.
You can configure the interval and timeout within which you must receive a `ENQUIRE_LINK_RESP` with command status `ESME_ROK`.

```php
<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use Amp;
use OperationHardcode\Smpp\Interaction\Connector;
use OperationHardcode\Smpp\Transport\ConnectionContext;
use OperationHardcode\Smpp\Interaction\SmppExecutor;
use OperationHardcode\Smpp\Interaction\Heartbeat\Heartbeat;
use OperationHardcode\Smpp\Time;

Amp\Loop::run(function (): \Generator {
    $logger = stdoutLogger('transceiver');

    $transceiver = Connector::connect()
        ->asTransceiver(
            ConnectionContext::default(uri: 'smscsim.melroselabs.com:2775', systemId: '900238', password: 'c58775'),
            $logger
        )
        ->withExtensions([
            new Heartbeat(
                Time::fromSeconds(10), // interval
                Time::fromSeconds(2), // timeout
                $logger,
            ),
        ]);

    Amp\Loop::unreference(
        Amp\Loop::onSignal(\SIGINT, function () use ($transceiver): \Generator {
            yield $transceiver->fin();
        })
    );

    try {
        yield $transceiver->consume(function (PDU $pdu, SmppExecutor $executor): \Generator {
           if ($pdu instanceof Smpp\Protocol\Command\Replyable) {
                $reply = $pdu->reply();

                yield $executor->produce($reply);
            }

            return new Amp\Success();
        });
    } catch (\Throwable $e) {
        echo $e->getMessage() . \PHP_EOL;

        Amp\Loop::stop();
    }
});
```

## Testing

``` bash
$ composer test
```  

## License

The MIT License (MIT). See [License File](LICENSE) for more information.