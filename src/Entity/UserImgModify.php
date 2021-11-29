<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class UserImgModify
{
    /**
     * @Assert\NotBlank(message="Veuillez ajouter une image")
     * @Assert\Image(mimeTypes={"image/png","image/jpeg","image/jpg","image/gif"}, mimeTypesMessage="Vous devez upload un fichier jpg, jpeg, png ou gif")
     * @Assert\File(maxSize="1024k", maxSizeMessage="La taille du fichier est trop grande")
     */
    private $newPicture;

    public function getNewPicture()
    {
        return $this->newPicture;
    }

    public function setNewPicture($newPicture)
    {
        $this->newPicture = $newPicture;
        return $this;
    }


}