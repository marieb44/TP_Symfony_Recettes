<?php

namespace App\Tests\Entity;

use App\Entity\Recette;
use PHPUnit\Framework\TestCase;

class RecetteTest extends TestCase
{
    public function test(): void
    {
        $recette = new Recette();
        $recette->setNom("Curry de pois chiches");

        $this->assertEquals($recette->getNom(), "Curry de pois chiches");
    }
}
