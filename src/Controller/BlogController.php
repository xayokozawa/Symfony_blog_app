<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use App\Repository\CategoryRepositoryRepository;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Post;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Form\PostType;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class BlogController
 * @package App\Controller
 */
class BlogController extends AbstractController
{
    /**
     * 記事を表示する最初のページの設定
     *
     *
     * @Route("/", name="blog_index", methods={"GET","POST"})
     * @param Request $request
     * @param PostRepository $postRepository
     * @param CategoryRepository $catogoryRepository
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function index(Request $request, PostRepository $postRepository ,CategoryRepository $categoryRepository, PaginatorInterface $paginator): Response
    {



        if($request->request->get('keyword'))
        {
            dump($request->request->get('keyword'));
            $keyword = $request->request->get('keyword');
            $postsQuery = $postRepository->createQueryBuilder('post')
                -> orderBy("post.created_at", "DESC")
                -> orWhere('post.category LIKE :keyword')
                -> orWhere('post.content LIKE :keyword')
                -> orWhere('post.title LIKE :keyword')
                -> setParameter('keyword', "%".$keyword."%");
        } else {
            $postsQuery = $postRepository->createQueryBuilder('post')
                ->orderBy("post.created_at", "DESC");
        }


        $posts = $paginator->paginate(
            $postsQuery,
            $request->query->getInt('page', 1),
            5
        );

        $categories = $categoryRepository->findAll();


        return $this->render('blog/index.html.twig', [
            'controller_name' => 'BlogController',
            'posts' => $posts,
            'categories' => $categories
        ]);
    }

    
    /**
     * 新規記事作成
     *
     * @Route("/new", name="post_new", methods={"GET","POST"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @throws Exception
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {

        $post = new Post();
        $category = new Category();

        $form = $this->createForm(PostType::class,$post);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $post = $form->getData();

            $post->setCreatedAt(new \DateTime());


            //newPostを管理下に置く。クエリは作成されない。
            $entityManager->persist($post);

            //INSERTクエリを実行
            //newPostオブジェクトのデータはデータベースに存在しないため、entityManagerはINSERTクエリを実行し、テーブルに新しい行を追加する。
            $entityManager->flush();

            $name = $request->request->get('post')['category'];
            dump($name);
            dump($request);
            $category->setName($name);
            $entityManager->persist($category);
            $entityManager->flush();

           // return $this->redirectToRoute('blog_index');
        }

        return $this->render('blog/new.html.twig',[
           'form' => $form->createView(),
        ]);
    }

    /**
     * 記事の詳細を表示
     *
     * ParamConverterによってURLのidと一致するpostを返す。
     *
     * @Route("/{id}", name="post_show", methods={"GET"})
     * @param Post $post
     * @return Response
     */
    public function show(Post $post): Response
    {
        return $this->render('blog/show.html.twig', [
            'post' => $post,
        ]);
    }

    /**
     * 既存の記事を編集するメソッド
     *
     * 将来getDoctrine()関数のサポートが切れる可能性があるので、entityがインジェクションされたEntryManagerInterfaceを用いる。
     * handleRequestで、postにフォームのrequestの内容を反映させる。
     *
     * @Route("/edit/{id}", name="post_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Post $post
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('blog_index');
        }

        return $this->render('blog/edit.html.twig',[
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    /**
     * 投稿した記事を消すメソッド
     *
     * 記事消去を実行する前に、クロスサイトリクエストフォージェリ(csrf)対策を行う。
     *
     *
     * @Route("/edit/{id}", name="post_delete",methods={"DELETE"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param Post $post
     * @return Response
     */
    public function delete(Request $request, EntityManagerInterface $entityManager, Post $post) : Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('blog_index');
    }

}
