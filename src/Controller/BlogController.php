<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Form\PostType;


class BlogController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        //全てのPOSTを取得する。
        $posts = $this->getDoctrine()
            ->getRepository(Post::class)
            ->findAll();

        return $this->render('blog/index.html.twig', [
            'controller_name' => 'BlogController',
            'posts' => $posts
        ]);
    }

    /**
     * @Route("/create", name="create_post")
     */
    public function create(Request $request):Response
    {

        $post = new Post();

        $form = $this->createForm(PostType::class,$post);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $post = $form->getData();

            $post->setCreatedAt(new \DateTime());


            //$this->getDoctrine()を通してEntityManagerを取得。
            //Doctrineを介してデータベースにオブジェクトを保存したり、データベースからオブジェクトを取得したりする。
            $entityManager = $this->getDoctrine()->getManager();

            //newPostを管理下に置く。クエリは作成されない。
            $entityManager->persist($post);

            //INSERTクエリを実行
            //newPostオブジェクトのデータはデータベースに存在しないため、entityManagerはINSERTクエリを実行し、テーブルに新しい行を追加する。
            $entityManager->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('blog/create_post.html.twig',[
           'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/{id}", name="edit_post")
     *
     */
    //postはParamConverterによって自動取得される。
    public function update(Request $request,Post $post,$id):Response
    {

        $entityManager = $this->getDoctrine()->getManager();

        $form = $this->createForm(PostType::class,$post);
        $form->handleRequest($request);

        //postが存在しない場合

        if(!$post){
            throw $this->createNotFoundException(
              'No post found for id'.$id
            );
        }

        if($form->isSubmitted() && $form->isValid()){
            $post = $form->getData();
            // $post->setUpdatedAt(new \DateTime());

            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('blog/edit_post.html.twig',[
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }


}
