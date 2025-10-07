<?php

namespace App\Security;

use App\Entity\Participant;
use App\Repository\ParticipantRepository;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;


class UserProvider implements UserProviderInterface
{

    public function __construct(private ParticipantRepository $participantRepository){}


    /**
     * @inheritDoc
     */
    public function refreshUser(UserInterface $user)
    {
       return $user;
    }

    /**
     * @inheritDoc
     */
    public function supportsClass(string $class)
    {
        return $class === Participant::class;
    }

    /**
     * @inheritDoc
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $participant = $this->participantRepository->findOneBy(['email' => $identifier]);
        if(!$participant){
            $participant = $this->participantRepository->findOneBy(['pseudo' => $identifier]);
        }
        if(!$participant){
            throw new UserNotFoundException(sprintf('User "%s" not found.', $identifier));
        }
        return $participant;
    }
}