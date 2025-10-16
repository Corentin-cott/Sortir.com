<?php

namespace App\Security\Voter;

use App\Entity\Commentaire;
use App\Entity\Participant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class CommentVoter extends Voter
{
    public const string DELETE = 'DELETE_COMMENT';
    public const string POST = 'POST_COMMENT';

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly AccessDecisionManagerInterface $accessDecisionManager)
    {}
    protected function supports(string $attribute, mixed $subject): bool
    {
        if (in_array($attribute, [self::POST,self::DELETE], true)) {
            return true;
        }
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::POST, self::DELETE])
            && $subject instanceof Commentaire;
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
            self::POST => $this->canPost($user, $token),
            self::DELETE => $this->canDelete($subject, $user, $token),
            default => false,
        };

    }

    private function canPost(UserInterface $user, TokenInterface $token): bool{
        $utilisateur = $this->entityManager->getRepository(Participant::class)->find($user);
        return $this->accessDecisionManager->decide($token, ['ROLE_USER']) && $utilisateur->isActif();
    }

    private function canDelete(Commentaire $comment, UserInterface $user, TokenInterface $token): bool{
        $utilisateur = $this->entityManager->getRepository(Participant::class)->find($user);

        if(!$utilisateur->isActif()){
            return false;
        }
        if($comment->getParticipant() === $utilisateur){
            return true;
        }
        $organisateur = $this->entityManager->getRepository(Participant::class)->find($comment->getSortie()->getOrganisateur()->getId());
        if($organisateur === $utilisateur){
            return true;
        }
        if($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])){
            return true;
        }
        return false;
    }




}
