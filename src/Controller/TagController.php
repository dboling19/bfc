<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Tag;
use App\Repository\TagRepository;
use Symfony\Component\Serializer\SerializerInterface;

class TagController extends AbstractController
{

  private $em;
  private $tag_repo;
  private $serializer;

  public function __construct(TagRepository $tag_repo, ManagerRegistry $doctrine, SerializerInterface $serializer)
  { 
    $this->em = $doctrine->getManager();
    $this->tag_repo = $tag_repo;
    $this->serializer = $serializer;
  }

  #[Route('/tags', name: 'tags_display')]
  public function tags_display(): Response
  {
    $tags = $this->tag_repo->findAll();
    $tags_array = [];
    foreach ($tags as $tag) 
    {
      $tags_array[] = $tag->getName();
    }

    return $this->render('displays/tags.html.twig', [
        'results' => $tags,
        'tags' => $tags_array,
    ]);
  }


  #[Route('/add_tag', name: 'add_tag')]
  public function add_tag(Request $request): Response
  {
    $params = $request->request->all();
    $tag = new Tag();
    $tag->setName(strtolower($params['tag_name']));
    $this->em->persist($tag);
    $this->em->flush();

    return $this->redirectToRoute('tags_display');

  }
}
