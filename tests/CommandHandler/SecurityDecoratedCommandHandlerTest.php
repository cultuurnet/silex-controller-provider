<?php
/**
 * @file
 */

namespace CultuurNet\UDB3SilexEntryAPI\CommandHandler;

use Broadway\CommandHandling\CommandHandlerInterface;
use CultuurNet\UDB3\Event\SecurityInterface;
use CultuurNet\UDB3\EventXmlString;
use CultuurNet\UDB3SilexEntryAPI\Event\Commands\UpdateEventFromCdbXml;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use ValueObjects\String\String;

class SecurityDecoratedCommandHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SecurityDecoratedCommandHandler
     */
    private $commandHandler;

    /**
     * @var SecurityInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $security;

    /**
     * @var CommandHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $wrappedCommandHandler;

    public function setUp()
    {
        $this->security = $this->getMockBuilder(SecurityInterface::class)
            ->setMethods(array('allowsUpdateWithCdbXml'))
            ->getMock();

        $this->wrappedCommandHandler = $this->getMock(CommandHandlerInterface::class);

        $this->commandHandler = new SecurityDecoratedCommandHandler(
            $this->wrappedCommandHandler,
            $this->security
        );
    }

    /**
     * @test
     */
    public function it_checks_security_for_an_update_from_cdbxml_command()
    {
        $this->setExpectedException(AccessDeniedHttpException::class);

        $command = new UpdateEventFromCdbXml(
            new String('foo'),
            new EventXmlString(
                file_get_contents(__DIR__ . '/Valid.xml')
            )
        );

        $this->security->expects($this->once())
            ->method('allowsUpdateWithCdbXml')
            ->with(new String('foo'))
            ->willReturn(
                false
            );

        $this->wrappedCommandHandler->expects($this->never())
            ->method('handle');

        $this->commandHandler->handle($command);
    }

    /**
     * @test
     */
    public function it_should_pass_the_original_update_from_cdbxml_command_to_the_wrapped_command_handler()
    {
        $command = new UpdateEventFromCdbXml(
            new String('foo'),
            new EventXmlString(
                file_get_contents(__DIR__ . '/Valid.xml')
            )
        );

        $this->security->expects($this->once())
            ->method('allowsUpdateWithCdbXml')
            ->with(new String('foo'))
            ->willReturn(
                true
            );

        $this->wrappedCommandHandler->expects($this->once())
            ->method('handle')
            ->with($command);

        $this->commandHandler->handle($command);
    }
}
