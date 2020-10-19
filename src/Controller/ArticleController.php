<?php 
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Article;
use App\Entity\Category;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class ArticleController extends AbstractController
{
    /**
     * @Route("/", name="artilce_list")
     * @Method({"GET"})
     */
    public function index()
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);

        $articles = $this->getDoctrine()->getRepository(Article::class)->findAll();
        // $articles = $this->getDoctrine()->getRepository(Article::class)->searchByTerm("haha");

       
        $response = JsonResponse::fromJsonString($serializer->serialize($articles, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['category']]));


        $response->headers->set('Access-Control-Allow-Origin', '*');
        
        return $response;
        
        // return $this->render('article/index.html.twig', array('articles' => $articles));
    }

    /**
     * @Route("/search/{term}", name="artilce_search")
     * @Method({"GET"})
     */
    public function search($term)
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);
        $articles = $this->getDoctrine()->getRepository(Article::class)->searchByTerm($term);

        $response = JsonResponse::fromJsonString($serializer->serialize($articles, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['category']]));

        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;



    }

    /**
     * @Route("/article/new", name="new_article")
     * @Method({"GET", "POST"})
     */
    public function new(Request $request)
    {
        $article = new Article();
        $form = $this->createFormBuilder($article)
            ->add('title', TextType::class, array('required' => false, 'constraints' => [new Assert\Length(['min' => 3])], 'attr' => array('class' => 'form-control')))
            ->add('body', TextareaType::class, array('required' => false ,'attr' => array('class' => 'form-control')))
            ->add('save', SubmitType::class, ['label' => 'Create', 'attr' => ['class' => 'btn btn-primary mt-3']])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $article = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($article);
            $entityManager->flush();

            $this->addFlash(
                'notice',
                'Your changes were saved!'
            );

            return $this->redirectToRoute('artilce_list');
        }
        return $this->render('article/new.html.twig', array(
            'form' => $form->createView()
        ));
    }


    /**
     * @Route("/article/catagory/new", name="new_article_category")
     * @Method({"POST", "OPTIONS"})
     */
    public function newArticleForCat(Request $request) {
        if($request->isMethod('OPTIONS')){
            $response = new Response();
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type');
            $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:4200');
            $response->headers->set('Access-Control-Allow-Methods', 'OPTIONS');
            $response->setStatusCode(Response::HTTP_OK);
            return $response;
        } else if($request->isMethod('POST'))
        {
       
            $requestContent = json_decode($request->getContent());
          
            $encoders = [new XmlEncoder(), new JsonEncoder()];
            $normalizers = [new ObjectNormalizer()];

            $serializer = new Serializer($normalizers, $encoders);
      
            $category = $this->getDoctrine()->getRepository(Category::class)->find($requestContent->catId);
        
            $article = new Article();
            $article->setTitle($requestContent->article->title);
        
            $article->setBody($requestContent->article->body);
            $article->setCategory($category);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($article);
            $entityManager->flush();

            $response = JsonResponse::fromJsonString($serializer->serialize($article, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['category']]));

            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type');
            $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:4200');
            
            return $response;
        }


    }

    /**
     * @Route("/article/edit/{id}", name="edit_article")
     * @Method({"PUT", "OPTIONS"})
     */
    public function edit(Request $request, $id)
    {
        if($request->isMethod('OPTIONS')){
            $response = new Response();
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type');
            $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:4200');
            $response->headers->set('Access-Control-Allow-Methods', 'OPTIONS, PUT');
            $response->setStatusCode(Response::HTTP_OK);
            return $response;
        } else if($request->isMethod('PUT'))
        {
            $requestContent = json_decode($request->getContent());
          
            $encoders = [new XmlEncoder(), new JsonEncoder()];
            $normalizers = [new ObjectNormalizer()];

            $serializer = new Serializer($normalizers, $encoders);
            $entityManager = $this->getDoctrine()->getManager();
            $article = $this->getDoctrine()->getRepository(Article::class)->find($id);
            $article->setTitle($requestContent->title);
            $article->setBody($requestContent->body);
            $entityManager->flush();
            $response = JsonResponse::fromJsonString($serializer->serialize($article, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['category']]));

            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type');
            $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:4200');
            
            return $response;


            // $form = $this->createFormBuilder($article)
            //     ->add('title', TextType::class, array('required' => false, 'constraints' => [new Assert\Length(['min' => 3])], 'attr' => array('class' => 'form-control')))
            //     ->add('body', TextareaType::class, array('required' => false ,'attr' => array('class' => 'form-control')))
            //     ->add('save', SubmitType::class, ['label' => 'edit', 'attr' => ['class' => 'btn btn-primary mt-3']])
            //     ->getForm();
            // $form->handleRequest($request);
            // if ($form->isSubmitted() && $form->isValid()) 
            // {
            //     $entityManager = $this->getDoctrine()->getManager();
            //     $entityManager->flush();
            //     return $this->redirectToRoute('artilce_list');
            // }

            // return $this->render('article/edit.html.twig', array(
            //     'form' => $form->createView()
            // ));
        }
    }

    /**
     * @Route("/article/{id}", name="artilce_show")
     * @Method({"GET"})
     */
    public function show($id)
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $article = $this->getDoctrine()->getRepository(Article::class)->find($id);
        $response = JsonResponse::fromJsonString($serializer->serialize($article, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['category']]));
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type');
        $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:4200');
        
        return $response;

        // return $this->render('article/show.html.twig', array('article' => $article));
    }

    /**
     * @Route("/article/delete/{id}", name="artilce_delete")
     * @Method({"DELETE", "OPTIONS"})
     */
    public function delete(Request $request, $id)
    {
        if($request->isMethod('OPTIONS')){
            $response = new Response();
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type');
            $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:4200');
            $response->headers->set('Access-Control-Allow-Methods', 'OPTIONS, DELETE');
            $response->setStatusCode(Response::HTTP_OK);
            return $response;
        } else if($request->isMethod('DELETE'))
        {
            $encoders = [new XmlEncoder(), new JsonEncoder()];
            $normalizers = [new ObjectNormalizer()];
            $serializer = new Serializer($normalizers, $encoders);
            $article = $this->getDoctrine()->getRepository(Article::class)->find($id);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($article);
            $entityManager->flush();
            $response = JsonResponse::fromJsonString($serializer->serialize($article, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['category']]));
            $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:4200');
            return $response;
        }
    }

    // /**
    //  * @Route("/article/save")
    //  */
    // public function save()
    // {
    //     $entityManager = $this->getDoctrine()->getManager();
    //     $article = new Article();
    //     $article->setTitle('Article 2');
    //     $article->setBody('This is article');
    //     $entityManager->persist($article);
    //     $entityManager->flush();
    //     return new Response('article with id '. $article->getId() .' has been saved');

    // }

}