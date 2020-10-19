<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Category;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;


class CategoryController extends AbstractController
{
    /**
     * @Route("/category", name="category")
     * @Method({"GET"})
     */
    public function index()
    {
        $category = $this->getDoctrine()->getRepository(Category::class)->findAll();
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $response = JsonResponse::fromJsonString($serializer->serialize($category, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['articles']]));
        $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:4200');

        return $response;
        
    }

    /**
     * @Route("/category/{id}", name="category_articles")
     * @Method({"GET"})
     */
    public function getArticlesByCat($id)
    {
        $category = $this->getDoctrine()->getRepository(Category::class)->find($id);
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $articles = $category->getArticles();
        $response = JsonResponse::fromJsonString($serializer->serialize($articles, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['category']]));
        $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:4200');

        return $response;
        
    }
}
