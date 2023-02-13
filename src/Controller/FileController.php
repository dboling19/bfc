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
use Symfony\Component\HttpFoundation\RequestStack;
use App\Repository\FileRepository;
use App\Repository\DirectoryRepository;
use App\Entity\Directory;
use App\Entity\File;

class FileController extends AbstractController
{

  private $root_dir;
  private $em;
  private $dir_repo;
  private $file_repo;
  private $request_stack;


  public function __construct(ContainerBagInterface $params, ManagerRegistry $doctrine, FileRepository $file_repo, DirectoryRepository $dir_repo, RequestStack $request_stack)
  { 
    $this->root_dir = $params->get('app.root_dir');
    $this->em = $doctrine->getManager();
    $this->dir_repo = $dir_repo;
    $this->file_repo = $file_repo;
    $this->request_stack = $request_stack;

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
        // retains the file extension and appends to the filename
        $file->setName($params['filename']);
        $file->setSize($this->formatBytes($result->getSize()));
        $file->setDateCreated(new \DateTime(date('Y-m-d H:i:s', $result->getCTime())));
        $file->setDateModified(new \DateTime(date('Y-m-d H:i:s', $result->getMTime())));
        $file->setNotes('Test File');
        $dir = $this->dir_repo->findOneBy(['name' => basename($this->request_stack->getSession()->get('dir'))]);
        $file->setDirectory($dir);
        $result->move($this->root_dir, $params['filename']);
        
        $this->em->persist($file);
        $this->em->flush();

      }
    }
    
    return $this->redirectToRoute('home');
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
