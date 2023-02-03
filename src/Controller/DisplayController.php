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


class DisplayController extends AbstractController
{

  private $dir;

  public function __construct(ContainerBagInterface $params, ManagerRegistry $doctrine, FileRepository $file_repo, DirectoryRepository $dir_repo, FileDirRepository $file_dir_repo)
  { 
    $this->dir = $params->get('app.content_dir');
    $this->em = $doctrine->getManager();
    $this->dir_repo = $dir_repo;
    $this->file_repo = $file_repo;
    $this->file_dir_repo = $file_dir_repo;
  }

  /**
   * Controls the main file display, search, and center page
   * 
   * @author Daniel Boling
   */
  #[Route('/', name: 'home')]
  public function home(Request $request): Response
  {

    $filesystem = new Filesystem();
    if (!$filesystem->exists($this->dir))
    // if the system dir does not exist, create it
    {
      $filesystem->mkdir($this->dir);
    }

    if (!$this->dir_repo->findBy(['name' => $this->dir]))
    // if directory does not exist in database create it
    {
      $dir = new Directory();
      $dir->setName($this->dir);
      $dir->setNotes('Home directory');
      
      $this->em->persist($dir);
      $this->em->flush();
    }

    // $finder = new Finder();
    // $finder->in($this->dir);

    // if ($finder->hasResults()) {
    //   $files = iterator_to_array($finder->getIterator());
    // } else {
    //   $files = null;
    // }

    $file = null;

    if ($params = $request->query->all())
    {
      $file = $this->file_repo->find($params['id']);
    }

    
    $files = $this->file_repo->findAll();


    return $this->render('display/home.html.twig', [
      'files' => $files,
      'file' => $file,
    ]);
  }
}
