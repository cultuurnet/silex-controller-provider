<?php
/**
 * @file
 */

namespace CultuurNet\UDB3SilexEntryAPI\CommandHandler;

use Broadway\CommandHandling\CommandHandlerInterface;
use CultuurNet\UDB3\Event\SecurityInterface;
use CultuurNet\UDB3SilexEntryAPI\Event\Commands\UpdateEventFromCdbXml;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use ValueObjects\String\String;

class SecurityDecoratedCommandHandler implements CommandHandlerInterface
{
    /**
     * @var CommandHandlerInterface
     */
    private $wrapped;

    /**
     * @var SecurityInterface
     */
    private $security;

    /**
     * @param CommandHandlerInterface $wrapped
     * @param SecurityInterface $security
     */
    public function __construct(
        CommandHandlerInterface $wrapped,
        SecurityInterface $security
    ) {
        $this->wrapped = $wrapped;
        $this->security = $security;
    }

    /**
     * @inheritdoc
     */
    public function handle($command)
    {
        $this->guardPermissions($command);

        $this->wrapped->handle($command);
    }

    /**
     * @param $command
     */
    private function guardPermissions($command)
    {
        $allowed = true;

        if ($command instanceof UpdateEventFromCdbXml) {
            $allowed = $this->security->allowsUpdateWithCdbXml(
                $command->getEventId()
            );
        }

        if (!$allowed) {
            throw new AccessDeniedHttpException();
        }
    }
}
