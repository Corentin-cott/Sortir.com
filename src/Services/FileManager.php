<?php

namespace App\Services;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileManager
{
    public function __construct(private SluggerInterface $slugger, private readonly LoggerInterface $logger){

    }
    public function upload(UploadedFile $file, $directory, String $nom): string
    {
        $title = $this->slugger->slug(($nom).'_'.uniqid()).".".$file->guessExtension();
        $file->move($directory, $title);
        return $title;
    }
    public function remove(string $directory) :void {
        if(\file_exists($directory)) {
            unlink($directory);
        } else {
            $this->logger->warning("Fichier introuvable : " . $directory);
        }
    }

}