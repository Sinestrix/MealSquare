<?php

namespace MealSquare\RecetteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use MealSquare\RecetteBundle\Entity\Like\Like;
use MealSquare\RecetteBundle\Entity\Note\Rate;
use MealSquare\RecetteBundle\Entity\GroupVersions;

class AsynchroneController extends Controller
{
    public function ingredientAction()
    {
        $request = $this->get('request');
 
        $term = $request->request->get('libelle');
        $array= $this->getDoctrine()
            ->getManager()
            ->getRepository('MealSquareRecetteBundle:Ingredient')
            ->findIngredientsLike($term);

        $response = new Response(json_encode($array));
        $response -> headers -> set('Content-Type', 'application/json');
        
        return $response;
    }
    
    public function likeAction()
    {
        
        $request    = $this->get('request');
        $em         = $this->getDoctrine()->getManager();
        $thread     = $request->request->get('id');
        $user       = $this->get('security.context')->getToken()->getUser();
        $like       = $em->getRepository('MealSquareRecetteBundle:Like\Like')->findOneBy(array(
                        'thread' => $thread,
                        'liker'  => $user
                    ));
        
        if(is_null($like)){

            $likeThread = $em->getRepository('MealSquareRecetteBundle:Like\LikeThread')->findOneById($thread);

            $recette = $em->getRepository("MealSquareRecetteBundle:Recette")->findOneByLike($likeThread);
            $auteurID = $recette->getAuteur();
            $auteur = $em->getRepository("ApplicationSonataUserBundle:User")->findOneById($auteurID);

            $like = new Like($likeThread,$user);
            $em->persist($like);
            $em->flush($like);

            if($likeThread->getNumLikes() > 4) {
                $badge = $auteur->getBadges();
                $badgeMeilleureRecette = $em->getRepository("ApplicationSonataUserBundle:Badge")->findOneByNom("Meilleure recette");
                if(!$badge->contains($badgeMeilleureRecette)){
                    $auteur->addBadge($badgeMeilleureRecette);

                    $em->persist($auteur);
                    $em->flush();
                } 
            }
        }
        else{
            $em->remove($like);
            $em->flush($like);
        }
        
        $thread = $like->getThread();
        $em->flush($thread);
        
        $response = new Response();
        $response->setContent(json_encode(array("success"=>true,"likes"=>$thread->getNumLikes())));
        $response -> headers -> set('Content-Type', 'application/json');
        
        return $response;
    }
    
    
    public function rateAction()
    {
        
        $request    = $this->get('request');
        $em         = $this->getDoctrine()->getManager();
        $thread     = $request->request->get('id');
        $note       = $request->request->get('note');
        $user       = $this->get('security.context')->getToken()->getUser();
        $rate       = $em->getRepository('MealSquareRecetteBundle:Note\Rate')->findOneBy(array(
                        'thread' => $thread,
                        'rater'  => $user
                    ));
        
        
        if(is_null($rate)){
            $rate = new Rate($em->getRepository('MealSquareRecetteBundle:Note\RateThread')->findOneById($thread), $user, $note);
            $em->persist($rate);
            $em->flush($rate);
        }
        

        
        $thread = $rate->getThread();
        $em->flush($thread);
        
        $response = new Response();
        $response->setContent(json_encode(array("success"=>true,"average"=>$thread->getAverage())));
        $response -> headers -> set('Content-Type', 'application/json');
        
        return $response;
    }
    
    public function favorisAction()
    {
        
        $request    = $this->get('request');
        $em         = $this->getDoctrine()->getManager();
        $id         = $request->request->get('id');

        $repository = $em->getRepository("MealSquareRecetteBundle:Recette");
        $recette = $repository->findOneById($id);
        
        if(is_null($recette)){
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Cette recette n'existe pas");
        }else{
            $user= $this->get('security.context')->getToken()->getUser();

            if($user->getRecettesFavoris()->contains($recette)){
                $user->removeRecettesFavori($recette);
            }else{
                $user->addRecettesFavori($recette);
            }

            $em->persist($user);
            $em->flush();
            
            $response = new Response();
            $response->setContent(json_encode(array("success"=>true)));
            $response -> headers -> set('Content-Type', 'application/json');
            return $response;

            
        }
    }

    public function recetteMereAction()
    {
        
        $request    = $this->get('request');
        $em         = $this->getDoctrine()->getManager();
        $id         = $request->request->get('id');

        $repository = $em->getRepository("MealSquareRecetteBundle:Recette");

        $recette = $repository->findOneById($id);

        if ( is_null($recette) ) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Cette recette n'existe pas");
        } else {
            $groupVersionsRepository = $em->getRepository("MealSquareRecetteBundle:GroupVersions");

            $versions = $recette->getVersions();
            
            $groupVersions = $groupVersionsRepository->findOneById($versions[0]->getId());

            if (!is_null($groupVersions->getRecetteMere())) {
                $groupVersions->getRecetteMere()->setVisibilite(false);
            }            

            if ( is_null($groupVersions->getRecetteMere()) || $groupVersions->getRecetteMere()->getId() != $id) {
                $groupVersions->setRecetteMere($recette);
            } else {
                $recette->setVisibilite(false);
                $groupVersions->setRecetteMere(); 
            }

            $em->persist($groupVersions);
            $em->flush();

            $response = new Response();
            $response->setContent(json_encode(array("success"=>true)));
            $response -> headers -> set('Content-Type', 'application/json');
            return $response;
        }
    }
}
