<?php

namespace App\Decorators;

class FavoriteServiceDecorator extends BaseServiceDecorator
{
    public function getUserFavorites($userId)
    {
        return $this->run('getUserFavorites', [$userId]);
    }

    public function addToFavorites($userId, $productId)
    {
        return $this->run('addToFavorites', [$userId, $productId]);
    }

    public function removeFromFavorites($userId, $productId)
    {
        return $this->run('removeFromFavorites', [$userId, $productId]);
    }
}
