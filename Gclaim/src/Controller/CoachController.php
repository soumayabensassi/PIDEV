<?php

namespace App\Controller;

use App\Entity\Coach;
use App\Entity\SimpleUtilisateur;
use App\Entity\Tournoi;
use App\Form\CoachsType;
use App\Form\CoachType;
use App\Form\SimpleUtilisateurType;
use App\Repository\CoachRepository;
use App\Repository\SimpleUtilisateurRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CoachController extends AbstractController
{
    /**
     * @Route("/coach", name="coach")
     */
    public function index(): Response
    { $user = $this->getUser();
        return $this->render('coach/index.html.twig', [
            'controller_name' => 'CoachController','user' => $user
        ]);
    }
    /**
     * @Route("/affichecoach", name="affichecoach"  , methods={"GET"})
     */
    public function readcoach(CoachRepository $repository,Request $request)
    { if (null != $request->get('searchname') && null != $request->get('searchspecialite')) {
        $evenement = $this->getDoctrine()->getRepository(Coach::class)->findBy(['username' => $request->get('searchname'), 'specialite' => $request->get('searchspecialite')]);

        return $this->render('/coach/list.html.twig', [
            'l' => $evenement,'user'=>$this->getUser()
        ]);
    }
        if (null != $request->get('searchspecialite')) {
        $evenement = $this->getDoctrine()->getRepository(Coach::class)->findBy(['specialite' => $request->get('searchspecialite')]);

        return $this->render('/coach/list.html.twig', [
            'l' => $evenement,'user'=>$this->getUser()
        ]);
    }
        if (null != $request->get('searchname')) {
            $evenement = $this->getDoctrine()->getRepository(Coach::class)->findBy(['username' => $request->get('searchname')]);

            return $this->render('/coach/list.html.twig', [
                'l' => $evenement,'user'=>$this->getUser()
            ]);
        }
        $evenement = $repository->findAll();

        return $this->render('/coach/list.html.twig', [
            'l' => $evenement,'user'=>$this->getUser()
        ]);
    }
    /**
     * @Route("/deletecoach/{id}", name="deletecoach")
     */
    public function deletecoach($id,CoachRepository $repository)
    {
        $coach=$repository->find($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($coach);
        $em->flush();
        return $this->redirectToRoute("affichecoach");
    }
    /**
     * @Route("/addcoach", name="addcoach")
     */

    public function addcoach(Request $request)
    {
        $coach = new Coach();
        $form = $this->createForm(CoachType::class, $coach);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form["verifpassword"]->getData() != $form["password"]->getData()) {
                return $this->render('coach/add.html.twig', [
                    "form" => $form->createView(),
                    "error" => "Les mots de passe ne correspondent pas!",
                    "user"=>$coach
                ]);
            }else{
            $coach->setRoles(['ROLE_COACH']);
            $coach->setIsVerified(1);
            $em = $this->getDoctrine()->getManager();
            $em->persist($coach);
            $em->flush();
            return $this->redirectToRoute('affichecoach');
        }
        }
        return $this->render("coach/add.html.twig",['form'=>$form->createView(),"user"=>$coach,'error'=>'']);
    }
    /**
     * @Route("/updatecoach/{id}", name="updatecoach")
     */

    public function updatecoach($id,Request $request,CoachRepository $repository)
    {
        $coach=$repository->find($id);
        $form = $this->createForm(CoachType::class, $coach);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form["verifpassword"]->getData() != $form["password"]->getData()) {
                return $this->render('coach/add.html.twig', [
                    "form" => $form->createView(),
                    "error" => "Les mots de passe ne correspondent pas!",
                    "user"=>$coach
                ]);
            }else{
            $em = $this->getDoctrine()->getManager();
            $em->persist($coach);
            $em->flush();
            return $this->redirectToRoute('affichecoach');
        }}
        return $this->render("coach/add.html.twig",['form'=>$form->createView(),'error'=>'']);
    }
    /**
     * @Route("/updatecoach1/{id}", name="updatecoach1")
     */

    public function updatecoach1($id,Request $request,CoachRepository $repository)
    {        $tournois=$this->getDoctrine()->getRepository(Tournoi::class)->findAll();

        $coach=$repository->find($id);
        $form = $this->createForm(CoachType::class, $coach);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form["verifpassword"]->getData() != $form["password"]->getData()) {
                return $this->render('coach/modifcoach.html.twig', [
                    "form" => $form->createView(),
                    "error" => "Les mots de passe ne correspondent pas!",
                    "user"=>$coach
                ]);
            }else{
                $em = $this->getDoctrine()->getManager();
                $em->persist($coach);
                $em->flush();
                return $this->redirectToRoute('profile');
            }}
        return $this->render("coach/modifcoach.html.twig",['form'=>$form->createView(),'error'=>'',"user"=>$coach,"tournois" => $tournois,]);
    }
    /**
     * @Route("/modifspecialite/{id}", name="modifspecialite")
     */
    public function modifspecialite($id,Request $request,CoachRepository $repository)
    {        $tournois=$this->getDoctrine()->getRepository(Tournoi::class)->findAll();

        $coach=$repository->find($id);
        $form = $this->createForm(CoachsType::class, $coach);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

                $em = $this->getDoctrine()->getManager();
                $em->persist($coach);
                $em->flush();
                return $this->redirectToRoute('profile');
            }
        return $this->render("simpleutilisateur/modifspecialite.html.twig",['form'=>$form->createView(),'error'=>'',"user"=>$coach,"tournois" => $tournois,]);
    }
    /**
     * @Route("/deletesimpleutilisateur1/{id}", name="deletesimpleutilisateur1")
     */
    public function deletesimpleutilisateur1($id,SimpleUtilisateurRepository $repository)
    {
        $simpleutilisateur=$repository->find($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($simpleutilisateur);
        $em->flush();
        return $this->redirectToRoute("affichesimpleutilisateur");
    }

    /**
     * @Route("/coach/tri", name="triii")
     */
    public function Tri(Request $request)
    {
        $em = $this->getDoctrine()->getManager();


        $query = $em->createQuery(
            'SELECT a FROM App\Entity\Coach a 
            ORDER BY a.username'
        );

        $activites = $query->getResult();
        return $this->render('coach/list.html.twig',
            array('l' => $activites,'user'=>$this->getUser()));

    }
}
