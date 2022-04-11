<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Recipe;
use App\Form\CommentType;
use App\Form\RecipeType;
use App\Repository\CommentRepository;
use App\Repository\RecipeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/')]
class RecipeController extends AbstractController
{

    public function __construct(
        private RecipeRepository $recipeRepository,
        private CommentRepository $commentRepository
    ) {
    }

    #[Route('', name: 'app_recipe_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('recipe/index.html.twig', [
            'recipes' => $this->recipeRepository->findAll(),
        ]);
    }

    #[Route('/action/new', name: 'app_recipe_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $recipe->setCreator($this->getUser());
            $this->recipeRepository->add($recipe);
            return $this->redirectToRoute('app_recipe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('recipe/new.html.twig', [
            'recipe' => $recipe,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_recipe_show', methods: ['GET', 'POST'])]
    public function show(Request $request, Recipe $recipe): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment
                ->setRecipe($recipe)
                ->setCreator($this->getUser());
            $this->commentRepository->add($comment);
            // return $this->redirectToRoute('app_comment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('recipe/show.html.twig', [
            'comment' => $comment,
            'form' => $form->createView(),
            'recipe' => $recipe
        ]);
    }

    #[Route('/action/{id}/edit', name: 'app_recipe_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Recipe $recipe): Response
    {
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->recipeRepository->add($recipe);
            return $this->redirectToRoute('app_recipe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('recipe/edit.html.twig', [
            'recipe' => $recipe,
            'form' => $form,
        ]);
    }

    #[Route('/action/{id}', name: 'app_recipe_delete', methods: ['POST'])]
    public function delete(Request $request, Recipe $recipe): Response
    {
        if ($this->isCsrfTokenValid('delete' . $recipe->getId(), $request->request->get('_token'))) {
            $this->recipeRepository->remove($recipe);
        }

        return $this->redirectToRoute('app_recipe_index', [], Response::HTTP_SEE_OTHER);
    }
}
