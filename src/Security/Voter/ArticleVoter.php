<?php

namespace App\Security\Voter;

use App\Entity\Article;
use App\Entity\Author;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ArticleVoter extends Voter
{
    public const EDIT = 'POST_EDIT';
    public const NEW = 'POST_NEW';


    protected function supports(string $attribute, $subject): bool
    {

        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT])
            && $subject instanceof Article;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /**
         * @var Author $user
         */
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        /**
         * @var Article $article
         */
        $article = $subject;

        // ... (check conditions and return true to grant permission) ...

           if($attribute === self::EDIT){

              return $user->getId()=== $article->getAuthor()->getId();

           }

                // logic to determine if the user can EDIT
                // return true or false



        return false;
    }
}
