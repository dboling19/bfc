<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Repository\FileRepository;
use App\Repository\DirectoryRepository;
use App\Entity\Directory;
use App\Entity\File;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use League\Flysystem\Filesystem;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Service\Uploader;
use App\Service\Fileinfo;


class FileController extends AbstractController
{

  private $root_dir;
  private $em;
  private $dir_repo;
  private $file_repo;
  private $request_stack;
  private $uploader;


  public function __construct(Fileinfo $fileinfo, Uploader $uploader, Filesystem $filesystem, ContainerBagInterface $params, ManagerRegistry $doctrine, FileRepository $file_repo, DirectoryRepository $dir_repo, RequestStack $request_stack)
  { 
    $this->root_dir = $params->get('app.root_dir');
    $this->em = $doctrine->getManager();
    $this->dir_repo = $dir_repo;
    $this->file_repo = $file_repo;
    $this->request_stack = $request_stack;
    $this->filesystem = $filesystem;
    $this->uploader = $uploader;
    $this->fileinfo = $fileinfo;

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
        if (isset($params['filename']) == false || in_array($params['filename'], ['', ' ', null]))
        {
          $file->setName($result->getClientOriginalName());
        } else {
          $file->setName($params['filename']);
        }
        $file->setSize($this->fileinfo->formatBytes($result->getSize()));
        $file->setDateCreated(new \DateTime(date('Y-m-d H:i:s', $result->getCTime())));
        $file->setDateModified(new \DateTime(date('Y-m-d H:i:s', $result->getMTime())));
        $file->setNotes('Test File');
        $dir = $this->dir_repo->findOneBy(['name' => basename($this->request_stack->getSession()->get('dir'))]);
        $file->setDirectory($dir);
        $file->setMimeType($result->getMimeType() ?? 'application/octet-stream');
        // setting database info

        $filename = $this->uploader->uploadFile($result, $file->getName(), '');
        $file->setFileName($filename);

        $this->em->persist($file);
        $this->em->flush();

      }
    }
    
    return $this->redirectToRoute('home');
  }

  /**
   * Download selected file
   * 
   * @author Daniel Boling
   */
  #[Route('/download/{id}', name: 'download', methods: ['GET'])]
  public function download_file(int $id, Uploader $uploader): Response
  {

    $file = $this->file_repo->find($id);
    $response = new StreamedResponse(function() use ($file, $uploader) {
      $output_stream = fopen('php://output', 'wb');
      $file_stream = $uploader->downloadFile($file);

      stream_copy_to_stream($file_stream, $output_stream);
    });
    $response->headers->set('Content-Type', $file->getMimeType());
    $disposition = HeaderUtils::makeDisposition(
      HeaderUtils::DISPOSITION_INLINE,
      $file->getName(),
    );
    $response->headers->set('Content-Disposition', $disposition);
  

    return $response;
  }

}


// EOF
