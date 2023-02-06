<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use App\Repository\FileRepository;
use App\Repository\DirectoryRepository;
use App\Repository\FileDirRepository;
use App\Entity\Directory;
use App\Entity\File;
use App\Entity\FileDir;

class FileController extends AbstractController
{

  private $dir;

  public function __construct(ContainerBagInterface $params, ManagerRegistry $doctrine, FileRepository $file_repo, DirectoryRepository $dir_repo, FileDirRepository $file_dir_repo)
  { 
    $this->home_dir = $params->get('app.home_dir');
    $this->trash_dir = $params->get('app.trash_dir');
    $this->em = $doctrine->getManager();
    $this->dir_repo = $dir_repo;
    $this->file_repo = $file_repo;
    $this->file_dir_repo = $file_dir_repo;
  }

  /**
   * Controls file saving to the directory and database
   * 
   * @author Daniel Boling
   */
  #[Route('/upload', name: 'file_upload')]
  public function file_upload(Request $request): Response
  {

    if (($files = $request->files->all()) && ($params = $request->request->all()))
    // ensures a file and data is submitted
    {

      foreach ($files as $result) {
        $file = new File();
        $params['filename'] .= '.' . $result->getClientOriginalExtension();
        $file->setName($params['filename']);
        $file->setSize($this->formatBytes($result->getSize()));
        $file->setDateCreated(new \DateTime(date('Y-m-d H:i:s', $result->getCTime())));
        $file->setDateModified(new \DateTime(date('Y-m-d H:i:s', $result->getMTime())));
        $file->setTrash(false);
        $file->setNotes('Test File');
        $result->move($this->home_dir, $params['filename']);

        $this->em->persist($file);
        $this->em->flush();

      }
    }
    
    return $this->redirectToRoute('home');
  }


  /**
   * Moves a file to the app trash folder for deletion.
   * Another function will completely delete the file.
   * 
   * @author Daniel Boling
   */
  #[Route('/trash', name:'trash_file')]
  public function move_to_trash(Request $request): Response
  {
    if ($params = $request->request->all())
    {
      $file = $this->file_repo->find($params['file_id']);
      $finder = new Finder();
      $result = $finder->files()->name($file->getName());
      $result->move($this->trash_dir);
    }
  }


  public function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
  }

}


// EOF
