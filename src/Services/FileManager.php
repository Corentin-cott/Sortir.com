<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileManager
{
    public function __construct(private SluggerInterface $slugger){

    }
    public function upload(UploadedFile $file, $directory, int $id): string
    {
        $title = $this->slugger->slug(($id).'_'.uniqid()).".".$file->guessExtension();
        $file->move($directory, $title);
        return $title;
    }
    public function remove(string $directory, int $id) :void {
        if(\file_exists($directory.'/'.$id)){
            unlink($directory.'/'.$id);
        }
    }

}