<?php

namespace App\Security\Voter;

use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class SortieVoter extends Voter
{
    public const CREATE = 'SORTIE_CREATE';
    public const EDIT = 'SORTIE_EDIT';
    public const DELETE = 'SORTIE_WITHDRAW';
    public const SIGNIN = 'SORTIE_SIGNIN';
    public const SIGNOUT = 'SORTIE_SIGNOUT';

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly AccessDecisionManagerInterface $accessDecisionManager)
    {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (in_array($attribute, [self::CREATE, self::SIGNIN, self::SIGNOUT], true)) {
            return true;
        }
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::DELETE ], true)
            && $subject instanceof Sortie;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        return match ($attribute) {
            self::EDIT => $this->canEdit($subject, $user),
            self::CREATE => $this->canCreate($token, $user),
            self::DELETE => $this->canDelete($subject, $user, $token),
            self::SIGNIN => $this->canSignin($subject, $user, $token),
            self::SIGNOUT => $this->canSignout($subject, $user, $token),
            default => false,
        };

    }
    private function canEdit(Sortie $sortie, UserInterface $user): bool
    {
        $utilisateur = $this->entityManager->getRepository(Participant::class)->find($user);
        return $utilisateur->getId() === $sortie->getOrganisateur()->getId();
    }

    private function canCreate(TokenInterface $token, UserInterface $user): bool
    {
        $utilisateur = $this->entityManager->getRepository(Participant::class)->find($user);
        return $this->accessDecisionManager->decide($token,['ROLE_USER']) && $utilisateur->isActif();
    }

    private function canDelete(Sortie $sortie, UserInterface $user, TokenInterface $token): bool
    {
        $utilisateur = $this->entityManager->getRepository(Participant::class)->find($user);
        if($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])){
            return true;
        }
        return $sortie->getOrganisateur()->getId() === $utilisateur->getId();
    }

    private function canSignin(Sortie $sortie, UserInterface $user, TokenInterface $token): bool
    {
        $utilisateur = $this->entityManager->getRepository(Participant::class)->find($user);

        if(!$this->accessDecisionManager->decide($token, ['ROLE_USER'])){
            return false;
        }
        if($sortie->getEtat()->getLibelle() !== 'Ouverte' )
        {
            return false;
        }
        if(!$utilisateur->isActif())
        {
            return false;
        }
        return true;
    }

    private function canSignout(Sortie $sortie, UserInterface $user, TokenInterface $token): bool
    {
        $utilisateur = $this->entityManager->getRepository(Participant::class)->find($user);
        if(!$this->accessDecisionManager->decide($token, ['ROLE_USER'])){
            return false;
        }
        if(!$sortie->estInscrit($utilisateur)){
            return false;
        }
        return true;
    }

}
