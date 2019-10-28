<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;


class BlogController extends AbstractController
{
    /**
     * @Route("/blog", name="blog")
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
     * @Route("/create-post", name="create_post")
     */
    public function createPost():Response
    {
        //$this->getDoctrine()を通してEntityManagerを取得。
        //Doctrineを介してデータベースにオブジェクトを保存したり、データベースからオブジェクトを取得したりする。
        $entityManager = $this->getDoctrine()->getManager();

        $newPost = new Post();
        $newPost -> setTitle('ブログはじめました。');
        $newPost -> setContent('日々の出来事や趣味などを投稿していきます。');
        $newPost -> setCreatedAt(new \DateTime());

        //newPostを保存するためにDoctrineと対話する。
        //newPostを管理下に置く。クエリは作成されない。
        $entityManager->persist($newPost);

        //INSERTクエリを実行
        //newPostオブジェクトのデータはデータベースに存在しないため、entityManagerはINSERTクエリを実行し、テーブルに新しい行を追加する。
        $entityManager->flush();

        return new Response('保存しました。');


    }


}
