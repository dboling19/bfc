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
use App\Repository\DocRepository;
use App\Repository\DirectoryRepository;
use App\Repository\TagRepository;
use App\Entity\Directory;
use App\Entity\Doc;
use App\Entity\Tag;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use League\Flysystem\Filesystem;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Service\Uploader;
use App\Service\Fileinfo;


class FileController extends AbstractController
{

  private $em;
  private $dir_repo;
  private $file_repo;
  private $tag_repo;
  private $request_stack;
  private $uploader;
  private $fileinfo;
  private $filesystem;
  private $root_dir;


  public function __construct(Fileinfo $fileinfo, Uploader $uploader, Filesystem $filesystem, ContainerBagInterface $params, ManagerRegistry $doctrine, TagRepository $tag_repo, DocRepository $file_repo, DirectoryRepository $dir_repo, RequestStack $request_stack)
  { 
    $this->root_dir = $params->get('app.root_dir');
    $this->em = $doctrine->getManager();
    $this->dir_repo = $dir_repo;
    $this->file_repo = $file_repo;
    $this->tag_repo = $tag_repo;
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
      // dd($params);
      foreach ($files['file'] as $result)
      {
        $file = new Doc();
        if (!isset($params['name']) || in_array($params['name'], ['', ' ', null]))
        {
          $file->setName(explode('.', $result->getClientOriginalName())[0]);
        } else {
          $file->setName(trim($params['name']));
        }
        $file->setSize($this->fileinfo->formatBytes($result->getSize()));
        $file->setDateCreated(new \DateTime(date('Y-m-d H:i:s', $result->getCTime())));
        $file->setDateModified(new \DateTime(date('Y-m-d H:i:s', $result->getMTime())));
        foreach (explode(',', $params['file_selected_tags']) as $tag)
        {
          $file->addTag($this->tag_repo->findOneBy(['name' => $tag]));
        }

        $cwd_id = $this->request_stack->getSession()->get('cwd_id');
        $cwd = $this->request_stack->getSession()->get('cwd');
        $dir = $this->dir_repo->find($cwd_id);
        $file->setDirectory($dir);
        $file->setMimeType($result->getMimeType() ?? 'application/octet-stream');
        // setting database info

        $cwd = str_replace($this->root_dir, '', $cwd);
        $filename = $this->uploader->uploadFile($result, $file->getName(), $cwd);
        $file->setFileName($filename);

        $this->em->persist($file);
        $this->em->flush();

      }
    }
    
    return $this->redirectToRoute('folder_display', [
      'id' => $cwd_id,
    ]);
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
    $cwd_id = $this->request_stack->getSession()->get('cwd_id');
    $cwd = $this->dir_repo->find($cwd_id)->getPath();
    $cwd = str_replace($this->root_dir, '', $cwd);
    $response = new StreamedResponse(function() use ($file, $uploader, $cwd) {
      $output_stream = fopen('php://output', 'wb');
      $file_stream = $uploader->downloadFile($file, $cwd);

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
