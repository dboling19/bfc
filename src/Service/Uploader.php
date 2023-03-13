<?php

namespace App\Service;

use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\HttpFoundation\File\File;
use League\Flysystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class Uploader
{

  private $filesystem;

  public function __construct(Filesystem $filesystem)
  {
    $this->filesystem = $filesystem;

  }

  public function uploadFile(File $file, string $name, string $dir): string
  {

    $new_filename = Urlizer::urlize($name).'-'.uniqid().'.'.$file->guessExtension();

    $stream = fopen($file->getPathname(), 'r');
    $this->filesystem->writeStream(
      $dir.'/'.$new_filename,
      $stream,
    );
    if (is_resource($stream)) 
    {
      fclose($stream);
    }

    return $new_filename;
  }

  public function downloadFile($file)
  {
    $resource = $this->filesystem->readStream('/'.$file->getFilename());

    if ($resource === false)
    {
      throw new \Exception(sprintf('Error opening stream for "%s"', $file));
    }

    return $resource;
  }

}